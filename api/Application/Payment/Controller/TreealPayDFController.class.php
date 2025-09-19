<?php

namespace Payment\Controller;

class TreealPayDFController extends PaymentController
{
    private $code = '';

    public function __construct()
    {
        $matches = [];
        preg_match('/([\da-zA-Z\_]+)Controller$/', __CLASS__, $matches);
        $this->code = $matches[1];
    }

    //代付提交
    public function PaymentExec($data, $config)
    {
        $type_arr = ['EMAIL','CPF','CNPJ','PHONE'];
        if(!in_array($data['type'],$type_arr)){
            $return = ['status' => 0, 'msg' => '支付类型错误'];
            return $return;
        }
        $post_data = array(
            'priority' => 'HIGH',           //立即处理
            'description' => 'remark',      //描述
            'paymentFlow' => 'INSTANT',     //INSTANT- 付款将立即发生，PPROVAL_REQUIRED- 仅当订单获得批准后才会付款。
            'expiration' => 600,            //等待处理的最长时间（秒）
            'creditorAccount' => [          //债权人账户
                'ispb' => 'string',
                'issuer' => 'string',
                'number' => $data['banknumber'],
                'accountType' => $data['type'],
                'name' => $data['bankfullname'],
            ],
            'payment' => [
                'currency' => 'BRL',
                'amount' => sprintf("%.2f", $data['money'])
            ],
//            'ispbDeny' => [       //不允许付款的 ISPB（巴西付款系统标识符）代码列表。
//                'string'
//            ]
        );

        log_place_order($this->code, $data['orderid'] . "----提交", json_encode($post_data, JSON_UNESCAPED_UNICODE));    //日志
        log_place_order($this->code, $data['orderid'] . "----提交地址", $config['exec_gateway']);    //日志

        $authorization = $this->getOAuth($config);
        $header = [
            'accept: application/json',
            'authorization: '.$authorization['token_type'] . ' ' . $authorization['access_token'],
            'content-type: application/json',
        ];

        // 记录初始执行时间
        $beginTime = microtime(TRUE);

        $result = $this->request($config['exec_gateway'], $post_data, $header);
        // if($data['userid'] == 2){
        try{

            $redis = $this->redis_connect();
            $userdfpost = $redis->get('userdfpost_' . $data['out_trade_no']);
            $userdfpost = json_decode($userdfpost,true);

            logApiAddPayment('下游商户提交YunPay', __METHOD__, $data['orderid'], $data['out_trade_no'], '/', $userdfpost, [], '0', '0', '1', '2');

            // 结束并输出执行时间
            $endTime = microtime(TRUE);
            $doTime = floor(($endTime-$beginTime)*1000);
            logApiAddPayment('YunPay订单提交上游WinPay', __METHOD__, $data['orderid'], $data['out_trade_no'], $config['exec_gateway'], $post_data, $result, $doTime, '0', '1', '2');
        }catch (\Exception $e) {
            // var_dump($e);
        }
        // }
        log_place_order($this->code, $data['orderid'] . "----返回", json_encode($result, JSON_UNESCAPED_UNICODE));    //日志

        // log_place_order($this->code, $data['orderid'] . "----状态：", $result['status']);    //日志
        if($result['code'] === '000000'){
            //保存第三方订单号
            $orderid = $data['orderid'];
            $Wttklistmodel = D('Wttklist');
            $date = date('Ymd',strtotime(substr($orderid, 1, 8)));  //获取订单日期
            $tableName = $Wttklistmodel->getRealTableName($date);
            $re_save = $Wttklistmodel->table($tableName)->where(['orderid' => $orderid])->save(['three_orderid'=>$result['order']]);

            switch ($result['ordStatus']) {      //订单状态 01:待结算06:清算中07:清算完成08:清算失败09:清算撤销
                case '01':
                case '06':
                    $return = ['status' => 1, 'msg' => '申请正常'];
                    break;
                case '07':
                    $return = ['status' => 2, 'msg' => '代付成功'];
                    break;
                case '08':
                case '09':
                    $return = ['status' => 3, 'msg' => '申请失败'];
                    break;
            }
        }elseif($result['code'] === '900003' || $result['code'] === '999999' || $result['code'] === '000218'){
            $return = ['status' => 3, 'msg' => $result['msg']];
        }else{
            $return = ['status' => 0, 'msg' => $result['msg']];
        }
        return $return;
    }

    public function notifyurl()
    {
        $result = json_decode(file_get_contents('php://input'), true);
        $re_data = $result['data'];
        $orderid = $re_data['txId'];
        //self::log_place_orderNotify($this->code . '_notifyserver', $orderid . "----异步回调报文头", json_encode($_SERVER));    //日志
        self::log_place_orderNotify($this->code . '_notifyurl', $orderid . "----异步回调", file_get_contents('php://input'));    //日志

        $tableName ='';
        $Wttklistmodel = D('Wttklist');
        $date = date('Ymd',strtotime(substr($re_data['createdAt'], 0, 10)));  //获取订单日期
        $tableName = $Wttklistmodel->getRealTableName($date);
        $Order = $Wttklistmodel->table($tableName)->where(['three_orderid' => $orderid])->find();

        if (!$Order) {
            self::log_place_orderNotify($this->code . '_notifyurl', $orderid . '----没有查询到Order！ ', $orderid);
            exit;
        }

//        $config = M('pay_for_another')->where(['code' => $this->code,'id'=>$Order['df_id']])->find();
//        //验证IP白名单
//        if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR']) {
//            $ip = $_SERVER['REMOTE_ADDR'];
//        } else {
//            $ip = getRealIp();
//        }
//        $check_re = checkNotifyurlIp($ip, $config['notifyip']);
//        if ($check_re !== true) {
//            self::log_place_orderNotify($this->code . '_notifyurl', $orderid . "----IP异常", $ip.'==='.$config['notifyip']);    //日志
//            $json_result = "IP异常:" . $ip.'==='.$config['notifyip'];
//            try{
//                self::logApiAddNotify($orderid, 1, $re_data, $json_result);
//            }catch (\Exception $e) {
//                // var_dump($e);
//            }
//            return;
//        }

        if ($_SERVER['HTTP_SIGN'] == "LTDA6013CURRAIS_NOVOS62070503") {
            if ($re_data['status'] === "LIQUIDATED") {       //成功LIQUIDATED，失败CANCELED
                //代付成功 更改代付状态 完善代付逻辑
                $data = [
                    'memo' => '代付成功',
                ];
                $this->changeStatus($Order['id'], 2, $data, $tableName);
                self::log_place_orderNotify($this->code . '_notifyurl', $orderid, "----代付成功");    //日志
                $json_result = "success";
            } elseif ($re_data['status'] === "CANCELED") {
                //代付失败
                $data = [
                    'memo' => '代付失败',
                ];
                $this->changeStatus($Order['id'], 3, $data, $tableName);
                self::log_place_orderNotify($this->code . '_notifyurl', $orderid, "----代付失败");    //日志
                $json_result = "success";
            }
        } else {
            self::log_place_orderNotify($this->code . '_notifyurl', $orderid . '----签名错误: ', $_SERVER['HTTP_SIGN']);
            $json_result = "sign fail";
        }
        echo $json_result;
        try{
            self::logApiAddNotify($orderid, 1, $re_data, $json_result);
        }catch (\Exception $e) {
            // var_dump($e);
        }
    }

    //账户余额查询
    public function queryBalance()
    {
        if (IS_AJAX) {
            $id = I('post.id', 1);
            $config = M('pay_for_another')->where(['id' => $id])->find();

            $authorization = $this->getOAuth($config);
            $header = [
                'accept: application/json',
                'authorization: '.$authorization['token_type'] . ' ' . $authorization['access_token'],
            ];
            $result = $this->http_get_json($config['serverreturn'], $header);
            log_place_order($this->code . '_queryBalance', "返回", json_encode($result, JSON_UNESCAPED_UNICODE));    //日志
            $available = $result['data']['balanceAmount']['available'];  //可用金额
            $blocked = $result['data']['balanceAmount']['blocked'];  //冻结金额
            $overdraft = $result['data']['balanceAmount']['overdraft'];  //冻结金额
            $html = <<<AAA
<!-- CSS goes in the document HEAD or added to your external stylesheet -->
<style type="text/css">
table.hovertable {width: 200px;font-family: verdana,arial,sans-serif;font-size:11px;color:#333333;border-width: 1px;border-color: #999999;border-collapse: collapse;}
table.hovertable th {background-color:#c3dde0;border-width: 1px;padding: 8px;border-style: solid;border-color: #a9c6c9;}
table.hovertable tr {background-color:#f5f5f5;}
table.hovertable td {border-width: 1px;padding: 8px;border-style: solid;border-color: #a9c6c9;}
</style>
<table class="hovertable">
<tr><th>说明</th><th>值</th></tr>
<tr onmouseout="this.style.backgroundColor='#f5f5f5';" onmouseover="this.style.backgroundColor='#009688';"><td>可用金额</td><td><b>$available </b></td></tr>
<tr onmouseout="this.style.backgroundColor='#f5f5f5';" onmouseover="this.style.backgroundColor='#009688';"><td>冻结金额</td><td><b>$blocked </b></td></tr>
<tr onmouseout="this.style.backgroundColor='#f5f5f5';" onmouseover="this.style.backgroundColor='#009688';"><td>透支金额</td><td><b>$overdraft </b></td></tr>
</table>
AAA;
            $this->ajaxReturn(['status' => 1, 'msg' => '成功', 'data' => $html]);
        }
    }

    //账户余额查询
    public function queryBalance2($config)
    {
        $authorization = $this->getOAuth($config);
        $header = [
            'accept: application/json',
            'authorization: '.$authorization['token_type'] . ' ' . $authorization['access_token'],
        ];
        $result = $this->http_get_json($config['serverreturn'], $header);
        log_place_order($this->code . '_queryBalance2', "返回", json_encode($result, JSON_UNESCAPED_UNICODE));    //日志
        // if($result['code']==="0"){
        $result_data['resultCode'] = "0";
        $result_data['balance'] = $result['data']['balanceAmount']['available'];
        // }
        return $result_data;
    }

    //代付订单查询
    public function PaymentQuery($data, $config)
    {
        $authorization = $this->getOAuth($config);
        $header = [
            'accept: application/json',
            'authorization: '.$authorization['token_type'] . ' ' . $authorization['access_token'],
        ];
        $url = $config['query_gateway'] .  $data['three_orderid'] . '/details';
        $result = $this->http_get_json($url, $header);

        log_place_order($this->code . '_PaymentQuery', $data['orderid'] . "----返回", json_encode($result, JSON_UNESCAPED_UNICODE));    //日志

        switch ($result['status']) {       //CANCELED, PROCESSING, LIQUIDATED, REFUNDED, PARTIALLY_REFUNDED,
            case 'PROCESSING':
                $return = ['status' => 1, 'msg' => '处理中'];
                break;
            case 'LIQUIDATED':
                $return = ['status' => 2, 'msg' => '成功'];
                break;
            case 'CANCELED ':
                $return = ['status' => 3, 'msg' => '失败'];
                break;
        }
        return $return;
    }

    public function PaymentVoucher($data, $config){
        if(isset($data['three_orderid'])){
            $authorization = $this->getOAuth($config);
            $header = [
                'accept: application/json',
                'authorization: '.$authorization['token_type'] . ' ' . $authorization['access_token'],
            ];
            $url = $config['query_gateway'] .  $data['three_orderid'] . '/details';
            $result = $this->http_get_json($url, $header);
            log_place_order($this->code . '_PaymentVoucher', $data['three_orderid'] . "----返回",  json_encode($result, JSON_UNESCAPED_UNICODE));    //日志
            if(!empty($result)){
                return  $result;
            }else{
                return false;
            }
        }else{
            return false;
        }

    }

    public function getOAuth($client){
        $redis = $this->redis_connect();
        $authorization_redis = $redis->get('getOAuth');
        $authorization = json_decode($authorization_redis,true);
        if(!$authorization_redis || empty($authorization)) {
            $url = 'https://api.pix.treeal.com/oauth/token';
            $header = [
                'accept: application/json',
                'content-type: application/x-www-form-urlencoded'
            ];
            $params = [
                'client_id'=> $client['mch_id'],
                'client_secret' => $client['signkey'],
                'grant_type' => 'client_credentials',
            ];
            log_place_order($this->code, "OAuth----body", json_encode($params, JSON_UNESCAPED_UNICODE));    //日志
//        log_place_order($this->code, "OAuth----url", $url);    //日志
            $authorization = $this->http_post_json($url, $params, $header);
//        log_place_order($this->code, "OAuth----return", json_encode($ans, JSON_UNESCAPED_UNICODE));    //日志
            $redis->set('getOAuth', json_encode($authorization, JSON_UNESCAPED_UNICODE));
            $redis->expire('getOAuth' , 60);
        };

        return $authorization;
    }

    //发送post请求
    private function http_get_json($url, $options = array())
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 10,  // 增加超时时间
            CURLOPT_FOLLOWLOCATION => true,

            // 关键：客户端证书配置
            CURLOPT_SSLCERT => '/www/wwwroot/r97/api/cert/Treeal/in/TREEAL_23.crt',
            CURLOPT_SSLKEY => '/www/wwwroot/r97/api/cert/Treeal/in/TREEAL_23.key',

            // SSL验证设置
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,

            // HTTP设置
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => $options,

            // 推荐添加的选项
            CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        ]);

        $response = curl_exec($curl);
        $result = [];

        if ($response === false) {
            $result['code'] = curl_errno($curl);
            $result['message'] = curl_error($curl);
            $result['curl_info'] = curl_getinfo($curl);
        } else {
            $result = json_decode($response, true);
        }

        curl_close($curl);
        return $result;
    }

    //发送post请求
    private function http_post_json($url, $postData, $options = array())
    {
        if (is_array($postData)) {
            $postData = http_build_query($postData);
        }
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 10,  // 增加超时时间
            CURLOPT_FOLLOWLOCATION => true,

            // 关键：客户端证书配置
            CURLOPT_SSLCERT => '/www/wwwroot/r97/api/cert/Treeal/in/TREEAL_23.crt',
            CURLOPT_SSLKEY => '/www/wwwroot/r97/api/cert/Treeal/in/TREEAL_23.key',

            // SSL验证设置
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,

            // HTTP设置
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_HTTPHEADER => $options,

            // 推荐添加的选项
            CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        ]);

        $response = curl_exec($curl);
        $result = [];

        if ($response === false) {
            $result['code'] = curl_errno($curl);
            $result['message'] = curl_error($curl);
            $result['curl_info'] = curl_getinfo($curl);
        } else {
            $result = json_decode($response, true);
        }

        curl_close($curl);
        return $result;
    }

    /**
     * 执行请求，http header验证
     *
     * @param string $url
     * @param array $params
     * @return Ambigous <mixed, multitype:NULL >
     */
    private function request($url, $params, $header)
    {
        try {
            $json = json_encode($params, JSON_UNESCAPED_UNICODE);
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 10,  // 增加超时时间
                CURLOPT_FOLLOWLOCATION => true,

                // 关键：客户端证书配置
                CURLOPT_SSLCERT => '/www/wwwroot/r97/api/cert/Treeal/in/TREEAL_23.crt',
                CURLOPT_SSLKEY => '/www/wwwroot/r97/api/cert/Treeal/in/TREEAL_23.key',

                // SSL验证设置
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0,

                // HTTP设置
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $json,
                CURLOPT_HTTPHEADER => $header,

                // 推荐添加的选项
                CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            ]);

            $response = curl_exec($curl);
            $result = [];

            if ($response === false) {
                $result['code'] = curl_errno($curl);
                $result['message'] = curl_error($curl);
                $result['curl_info'] = curl_getinfo($curl);
            } else {
                $result = json_decode($response, true);
            }

            curl_close($curl);
            return $result;
        } catch (\Exception $e) {
            log_place_order($this->code. '_request', $params["reference"] . "----body错误", $e->getMessage());    //日志
        }
    }

    /**
     *记录日志
     */
    function log_place_orderNotify($file, $notify, $notifystr)
    {
        $filePath = '/www/wwwroot/r97/api/Data/' . date('Ymd') . '/';
        if (@mkdirs($filePath)) {
            $destination = $filePath . $file . '_' . date('H') . '.log';
            if (!file_exists($destination)) {
                @fopen($destination, 'wb ');
            }
            @file_put_contents($destination, "【" . date('Y-m-d H:i:s') . "】\r\n" . $notify . "：" . $notifystr . "\r\n\r\n", FILE_APPEND);
            return true;
        }
        return false;
    }

    function logApiAddNotify($orderid, $type, $oper_param=[], $json_result=[]){
        $log = [
            'memberid' => 2,   //用户id
            'order_id' => $orderid,   //订单号
            'out_trade_id' => $orderid,  //下游订单号
            'oper_param' => $oper_param,      //请求参数
            'json_result' => $json_result,    //返回参数
            'type' => $type,      //业务类型（0入款 1出款）
            'create_time' => date('Y-m-d H:i:s'),       //创建时间
        ];
        $url = 'http://r97pay.com/Log_Api_addNotifyLog.html';
        self::log_place_order('logApiNotify', $orderid . "----提交地址", $url);    //日志
        self::log_place_order('logApiNotify', $orderid . "----提交", json_encode($log, JSON_UNESCAPED_UNICODE));    //日志
        $res = http_post_json($url, $log);
        self::log_place_order('logApiNotify', $orderid . "----返回", json_encode($res, JSON_UNESCAPED_UNICODE));    //日志

        if($res && $res['status'] === 'success'){
            return true;
        }else{
            return false;
        }
    }
}

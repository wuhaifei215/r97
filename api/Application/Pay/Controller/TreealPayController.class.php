<?php

namespace Pay\Controller;

class TreealPayController extends PayController
{
    private $code = '';

    public function __construct()
    {
        parent::__construct();
        $matches = [];
        preg_match('/([\da-zA-Z\_]+)Controller$/', __CLASS__, $matches);
        $this->code = $matches[1];
    }

    //支付
    public function Pay($array)
    {
        $orderid = I("request.pay_orderid", '');
        $body = I('request.pay_productname', '');
        $pay_callbackurl = I('request.pay_callbackurl', '');
        $parameter = [
            'code' => $this->code,
            'title' => 'Treeal',
            'exchange' => 1, // 金额比例
            'gateway' => "",
            'orderid' => '',
            'out_trade_id' => $orderid, //外部订单号
            'channel' => $array,
            'body' => $body,
        ];
        // 订单号，可以为空，如果为空，由系统统一的生成
        $return = $this->orderadd($parameter);
        //如果生成错误，自动跳转错误页面
        $return["status"] == "error" && $this->showmessage($return["errorcontent"]);
        //跳转页面，优先取数据库中的跳转页面
        $_site = ((is_https()) ? 'https' : 'http') . '://' . C("DOMAIN") . '/';
        $site = trim($return['unlockdomain']) ? $return['unlockdomain'] . '/' : $_site;

        /*********************************引入支付方类*********************************/
        $authorization = $this->getOAuth($return);
        $header = [
            'Authorization: '.$authorization['token_type'] . ' ' . $authorization['access_token'],
            'Content-Type: application/json'
        ];

        $params = [
            "calendario" => [
                "expiracao" => 3600,
            ],
            "valor" => [
                "original" => sprintf("%.2f", $return['amount']),     //立即付款金额。必须大于零
                "modalidadeAlteracao" => 0      //应用的模式将被假定为 0，这意味着收费金额无法更改。如果值为 1，则收费金额可以修改
            ],
            "chave" => $return['appid'],
        ];

        log_place_order($this->code, $return['orderid'] . "----header", json_encode($header, JSON_UNESCAPED_UNICODE));    //日志
        log_place_order($this->code, $return['orderid'] . "----body", json_encode($params, JSON_UNESCAPED_UNICODE));    //日志
        log_place_order($this->code, $return['orderid'] . "----地址", $return['gateway']);    //日志

        // 记录初始执行时间
        $beginTime = microtime(TRUE);

        $ans = $this->request($return['gateway'], $params, $header);
        log_place_order($this->code, $return['orderid'] . "----return", json_encode($ans, JSON_UNESCAPED_UNICODE));    //日志

        if($ans['status'] ==='ATIVA'){
            $payurl = $site . 'PayPage.html?sid=' . $return['orderid'] . '&amount=' . $return['amount']. '&qrcode=' .$ans['pixCopiaECola'];
            $return_arr = [
                'status' => 'success',
                'H5_url' => $payurl,
                'QRcode' => $ans['pixCopiaECola'],
                'pay_orderid' => $orderid,
                'out_trade_id' => $return['orderid'],
                'amount' => $return['amount'],
                'datetime' => date('Y-m-d')
            ];
        }else{
            $return_arr = [
                'status' => 'error',
                'msg' => $ans['detail']?$ans['detail']:'fail',
            ];
        }
        echo json_encode($return_arr);

        // if($array['userid'] == 2){
        try{
            $redis = $this->redis_connect();
            $userpost = $redis->get('userpost_' . $return['out_trade_id']);
            $userpost = json_decode($userpost,true);

            logApiAddReceipt('下游商户body', __METHOD__, $return['orderid'], $return['out_trade_id'], '/', $userpost, $return_arr, '0', '0', '1', '2');

            // 结束并输出执行时间
            $endTime = microtime(TRUE);
            $doTime = floor(($endTime-$beginTime)*1000);
            logApiAddReceipt('订单body上游' . $this->code, __METHOD__, $return['orderid'], $return['out_trade_id'], $return['gateway'], $params, $ans, $doTime, '0', '1', '2');
        }catch (\Exception $e) {
            // var_dump($e);
        }
        // }
        exit;
    }

    public function getOAuth($client){
        $url = 'https://api.pix.treeal.com/oauth/token';
        $header = [
            'accept: application/json',
            'content-type: application/x-www-form-urlencoded'
        ];
        $params = [
            'clientId'=> $client['mch_id'],
            'clientSecret' => $client['signkey'],
            'grantType' => 'client_credentials',
        ];
        log_place_order($this->code, "OAuth----body", json_encode($params, JSON_UNESCAPED_UNICODE));    //日志
        log_place_order($this->code, "OAuth----url", $url);    //日志
        $ans = $this->http_post_json($url, $params, $header);
        log_place_order($this->code, "OAuth----return", json_encode($ans, JSON_UNESCAPED_UNICODE));    //日志
        return $ans;
    }

    //异步通知
    public function notifyurl()
    {
        // log_place_order($this->code . '_notifyurl', $orderid . "----异步回调", file_get_contents('php://input'));    //日志
        //获取报文信息
        $result = json_decode(file_get_contents('php://input'), true);
        $arrayData = json_decode($result['data'], true);
        $orderid = $arrayData['reference'];
        //log_place_order($this->code . '_notifyserver', $orderid . "----异步回调报文头", json_encode($_SERVER));    //日志
        log_place_order($this->code . '_notifyurl', $orderid . "----异步回调", file_get_contents('php://input'));    //日志
        if (!$orderid) return;

        //过滤数据，防SQL注入
        // $check_data = sqlInj($arrayData);
        // if ($check_data === false) return;
        $OrderModel = D('Order');
        $date = date('Ymd',strtotime(substr($orderid, 0, 8)));  //获取订单日期
        $tablename = $OrderModel->getRealTableName($date);

        $orderList = $OrderModel->table($tablename)->where(['pay_orderid' => $orderid])->find();
        if (!$orderList) return;

        //验证IP白名单
        if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR']) {
            $ip = $_SERVER['REMOTE_ADDR'];
        } else {
            $ip = getRealIp();
        }

        $check_re = check_IP($orderList['channel_id'], $ip, $orderid);
        if ($check_re !== true) {
            log_place_order($this->code . '_notifyurl', $orderid . "----IP异常", $ip);    //日志
            $json_result = "IP异常:" . $ip;
            try{
                logApiAddNotify($orderid, 1, $result, $json_result);
            }catch (\Exception $e) {
                // var_dump($e);
            }
            return;
        }

        $sign = sha1(md5($result['data'] . $result['timestamp']) . $orderList['key']);
        if ($sign == $result["sign"]) {
            if($arrayData['status'] === 'SUCCESS' || $arrayData['status'] === 'SETTLED'){     //订单状态 PENDING,SUCCESS,FAIL（进行中，成功，失败）SETTLED 结算的成功
                $re = $this->EditMoney($orderList['pay_orderid'], $this->code, 0);
                if ($re !== false) {
                    log_place_order($this->code . '_notifyurl', $orderid . "----回调上游", "成功");    //日志
                }else{
                    log_place_order($this->code . '_notifyurl', $orderid . "----回调上游", "失败");    //日志
                }
            }else{
                log_place_order($this->code . '_notifyurl', $orderid . "----订单状态异常", $arrayData['status']);    //日志
            }
            $json_result = "SUCCESS";
        } else {
            log_place_order($this->code . '_notifyurl', $orderid . "----签名错误，加密后", $sign);    //日志
        }
        echo $json_result;
        try{
            logApiAddNotify($orderid, 0, $result, $json_result);
        }catch (\Exception $e) {
            // var_dump($e);
        }
    }
    //发送post请求
    private function http_post_json($url, $postData, $options = array())
    {
        if (is_array($postData)) {
            $postData = http_build_query($postData);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); //设置cURL允许执行的最长秒数
        if (!empty($options)) {
            curl_setopt_array($ch, $options);
        }
        //https请求 不验证证书和host
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
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
}

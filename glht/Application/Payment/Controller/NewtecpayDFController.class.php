<?php

namespace Payment\Controller;

class NewtecpayDFController extends PaymentController
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
        $post_data = array(
            "merchant_order_no" => $data['orderid'], //订单号
            "account_type" => $data['type'],        // 账号类型：0-EMAIL, 1-CPF, 2-CNPJ , 3-PHONE
            "account_no" => $data['banknumber'],    //如CPF为CPF号码，CNPJ为CNPJ号码，PHONE为⼿机号码，EMAIL为邮箱地址，EVP为evp地址
            "amount" => sprintf("%.2f", $data['money']) * 100,  //提现金额（单位分）
            'description'=>'Confirmação de pagamento', // 交易描述，要求300个字符内


            "appId" => $config['appid'], //appId
            "backUrl" => 'https://' . C('NOTIFY_DOMAIN') . "/Payment_" . $this->code . "_notifyurl.html",      //异步通知地址
            'countryCode' =>'BR',
            'currencyCode' =>'BRL',
            "custId" => $config['mch_id'], //商户号
            "remark" => $data['extends']?$data['extends']:'remark',
            "email" => '123456789@gmail.com',
            "cpf" => '12345678901',
            "phone" => '12345678901',
            "account_type" => 'PIX',
            "userName" => $data['bankfullname'],  //户名
        );
        $post_data["sign"] = $this->get_sign($post_data, $config['signkey']);
        log_place_order($this->code, $data['orderid'] . "----提交", json_encode($post_data, JSON_UNESCAPED_UNICODE));    //日志
        log_place_order($this->code, $data['orderid'] . "----提交地址", $config['exec_gateway']);    //日志
        
        // 记录初始执行时间
        $beginTime = microtime(TRUE);
        
        $returnContent = $this->http_post_json($config['exec_gateway'], $post_data);
        $result = json_decode($returnContent, true);
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
        $re_data = $_REQUEST;
        //获取报文信息
        $orderid = $re_data['merchantOrderId'];
        //log_place_order($this->code . '_notifyserver', $orderid . "----异步回调报文头", json_encode($_SERVER));    //日志
        log_place_order($this->code . '_notifyurl', $orderid . "----异步回调", json_encode($_REQUEST, JSON_UNESCAPED_UNICODE));    //日志
        
        $tableName ='';
        $Wttklistmodel = D('Wttklist');
        $date = date('Ymd',strtotime(substr($orderid, 1, 8)));  //获取订单日期
        $tableName = $Wttklistmodel->getRealTableName($date);
        $Order = $Wttklistmodel->table($tableName)->where(['orderid' => $orderid])->find();
        
        // $Order = $this->selectOrder(['orderid' => $orderid]);
        if (!$Order) {
            log_place_order($this->code . '_notifyurl', $orderid . '----没有查询到Order！ ', $orderid);
            exit;
        }
        
        $config = M('pay_for_another')->where(['code' => $this->code,'id'=>$Order['df_id']])->find();

        $sign = $this->get_sign($re_data, $config['signkey']);
        if ($sign === $re_data["sign"]) {
            if ($re_data['orderStatus'] === "07") {     //订单状态 01:待结算06:清算中07:清算完成08:清算失败09:清算撤销
                //代付成功 更改代付状态 完善代付逻辑
                $data = [
                    'memo' => '代付成功',
                ];
                $this->changeStatus($Order['id'], 2, $data, $tableName);
                // $this->handle($Order['id'], 2, $data, $tableName);
                log_place_order($this->code . '_notifyurl', $orderid, "----代付成功");    //日志
                $json_result = "000000";
            } elseif ($re_data['orderStatus'] === "08" || $re_data['orderStatus'] === "09") {
                //代付失败
                $data = [
                    'memo' => '代付失败-' . $re_data['casDesc'],
                ];
                $this->changeStatus($Order['id'], 3, $data, $tableName);
                // $this->handle($Order['id'], 3, $data, $tableName);
                log_place_order($this->code . '_notifyurl', $orderid, "----代付失败");    //日志
                $json_result = "000000";
            }
        } else {
            log_place_order($this->code . '_notifyurl', $orderid . '----签名错误: ', $sign);
            // $data = [
            //     'memo' => '签名错误',
            // ];
            
            // $this->changeStatus($Order['id'], 0, $data, $tableName);
            // $this->handle($Order['id'], 0, $data, $tableName);
            $json_result = "fail";
        }
        echo $json_result;
        try{
            logApiAddNotify($orderid, 1, $_REQUEST, $json_result);
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
            $post_data = array(
                "custId" => $config['mch_id'], //商户号
                "appId" => $config['appid'], //商户号
                "currencyCode" => 'BRL',
            );
            $post_data["sign"] = $this->get_sign($post_data, $config['signkey']);
            log_place_order($this->code . '_queryBalance', "提交", json_encode($post_data));    //日志
            $returnContent = $this->http_post_json($config['serverreturn'], $post_data);
            log_place_order($this->code . '_queryBalance', "返回", $returnContent);    //日志
            $result = json_decode($returnContent, true);
            $acBal = $result['acBal'] / 100;  //总金额
            $available = $result['available'] / 100;  //可用金额
            $frozen = $result['frozen'] / 100;  //不可用金额
            $acT0Froz = $result['acT0Froz'] / 100;  //冻结金额
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
<tr onmouseout="this.style.backgroundColor='#f5f5f5';" onmouseover="this.style.backgroundColor='#009688';"><td>总金额</td><td><b>$acBal </b></td></tr>
<tr onmouseout="this.style.backgroundColor='#f5f5f5';" onmouseover="this.style.backgroundColor='#009688';"><td>可用金额</td><td><b>$available </b></td></tr>
<tr onmouseout="this.style.backgroundColor='#f5f5f5';" onmouseover="this.style.backgroundColor='#009688';"><td>不可用金额</td><td><b>$frozen </b></td></tr>
<tr onmouseout="this.style.backgroundColor='#f5f5f5';" onmouseover="this.style.backgroundColor='#009688';"><td>冻结金额</td><td><b>$acT0Froz </b></td></tr>
</table>
AAA;
            $this->ajaxReturn(['status' => 1, 'msg' => '成功', 'data' => $html]);
        }
    }
    
        //账户余额查询
    public function queryBalance2($config)
    {
        $post_data = array(
            "custId" => $config['mch_id'], //商户号
            "appId" => $config['appid'], //商户号
            "currencyCode" => 'BRL',
        );
        $post_data["sign"] = $this->get_sign($post_data, $config['signkey']);
        log_place_order($this->code . '_queryBalance2', "提交", json_encode($post_data));    //日志
        $returnContent = $this->http_post_json($config['serverreturn'], $post_data);
        log_place_order($this->code . '_queryBalance2', "返回", $returnContent);    //日志
        $result = json_decode($returnContent, true);
        // if($result['code']==="0"){
            $result_data['resultCode'] = "0";
            $result_data['balance'] = $result['available'] / 100;
        // }
        return $result_data;
    }

    //代付订单查询
    public function PaymentQuery($data, $config)
    {
        $post_data = [
            'custId' => $config['mch_id'],
            'appId' => $config['appid'],
            'order' => $data['three_orderid'],
            'merchantOrderId' => $data['orderid'],
        ];
        $post_data["sign"] = $this->get_sign($post_data, $config['signkey']);
        log_place_order($this->code . '_PaymentQuery', $data['orderid'] . "----提交", json_encode($post_data, JSON_UNESCAPED_UNICODE));    //日志
        $returnContent = $this->http_post_json($config['query_gateway'], $post_data);
        log_place_order($this->code . '_PaymentQuery', $data['orderid'] . "----返回", $returnContent);    //日志
        $result = json_decode($returnContent, true);
        if ($result['code'] === "000000") {
            switch ($result['orderStatus']) {       //01:待结算06:清算中07:清算完成08:清算失败09:清算撤销
                case '01':
                case '06':
                    $return = ['status' => 1, 'msg' => '处理中'];
                    break;
                case '07':
                    $return = ['status' => 2, 'msg' => '成功'];
                    break;
                case '08':
                case '09':
                    $return = ['status' => 3, 'msg' => '失败','remark' => $result['remark']];
                    break;
            }
        } else {
            $return = ['status' => 7, 'msg' => "查询接口失败:".$result['code']];
        }
        return $return;
    }
    
    public function PaymentVoucher($data, $config){
        if(isset($data['three_orderid'])){
            $post_data = [
                'custId' => $config['mch_id'],
                'appId' => $config['appid'],
                // 'order' => $data['three_orderid'],
                'order' => $data['orderid'],
            ];
            $post_data["sign"] = $this->get_sign($post_data, $config['signkey']);
            log_place_order($this->code . '_PaymentVoucher', $data['orderid'] . "----提交", json_encode($post_data, JSON_UNESCAPED_UNICODE));    //日志
            $returnContent = $this->http_post_json('https://api.winpay.site/br/voucherData.json', $post_data);
            log_place_order($this->code . '_PaymentVoucher', $data['orderid'] . "----返回", $returnContent);    //日志
            $result = json_decode($returnContent, true);
        
            // $redata = json_decode(file_get_contents('https://api.winpay.site/payment/br/voucherData.webapp?casOrdNo=' . $data['three_orderid']),true);
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

    /*********************************辅助方法*********************************/

    private function http_post_json($url, $params){
        return $this->send($url, $params);
    }

    /**
     * 发送HTTP请求核心函数
     *
     * @param string $method  使用GET还是POST方式访问
     * @param int $timeout  连接对方服务器访问超时时间，单位为秒
     * @param array $options
     * @param boolean $isVerifySign 是否验签，对账单下载接口响应结果不支持对响应结果验签
     * @return mixed
     * @throws Exception
     */
    private function send($url, $params, $isVerifySign=true,$timeout=10,$method='POST'){

        //初始化CURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

        //设置特殊属性
        if (!empty($options)){
            curl_setopt_array($ch , $options);
        }

        $_json_data = json_encode($params,JSON_UNESCAPED_UNICODE);

//        echo "请求报文:".$_json_data."\n";

        //处理POST请求数据
        if ($method == 'POST'){
            curl_setopt($ch, CURLOPT_POST, 1 );
            curl_setopt($ch, CURLOPT_POSTFIELDS, $_json_data);
        }

        $defaultHeader = array(
            'lang:PHP',
            'publisher:yy',
            'sdk-version:1.0.0',
            'uname:'.php_uname(),
            'lang-version:'.PHP_VERSION
        );

        $nonce = $this->createUniqid();
        $timestamp = $this->getMicroTime();
        $SecretKey = '5642bd24f179435e934a7314fa0eb4ec';

        $header = array(
            'Content-Type:application/json; charset=UTF-8',
            'nonce:'.$nonce,
            'timestamp:'.$timestamp,
            'Authorization:'.$SecretKey,
            'X-yy-Client-User-Agent:'.json_encode($defaultHeader)
        );

        $header_array = array(
            "nonce"=>$nonce,
            "timestamp"=>$timestamp,
            "Authorization"=>$SecretKey,
        );

        if ($method == 'GET'){
            $sign = $this->to_sign_data($method);
        }else{
            $sign = $this->to_sign_data($method, $_json_data, $header_array);
        }
        $header[] ='sign:'.$sign;

        log_place_order($this->code, "----header", json_encode($header));    //日志
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        //发送请求读取输数据
        $data = curl_exec($ch);
        try{
            $body_data = null;
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $res_header = substr($data, 0, $header_size);
            $body_data = substr($data, $header_size);
            $response_code=intval(curl_getinfo($ch, CURLINFO_HTTP_CODE));
//            echo "\n响应data：".$data."\n响应code：".$response_code."\n响应body报文：".$body_data;
//            if ($response_code==200){
//                if ($isVerifySign) {
//                    $this->to_verify_data($res_header, $body_data);
//                }
//            }
        }catch (Exception $e) {
            return $e->getMessage();
        }
        finally
        {
            curl_close($ch);
        }
        return $body_data;
    }

    /**
     * 签名数据
     *
     * @param $method
     * @param $body_data
     * @return string
     */
    private function to_sign_data($method,$body_data=null,$header_array){
        $_query_string = "";
        $_to_sign_data = utf8_encode(strtolower($method))."\n".utf8_encode($_query_string)."\n"
            .utf8_encode($header_array['nonce'])."\n".utf8_encode($header_array['timestamp'])."\n".utf8_encode($header_array['Authorization'])."\n"
            .$body_data;
        return $this->sign($_to_sign_data);
    }


    /**
     * 验签
     *
     * @param $res_header
     * @param $body_data
     */
    private function to_verify_data($res_header, $body_data)
    {
        $res_header_array = explode("\r\n", $res_header);
        // 构造响应header数据
        $headList = array();
        foreach ($res_header_array as $head) {
            $value = explode(':',$head);
            $headList[$value[0]] = trim($value[1]);
        }
        $_res_nonce = $headList['nonce']==''||$headList['nonce']==null?$headList['Nonce']:$headList['nonce'];
        $_res_timestamp = $headList['timestamp']==''||$headList['timestamp']==null?$headList['Timestamp']:$headList['timestamp'];;
        $_res_secret_key = $headList['Authorization']==''||$headList['Authorization']==null?$headList['authorization']:$headList['Authorization'];;
        $_res_sign = $headList['sign']==''||$headList['sign']==null?$headList['Sign']:$headList['sign'];;

        $_to_verify_data = utf8_encode($_res_nonce)."\n".$_res_timestamp."\n".$_res_secret_key."\n".$body_data;
        echo "\n同步响应报文验签原文数据:".$_to_verify_data."\n";
        $verify_result = $this->is_verify($_to_verify_data, $_res_sign);
        echo "\n同步响应验签结果:".$verify_result."\n";
        if(empty($verify_result) || intval($verify_result)!=1){
            echo "Fail";
        }

        if (strcmp($this->privKey,$_res_secret_key)){
            echo "Secret Key Is Invalid";
        }
    }

    /**
     * 签名数据
     *
     * @param $data 待签名数据
     * @return string 签名后的数据
     */
    public function sign($data)
    {
        //转换为openssl密钥，必须是没有经过pkcs8转换的私钥
        $res = openssl_get_privatekey($this->privKey);

        //调用openssl内置签名方法，生成签名$sign
        openssl_sign($data, $sign, $res,OPENSSL_ALGO_SHA256);

        //释放资源
        openssl_free_key($res);
        //base64编码
        $sign = base64_encode($sign);
        return $sign;
    }

    /**
     * 验签数据
     *
     * @param $data 原始数据
     * @param $sign 签名数据
     * @return bool 验签结果
     */
    public function is_verify($data, $sign)  {
        //转换为openssl格式密钥
        $res = openssl_get_publickey($this->pbulicKey);
        //调用openssl内置方法验签，返回bool值
        $result = (bool)openssl_verify($data,base64_decode($sign), $res,OPENSSL_ALGO_SHA256);
        //释放资源
        openssl_free_key($res);
        //返回资源是否成功
        return $result;
    }


    /**
     * 使用 RSA 私钥加密数据
     *
     * @param string $data 要加密的数据
     * @return string 加密后的数据
     */
    public function rsaPrivateEncrypt($data) {
        //转换为openssl密钥，必须是没有经过pkcs8转换的私钥
        $res = openssl_get_privatekey($this->privKey);
        if ($res === false) {
            die('私钥获取失败');
        }
        // 加密数据
        $crypted = '';
        $result = openssl_private_encrypt($data, $crypted, $res);
        if (!$result) {
            die('加密失败');
        }
        // 释放资源
        openssl_free_key($res);
        // 返回 base64 编码的加密数据以便于传输和存储
        return base64_encode($crypted);
    }


    /**
     * 生成唯一id[32位]
     * @param string $namespace
     * @return string
     */
    public function createUniqid($namespace = ''){
        $uniqid = '';
        $uid = uniqid("", true);
        $data = $namespace;
        $data .= isset($_SERVER['REQUEST_TIME']) ? $_SERVER['REQUEST_TIME'] : "";
        $data .= isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : "";
        $data .= isset($_SERVER['LOCAL_ADDR']) ? $_SERVER['LOCAL_ADDR'] : "";
        $data .= isset($_SERVER['LOCAL_PORT']) ? $_SERVER['LOCAL_PORT'] : "";
        $data .= isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : "";
        $data .= isset($_SERVER['REMOTE_PORT']) ? $_SERVER['REMOTE_PORT'] : "";
        $hash = strtoupper(hash('ripemd128', $uid . $uniqid . md5($data)));
        $uniqid = substr($hash,  0,  8) .
            substr($hash,  8,  4) .
            substr($hash, 12,  4) .
            substr($hash, 16,  4) .
            substr($hash, 20, 12);
        return $uniqid;
    }
    /**
     * 获取当前时间的毫秒数
     *
     * @return float
     */
    public function getMicroTime(){
        list($t1, $t2) = explode(' ', microtime());
        return (float)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
    }
}

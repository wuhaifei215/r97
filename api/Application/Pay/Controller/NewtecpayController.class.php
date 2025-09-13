<?php

namespace Pay\Controller;

class NewtecpayController extends PayController
{
    private $code = '';

    private $_pbulicKey = '-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAxhabKYXpiBO5A+Ph5GGn
MgpT6U6I5asWZnEfgLzak78KhrTf4G5o8MgdI5OHxnhxXwA1J6oj6TxScV3D6Qgc
EmKla10nm2fe30I6YlrSv8/b1RqphpoM2kzjYZoIC8yjvHRELI0cjlz9F681RePt
fa6HxOdBOlfU/S+iZuvMe3W8Upg7KE5YuoX95UVzil14J0N9P572hIx/lvFaXN8f
6RNPyk2mW3QWA5cY9tR8PlDGGnIjo603OppzYfNTnIev92ZlZNncIrqw4YhAeaCC
7Uhs3lF/1f5OYLL+XhdTfBNvzG1lSwB7BMe3h3Fp8doqLvP9ueRDEhM2VGYXzN2d
JQIDAQAB
-----END PUBLIC KEY-----';

    private $_privKey = '-----BEGIN PRIVATE KEY-----
MIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQC0gOCwhfnlyTg1
Gdu7Bk2jd53K1eXt45z3BCY08RkDn+6pdzb+2dIzlWHpiI1O4s8eqtaEHuuNKgZz
2XRUDoUIhJDqc3O7cjRSqlNpUGJq04xBlS9uqjz7+W62QCbo6vNegUyC7Rz40izG
RNXW+3dhKDNOV/qZBiq+XnraYSJKQSRkKXs4ctVl4gdsX8vo0NpsCGRGCwgH+DWL
g1MkGszux1t7aup0KHwSHIJifNE7E/sr/r9BvmIYfsNdJ9AqUzTXULmG6DlB+b7g
kzcp1GjuASQLQJrYZgxxnddUUxJndcBTOexvxnKe6MzobsNZLboZW8qtqbytq2so
/TdoyZOlAgMBAAECggEBAKFhgIISpsSdTRWc0um5zvxR29AXwYUZNaNcIFTBIl4t
RZJaNd9RHDBmZK4JGl3nRsribvydMHh7lF1LlEf621IsQ+x54IU8rC8kfYLxDaz5
CrMwEMJejjuyXRuw65jfR0u9SozcNkT4lHOH47BcD/XbnVN0MWdTLjAaBLjuk1vS
tXv+pBP3daCGVp7Jou8Zaz4S24rLsNOnTYCnwzKtqEk/KIs/3SLL9odbIx6xm2pj
RCnXN/mf8qmOBWFiS0MCdMe/CAnOUnPWbOuYaQzCVqfXHb22w415VWxhfZVtECPd
hhjyiGT3PtdVcZghl1bvewe/dGYz1JXjMxmIuHXnAtkCgYEA3nuoM3jHDk3KvkcI
aVnveWcB3S2BG0B7kMNyAkTjDxaiWdQQ09H8rCUVkYq43GxqrX5H02JRKK/bSuu+
SODJ2mLMa8hW62Uzpkc0Qikgwb0cebnGAEIW/Z+AFSiraXls6vuznIpRYKXA+X5p
nIQn1qbhq5fe0Fv/YGpYILqtRScCgYEAz7I5MM79rESqBQKQBsvRfduJXWqpqdA3
NuZvjFUv4b7FhHhQHdbrENyVXqNDiDFYru/+5dt1u+EiSEo7ZbE7lc/ozS1Y/Gn1
y0aN3nrEU/N7niR5u7ZUf3BH0Km77Oh8vo9DILQ8uBT59tlWmg8gtLFoHI+yZE/S
DGJfEfwrmFMCgYEAn9KG/wrJPA5IEa5nbX7s1/JWVXPF5jTJTzIHqXehAQrKb+s2
d2PGtkchml7j83xymdU4dbKQCMFjsAtvs1y/xIDqxpaxUgJuOwm1kb2HyYU3en4o
rbDMYT9+PDTuBiPzSU6tNUvrr5kC6neUGJqbH8jcHm9H4bfk2Xcrfb98j0sCgYA+
PFNy0rOkyTaBJdFul+iAZCZ3PZz1a5T/+HD4hhZA+N9K4JpxNpNdjBueLiHwT4kZ
coTY77gJwl55dvTxdfg63gAOa0Y87KtgbBXa0sK9vToPmzJ3Ex2iykxatGHBjbDD
kT42MIv5RR/Z3ipbI6lTO83MsSptcJWqbFe8lemiTQKBgFXvyrd+AvHrokQJEz2A
P9S3/lFhBZyXOAZUVnnpICmsoCa5i5UJ041GPda4MHe7aNc3rXp+k27eApsvrMB5
Mf5BYafNnkCn3r72qYsD0Zv5wIXGjDU7XCZeOVRxY6XxgMk0JmlWdZ7j8mSIJKEb
u0W5bbqUf1nOeiqOV9S8Giz0
-----END PRIVATE KEY-----';

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
        $pay_IP = I('request.pay_IP', '');
        $parameter = [
            'code' => $this->code,
            'title' => 'Ecomovi',
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
        $native = array(
            'merchant_order_no' => $return['orderid'],
            'product_title' => $body,
            'product_desc' => $body,
            'amount' => intval(sprintf("%.2f", $return['amount']) * 100),
            'trade_type' =>'pix',
            'notify_url' => $return["notifyurl"],
            'user_ip' => $pay_IP,
            'time_start' => date("Y-m-d H:i:s"),
        );

        log_place_order($this->code, $return['orderid'] . "----提交", json_encode($native, JSON_UNESCAPED_UNICODE));    //日志
        log_place_order($this->code, $return['orderid'] . "----提交地址", $return['gateway']);    //日志

        // 记录初始执行时间
        $beginTime = microtime(TRUE);

        $returnContent =  $this->http_post_json($return['gateway'], $native);

        log_place_order($this->code, $return['orderid'] . "----返回", $returnContent);    //日志
        $ans = json_decode($returnContent, true);
        if($ans['return_code'] === 'SUCCESS' && $ans['status'] ==='PROCESSING'){
            $payurl = $site . 'PayPage.html?sid=' . $return['orderid'] . '&amount=' . $return['amount']. '&qrcode=' .$ans['credential'];
            $return_arr = [
                'status' => 'success',
                'H5_url' => $payurl,
                'QRcode' => $ans['credential'],
                'pay_orderid' => $orderid,
                'out_trade_id' => $return['orderid'],
                'amount' => $return['amount'],
                'datetime' => date('Y-m-d')
            ];
        }else{
            $return_arr = [
                'status' => 'error',
                'msg' => $ans['return_msg'],
            ];
        }
        echo json_encode($return_arr);

        // if($array['userid'] == 2){
        try{
            $redis = $this->redis_connect();
            $userpost = $redis->get('userpost_' . $return['out_trade_id']);
            $userpost = json_decode($userpost,true);

            logApiAddReceipt('下游商户提交YunPay', __METHOD__, $return['orderid'], $return['out_trade_id'], '/', $userpost, $return_arr, '0', '0', '1', '2');

            // 结束并输出执行时间
            $endTime = microtime(TRUE);
            $doTime = floor(($endTime-$beginTime)*1000);
            logApiAddReceipt('YunPay订单提交上游WinPay', __METHOD__, $return['orderid'], $return['out_trade_id'], $return['gateway'], $native, $ans, $doTime, '0', '1', '2');
        }catch (\Exception $e) {
            // var_dump($e);
        }
        // }
        exit;
    }

    //异步通知
    public function notifyurl()
    {
        //获取报文信息
        $json = file_get_contents("php://input");
        $data = json_decode($json, true);
        $orderid = $data['out_trade_no'];
        //log_place_order($this->code . '_notifyserver', $orderid . "----异步回调报文头", json_encode($_SERVER));    //日志
        log_place_order($this->code . '_notifyurl', $orderid . "----异步回调", $json);    //日志
        if (!$orderid) return;
        $result = $data;
        //过滤数据，防SQL注入
        // $check_data = sqlInj($result);
        // if ($check_data === false) return;

        $orderList = M('Order')->where(['pay_orderid' => $orderid])->find();
        if (!$orderList) return;

        //验证IP白名单
        $check_re = check_IP($orderList['channel_id'], getIP(), $orderid);
        if ($check_re !== true) return;

        if ($this->publicVerify($data)) {
            if ($result['trade_status'] === 'TRADE_SUCCESS') {     //	1：交易成功
                $re = $this->EditMoney($orderList['pay_orderid'], $this->code, 0);
                if ($re !== false) {
                    log_place_order($this->code . '_notifyurl', $orderid . "----回调上游", "成功");    //日志
                } else {
                    log_place_order($this->code . '_notifyurl', $orderid . "----回调上游", "失败");    //日志
                }
            } else {
                log_place_order($this->code . '_notifyurl', $orderid . "----订单状态异常", $result['trade_status']);    //日志
            }
            echo 'success';
        } else {
            log_place_order($this->code . '_notifyurl', $orderid . "----验签", '失败');    //日志
        }
    }

    /**
     * 订单查询
     * @param $return
     * @param $key
     * @return int
     */
    private function queryOrder($return, $key)
    {
        $native = array(
            "pid" => $return['merch_no'],
            "out_trade_no" => $return['out_trade_no'],
            "trade_no" => $return['trade_no'],
            "req_id" => time(),
        );
        $native['sign'] = $this->sign($native, $key);//签名

        $param = [
            'param' => json_encode($native),
            'url' => $return['gateway'],
        ];
        log_place_order($this->code . '_checkorder', $native['mer_order_no'] . "----提交", json_encode($native));    //日志
        $returnContent = $this->http_post_json($return['unlockdomain'] . '/open/index.php', $param);

        log_place_order($this->code . '_checkorder', $native['mer_order_no'] . "----返回", $returnContent);    //日志
        $ans = json_decode($returnContent, true);
        if ($ans['result_code'] === '10000') {
            if ($ans['trade_status'] === '1') {         //交易状态.1：交易成功,2：交易失败,3：交易进行中
                return 11;
            } else {
                return $ans;
            }
        } else {
            die($ans['result_msg']);
        }
    }

    /*********************************辅助方法*********************************/

    private function http_post_json($url, $params){
        $this->send($url, $params);
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

        echo "请求报文:".$_json_data."\n";

        //处理POST请求数据
        if ($method == 'POST'){
            curl_setopt($ch, CURLOPT_POST, 1 );
            curl_setopt($ch, CURLOPT_POSTFIELDS, $_json_data);
        }
        $header = array(
            "nonce"=>$this->createUniqid(),
            "timestamp"=>$this->getMicroTime(),
            "Authorization"=>"5642bd24f179435e934a7314fa0eb4ec",
        );

        if ($method == 'GET'){
            $sign = $this->to_sign_data($method);
        }else{
            $sign = $this->to_sign_data($method, $_json_data, $header);
        }
        $header['sign:'] = $sign;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        //发送请求读取输数据
        $data = curl_exec($ch);
        try{
            $body_data = null;
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $res_header = substr($data, 0, $header_size);
            $body_data = substr($data, $header_size);
            $response_code=intval(curl_getinfo($ch, CURLINFO_HTTP_CODE));
            echo "\n响应data：".$data."\n响应code：".$response_code."\n响应body报文：".$body_data;
            if ($response_code==200){
                if ($isVerifySign) {
                    $this->to_verify_data($res_header, $body_data);
                }
            }
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
        $verify_result = $this->verify($_to_verify_data, $_res_sign);
        echo "\n同步响应验签结果:".$verify_result."\n";
        if(empty($verify_result) || intval($verify_result)!=1){
            throw new InvalidResponseException("Invalid Response.[Response Data And Sign Verify Failure.]");
        }

        if (strcmp(SignConfig::getSecretKey(),$_res_secret_key)){
            throw new InvalidResponseException("Invalid Response.[Secret Key Is Invalid.]");
        }
    }

    /**
    * 签名数据
    *
    * @param $data 待签名数据
    * @return string 签名后的数据
    */
    public static function sign($data)
    {
        //读取私钥文件
        $priKey = file_get_contents(SignConfig::getPrivateKeyPath());

        //转换为openssl密钥，必须是没有经过pkcs8转换的私钥
        $res = openssl_get_privatekey($priKey);

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
    public static function verify($data, $sign)  {

        //读取支付平台公钥文件
        $pubKey = file_get_contents(SignConfig::getYhbPublicKeyPath());
        //转换为openssl格式密钥
        $res = openssl_get_publickey($pubKey);
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
    public static function rsaPrivateEncrypt($data) {
        //读取私钥文件
        $priKey = file_get_contents(SignConfig::getPrivateKeyPath());

        //转换为openssl密钥，必须是没有经过pkcs8转换的私钥
        $res = openssl_get_privatekey($priKey);
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

    /** JAVA格式的公钥转换为PHP格式的公钥
     * @param $java_rsa_public_key
     * @return string
     */
    public function Java2PhpRSAPublicKey($java_rsa_public_key) {
        return $res = "-----BEGIN PUBLIC KEY-----\n" . wordwrap($java_rsa_public_key, 64, "\n", true) . "\n-----END PUBLIC KEY-----";
    }

    /** JAVA格式的私钥转换为PHP格式的私钥
     * @param $java_rsa_private_key
     * @return string
     */
    public function Java2PhpRSAPrivateKey($java_rsa_private_key) {
        return $res = "-----BEGIN PRIVATE KEY-----\n" . wordwrap($java_rsa_private_key, 64, "\n", true) . "\n-----END PRIVATE KEY-----";
    }
    
    /**
     * 生成唯一id[32位]
     * @param string $namespace
     * @return string
     */
    public static function createUniqid($namespace = ''){
        static $uniqid = '';
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
    public static function getMicroTime(){
        list($t1, $t2) = explode(' ', microtime());
        return (float)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
    }
}

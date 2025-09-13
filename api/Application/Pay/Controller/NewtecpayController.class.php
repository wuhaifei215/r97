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
        vendor('yy.init');
        $this->initSignConfig();

        log_place_order($this->code, $return['orderid'] . "----提交", json_encode($native, JSON_UNESCAPED_UNICODE));    //日志
        log_place_order($this->code, $return['orderid'] . "----提交地址", $return['gateway']);    //日志

        // 记录初始执行时间
        $beginTime = microtime(TRUE);

        $returnContent =  self::_request($return['gateway'],$native);

//        $returnContent = $this->http_post_json($return['gateway'], $native);
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
    /**
     * 设置需要发送的HTTP头信息
     *
     * @return void
     */
    private function setHeader(){

        $defaultHeader = array(
            'lang:PHP',
            'publisher:yy',
            'sdk-version:1.0.0',
            'uname:'.php_uname(),
            'lang-version:'.PHP_VERSION
        );

        $nonce = YYUtil::createUniqid();
        $timestamp = YYUtil::getMicroTime();

        $header = array(
            'Content-Type:application/json; charset='.PayConfig::$CHARSET,
            'nonce:'.$nonce,
            'timestamp:'.$timestamp,
            'Authorization:'.SignConfig::getSecretKey(),
            'X-yy-Client-User-Agent:'.json_encode($defaultHeader)
        );

        $header_array = array(
            "nonce"=>$nonce,
            "timestamp"=>$timestamp,
            "Authorization"=>SignConfig::getSecretKey()
        );

        $this->header = $header;
        $this->header_array = $header_array;
    }

    /**
     * 初始化签名信息
     */
    private function initSignConfig(){
        //支付平台支付平台提供给商户的SecretKey，登录支付平台支付平台查看
        // 需要替换为实际的数值
        \SignConfig::setSecretKey("2a6e38db0f44492a8a3aa0647a7cf311");
        //商户自己的私钥[公钥通过登录支付平台支付平台进行配置，私钥设置到下面的变量中]
        //样例 见 merchant_rsa_private_key.pem
        // 需要替换为实际的数值
        \SignConfig::setPrivateKeyPath("merchant_rsa_private_key.pem");
        //支付平台支付平台提供给商户的公钥，响应结果验签使用，登录支付平台支付平台查看,把它保存到一个pem文件中
        //样例 见 yy_rsa_public_key.pem
        // 需要替换为实际的数值
        \SignConfig::setYhbPublicKeyPath("yy_rsa_public_key.pem");
    }

    //发送post请求，提交json字符串
    private function http_post_json($url, $post = array())
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $httpheader[] = "Accept: */*";
        $httpheader[] = "Accept-Language: zh-CN,zh;q=0.8";
        $httpheader[] = "Connection: close";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if($post){
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
        }
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
    private function isEmpty($value)
    {
        return $value === null || trim($value) === '';
    }

    // 获取待签名字符串
    private function getSignContent($params)
    {
        ksort($params);
        $signstr = '';
        foreach ($params as $k => $v) {
            if (is_array($v) || $this->isEmpty($v) || $k == 'sign' || $k == 'sign_type') continue;
            $signstr .= '&' . $k . '=' . $v;
        }
        $signstr = substr($signstr, 1);
        return $signstr;
    }

    // 回调验证
    public function publicVerify($arr)
    {
        if (empty($arr) || empty($arr['sign'])) return false;
        if (empty($arr['timestamp']) || abs(time() - $arr['timestamp']) > 300) return false;
        $sign = $arr['sign'];
        return $this->rsaPublicVerify($this->getSignContent($arr), $sign);
    }

    // 商户私钥签名
    private function privateEncrypt($data)
    {
        $data = $this->getSignContent($data);
        $key = "-----BEGIN PRIVATE KEY-----\n" . wordwrap($this->_privKey, 64, "\n", true) . "\n-----END PRIVATE KEY-----";
        $privatekey = openssl_get_privatekey($key);
        if (!$privatekey) {
            throw new \Exception('签名失败，商户私钥错误');
        }
        openssl_sign($data, $sign, $privatekey, OPENSSL_ALGO_SHA256);
        return base64_encode($sign);
    }

    // 平台公钥验签
    private function rsaPublicVerify($data, $sign)
    {
        $key = "-----BEGIN PUBLIC KEY-----\n" . wordwrap($this->_pbulicKey, 64, "\n", true) . "\n-----END PUBLIC KEY-----";
        $publickey = openssl_get_publickey($key);
        if (!$publickey) {
            throw new \Exception("验签失败，平台公钥错误");
        }
        $result = openssl_verify($data, base64_decode($sign), $publickey, OPENSSL_ALGO_SHA256);
        return $result === 1;
    }

}

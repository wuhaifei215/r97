<?php

namespace Pay\Controller;

class CBPayController extends PayController
{
    private $code = '';
    private $privateKeyPEM = <<<EOD
-----BEGIN PRIVATE KEY-----
MIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQCiw1biEbrFS34x
tC0H6NloQ70mjNrFOG74ILAj3UfNmpHW5jfsrovRQvZfgwy8hWz/dhX5GC81yG7d
LLlhPJqOxYHKAruBq0zXnm4lPc1hay73x/nH3lPfRoHaUlJy8i+Lf0ruqEZhduLi
cvGTczlz1NExd9Lwz9QZFED60Pk22xfmTIpLFrIK6iWNSFaVA+1IL6i9l+o0M+M1
vzCi0e6Q2NSUiIIyeUFRaYhl0M+uFHtQAUZ0UT8IR1dxvS/uvHKt60WQmhTSUxvQ
qiEAYk0wVhMOb14qFQ6oez9lisOjRkfSydvajPBE/abt+cokQ8jtj9ROgHR0YxUH
5ijXFFh7AgMBAAECggEAMXuqy/Mw8a+Ib6sD774qbqj9dh1nm4MTNauWcWjiXg9K
nGeEhBfy2Q1/Ir2QXzY/U5E+j+r622KS1fo1Z+sJYDOKyHQ5NmxyYbFAcmEOMjpo
Xpt1BH9Cx4RDkTDq8RhRPRtXmCQftZzz0H26lSX4Rw9iBCSWRHEi3fcK5FYpFjRc
l5Ygf5m28un8xXhQT5FgzRnxdbA8zIiOosT2gD8XE2GvtseiolEKtg2tlu/GRtL/
J7hPbZjR6S0NvUN7vBfSO1hPDy9ylpORN3wwMBAGcFjBcUWH+G4Dl5JnbxUpNea9
hsQUu3MFF6x+/RJn1d90nAi2gbsCgIZLQZyCXxeHbQKBgQDV/hMQzv9BSKVOr4LQ
V/cNxsmOqomkkM/xnNc7+jeIbtfgPHJhc3kqJfdo86v75tvMRpskUNHAZAg0sSoj
wha2e/t7+3wI7eSVL9xnAd2UJ2fY5UKb7TttiHL/hd/2C5QOY0MliM9r6UAKJjJI
1MfhYE6PCk2Y09XKLZGM933EzwKBgQDCtssjDm1GNZq0F6WFhT5cWskzmkvrV4c8
hOZSCfE0qpywoGFm5+1P42FFKug2MkweC4T3xZxfIXSrjXd0ljdmnzTMR99AlxNI
73T6ziXEEsoPtYNQ5Schm+rj/oWLi0W2l8QvlUTPrH4Mfaa3iLUeffr507+UAlgx
PZaUad10lQKBgDVs64GkzGGWK39LnlM5wvpziNrPhPHLHb9qsunMfJTZZ5gaP0xJ
MhTtyakwPN31Myb68bzNWLC5yLqvCKBI0rbYmV7I2Jy6F/mPK22kL5fPhsPF6S1Y
ux+Lk3psCBA7r8kvyxHR6Ec+wrhF9QPt68E+9B4OKnVfXd7OJaqWF5ZNAoGBALyf
lXOVYzgzb3VsvtT0ue9/pw+NNmR6ezCeUfxBU1/As1/LTfABVvwf+jemFCOEYAZv
BFV+Ijhp0Xrq5UCU0IBiVCRcr6IDeBQcnEbmcuFZjlLfdKFmC51cTJSnGLmQpmz2
4n9x66H+qHDzPBDppwMt6XYgsaLxDnxM/FUzpOttAoGBAK9A/Jj2WnlK8NHv5/nt
1sYgYtNZ+Ioj0D5AaMamAy9cuPHJ0DUj4H++VGhhDOZsEDAqCGFTc/GWlPBAls0W
6iMwUbV2/769lNOaCbsCTc/u6UbOIVbFGnX+ZYSXKl9CC9xv0KU18XfZxKPes7fI
OajnEvZGH6RA8+1I9w3leVrh
-----END PRIVATE KEY-----
EOD;

    private $publicKeyPEM = <<<EOD
-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA43J7FUIdwD6MF7n4gzV9
Q7kUJkbUYFVaAQsCNpKZ9hAbA/P6UvVeA6N2StCbgowxIyHM2DVCSKWmehgTLjnu
Rv9Lm/E2xBBXIHrOH8IsUN39ba1fB6FrN0tDnBaT4q62MNJfo55mLYCZ8evR5t+B
9dR8It5MS5O9j27GBGYj2E01ZhGBm0n/qSevbNCFzMtwzM7K0zXH9xynwDlAVEjh
FyZVCNcozJnrgZbPUF59L7PXe7Cd/r+hTfaB2nldrkfcbbPz9RxXcdVsLAn6VlDy
RTt/31QNZ5hR2gySew99q2r+symKU/fHCajqO8QmNzMjv7Qh0y4+tJjVY9+UqZsK
9wIDAQAB
-----END PUBLIC KEY-----
EOD;

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
            'title' => 'CBPay',
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
        
        $native = array(
            'mchId' => $return['mch_id'],
            'mchOrderNo' => $return['orderid'],
            'amount' => sprintf('%.2f', $return['amount']), 
            'notifyUrl' => $return["notifyurl"],
        );
        log_place_order($this->code, $return['orderid'] . "----提交", json_encode($native, JSON_UNESCAPED_UNICODE));    //日志
        log_place_order($this->code, $return['orderid'] . "----提交地址", $return['gateway']);    //日志
                
        // 记录初始执行时间
        $beginTime = microtime(TRUE);
        
        $returnContent = $this->http_post_data($this->privateKeyPEM,json_encode($native), $return['gateway']);
        log_place_order($this->code, $return['orderid'] . "----返回", json_encode($returnContent, JSON_UNESCAPED_UNICODE));    //日志
        $ans = json_decode($returnContent['body'], true);
        if($ans['code'] === 0){
        
            $payurl = $site . 'PayPage.html?sid=' . $return['orderid'] . '&amount=' . $return['amount']. '&qrcode=' . $ans['data']['qrcode'];
            $return_arr = [
                'status' => 'success',
                'H5_url' => $payurl,
                'QRcode' => $ans['data']['qrcode'],
                'pay_orderid' => $orderid,
                'out_trade_id' => $return['orderid'],
                'amount' => sprintf('%.2f', $return['amount']),
                'datetime' => date('Y-m-d')
            ];
        }else{
            $return_arr = [
                'status' => 'error',
                'msg' => $ans['message'], 
            ];
        }
        if($array['userid'] == 2){
            echo '<script type="text/javascript">window.location.href="' . $payurl . '"</script>';
        }else{
            echo json_encode($return_arr);
        
        }
        // if($array['userid'] == 2){
            try{
                $redis = $this->redis_connect();
                $userpost = $redis->get('userpost_' . $return['out_trade_id']);
                $userpost = json_decode($userpost,true);
                
                logApiAddReceipt('下游商户提交YunPay', __METHOD__, $return['orderid'], $return['out_trade_id'], '/', $userpost, $return_arr, '0', '0', '1', '2');
                
                // 结束并输出执行时间
                $endTime = microtime(TRUE);
                $doTime = floor(($endTime-$beginTime)*1000);
                logApiAddReceipt('YunPay订单提交上游CBpay', __METHOD__, $return['orderid'], $return['out_trade_id'], $return['gateway'], $native, $ans, $doTime, '0', '1', '2');
            }catch (\Exception $e) {
                // var_dump($e);
            }
        // }
        exit;
    }
    //异步通知
    public function notifyurl()
    {
        $result = json_decode(file_get_contents('php://input'),true);
        //获取报文信息
        $orderid = $result['mchOrderNo'];
        //log_place_order($this->code . '_notifyserver', $orderid . "----异步回调报文头", json_encode($_SERVER));    //日志
        log_place_order($this->code . '_notifyurl', $orderid . "----异步回调", json_encode($result, JSON_UNESCAPED_UNICODE));    //日志
        if (!$orderid) return;

        //过滤数据，防SQL注入
        // $check_data = sqlInj($result);
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
        
        $sign = $_SERVER['HTTP_SIGNATURE'] ?? "";
        $verify = $this->RSAVerifyPKCS8(file_get_contents('php://input'), $sign, $this->publicKeyPEM);
        if ($verify) {
            if($result['status'] === 'PAID'){     //订单状态枚举值:CREATED订单创建成功,PAYING等待用户支付,PAID订单支付成功,FAILED订单创建失败
                $re = $this->EditMoney($orderList['pay_orderid'], $this->code, 0);
                if ($re !== false) {
                    log_place_order($this->code . '_notifyurl', $orderid . "----回调上游", "成功");    //日志
                }else{
                    log_place_order($this->code . '_notifyurl', $orderid . "----回调上游", "失败");    //日志
                }
            }else{
                log_place_order($this->code . '_notifyurl', $orderid . "----订单状态异常", $result['status']);    //日志
            }
            $json_result = "success";
        } else {
            log_place_order($this->code . '_notifyurl', $orderid . "----签名错误，加密后", $sign);    //日志
            $json_result = "sign error";
        }
        echo $json_result;
        try{
            logApiAddNotify($orderid, 0, $result, $json_result);
        }catch (\Exception $e) {
            // var_dump($e);
        }
    }
    
        
    //签名
    function RSASignaturePKCS8($priKey, $message){
        $privateKey = openssl_pkey_get_private($priKey);
        if (!$privateKey) {
            die('Failed to get private key');
        }
    
        $signature = '';
        openssl_sign($message, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        openssl_free_key($privateKey);
    
        return base64_encode($signature);
    }
    
    //验签
    function RSAVerifyPKCS8($message, $sign, $pubKey){
        $sign = base64_decode($sign);
        // 验证签名
        $publicKey = openssl_pkey_get_public($pubKey);
        if (!$publicKey) {
            die('Failed to get public key');
        }
    
        $verified = openssl_verify($message, $sign, $publicKey, OPENSSL_ALGO_SHA256);
        openssl_free_key($publicKey);
    
        return $verified === 1;
    }
    
    //发送post
    function http_post_data($priKey, $message, $url) {
        $sign = $this->RSASignaturePKCS8($priKey, $message);
    
        $header[] = 'Content-Type: application/json';
        $header[] = 'Signature: '.$sign;
    
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $message,
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_HEADER => true, //设置获取header
        ));
    
        $response = curl_exec($curl);
    
        // 检查是否有错误发生
        if(curl_errno($curl)){
            echo 'Curl error: ' . curl_error($curl);
            return [];
        }
        
        curl_close($curl);
    
        // 分割header和body
        list($header, $body) = explode("\r\n\r\n", $response, 2);
    
        return ['header' => $header, 'body' => $body];
    }
    

}

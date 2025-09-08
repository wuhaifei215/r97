<?php

namespace Pay\Controller;

class CBPay2Controller extends PayController
{
    private $code = '';
    private $privateKeyPEM = <<<EOD
-----BEGIN PRIVATE KEY-----
MIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQDhgZpKswCywAzK
EufxxJsNRtB+3u5ed6FvK8uK/2YGQ/yNR/8gfmcRPFjKPuRAfh8UoS6YK4N+QI/Y
3pOdqvjSbZ2izuKXe/Og6Xj36cv2CAt5WThznIuc1a2u3UXnU3rVQMtqUYzDNThZ
RPrLa3v1h0YuoAxA9K+mz0fEWTE3ebGsxitHDrGhCkXQuI5qdkCwZxdaEO677UiO
T8ji9u/EpQz31XP2FCGSM1QBsyUS4X+GqTp8QMJPlYbqPA0W1nfDWdfho8ORXtfD
dNkuSstSC0WD7QehjBvZ9Ci4Vp203lzRya7/YvCDbGqQmmX/BuOUqhsdETldErdy
GLWHDU+JAgMBAAECggEAHhYJfMqHCad/zVIFFLIosaEaCLiQ0D+2ZRD7FgS0cETl
JABQCd4eEs7yAFcFuD5bbTLX9utknr/ZsT3OL1dJMoyiCb+qZuWIVUFGUg/PliKa
op+vCBWsYXSiDdYYZnWHyiiGkHJoN6nDu9KX10ie1YeCazllM8wGx1hDo0RMbFxq
5pBjqjS5qC8W/vOOe1AhW0IBdYHADQzZIMXikBzr3+22RPDRlX2ubMbzrVi666S5
XDggLbBZGgaJIz76k3PFDNnss5pzyRDTZ67zlcFIm2otZJm2oqzc8daYWraVMwi7
pDkDi2+4kECZ6jkzB+KPZNUkgnA0EwKNnbrpUfCDNQKBgQD8iYq+ZQFImhHFkWWR
pwBZkxmQCc3kQnhtCSfG72qkWwpEeOxjjMf3tZTPyfA/Jt56WP5ihpuzcP3pOxbA
i4L+Ld9om75rtotO5Ev2HJSUuBvv/t9a8YfK03pNsC8v1fQ/WUIIYmP5YPCiJqst
tp5Gf5lOJmso0xEQgQMwuuahKwKBgQDkmS0he8+PFPug5DkctcKIq7O6XqQkDM66
HWm6Ry6asWC/a3yNkS+1GxuPii/7PEEtkwtD2IvTh+jjSP/77alw4TtX2ruPtmLb
m22xZvbg5xbxim2BYh80hFZnVZjYo/qtOA8v4t2liWlcpThAiXAjxUk1wM3yOtHI
gYSl5+bwGwKBgQDRHxyzeXTMqDjbQAG50W8qvfNHIbdLs/eBYzl7NTs8Ct+/v9sQ
vL3C1Kz8PJ8jzI4jBprw/8Ljn0fD4Vh/7Yd18Iq2V3IiRDGsDMUScqxfUu5fmNFg
v+3Q+bqnpqQMmsH9y24w/zkGg04BGMBbxIPgoT1UR+ApGE9jOVJpAfyFrQKBgQDQ
pmclCscWSAasgeMHK3eWG3J+h2e2Z+JAyhv4pIQLOh+eWFNlO8GZwlZTm8PTwtmS
6YX8tShbOM/+tGDB59kiaNIrjEBqGgT9gU6L//kSqpqseLcewxrMaoYa52wNQQ7L
EEH8aUv5jgSXRU6kyU2LuMTrxUG0+K8GHgGfFJ4bRQKBgFCNP5ii93XA9c27+FVk
WqNEB/2c+EGBb/AtArclxqkS6lgZmO+ZpvNsMrmccE2CASADN97ZEBVo6PR9IMXe
NV17UEvCqTTIUoME9Wfww2AbGbEf3kEFI8c2L9fB8NaoXa7TZy/EufX6hcqPH9Lc
yybeXiy30t9LYon43uNVtt+W
-----END PRIVATE KEY-----
EOD;

    private $publicKeyPEM = <<<EOD
-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAvrQDGwEkWuwDp6UGm4hR
iD4FYuNs2GuKjy1HDEPmF7hCjxbFFWiXfoHhBLGaRBt1fB/MgFCgNZydJ8aA1nNX
00G3eupdrFhJyMjsCEkf9lhOdsk+AM/pxNjfpEPvMSLU0s/2gxy0cswPn9bDA13n
hBkw6HmAWnk5cSTdz7LU0dw8O0zquZB/KG5UQwp+OsdAZwUzVSsSs/DWrcrLQ7IZ
ZKK1QZo6fxbDEUuNSWLboAkUEBiKFAejKuQRMZr6IhaLm5apYPaW/7NU+e5QDjBq
benyc+iIu9RpnkhPaiB+JKaAWSo+m+mXXz5dKuCBLhicRPN9+FWBg8cbhPDOxzN3
/wIDAQAB
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
            'title' => 'CBPay2',
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

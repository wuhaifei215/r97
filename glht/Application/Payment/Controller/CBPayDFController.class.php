<?php

namespace Payment\Controller;

class CBPayDFController extends PaymentController
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
        $matches = [];
        preg_match('/([\da-zA-Z\_]+)Controller$/', __CLASS__, $matches);
        $this->code = $matches[1];
    }

    //代付提交
    public function PaymentExec($data, $config)
    {
        $post_data = array(
            "mchId" => $config['mch_id'], //商户号
            "mchOrderNo" => $data['orderid'], //订单号
            "amount" => sprintf('%.2f', $data['money']),  //提现金额
            "notifyUrl" => 'https://' . C('NOTIFY_DOMAIN') . "/Payment_" . $this->code . "_notifyurl.html",      //异步通知地址
            'accountType' => 'PIX_' . $data['type'],
            "accountNo" => $data['banknumber'],    //账号
            "accountName" => $data['bankfullname'],  //户名
        );
        log_place_order($this->code, $data['orderid'] . "----提交", json_encode($post_data, JSON_UNESCAPED_UNICODE));    //日志
        log_place_order($this->code, $data['orderid'] . "----提交地址", $config['exec_gateway']);    //日志
        
        // 记录初始执行时间
        $beginTime = microtime(TRUE);
        
        $returnContent = $this->http_post_data($this->privateKeyPEM,json_encode($post_data), $config['exec_gateway']);
        $result = json_decode($returnContent['body'], true);
        // if($data['userid'] == 2){
            try{
                
                $redis = $this->redis_connect();
                $userdfpost = $redis->get('userdfpost_' . $data['out_trade_no']);
                $userdfpost = json_decode($userdfpost,true);
                
                logApiAddPayment('下游商户提交YunPay', __METHOD__, $data['orderid'], $data['out_trade_no'], '/', $userdfpost, [], '0', '0', '1', '2');
                
                // 结束并输出执行时间
                $endTime = microtime(TRUE);
                $doTime = floor(($endTime-$beginTime)*1000);
                logApiAddPayment('YunPay订单提交三方CBPay', __METHOD__, $data['orderid'], $data['out_trade_no'], $config['exec_gateway'], $post_data, $result, $doTime, '0', '1', '2');
            }catch (\Exception $e) {
                // var_dump($e);
            }
        // }
        log_place_order($this->code, $data['orderid'] . "----返回", json_encode($result, JSON_UNESCAPED_UNICODE));    //日志

        // log_place_order($this->code, $data['orderid'] . "----状态：", $result['status']);    //日志
        if($result['code'] === 0){
            //保存第三方订单号
            // $orderid = $data['orderid'];
            // $Wttklistmodel = D('Wttklist');
            // $date = date('Ymd',strtotime(substr($orderid, 1, 8)));  //获取订单日期
            // $tableName = $Wttklistmodel->getRealTableName($date);
            // $re_save = $Wttklistmodel->table($tableName)->where(['orderid' => $orderid])->save(['three_orderid'=>$result['order_no']]);
            $return = ['status' => 1, 'msg' => '申请正常'];
        }else{
            $return = ['status' => 0, 'msg' => $result['message']];
        }
        return $return;
    }

    public function notifyurl()
    {
        $re_data = json_decode(file_get_contents('php://input'),true);
        //获取报文信息
        $orderid = $re_data['mchOrderNo'];
        //log_place_order($this->code . '_notifyserver', $orderid . "----异步回调报文头", json_encode($_SERVER));    //日志
        log_place_order($this->code . '_notifyurl', $orderid . "----异步回调", json_encode($re_data, JSON_UNESCAPED_UNICODE));    //日志
        
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

        $sign = $_SERVER['HTTP_SIGNATURE'] ?? "";
        $verify = $this->RSAVerifyPKCS8(json_encode($re_data), $sign, $this->publicKeyPEM);
        if ($verify) {
            if ($re_data['status'] === "PAID") {      //订单状态:CREATED订单创建成功,PAYING等待用户支付,PAID订单支付成功,FAILED订单创建失败
                $data = [
                    'memo' => '代付成功',
                ];
                $this->changeStatus($Order['id'], 2, $data, $tableName);
                log_place_order($this->code . '_notifyurl', $orderid, "----代付成功");    //日志
                $json_result = "OK";
            } elseif ($re_data['status'] === "FAILED" || $re_data['status'] === "REFUND") {
                //代付失败
                $data = [
                    'memo' => '代付失败-' . $re_data['message'],
                ];
                $this->changeStatus($Order['id'], 3, $data, $tableName);
                log_place_order($this->code . '_notifyurl', $orderid, "----代付失败");    //日志
                $json_result = "success";
            }
        } else {
            log_place_order($this->code . '_notifyurl', $orderid . '----签名错误: ', $sign);
            $json_result = "sign error";
        }
        echo $json_result;
        try{
            logApiAddNotify($orderid, 1, $re_data, $json_result);
        }catch (\Exception $e) {
            // var_dump($e);
        }
    }

    //代付订单查询
    public function PaymentQuery($data, $config)
    {
        $post_data = [
            'mchId' => $config['mch_id'],
            'mchOrderNo' => $data['orderid'],
        ];
        log_place_order($this->code . '_PaymentQuery', $data['orderid'] . "----提交", json_encode($post_data, JSON_UNESCAPED_UNICODE));    //日志
        $returnContent = $this->http_post_data($this->privateKeyPEM,json_encode($post_data), $config['query_gateway']);
        log_place_order($this->code . '_PaymentQuery', $data['orderid'] . "----返回", $returnContent);    //日志
        $result = json_decode($returnContent['body'], true);
        if ($result['code'] === 0) {
            switch ($result['data']['status']) {       //订单状态:CREATED订单创建成功,PAYING等待用户支付,PAID订单支付成功,FAILED订单创建失败
                case "CREATED":
                case "PAYING":
                    $return = ['status' => 1, 'msg' => '处理中'];
                    break;
                case "PAID":
                    $return = ['status' => 2, 'msg' => '成功'];
                    break;
                case "FAILED":
                case "REFUND":
                    $return = ['status' => 3, 'msg' => $result['message'],'remark' => $result['message']];
                    break;
            }
        } else {
            $return = ['status' => 7, 'msg' => "查询接口失败:".$result['message']];
        }
        return $return;
    }
    
    
    //账户余额查询
    public function queryBalance()
    {
        if (IS_AJAX) {
            $config = M('pay_for_another')->where(['code' => $this->code])->find();
            $post_data = array(
                "mchId" => $config['mch_id'], //商户号
                "timestamp" => time(),
            );
            log_place_order($this->code . '_queryBalance', "提交", json_encode($post_data));    //日志
            $returnContent = $this->http_post_data($this->privateKeyPEM,json_encode($post_data), $config['serverreturn']);
            log_place_order($this->code . '_queryBalance', "返回", json_encode($returnContent));    //日志
            $result = json_decode($returnContent['body'], true);
            if($result['code'] === 0){
                $amount = $result['data']['amount'];  //总金额
                $available = $result['data']['available'];  //可用余额
                $frozen = $result['data']['frozen'];  //冻结余额
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
    <tr onmouseout="this.style.backgroundColor='#f5f5f5';" onmouseover="this.style.backgroundColor='#009688';"><td>总金额</td><td><b>$amount </b></td></tr>
    <tr onmouseout="this.style.backgroundColor='#f5f5f5';" onmouseover="this.style.backgroundColor='#009688';"><td>可用金额</td><td><b>$available </b></td></tr>
    <tr onmouseout="this.style.backgroundColor='#f5f5f5';" onmouseover="this.style.backgroundColor='#009688';"><td>冻结余额</td><td><b>$frozen </b></td></tr>
    </table>
AAA;
                $this->ajaxReturn(['status' => 1, 'msg' => '成功', 'data' => $html]);
            }
            
        }
    }
        //账户余额查询
    public function queryBalance2($config)
    {
        $post_data = array(
            "mchId" => $config['mch_id'], //商户号
            "timestamp" => time(),
        );
        log_place_order($this->code . '_queryBalance2', "提交", json_encode($post_data));    //日志
        $returnContent = $this->http_post_data($this->privateKeyPEM,json_encode($post_data), $config['serverreturn']);
        log_place_order($this->code . '_queryBalance2', "返回", json_encode($returnContent));    //日志
        $result = json_decode($returnContent['body'], true);
        if($result['code'] === 0){
            $result_data['resultCode'] = "0";
            $result_data['balance'] = $result['data']['available'];
         }
        return $result_data;
    }
    // public function PaymentVoucher($data, $config){
    //     if(isset($data['three_orderid'])){
    //         $post_data = [
    //             'custId' => $config['mch_id'],
    //             'appId' => $config['appid'],
    //             // 'order' => $data['three_orderid'],
    //             'order' => $data['orderid'],
    //         ];
    //         $post_data["sign"] = $this->get_sign($post_data, $config['signkey']);
    //         log_place_order($this->code . '_PaymentVoucher', $data['orderid'] . "----提交", json_encode($post_data, JSON_UNESCAPED_UNICODE));    //日志
    //         $returnContent = $this->http_post_data('https://phlapi.newwinpay.site/br/voucherData.json', $post_data);
    //         log_place_order($this->code . '_PaymentVoucher', $data['orderid'] . "----返回", $returnContent);    //日志
    //         $result = json_decode($returnContent, true);
        
    //         // $redata = json_decode(file_get_contents('https://api.winpay.site/payment/br/voucherData.webapp?casOrdNo=' . $data['three_orderid']),true);
    //         log_place_order($this->code . '_PaymentVoucher', $data['three_orderid'] . "----返回",  json_encode($result, JSON_UNESCAPED_UNICODE));    //日志
    //         if(!empty($result) && $result['code'] === '000000'){
    //             return  $result;
    //         }else{
    //             return false;
    //         }
    //     }else{
    //         return false;
    //     }
        
    // }

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

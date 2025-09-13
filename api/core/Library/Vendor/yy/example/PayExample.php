<?php

namespace yy\example;

use yy\config\SignConfig;
use yy\config\PayConfig;
use yy\sign\RSAUtil;
use yy\util\YYUtil;
use yy\model\ApiResource;

require ("../init.php");

class PayExample extends ApiResource
{


    /**
     * 代付下单
     */
    public function payOrderCreate(){
        $this->initSignConfig();

        $req_data=array(
            // 商户系统内部订单号，要求64个字符内，只能是数字、大小写字母_-|* 且在同一个商户号下唯一
            'merchant_order_no'=>YYUtil::createUniqid(),
            // 代付金额，单位为分
            'amount'=>100,
            // 账号类型：0-EMAIL, 1-CPF, 2-CNPJ , 3-PHONE
            'account_type'=>'1',
            // 账号类型对应的数值，如CPF为CPF号码，CNPJ为CNPJ号码，PHONE为手机号码，EMAIL为邮箱地址
            'account_no'=>'12345678901',
            // 交易描述，要求300个字符内
            'description'=>'Confirmação de pagamento',
            // 交易发起时间时间，格式为yyyyMMddHHmmss
            'apply_time'=>YYUtil::getCurrentTimeYmdStr(),
            // 代付结果异步通知URL
            'notify_url'=>'https://pay.notify.com'
        );
        echo self::_request(PayConfig::$API_BASE_URL.PayConfig::$PAY_ORDER_CREATE,$req_data);
    }

    /**
     * 代付订单查询
     */
    public function payOrderQuery(){
        $this->initSignConfig();

        $req_data=array(
            // 商户系统内部订单号
            'merchant_order_no'=>'',
            // 支付平台订单号
            'order_no'=>'CF201911081009355181265408000',
        );
        echo self::_request(PayConfig::$API_BASE_URL.PayConfig::$PAY_ORDER_QUERY,$req_data);
    }

    /**
     * 代付余额查询
     */
    public function payBalanceQuery(){
        $this->initSignConfig();
        $req_data=array(
            // 商户系统内部订单号，要求64个字符内，只能是数字、大小写字母_-|* 且在同一个商户号下唯一
            'merchant_order_no'=>YYUtil::generateString(32),
        );
        echo self::_request(PayConfig::$API_BASE_URL.PayConfig::$PAY_BALANCE_QUERY,$req_data);
    }


    /**
     * 初始化签名信息
     */
    private function initSignConfig(){
        //支付平台支付平台提供给商户的SecretKey，登录支付平台支付平台查看
        // 需要替换为实际的数值
        SignConfig::setSecretKey("2a6e38db0f44492a8a3aa0647a7cf311");
        //商户自己的私钥[公钥通过登录支付平台支付平台进行配置，私钥设置到下面的变量中]
        //样例 见 merchant_rsa_private_key.pem
        // 需要替换为实际的数值
        SignConfig::setPrivateKeyPath("merchant_rsa_private_key.pem");
        //支付平台支付平台提供给商户的公钥，响应结果验签使用，登录支付平台支付平台查看,把它保存到一个pem文件中
        //样例 见 yy_rsa_public_key.pem
        // 需要替换为实际的数值
        SignConfig::setYhbPublicKeyPath("yy_rsa_public_key.pem");
    }
}

// 支付平台公钥转换方法
//$rsaUtil = new RSAUtil();
// JAVA 格式公钥数值 请将转换后的支付平台公钥数值替换到 yy_rsa_public_key.pem 文件中
//$java_rsa_public_key="MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAyMuoP7k54ebs7DX1B3a7j/gi7lkLAPh+u3HKiI6ObTiLdGxqMaiWE51zQDo9SEcpmfJaaSUQlk2H+H6NBdqDLL6UIcXgstGqx0XESRqmMm1rwjRbhzJ4dFuHUIOD0MwhdxigL+MmILldFjgQTNo0D9TJSokqHClSR5DIJV9uTZ7kEt+w0Hsz2BxWlTGsNz7w283CFcezciHYETfRncDmjV+SK2rA9B6oUUywArPrjK1unOycIsS7TqrFMBflqesOXOS5NgE9nuV3yeLU9Z0g5txxhU+j9fO5ViYmirE4zCg6soBIv12+JmzBlDObv4iQiMMvupGDrUc9IrYXENv72QIDAQAB";
//echo "JAVA 格式公钥数值转换为PHP格式公钥数值:\n".$rsaUtil->Java2PhpRSAPublicKey($java_rsa_public_key);

$chargeObj = new PayExample();
// 代付下单
$chargeObj->payOrderCreate();
// 代付订单查询
//$chargeObj->payOrderQuery();
// 代付余额查询
//$chargeObj->payBalanceQuery();

<?php

namespace yy\config;

class SignConfig
{
    //商户自己的私钥[公钥通过登录支付平台支付平台进行配置，私钥设置到下面的变量中]
    //样例 见 merchant_rsa_private_key.pem
    private static $privateKeyPath;

    //支付平台支付平台提供给商户的SecretKey，登录支付平台支付平台查看
    private static $secretKey;

    //支付平台支付平台提供给商户的公钥，响应结果验签使用，登录支付平台支付平台查看,把它保存到一个pem文件中
    //样例 见 yy_rsa_public_key.pem
    private static $yhbPublicKeyPath;

    /**
     * @return mixed
     */
    public static function getPrivateKeyPath()
    {
        return self::$privateKeyPath;
    }

    /**
     * @param mixed $privateKeyPath
     */
    public static function setPrivateKeyPath($privateKeyPath)
    {
        self::$privateKeyPath = $privateKeyPath;
    }

    /**
     * @return mixed
     */
    public static function getSecretKey()
    {
        return self::$secretKey;
    }

    /**
     * @param mixed $secretKey
     */
    public static function setSecretKey($secretKey)
    {
        self::$secretKey = $secretKey;
    }

    /**
     * @return mixed
     */
    public static function getYhbPublicKeyPath()
    {
        return self::$yhbPublicKeyPath;
    }

    /**
     * @param mixed $yhbPublicKeyPath
     */
    public static function setYhbPublicKeyPath($yhbPublicKeyPath)
    {
        self::$yhbPublicKeyPath = $yhbPublicKeyPath;
    }

}
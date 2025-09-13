<?php

namespace yy\sign;

use yy\config\SignConfig;

class RSAUtil
{
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
}




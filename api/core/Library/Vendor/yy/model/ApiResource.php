<?php
namespace yy\model;

use yy\config\SignConfig;
use yy\exception\AuthorizationException;
use yy\exception\InvalidRequestException;
use yy\util\HttpCurlUtil;

class ApiResource extends YY
{
    protected static function _request($url = null, $params = null,$rpsResultVerifySign=true)
    {
        self::_validateParams();
        $_http = new HttpCurlUtil();
        if (empty($params)){
            return $_http -> get($rpsResultVerifySign,$url, null, 60);
        }else {
            return $_http -> post($rpsResultVerifySign,$url, $params, 60);
        }
    }

    private static function _validateParams()
    {
        if (empty(SignConfig::getSecretKey())){
            throw new AuthorizationException("Secret key can not be blank.");
        }

        if (empty(SignConfig::getPrivateKeyPath())){
            throw new InvalidRequestException("The Path of RSA Private Key can not be blank.");
        }

        if (empty(SignConfig::getYhbPublicKeyPath())){
            throw new AuthorizationException("The Path of yy Public Key can not be blank.");
        }
    }
}
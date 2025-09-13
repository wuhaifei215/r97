<?php

if (!function_exists('curl_init')) {
    throw new Exception('yy needs the CURL PHP extension.');
}
if (!function_exists('json_decode')) {
    throw new Exception('yy needs the JSON PHP extension.');
}

//配置
require(dirname(__FILE__) . '/config/PayConfig.php');
require(dirname(__FILE__) . '/config/SignConfig.php');

//异常
require(dirname(__FILE__) . '/exception/YYException.php');
require(dirname(__FILE__) . '/exception/AuthorizationException.php');
require(dirname(__FILE__) . '/exception/InvalidRequestException.php');
require(dirname(__FILE__) . '/exception/InvalidResponseException.php');

//model
require(dirname(__FILE__) . '/model/YY.php');
require(dirname(__FILE__) . '/model/ApiResource.php');

//签名和验签
require(dirname(__FILE__) . '/sign/RSAUtil.php');

//Util
require(dirname(__FILE__) . '/util/HttpCurlUtil.php');
require(dirname(__FILE__) . '/util/YYUtil.php');








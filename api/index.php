<?php
//$serverMainIp = $_SERVER['SERVER_ADDR'];
//echo "服务器的主IP地址是: " . $serverMainIp;
//die;

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, "https://www.baidu.com");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// 指定CA证书文件路径
curl_setopt($ch, CURLOPT_CAINFO, "./cert/cacert.pem");

$response = curl_exec($ch);
if (curl_errno($ch)) {
    echo 'Curl error: ' . curl_error($ch);
}

curl_close($ch);

echo $response;
die;

// 检测PHP环境
if (version_compare(PHP_VERSION, '5.4.0', '<')) {
    die('require PHP > 5.4.0 !');
}
    
// 定义应用目录
define('APP_PATH', './Application/');
/**
 * 系统调试设置
 * 项目正式部署后请设置为false
 */
define('APP_DEBUG', true);

/*
 * 缓存目录设置
 * 此目录必须可写，建议移动到非WEB目录
 */
define ( 'RUNTIME_PATH', './Runtime/' );

//系统安装及开发模式检测
if (is_file(APP_PATH. 'Common/Conf/install.lock') === false) {
    define ( 'BIND_MODULE','Install');
}

// 引入ThinkPHP入口文件
require './core/ThinkPHP.php';
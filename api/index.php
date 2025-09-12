<?php
// 诊断证书链
$certPath = '/www/wwwroot/r97/api/cert/ecomovi/in/ECOMOVI_50.crt';

// 检查证书内容
$certContent = file_get_contents($certPath);
echo "证书内容:\n";
echo $certContent;
echo "\n\n";

// 使用openssl验证证书
$output = [];
$returnCode = 0;
exec("openssl verify -CAfile /www/wwwroot/r97/api/cert/cacert.pem " . escapeshellarg($certPath), $output, $returnCode);

echo "OpenSSL验证结果:\n";
print_r($output);
echo "返回代码: " . $returnCode . "\n";

// 检查证书详细信息
exec("openssl x509 -in " . escapeshellarg($certPath) . " -text -noout", $certInfo);
echo "\n证书详细信息:\n";
print_r($certInfo);
?>

<?php
//$serverMainIp = $_SERVER['SERVER_ADDR'];
//echo "服务器的主IP地址是: " . $serverMainIp;
//die;


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
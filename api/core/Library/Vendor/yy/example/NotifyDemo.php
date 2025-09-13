<?php
namespace yy\example;
use yy\config\SignConfig;
use yy\sign\RSAUtil;

require ("../init.php");

// 平台异步通知的请求方式是POST 参数内容格式是：application/json;charset=UTF-8

// 获取平台的签名
$sign = $_SERVER["HTTP_SIGN"];
//print_r ("平台签名:".$sign);
// 获取平台请求的body
$body = file_get_contents('php://input');
//print_r("平台body内容:".$body);

// 初始化平台公钥信息
SignConfig::setYhbPublicKeyPath("yy_rsa_public_key.pem");
// 开始验签
if(RSAUtil::verify($body,$sign)){ // 验签成功
    // 解析body内容，开始业务处理
    $jsonBody = json_decode($body);
    $jsonData =$jsonBody->{'data'};
    // 一定要判断商户订单号是不是存在
    $merchantOrderNo = $jsonData->{'merchant_order_no'};// 商户订单号
    // 注意 一定要根据订单状态 判断代付结果
    $status = $jsonData->{'status'};// 订单状态 SUCCESS 成功 FAIL 失败
    if (strcmp('SUCCESS',$status)==0){ // 代付成功
        // 处理业务逻辑
    } elseif (strcmp('FAIL',$status)==0){ // 代付失败
        // 处理业务逻辑
    }
    // 注意 一定要给平台返回小写的 success 字符串，否则平台会通知17次
    echo "success";
}else{
    print_r("签名验证失败");
}







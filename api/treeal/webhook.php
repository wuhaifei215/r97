<?php
// /api/notify.php   （或任意可以直接被访问的路径）

// 1. 强制模块为 Pay
$_GET['g'] = 'Pay';

// 2. 定义 Application 路径
if (!defined('APP_PATH')) {
    define('APP_PATH', dirname(__DIR__) . '/Application/');
}

// 3. 加载 ThinkPHP
require dirname(__DIR__) . '/core/ThinkPHP.php';

// 4. 调用控制器方法
R('Pay/TreealPay/notifyurl');   // 或者
// $c = A('Pay/TreealPay'); $c->notifyurl();

exit;

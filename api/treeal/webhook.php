<?php
/**
 *  /api/notify.php   （或放在任意可直接访问的目录下）
 *
 *  目的：外部（如支付平台）回调时直接执行 Pay 模块的 TreealPayController::notifyurl()
 *  说明：在加载 ThinkPHP 之前强制把模块设为 Pay，并手动导入 Controller
 */

/* -------------------------------------------------
   1️⃣ 先把模块锁定为 Pay（必须在加载框架之前执行）
   ------------------------------------------------- */
if (!defined('MODULE_NAME')) {
    define('MODULE_NAME', 'Pay');   // 防止入口文件把默认模块改回 Home
}
$_GET['g']     = 'Pay';             // 让入口代码看到请求的模块是 Pay
$_REQUEST['g'] = 'Pay';

/* -------------------------------------------------
   2️⃣ 定义 Application 目录的完整路径
   ------------------------------------------------- */
if (!defined('APP_PATH')) {
    // 本文件假设在站点根目录的子目录 /api/
    // dirname(__DIR__) 返回站点根目录，例如 /var/www/html
    define('APP_PATH', dirname(__DIR__) . '/Application/');
}

/* -------------------------------------------------
   3️⃣ 加载 ThinkPHP 正确的入口文件
   ------------------------------------------------- */
// 下面两行任选其一，取决于你的框架实际所在位置
//require dirname(__DIR__) . '/ThinkPHP/ThinkPHP.php';   // 官方默认路径
 require dirname(__DIR__) . '/core/ThinkPHP.php';    // 如果框架在 core/ 里，打开此行

/* -------------------------------------------------
   4️⃣ 手动导入并实例化 TreealPayController（不走路由回退）
   ------------------------------------------------- */
import('Pay.Controller.TreealPayController');   // 导入  Application/Pay/Controller/TreealPayController.class.php
$controller = new TreealPayController();       // 实例化（类名必须是 TreealPayController）
$controller->notifyurl();                       // 直接执行业务方法

/* -------------------------------------------------
   5️⃣ 结束脚本，防止 ThinkPHP 再渲染默认模板
   ------------------------------------------------- */
exit;

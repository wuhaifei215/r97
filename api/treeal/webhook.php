<?php
/*-------------------------------------------------
 1️⃣ 定义 Application 目录（指向 /Application/）
 -------------------------------------------------*/
if (!defined('APP_PATH')) {
    // 本文件在 /api/（或任意子目录），Application 与此目录同层
    define('APP_PATH', dirname(__DIR__) . '/Application/');
}

/*-------------------------------------------------
 2️⃣ 加载 ThinkPHP 核心（让 Autoload、import 能工作）
 -------------------------------------------------*/
require dirname(__DIR__) . '/core/ThinkPHP.php';

/*-------------------------------------------------
 3️⃣ 手动导入目标 Action 类
    import() 使用 ThinkPHP 的类映射规则把文件 include 进来
 -------------------------------------------------*/
import('Pay.Controller.TreealPayController');   // 对应 /Application/Pay/Action/TreealPayAction.class.php

/*-------------------------------------------------
 4️⃣ 实例化并调用 notifyurl 方法
 -------------------------------------------------*/
$controller = new TreealPayController();   // 类名必须与文件里定义的类名一致
$controller->notifyurl();              // 直接执行业务代码

/*-------------------------------------------------
 5️⃣ 结束脚本
 -------------------------------------------------*/
exit;

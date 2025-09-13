<?php

namespace yy\config;


class PayConfig
{
    //支付平台支付平台生产服务器地址
    // 需要替换为实际的域名地址
    public static $API_BASE_URL = "替换支付系统域名地址/merchant-api/";

    //编码集
    public static $CHARSET = "UTF-8";

    //请求method
    // 代付下单
    public static $PAY_ORDER_CREATE = "v1/single/pay/create";
    // 代付订单查询
    public static $PAY_ORDER_QUERY = "v1/single/pay/query";
    // 代付可用余额查询
    public static $PAY_BALANCE_QUERY = "v1/single/pay/balance";

    //SDK版本
    public static  $SDK_VERSION = "1.0.0";
}
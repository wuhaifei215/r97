<?php 
	return array(
		'WEB_TITLE' => 'YUN国际支付',
		'DOMAIN' => 'yunadmin.r97pay.com',
		'MODULE_ALLOW_LIST'   => array('Home','User','sysadmin','Install', 'Weixin','Pay','Cashier','Agent','Payment','Cli'),
		'URL_MODULE_MAP'  => array('sysadmin'=>'admin', 'agent'=>'user', 'user'=>'user'),
		'LOGINNAME' => 'user',
		'HOUTAINAME' => 'sysadmin',
		'API_DOMAIN' => 'api.r97pay.com',
        'NOTIFY_DOMAIN' => 'napi.r97pay.com',
        'LOG_API_URL' => 'https://log.r97pay.com',
    );
?>
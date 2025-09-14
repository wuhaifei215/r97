<?php
// 数据库配置
return array(
    'DB_TYPE'   => 'mysql',       // 数据库类型
    'DB_HOST'   => '172.31.9.0',       // 服务器地址
    'DB_NAME'   => 'rlogmarsdb',       // 数据库名
    'DB_USER'   => 'rlogmarsdb',       // 用户名
    'DB_PWD'    => 'BAzT4MJ6PEJBi3KG',        // 密码
    'DB_PORT'   => '3306',       // 端口
    'DB_PREFIX' => 'custom_',     // 数据库表前缀
    
    // 'DB_DEPLOY_TYPE'        =>  1, // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
    // 'DB_RW_SEPARATE'        =>  true,       // 数据库读写是否分离 主从式有效
    // 'DB_MASTER_NUM'         =>  1, // 读写分离后 主服务器数量
    // 'DB_SLAVE_NO'           =>  1, // 指定从服务器序号

    
    'REDIS_HOST'   => '127.0.0.1',       // 服务器地址
    'REDIS_PORT'   => '52940',       // 端口
    'REDIS_PWD'   => 'R97!x@y#z99pay',       //密码
    'REDIS_SELECT' => '0',
);


<?php

namespace yy\util;


// 定义字符集
define('ALLCHAR', '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');

// 设置默认时区
date_default_timezone_set('Asia/Shanghai');

class YYUtil
{
    /**
     * 获取当前时间的毫秒数
     * 
     * @return float 
     */
    public static function getMicroTime(){
        list($t1, $t2) = explode(' ', microtime());
        return (float)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
    }


    /** 获取当前格式YmdHis的日期字符串
     * @return false|string
     */
    public static function getCurrentTimeYmdStr(){
        return date("YmdHis");
    }

    /**
     * 生成唯一id[32位]
     * @param string $namespace
     * @return string
     */
    public static function createUniqid($namespace = ''){
        static $uniqid = '';
        $uid = uniqid("", true);
        $data = $namespace;
        $data .= isset($_SERVER['REQUEST_TIME']) ? $_SERVER['REQUEST_TIME'] : "";
        $data .= isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : "";
        $data .= isset($_SERVER['LOCAL_ADDR']) ? $_SERVER['LOCAL_ADDR'] : "";
        $data .= isset($_SERVER['LOCAL_PORT']) ? $_SERVER['LOCAL_PORT'] : "";
        $data .= isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : "";
        $data .= isset($_SERVER['REMOTE_PORT']) ? $_SERVER['REMOTE_PORT'] : "";
        $hash = strtoupper(hash('ripemd128', $uid . $uniqid . md5($data)));
        $uniqid = substr($hash,  0,  8) .
            substr($hash,  8,  4) .
            substr($hash, 12,  4) .
            substr($hash, 16,  4) .
            substr($hash, 20, 12);
        return $uniqid;
    }

    /**
     * 返回一个定长的随机字符串(只包含大小写字母、数字)
     *
     * @param int $length 随机字符串长度
     * @return string 随机字符串
     */
    public static function generateString($length) {
        $str = '';
        $allCharLength = strlen(ALLCHAR);
        for ($i = 0; $i < $length; $i++) {
            // 使用 mt_rand 替代 rand 获取更好的随机性
            $str .= ALLCHAR[mt_rand(0, $allCharLength - 1)];
        }
        return $str;
    }
}
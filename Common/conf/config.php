<?php
//环境设置

ini_set("magic_quotes_runtime",0);
date_default_timezone_set('Asia/Shanghai');

//过滤全局变量
$defined_vars = get_defined_vars();
foreach ($defined_vars as $key => $val) {
    if ( !in_array($key, array('_GET', '_POST', '_COOKIE', '_FILES', 'GLOBALS', '_SERVER')) ) {
        ${$key} = '';
        unset(${$key});
    }
}
unset($defined_vars);


header("Content-type: text/html; charset=utf-8");


////redis保存session
ini_set("session.save_handler","redis");
ini_set("session.save_path","tcp://127.0.0.1:6379?auth=[PASS_WORD]");
//\\

ob_start();
session_start();

/*
 *
#nginx 伪静态重写 例子:
#http://smrt.host.com/Api/wxpay/result/a-1/b-2/c-3.html
#http://smrt.host.com/Api/?/wxpay/result/a-1/b-2/c-3


        location ~ ^\/(\w+)\/(\w+)\/(\w+)$ {
            rewrite ^\/(\w+)\/(\w+)\/(\w+)$  /$1/?/$2/$3/ last;
        }
        location ~ ^\/(\w+)\/(\w+)\/(\w+)\/(|\w+[-=_\+]\w+\/|\w+[-=_\+]\w+)+(|\.\w+)$ {
            rewrite ^\/(\w+)\/(\w+)\/(\w+)\/(|\w+[-=_\+]\w+\/|\w+[-=_\+]\w+)+(|\.\w+)$  /$1/?/$2/$3/$4/ last;
        }

*/



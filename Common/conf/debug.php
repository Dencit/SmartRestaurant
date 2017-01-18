<?php
/* Created by User: soma Worker: 陈鸿扬 Date: 2016/5/12 Time: 10:43 */
//调试信息开关
ini_set("display_errors","On");
error_reporting(E_ALL);

//框架调试开关
define('FRAM_DEBUG','on');


//微信测试地址开关
//DEBUG_PREFIX
define('DEBUG_URL','pub');

switch(DEBUG_URL){
    case 'pub' :

        //http_base
        define('HTTP_BASE','http://smrt.host.com');
        //api_url
        define('API_URL','http://smrt.host.com/Api/?/weixinco');
        //redirect_uri
        define('OAUTH2_URI','http://smrt.host.com/Api/?/weixinco/oauth');
        //access_token
        define('ACCESS_TOKEN','http://smrt.host.com/Api/?/weixinco/access_token');

        break;
    case 'test':

        //http_base
        define('HTTP_BASE','http://smrt.host.test');
        //api_url
        define('API_URL','http://smrt.host.test/Api/?/weixinco');
        //redirect_uri
        define('OAUTH2_URI','http://smrt.host.test/Api/?/weixinco/oauth');
        //access_token
        define('ACCESS_TOKEN','http://smrt.host.test/Api/?/weixinco/access_token');

        break;
}




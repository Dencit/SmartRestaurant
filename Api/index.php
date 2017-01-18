<?php
/* Created by User: soma Worker:陈鸿扬  Date: 16/10/28  Time: 09:26 */

require_once('../Common/app.php');

//__自定义部分__

//接口类
require_once(LIB_APIS.'/weiApi.class.php');
require_once(LIB_APIS.'/weicoApi.class.php');

//作者邮箱标记
define("Author","632112883@qq.com");

//认证号//服务号
//AppID
//define("AppID","wxa544f4ffe0ce6025");
//AppSecret
//define("AppSecret","037f427292c336bc81bac0f1c84d59cf");

//企业号
//corpId
define("corpId","wx0dc37f4ed9d9682f");
//corpSecret
define("corpSecret","TXzseNOj5zSlgyyFm-zbNyBJld4UbpCAgWBQ64BDzsxLxtbjC7rHy3e9wkUVz0Wy");


//企业号 回调参数
define("token","smrt");
define("encodingAesKey","Ru37BRJwMWvUH6jOfRUi9DbfRchv7FtEf62DkVKbrwY");



//-__自定义部分__



//__框架部分__


define('PATH_URI','Rewrite');//路由规则
define('ROOT_PROJECT','Api');//当前项目主目录 controler.class.php , model.class.php 调用
define('ROOT_CONTROLER','Controler');//二级目录//控制器目录//controler.class.php 调用
define('ROOT_MODELER','Modeler');//二级目录//数据模型目录//model.class.php 调用

$controler=new \controlers\controler();
$controler->uri('Rewrite');//开始侦听url路由参数,加载 controler 虚拟页面

//-__框架部分__


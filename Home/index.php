<?php
/* Created by User: soma Worker: 陈鸿扬  Date: 16/7/28  Time: 09:27 */

require_once('../Common/app.php');

require_once(LIB_COMMONS.'/probability.class.php');//概率工具组
require_once(LIB_3RD.'/aliDayu.php');//大于短信类

define('PATH_URI','Path_Info');//路由规则
define('ROOT_PROJECT','Home');//当前项目主目录 controler.class.php , model.class.php 调用
define('ROOT_CONTROLER','Controler');//二级目录//控制器目录//controler.class.php 调用
define('ROOT_MODELER','Modeler');//二级目录//数据模型目录//model.class.php 调用

define('AGENT_ID','1');//企业号应用ID

$controler=new \controlers\controler();
$controler->uri('Path_Info');//开始侦听url路由参数,加载 controler 虚拟页面







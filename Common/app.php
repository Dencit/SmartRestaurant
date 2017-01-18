<?php

//相对于 本文件的路径
define('ROOT',str_replace('\\','/',realpath(dirname(__FILE__).'/../')) );
define('WEB_ROOT',str_replace('\\','/',realpath(dirname(__FILE__).'/../../') ) );
//一级目录
define('COMMON',ROOT."/Common");
define('ADMIN',ROOT."/Admin");
define('MODEL',ROOT."/Modeler");
define('CACHE',ROOT."/Cache");
define('LIBRARY',ROOT."/Library");
define('STATIC',ROOT."/Static");
//二级目录 - 公用设置
define('CONF',COMMON."/conf");
//二级目录 - 总库
define('LIB_3RD',LIBRARY."/Thirds");
define('LIB_COMMONS',LIBRARY."/Commons");
define('LIB_CONTROLERS',LIBRARY."/Controlers");
define('LIB_DEBUGS',LIBRARY."/Debugs");
define('LIB_FUNCTIONS',LIBRARY."/Functions");
define('LIB_HTTPS',LIBRARY."/Https");
define('LIB_APIS',LIBRARY."/Apis");
define('LIB_NOSQL',LIBRARY."/NoSql");
define('LIB_MODELS',LIBRARY."/Modelers");
define('LIB_VIEWS',LIBRARY."/Views");
//

//公用设置
require_once(CONF.'/debug.php');
require_once(CONF.'/config.php');
require_once(CONF.'/database.php');//数据库账号
//工具类
require_once(LIB_COMMONS.'/rootProj.class.php');
require_once(LIB_COMMONS.'/emoji.php');
require_once(LIB_COMMONS.'/jump.php');
require_once(LIB_COMMONS.'/tool.php');
require_once(LIB_COMMONS.'/inputCheck.php');
require_once(LIB_COMMONS.'/date.php');
require_once(LIB_COMMONS.'/encryp.php');
require_once(LIB_COMMONS.'/forPage.php');
require_once(LIB_COMMONS.'/imgMaker.php');


//控制器类
require_once(LIB_CONTROLERS.'/baseControler.class.php');
require_once(LIB_CONTROLERS.'/controler.class.php');
require_once(LIB_CONTROLERS.'/urlSerial.class.php');
require_once(LIB_CONTROLERS.'/urlRoute.class.php');
//测试类
require_once(LIB_DEBUGS.'/frameDebug.class.php');
//nosql类
require_once(LIB_NOSQL.'/redis.class.php');
//通讯类
require_once(LIB_HTTPS.'/authApi.class.php');
require_once(LIB_HTTPS.'/weiApi.class.php');
require_once(LIB_HTTPS.'/authcoApi.class.php');
require_once(LIB_HTTPS.'/weicoApi.class.php');
//数据库操作相关
require_once(LIB_MODELS.'/model.class.php');
require_once(LIB_MODELS.'/wpDb.class.php');//绝对路径加载//相对路径容易出错
require_once(LIB_MODELS.'/baseModel.class.php');
require_once(LIB_MODELS.'/homeModel.class.php');
//视图类
require_once(LIB_VIEWS.'/view.class.php');

////数据表名全局变量
//PREFIX
define('PREFIX','smrt_');
//all databases name
define('USR',PREFIX.'user_wx');
define('USR_INFO',PREFIX.'user_info');
define('FOOD',PREFIX.'food');
define('ORDER',PREFIX.'user_order');
define('PAYED',PREFIX.'user_payed');
define('ACCOUNT',PREFIX.'user_account');
define('RATED',PREFIX.'user_rated');
define('BOOK',PREFIX.'user_book');
define('RECHARGE',PREFIX.'user_recharge');

define('ADM',PREFIX.'admin');
define('AUTH',PREFIX.'auth');
define('LOG',PREFIX.'log');
define('UPLOAD',PREFIX.'upload');



//时间
define('TIME',time());
//开始&结束时间
require_once(LIB_COMMONS.'/timeInterval.php');
$timeVal=timeInterval::fileGetVal(CACHE.'/timeInterval.php');
//开始&结束日期
define('START_DATE',$timeVal[0]);
define('END_DATE',$timeVal[1]);
//开始&结束时间戳
$startTime=strtotime($timeVal[0]);
$endTime=strtotime($timeVal[1]);
define('START_TIME',$startTime);
define('END_TIME',$endTime);







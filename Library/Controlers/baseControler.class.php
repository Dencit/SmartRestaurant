<?php
/* Created by User: soma Worker: 陈鸿扬 Date: 16/7/28  Time: 09:21 */

namespace Controlers;

use \Commons\tool as tool;//工具类
use \Commons\jump as jump;//跳转类

use \Controlers\urlRoute as urlRoute;
use \Controlers\urlSerial as I;

class baseControler {

    //static $serial;//序列类
    static $route;//路由类

    function __construct(){

        //echo 'baseControler OK'.'<br/>';

        //self::$serial=new urlSerial();
        self::$route=new urlRoute();

        //echo tool::jsonExit(array("uid"=>'1')) ;exit;

    }

    function initial(){

        echo 'initial OK'.'<br/>';

    }

    function homeDesc(){

        //管理员登录状态

        $glob_usr=@tool::get_session( array('glob_usr') )->glob_usr;
        //var_dump($glob_usr);//exit;

        $sign=@$glob_usr->sign;
        $uid=@$glob_usr->uid;
        $type=@$glob_usr->type;
        $ad_type=@$glob_usr->ad_type;


        $uri=urlRoute::get();//var_dump($uri);exit;

       /* if(!$uid&&$uri[1]=='pushnews'){
            $aop=tool::isSetRe( @I::have('aop') );//早餐 午餐 类型判断
            $date=tool::isSetRe( @I::have('date') );
            //jump::alertTo('身份过期,请重新登录','/Home/?/weixin/index/act-pushNews/aop-'.$aop.'/date-'.$date.'/');
            jump::head('/Home/?/weixin/index/act-pushNews/aop-'.$aop.'/date-'.$date.'/');
            exit;
        }*/

        if(!$uid){
            switch($uri[1]){
                default:
                    //jump::alertTo('身份过期,请重新登录','/Home/?/weixin/index/'); exit;
                    jump::head('/Home/?/weixin/index/'); break;
                    break;
                case 'perInfo':
                    tool::mk_session(array('jumpTo'=>'perInfo'));//
                    jump::head('/Home/?/weixin/index/');
                    break;
                case 'pushnews':
                    $aop=tool::isSetRe( @I::have('aop') );//早餐 午餐 类型判断
                    $date=tool::isSetRe( @I::have('date') );
                    jump::head('/Home/?/weixin/index/act-pushNews/aop-'.$aop.'/date-'.$date.'/');
                    break;
            }
        }

        if($sign!=session_id()){
            tool::get_session( array('glob_usr'),1 );
            session_regenerate_id(true);
            jump::alertTo('身份过期,请重新登录','/Home/?/weixin/index/'); exit;
        }

        return $glob_usr;

        //\\
    }

    function check_abort()
    {
        //if ( connection_aborted() ) {session_regenerate_id(true);}
    }


    function adminDesc(){

        //管理员登录状态

        $uri=urlRoute::get();

        $glob_usr=@tool::get_session( array('glob_usr') )->glob_usr;

        //register_shutdown_function( array($this, "check_abort") );

        //var_dump($glob_usr->sign);//exit;
        //var_dump(session_id());

        //exit;

        $sign=@$glob_usr->sign;
        $uid=@$glob_usr->uid;
        $type=@$glob_usr->type;
        $ad_type=@$glob_usr->ad_type;

        if($sign!=session_id()){
            tool::get_session( array('glob_usr'),1 );
            session_regenerate_id(true);
            jump::alertTo('身份过期,请重新登录','/Admin/?/login/in/'); exit;
        }

        if(!$uid){ jump::alertTo('身份过期,请重新登录','/Admin/?/login/in/'); exit; }

        if($ad_type=='1'){
            tool::get_session( array('glob_usr'),1 );
            jump::alert('授权成功,您可在企业号应用中点餐!'); exit;
        }

        //var_dump($uri);

        if($ad_type==2&&$uri[1]!='scangun'){

            jump::alertTo('收银员 无权限访问!','back');
        }

        return $glob_usr;

        //\\
    }


} 
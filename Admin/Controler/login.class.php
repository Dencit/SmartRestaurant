<?php
/* Created by User: soma Worker:陈鸿扬  Date: 16/11/23  Time: 10:55 */

namespace Controlers;

use \Commons\tool as tool;//工具类
use \Commons\jump as jump;

use \Controlers\urlSerial as I;//序列类
use \Modelers\model as M;//model装载器
use \Views\view as V;//视图类

use \Https\weicoApi as weicoApi;//微信功能接口
use \Https\authcoApi as authcoApi;//微信授权接口

class login extends baseControler {

    private static $M;

    function __construct(){
        ////model初始化
        self::$M=M::init('login');
        //\\

    }

    function in(){

        //判断微信用户登录状态
        //tool::get_session( array('openid','access_token'),1 ); exit('clear');//清除记号//测试用
        $glob_usr=tool::get_session( array('glob_usr') )->glob_usr;
        //var_dump($get_session->openid);exit;//
        if(!$glob_usr->openId||!$glob_usr->access_token){
            authcoApi::usrCodeAuth(HTTP_BASE.'/Admin/?/login/auth/');
            exit;
        }
        //\\

        if($glob_usr->ad_type==2){ header('location:/Admin/?/book/scangun/'); exit;}

        header('location:?/base/welcome/');exit;
    }

    function auth(){


        $auth_code=tool::is_Get('auth_code');
        $access_token=tool::is_Get('access_token');
        //var_dump($auth_code);var_dump($access_token);exit;//

        if(empty($auth_code)&&empty($access_token)){
            jump::alertTo('授权码过期,请重试！','?/login/in/');//exit('fail auth_code & access_token!');//
        }

        $usrCodeInfo=authcoApi::usrCodeInfo($auth_code,$access_token);
        //var_dump($usrCodeInfo->redirect_login_info->login_ticket);exit;//

        if($usrCodeInfo=='code_fail'){ jump::alertTo('授权码过期！','?/login/in/'); exit; }
        else if($usrCodeInfo=='user_fail'){ jump::alertTo('企业通讯录无此用户，请联系管理员！','?/login/in/'); exit; }


        $user2openid=weicoApi::convert_to_openid($access_token,array("userid"=>$usrCodeInfo->user_info->userid));
        //var_dump($user2openid);exit;//企业号的openid

        //$user2agent_openid=weicoApi::convert_to_openid($access_token,array("userid"=>$usrCodeInfo->user_info->userid,"agentid"=>AGENT_ID));
        //var_dump($user2openid);exit;//企业号应用的openid+appid

        //$open2userid=weicoApi::convert_to_userid($access_token,array("openid"=>$user2openid->openid));
        //var_dump($open2userid);exit;//

        $userInfoData=weicoApi::userInfo($usrCodeInfo,$user2openid);
        $userInfoData['access_token']=$access_token;
        //var_dump($userInfoData);//

        ////收集接口信息,更新数据,生成用户标记
        self::$M->addInfoData($usrCodeInfo,$userInfoData,$user2openid,$access_token);
        //\\

        $glob_usr=tool::get_session( array('glob_usr') )->glob_usr;
        //var_dump($glob_usr);exit;//

        if($glob_usr->openId && $glob_usr->access_token){
            header('location:?/login/in/');
        }

    }

    function out(){

        //登出

        session_regenerate_id(true);

        tool::get_session( array('glob_usr'),1 );

        tool::jsonResult('login_out','0','','?/login/in');

    }






} 
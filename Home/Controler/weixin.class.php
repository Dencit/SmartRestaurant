<?php
/* Created by User: soma Worker: 陈鸿扬 Date: 16/8/1  Time: 15:42 */

namespace Controlers;

use \Commons\tool as tool;//工具类
use \Commons\jump as jump;
use \Controlers\urlSerial as I;

use \Modelers\model as model;//model装载器

use \Https\weiApi as weiApi;
use \Https\weicoApi as weicoApi;


class weixin extends baseControler {

    private static $u_type;
    private static $ts_type;
    private static $uid_type;

    private static $userId;
    private static $deviceId;
    private static $openId;
    private static $access_token;
    private static $appId;

    function __construct(){
        new parent;

        //Home/?/weixin/test/u=[PASS_WORD]/ts=1/uid=1/
        self::$u_type=$u_type=tool::isSetRe( I::have('u') );
        self::$ts_type=$ts_type=tool::isSetRe( I::have('ts') );
        self::$uid_type=$uid_type=tool::isSetRe( I::have('uid') );//序列引用
        //var_dump($u_type); var_dump($ts_type); var_dump($uid_type);exit;//

        //if($u_type||$ts_type||$uid_type){exit('ok');}

        $glob_usr=@tool::get_session( array('glob_usr') )->glob_usr;
        //var_dump($glob_usr);//exit;
        self::$userId = tool::isSetRe(@$glob_usr->userId);
        self::$deviceId = tool::isSetRe(@$glob_usr->deviceId);
        self::$openId = tool::isSetRe(@$glob_usr->openId);
        self::$access_token = tool::isSetRe(@$glob_usr->access_token);
        self::$appId = tool::isSetRe(@$glob_usr->appId);

        model::init('weixin');//一个 controler 对应一个 同名 modeler
        model::set();//加载 index modeler 可同时传参给 构造函数

    }

    function test()//伪装验证
    {

        $MS=model::set();

        $u_type=self::$u_type;
        $ts_type=self::$ts_type;
        $uid_type=self::$uid_type;//序列引用
        //echo $u_type.$ts_type.$uid_type;//

        $hint['noData']=" 没有 access_token 或找不到当前'uid'. 如果没有 access_token请在微信登录一次非测试页，再回来用PC访问该测试页. ";
        $hint['noDataEn']="no token for this ' uid ' or no found this 'uid'. if no token, please Login on WeiXinApp then Comeback to Visit on PC";

        if($u_type=='[PASS_WORD]' && $ts_type!='' && $uid_type!='')
        {

            switch($ts_type){
                default:
                    break;
                case 0 :

                    tool::mk_session(array('glob_usr'),1);
                    session_unset();session_destroy();//
                    tool::jsonExit(array("state"=>'noDebug',"SESSION"=>'unset'));
                    break;

                case 1 :

                    $whereArray['uid']=$uid_type;
                    $user=$MS->rowSelect(USR,'*',$whereArray);
                    //var_dump($user);exit;//

                    if($user){

                        $glob_usr=new \stdClass();
                        $glob_usr->sign=session_id();//设置服务器的sign
                        $glob_usr->uid = $user->uid;
                        $glob_usr->userId = $user->userid;
                        $glob_usr->deviceId = $user->deviceid;
                        $glob_usr->openId= $user->openid;
                        $glob_usr->access_token= $user->access_token;
                        $glob_usr->appId = $user->appid;
                        tool::mk_session( array('glob_usr' => $glob_usr) );
                        //var_dump( tool::get_session('glob_usr') );exit;//

                        jump::head("?/account/login");//成功跳转index

                    }else{

                        $orderArray['uid']='ASC';
                        //$orderArray['limit']='0,5';//
                        $selectArray=array('uid','name');
                        $usrTest=$MS->resultSelect(USR,$selectArray,'-',$orderArray);

                        //var_dump($usrTest);exit;//

                        echo "| UID || NICK_NAME |"."<br/>";
                        foreach($usrTest as $k=>$v){
                            echo "| ".$usrTest[$k]->uid." || ".$usrTest[$k]->name." |"."<br/>";
                        }
                        echo $hint['noData']."<br/>";
                        tool::jsonExit($hint['noDataEn']);

                    }

                    //tool::jsonExit(array("state"=>'debug'));
                    break;
            }

        }


    }

    function index(){

        //weicoApi::usrAuth(HTTP_BASE."/Home/?/weixin/auth/","snsapi_base");exit;//

        $order_no=tool::isSetRe( @I::have('order_no') );//侦听url中的order_no序列单元
        //var_dump($order_no);//exit;//

        $act=tool::isSetRe( @I::have('act') );//进入推送时 身份过期。
        $aop=tool::isSetRe( @I::have('aop') );//进入推送时 身份过期。
        $date=tool::isSetRe( @I::have('date') );//进入推送时 身份过期。
        //var_dump($act);exit;//

        $openId=self::$openId;
        $access_token=self::$access_token;
        //var_dump($openId);var_dump($access_token);exit;//

        //$openId=true;$access_token=true;//
        if(!$openId||!$access_token)//没有$openId和$access_token的情况
        {

            switch($order_no!=''){
                case true :
                    weicoApi::usrAuth(HTTP_BASE."/Home/?/weixin/auth/order_no-".$order_no."/","snsapi_base");
                    break;
            }

            switch ($act!=''){
                case true :
                    weicoApi::usrAuth(HTTP_BASE."/Home/?/weixin/auth/act-".$act."/aop-".$aop."/date-".$date."/","snsapi_base");
                    break;
            }

            weicoApi::usrAuth(HTTP_BASE."/Home/?/weixin/auth/","snsapi_base");
        }

        //var_dump($openId);exit('ok');//

        //var_dump($act);exit;//


        $jumpTo=tool::get_session('jumpTo');//
        if($jumpTo=='perInfo'){
            tool::get_session('jumpTo','1');
            jump::head("?/account/perInfo/");
        }

        if($act!=''){
            jump::head("?/info/pushNews/act-".$act."/aop-".$aop."/date-".$date."/");
        }

        if($order_no!='') {
            $_SESSION[PREFIX.'order_cashier_no']=$order_no;
            jump::head("?/account/cashier_login/");
        }
        else {
            unset($_SESSION[PREFIX.'order_cashier_no']);//清除收银员身份 扫码 缓存
            //jump::head("?/account/login/");
            jump::head("?/info/news/");
        }



    }

    function auth(){

        //var_dump($_GET);exit;//

        $userId = tool::isSetRe( $_GET['userId'] );
        $openId = tool::isSetRe( $_GET['openId'] );
        $access_token = tool::isSetRe( $_GET['access_token'] );
        //var_dump($userId);var_dump($deviceId);var_dump($openId);var_dump($access_token);var_dump($appId);exit;//

        //var_dump($appId);exit;

        if (!$openId || !$access_token){ exit('fail to get openid or access_token.'); }


        ////数据入库
        $MS = model::set();

        //获取通讯录用户补充信息
        $usrInfoAddGet=weicoApi::usrInfoAddGet($userId,$access_token);
        //var_dump($usrInfo);exit;//

        //拼装基础数据//入user_wx表
        $usrArray['userid'] = $userId;
        $usrArray['openid'] = $openId;
        $usrArray['access_token'] = $access_token;
        $usrInfoBase=weicoApi::usrInfoBase($usrArray,$usrInfoAddGet);
        //var_dump($usrInfoBase);exit;//
        //

        $departGet=weicoApi::departGet('1',$access_token);
        //print_r($departGet);//exit;//

        //拼装通讯录用户补充信息//入user_info表
        $infoData=weicoApi::usrInfoAdd($usrInfoAddGet,$departGet);
        //print_r($infoData);exit;//
        //

        $whereArray['openid'] = $openId;
        //var_dump($openId);exit;//

        //检查式新增微信用户数据
        $rowAddCheck=$MS->rowAddCheck(USR,'uid',$whereArray,$usrInfoBase);
        //var_dump($rowAddCheck);exit;//
        $user = $MS->rowSelect(USR, 'uid', $whereArray);//筛选

        switch($rowAddCheck){
            case 'insertOk'://用户无登记的情况

                //新建用户数据
                $infoData['uid']=$user->uid;
                $infoAddCheck = $MS->rowInsert(USR_INFO,$infoData);
                if (!$infoAddCheck) { exit ('$infoAddCheck fail!'); }

                //新建钱包数据
                $time = TIME;
                $accData['uid'] = $user->uid;
                $accData['time_recharge'] = $time;
                $accData['time_pay'] = $time;
                $accountAddCheck = $MS->rowInsert(ACCOUNT,$accData);
                if (!$accountAddCheck) { exit ('$accountAddCheck fail!'); }

                break;
            case 'updateOk'://用户有登记的情况

                //更新微信用户资料
                $userUpdate = $MS->rowAddCheck(USR,'uid',$whereArray,$usrInfoBase);//检测到数据重复 会跳过更新

                unset($whereArray);
                $whereArray['uid']=$user->uid;
                $infoUpdate = $MS->rowAddCheck(USR_INFO,'uid',$whereArray,$infoData);//检测到数据重复 会跳过更新

                break;
            default :
                //更新微信用户资料
                $userUpdate = $MS->rowAddCheck(USR,'uid',$whereArray,$usrInfoBase);//检测到数据重复 会跳过更新

                unset($whereArray);
                $whereArray['uid']=$user->uid;
                $infoUpdate = $MS->rowAddCheck(USR_INFO,'uid',$whereArray,$infoData);//检测到数据重复 会跳过更新

                break;
        }
        //\\

        //获取微信用户uid
        $user = $MS->rowSelect(USR, '*', $whereArray);

        $glob_usr=new \stdClass();
        $glob_usr->sign=session_id();//设置服务器的sign
        $glob_usr->uid = $user->uid;
        $glob_usr->userId = $userId;
        $glob_usr->openId= $openId;
        $glob_usr->access_token= $access_token;
        tool::mk_session( array('glob_usr' => $glob_usr) );
        //var_dump( tool::get_session('glob_usr') );exit;//

        $order_no=tool::isSetRe( @I::have('order_no') );//侦听url中的order_no序列单元
        //var_dump($order_no);exit;//

        $act=tool::isSetRe( @I::have('act') );
        $aop=tool::isSetRe( @I::have('aop') );
        $date=tool::isSetRe( @I::have('date') );
        //var_dump($date);exit;//


        if($order_no!=''){
            jump::head("./?/weixin/index/order_no-".$order_no);
        }
        if($act!=''){
            //exit("./?/weixin/index/act-".$act."/aop-".$aop."/date-".$date."/");//
            jump::head("./?/weixin/index/act-".$act."/aop-".$aop."/date-".$date."/");
        }else{
            jump::head("./?/weixin/index/");
        }

    }



} 
<?php
/* Created by User:soma Worker:陈鸿扬 Date: 16/8/15  Time: 09:18 */

namespace Controlers;

use \Commons\tool as tool;//工具类
use \Commons\jump as jump;//跳转类

use \Controlers\urlSerial as I;//序列类
use \Modelers\model as model;//model装载器
use \Views\view as V;//视图类
use \Commons\forPage as FP;//翻页类


use \Https\weicoApi as weicoApi;//微信功能接口
use \Https\authcoApi as authcoApi;//微信授权接口


class base extends baseControler {

    private static $adminDesc;//parent object

    private static $MS;

    private static $page;//数据库翻页
    private static $step;//翻页步进值
    private static $start;//翻页起始值

    private static $uAuth;

    function __construct(){

        //用户登录状态
        self::$adminDesc=self::adminDesc();
        //\\

        ////获取翻页值
        $page=I::have('p')-1;$page=$page>0?$page:'0';
        $step=30;
        $start=$step*$page;//$start=$start>0?$start:$step;
        self::$page=$page; self::$step=$step; self::$start=$start;
        //\\

        ////model初始化
        self::$MS=model::init('base');
        //\\

        ////
        $where['id/!=']='0';
        $uAuth=self::$MS->resultSelect(AUTH,'id,type',$where,'id asc');
        if(!$uAuth){return "\$uAuth fail!";}
        self::$uAuth=$uAuth;
        //\\
    }


    function welcome(){


        $welcomeSelect=self::$MS->welcomeSelect(self::$adminDesc->uid);
        if(!$welcomeSelect){ exit('$welcomeSelect fail'); }

        V::tamplate('index');

        $cArr['content']='change_welcome';
        V::asChangeArr($cArr);


        $sign['ad_type']=$welcomeSelect->ad_type;
        $sign['name']=$welcomeSelect->name;

        V::asSignArr($sign);

        V::show();//输出视图

    }


    function admin(){

        if(self::$adminDesc->ad_type!='a'){ jump::alertto('不是超级管理员,不能访问！','?/base/welcome/'); };

        V::tamplate('index');
        $cArr['content']='change_admin';
        V::asChangeArr($cArr);

        $base_url='?/base/admin';//基础链接

        $select='uid,name,sex,mobile,idcard,idcheck,depart,join,subsidy_type,type,ad_type';
        $where['ad_type']='a';
        $memberInfo=self::$MS->adminSelect(USR_INFO,$select,$where,'depart asc limit '.self::$start.','.self::$step);
        //var_dump($USR_INFO);exit;

        if(!$memberInfo){$memberInfo=array();$noneTip='没有了!';}
        else{$noneTip='';}

        V::forList('admin',$memberInfo);
        V::asSign('noneTip',$noneTip);


        ////翻页栏
        $list=self::$MS->resultSelect(USR_INFO,'uid',$where);
        if(!$list){ $countList=0; }else{ $countList=count( $list ); }
        //var_dump($countList);//

        FP::getPage($countList,I::have('p'),self::$step,$base_url);
        v::asSign('countList',FP::$countList);
        V::asSign('pages',FP::$pages);
        V::asSign('cur_page',FP::$cur_page);
        V::asSign('previous',FP::$previous);
        V::asSign('next',FP::$next);
        V::forList('plist',FP::$plist);
        //\\

        V::show();

    }


    function admin_edit_ax(){

        $rowId=tool::is_Post('rowId');

        $select='uid,name,sex,mobile,idcard,idcheck,depart,join,subsidy_type,type,ad_type';
        $where['uid']=$rowId;
        $rowSelect=self::$MS->rowSelect(USR_INFO,$select,$where);

        if($rowSelect){

            switch($rowSelect->idcheck){
                case '0' :$rowSelect->idcheck='未验证'; break;
                case '1' :$rowSelect->idcheck='已验证'; break;
            }

            tool::jsonResult($rowSelect,'0');

        }

    }

    function admin_submit_ax(){

        $rowId=tool::is_Post('uid');

        $data=tool::is_Post($_POST);
        unset($data['uid']);unset($data['idcheck']);//排除部分
        //var_dump($data);exit;//


        ////更新微企通讯录
        $infoUp=self::$MS->memberInfo($data,$rowId);
        $access_token=weicoApi::globeAccessToken();
        $post_array_data=weicoApi::usrInfoUpdateBase($infoUp);//组装信息
        $usrInfoUpdate=weicoApi::usrInfoUpdate($post_array_data,$access_token);
        //var_dump($usrInfoUpdate);exit;
        if($usrInfoUpdate->errmsg!='updated'){
            tool::jsonResult($_POST,'-1','企业通讯录更新失败！');
        }
        //\\


        $rowUpdate=self::$MS->rowUpdate(USR_INFO,$data,'uid/'.$rowId);
        if($rowUpdate){
            tool::jsonResult($_POST,'1','提交成功！','?/base/member');
        }

        tool::jsonResult($_POST,'0','无修改！');

    }


    function member(){

        V::tamplate('index');
        $cArr['content']='change_member';
        V::asChangeArr($cArr);


        ////搜索处理
        $search='';//搜索字段
        $where=[];//基础条件
        $base_url='?/base/member';//基础链接
        V::asSign('base_url',$base_url);

        $name=I::have('name') ;
        $type=I::have('type') ;
        $ad_type=I::have('ad_type') ;
        if($name){ $where['name/%%']=$name; $search.=DIRECTORY_SEPARATOR.'name-'.$name; }
        if($type){ $where['type']=$type; $search.=DIRECTORY_SEPARATOR.'type-'.$type; }
        if($ad_type){ $where['ad_type']=$ad_type; $search.=DIRECTORY_SEPARATOR.'ad_type-'.$ad_type; }
        //var_dump($where);exit;//

        $base_url.=$search;
        //\\


        $select='uid,name,sex,mobile,idcard,idcheck,depart,join,subsidy_type,type,ad_type';
        $where['ad_type/!=']='a';
        $memberInfo=self::$MS->memberSelect(USR_INFO,$select,$where,'depart asc limit '.self::$start.','.self::$step);
        //var_dump($memberInfo);exit;

        if(!$memberInfo){$memberInfo=array();$noneTip='没有了!';}
        else{$noneTip='';}

        V::forList('member',$memberInfo);
        V::asSign('noneTip',$noneTip);


        ////翻页栏
        $countList=count( self::$MS->resultSelect(USR_INFO,'uid',$where) );
        FP::getPage($countList,I::have('p'),self::$step,$base_url);
        v::asSign('countList',FP::$countList);
        V::asSign('pages',FP::$pages);
        V::asSign('cur_page',FP::$cur_page);
        V::asSign('previous',FP::$previous);
        V::asSign('next',FP::$next);
        V::forList('plist',FP::$plist);
        //\\

        V::show();

    }

    function member_edit_ax(){

        $rowId=tool::is_Post('rowId');

        $select='uid,name,sex,mobile,idcard,idcheck,depart,join,subsidy_type,type,ad_type';
        $where['uid']=$rowId;
        $rowSelect=self::$MS->rowSelect(USR_INFO,$select,$where);

        if($rowSelect){

            switch($rowSelect->idcheck){
                case '0' :$rowSelect->idcheck='未验证'; break;
                case '1' :$rowSelect->idcheck='已验证'; break;
            }

            tool::jsonResult($rowSelect,'0');

        }

    }

    function member_submit_ax(){

        $rowId=tool::is_Post('uid');

        $data=tool::is_Post($_POST);
        unset($data['uid']);unset($data['idcheck']);//排除部分
        //var_dump($data);//exit;//


        ////更新微企通讯录
        $infoUp=self::$MS->memberInfo($data,$rowId);
        $access_token=weicoApi::globeAccessToken();
        $post_array_data=weicoApi::usrInfoUpdateBase($infoUp);//组装信息
        $usrInfoUpdate=weicoApi::usrInfoUpdate($post_array_data,$access_token);
        //var_dump($usrInfoUpdate);exit;
        if($usrInfoUpdate->errmsg!='updated'){
            tool::jsonResult($_POST,'-1','企业通讯录更新失败！');
        }
        //\\

        $rowUpdate=self::$MS->rowUpdate(USR_INFO,$data,'uid/'.$rowId);
        if($rowUpdate){
            tool::jsonResult($_POST,'1','提交成功！','?/base/member');
        }

        tool::jsonResult($_POST,'0','无修改！');

    }

    function cashier(){

        V::tamplate('index');
        $cArr['content']='change_cashier';
        V::asChangeArr($cArr);

        $base_url='?/base/cashier';//基础链接

        $select='uid,name,sex,mobile,idcard,idcheck,depart,join,subsidy_type,type,ad_type';
        $where['ad_type/!=']='a';$where['ad_type']='2';
        $cashierInfo=self::$MS->cashierSelect(USR_INFO,$select,$where,'depart asc limit '.self::$start.','.self::$step);
        //var_dump($USR_INFO);exit;

        if(!$cashierInfo){$cashierInfo=array();$noneTip='没有了!';}
        else{$noneTip='';}

        V::forList('cashier',$cashierInfo);
        V::asSign('noneTip',$noneTip);


        ////翻页栏
        $countList=count( self::$MS->resultSelect(USR_INFO,'uid',$where) );
        FP::getPage($countList,I::have('p'),self::$step,$base_url);
        v::asSign('countList',FP::$countList);
        V::asSign('pages',FP::$pages);
        V::asSign('cur_page',FP::$cur_page);
        V::asSign('previous',FP::$previous);
        V::asSign('next',FP::$next);
        V::forList('plist',FP::$plist);
        //\\

        V::show();

    }


    function cashier_edit_ax(){

        $rowId=tool::is_Post('rowId');

        $select='uid,name,sex,mobile,idcard,idcheck,depart,join,subsidy_type,type,ad_type';
        $where['uid']=$rowId;
        $rowSelect=self::$MS->rowSelect(USR_INFO,$select,$where);

        if($rowSelect){

            switch($rowSelect->idcheck){
                case '0' :$rowSelect->idcheck='未验证'; break;
                case '1' :$rowSelect->idcheck='已验证'; break;
            }

            tool::jsonResult($rowSelect,'0');

        }


    }


    function cashier_submit_ax(){

        $rowId=tool::is_Post('uid');

        $data=tool::is_Post($_POST);
        unset($data['uid']);unset($data['idcheck']);//排除部分
        //var_dump($data);exit;//

        ////更新微企通讯录
        $infoUp=self::$MS->memberInfo($data,$rowId);
        $access_token=weicoApi::globeAccessToken();
        $post_array_data=weicoApi::usrInfoUpdateBase($infoUp);//组装信息
        $usrInfoUpdate=weicoApi::usrInfoUpdate($post_array_data,$access_token);
        //var_dump($usrInfoUpdate);exit;
        if($usrInfoUpdate->errmsg!='updated'){
            tool::jsonResult($_POST,'-1','企业通讯录更新失败！');
        }
        //\\

        $rowUpdate=self::$MS->rowUpdate(USR_INFO,$data,'uid/'.$rowId);
        if($rowUpdate){
            tool::jsonResult($_POST,'1','提交成功！','?/base/cashier');
        }

        tool::jsonResult($_POST,'0','无修改！');

    }


    function system(){

        V::tamplate('index');
        $cArr['content']='change_system';
        V::asChangeArr($cArr);

        $base_url='?/base/system';//基础链接

        $select='log_id,type,type_name,desc,time,ip';
        $log=self::$MS->systemSelect(LOG,$select,'-','time desc limit '.self::$start.','.self::$step);
        //var_dump($USR_INFO);exit;

        if(!$log){$log=array();$noneTip='没有了!';}
        else{$noneTip='';}

        V::forList('system',$log);
        V::asSign('noneTip',$noneTip);

        ////翻页栏
        $countList=count( self::$MS->resultSelect(LOG,'log_id') );
        FP::getPage($countList,I::have('p'),self::$step,$base_url);
        v::asSign('countList',FP::$countList);
        V::asSign('pages',FP::$pages);
        V::asSign('cur_page',FP::$cur_page);
        V::asSign('previous',FP::$previous);
        V::asSign('next',FP::$next);
        V::forList('plist',FP::$plist);
        //\\

        V::show();

    }


} 
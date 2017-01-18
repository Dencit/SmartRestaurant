<?php
/* Created by User: soma Worker: 陈鸿扬 Date: 16/7/30  Time: 12:01 */

namespace Modelers;
use Modelers\baseModel;

class index extends baseModel {

    static $uid;
    static $nickname;
    static $headimgurl;
    static $sex;

    static $reced;

    static $gift;

    static $mobile;



    function __construct(){
        new parent;//可以使用baseModel所有查询方法,包括wpdb的;

    }


    function indexModel($uid_get){

        $whereArray['uid']=$uid_get;

        $user_row=self::rowSelect(USR,'*',$whereArray);
        if(!$user_row){
            self::$uid=0;
            self::$nickname=0;
            self::$headimgurl=0;
            self::$sex =0;
        }else{
            self::$uid =$user_row->uid ;
            self::$nickname =$user_row->nickname ;
            self::$headimgurl =$user_row->headimgurl ;
            self::$sex =$user_row->sex ;
        }


        $userRec_row=self::rowSelect(USR_REC,'*',$whereArray,'reced desc');
        if(!$userRec_row){
            self::$reced=0;
        }else {
            self::$reced = $userRec_row->reced;
        }


        $userGet_row=self::rowSelect(USR_GET,'*',$whereArray,'time desc');
        if(!$userGet_row){
            self::$gift=0;
        }else{
            self::$gift=$userGet_row->gift;
        }

        $userInfo_row=self::rowSelect(USR_INFO,'*',$whereArray);
        if(!$userInfo_row){
            self::$mobile=0;
        }else{
            self::$mobile=$userInfo_row->mobile;
        }

    }


    function shareModel($sid_get){

        $whereArray['uid']=$sid_get;

        self::$uid=$sid_get;

        $sharer_row=self::rowSelect(USR,'*',$whereArray);
        if(!$sharer_row){
            self::$nickname='0';
            self::$headimgurl='0';
            self::$sex='0';
        }else{
            $nickname=$sharer_row->nickname;
            self::$nickname=strip_tags($nickname);
            self::$headimgurl=$sharer_row->headimgurl;
            self::$sex=$sharer_row->sex;
        }

        $sharerGet_row=self::rowSelect(USR_GET,'*',$whereArray,'time desc');
        if(!$sharerGet_row){
            self::$gift='0';
        }
        else{
            self::$gift=$sharerGet_row->gift;
        }

        $sharerInfo_row=self::rowSelect(USR_INFO,'*',$whereArray);
        if(!$sharerInfo_row){
            self::$mobile='0';
        }else{
            self::$mobile=$sharerInfo_row->mobile;
        }


    }


} 
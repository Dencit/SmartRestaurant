<?php
/* Created by User: soma Worker:陈鸿扬  Date: 16/11/23  Time: 11:38 */

namespace Modelers;

use \Modelers\baseModel;
use \Commons\tool as tool;

class login extends baseModel{

    function __construct(){
        new parent;//可以使用baseModel所有查询方法,包括wpdb的;

    }

    function addInfoData($usrCodeInfo,$userInfoData,$user2openid,$access_token){

        //var_dump($userInfoData['openid']);exit;//

        $rowAddCheck=$this->rowAddCheck(USR,'*','openid/'.$userInfoData['openid'],$userInfoData);
        //var_dump($rowAddCheck);exit;//

        $uid='';$ad_type='';//后台权限 普通
        ////管理员微企扫码+密码登录
        if( !empty($usrCodeInfo->redirect_login_info->login_ticket) ){ $ad_type='a';}//后台权限 超管
        //\\
        switch($rowAddCheck){
            case 'insertOk':
                $in_data['name']=$usrCodeInfo->user_info->name;
                $in_data['photo']=$usrCodeInfo->user_info->avatar;
                $rowSelect=$this->rowSelect(USR,'uid','openid/'.$user2openid->openid);
                if($rowSelect){
                    $uid=$rowSelect->uid;

                    //$ad_type_sel=$this->rowSelect(USR_INFO,'ad_type','uid/'.$uid)->ad_type;
                    if($ad_type=='a'){ $in_data['ad_type']=$ad_type;}

                    $in_data['uid']=$uid;
                    $this->rowInsert(USR_INFO,$in_data);

                    unset($in_data['name']);unset($in_data['photo']);unset($in_data['ad_type']);
                    $in_data['time_recharge']=time(); $in_data['time_pay']=time();
                    $this->rowInsert(ACCOUNT,$in_data);

                    //echo('all ok insertOk');
                }
                break;
            case 'updateOk':
                $up_data['name']=$usrCodeInfo->user_info->name;
                $up_data['photo']=$usrCodeInfo->user_info->avatar;
                $rowSelect=$this->rowSelect(USR,'uid','openid/'.$user2openid->openid);
                if($rowSelect){
                    $uid=$rowSelect->uid;

                    //$ad_type_sel=$this->rowSelect(USR_INFO,'ad_type','uid/'.$uid)->ad_type;
                    if($ad_type=='a'){ $up_data['ad_type']=$ad_type;}

                    $this->rowUpdate(USR_INFO,$up_data,'uid/'.$uid);

                    //echo('all ok updateOk');
                }
                break;
        }


        $glob_usr=new \stdClass();
        $glob_usr->sign=session_id();//设置服务器的sign
        $glob_usr->uid=$uid;
        $glob_usr->userId = $userInfoData['userid'];
        $glob_usr->openId = $user2openid->openid;
        $glob_usr->access_token = $access_token;

        $usrInfo=$this->rowSelect(USR_INFO,'type,ad_type','uid/'.$uid);
        //var_dump($usrInfo);exit;//
        if($usrInfo){
            $glob_usr->type = $usrInfo->type;
            $glob_usr->ad_type = $usrInfo->ad_type;
        }

        //var_dump($glob_usr);exit;//

        tool::mk_session( array('glob_usr'=>$glob_usr) );



    }

} 
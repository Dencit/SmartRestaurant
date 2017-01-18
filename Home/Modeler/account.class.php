<?php
/* Created by User: soma Worker:陈鸿扬  Date: 16/10/19  Time: 17:45 */

namespace Modelers;

use \Commons\inputCheck as inputCheck;//输入验证类
use Modelers\baseModel;

class account extends baseModel{



    function __construct(){
        new parent;//可以使用baseModel所有查询方法,包括wpdb的;

    }


    function idCardCheck($id_card,$uid=''){

        if( empty($id_card) ){ return false; }

        $id_card_check=inputCheck::check_identity($id_card);
        //var_dump($id_card_check);exit;//
        if(!$id_card_check){ return false; }

        $whereArray['idcard']=$id_card;
        if($uid!=''){ $whereArray['uid']=$uid; }

        $userInfo_row=self::rowSelect(USR_INFO,'*',$whereArray);
        //var_dump($userInfo_row);exit;//
        if($userInfo_row){
            return $userInfo_row;
        }

        return false;

    }




} 
<?php
/* Created by User: soma Worker:陈鸿扬  Date: 16/11/29  Time: 14:30 */

namespace Modelers;
use Commons\tool;
use Modelers\baseModel;
use Commons\date as date;

class wxpay extends baseModel
{

    function __construct()
    {
        //new parent;//可以使用baseModel所有查询方法,包括wpdb的;
        self::init();//可以使用baseModel所有查询方法,包括wpdb的;
    }

    function accountRefl($uid,$balance_add,$subsidy_add,$time_recharge){

        $select="balance+".$balance_add.",subsidy+".$subsidy_add;

        $where['uid']=$uid;
        $balanceSum=$this->rowSelectMath(ACCOUNT,$select,$where);
        $balanceSum->time_recharge=$time_recharge;
        $balanceAdd=$this->rowUpdate(ACCOUNT,$balanceSum,$where);
        if(!$balanceAdd){return 'balanceAdd had!';}
        else{return true;}

    }



}
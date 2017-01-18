<?php
/* Created by User: soma Worker:陈鸿扬  Date: 16/10/21  Time: 16:22 */

namespace Modelers;
use Commons\jump;
use Modelers\baseModel;
use \Commons\date as date;//日期类
use \Commons\tool as tool;//工具类

class payment extends baseModel {

    function __construct(){
        //new parent;//可以使用baseModel所有查询方法,包括wpdb的;
        self::init();//可以使用baseModel所有查询方法,包括wpdb的;
    }

    //订单 单品价格 总计//*1
    function orderListSum($order_no){

        $orderList=self::resultSelect(ORDER,'subtotal_money',array("order_no"=>$order_no));
        //var_dump($orderList);//

        $total_money=floatval('0.00');
        foreach($orderList as $k=>$v){
            //var_dump(strval($v->subtotal_money));
            $total_money+=floatval($v->subtotal_money);
        }

        //echo($total_money);exit;//
        return $total_money;
    }

    //判断时段区间内 是否有已下订单//*2
    function timeStampHeaveOrderCheck($uid){

        $selectArray=array('order_no','time','time_type');
        $whereArray['uid']=$uid;
        $whereArray['payed']='1';
        $whereArray['time/>']=date::AoPoN_Check('side')[0];//时段区间开始
        $whereArray['time/<']=date::AoPoN_Check('side')[1];//时段区间结束
        $whereArray['time_type']=date::AoPoN_Check('num');

        $order_list=$this->resultSelect(ORDER,$selectArray,$whereArray);

        //var_dump(date::AoPoN_Check('num'));//
        //var_dump($order_list);//

        if($order_list)$timeStampFirstOrderCheck=true;
        else $timeStampFirstOrderCheck=false;

        //var_dump($timeStampFirstOrderCheck);exit;//

        return $timeStampFirstOrderCheck;

    }

    //收银权限检查
    function cashierAuthCheck($uid){

        $whereArray['uid']=$uid;
        $whereArray['type']='2';
        $type=$this->rowSelect(USR_INFO,'type',$whereArray);

        if ($type) return true ;
        else return false;

    }
    function cashierAuthCheck2($uid){

        $whereArray['uid']=$uid;
        //$whereArray['ad_type']='2';
        $USR_INFO=$this->rowSelect(USR_INFO,'ad_type',$whereArray);
        if($USR_INFO)$type=$USR_INFO->ad_type;
        if ($type!=1||$type!=0) return true ;
        else return false;

    }

    //检查钱包余额//*2
    function accountCheck($uid){
        $select=array('uid','balance','subsidy');
        $where['uid']=$uid;
        $account=$this->rowSelect(ACCOUNT,$select,$where);
        return $account;
    }

    //钱包扣款
    private function accountPay($uid,$people_money,$total_benefit){

        $select=array('balance','subsidy');
        $num=array($people_money,$total_benefit);
        $where['uid']=$uid;

        //自动从指定表中 获取字段组合$select 把指定值组合$num update给select好的字段
        $this->fieldNumSUM(ACCOUNT,$select,$where,'-',$num);
    }

    //更新已支付记录//*2
    function updateCashierOrder($order_cashier_no,$cid){

        //获取未支付订单 order表
        $selectArray=array('order_no','food_id','uid','subtotal_money','subtotal_benefit');
        $whereArray['order_no']=$order_cashier_no;
        $whereArray['payed']=0;
        $cashier_order_list=$this->resultSelect(ORDER,$selectArray,$whereArray);
        //var_dump($cashier_order_list);exit;//
        if(!$cashier_order_list){ tool::jsonResult('','-1','该订单已支付或无效！');}
        //

        //获取订单用户 uid
        $uid=$cashier_order_list[0]->uid;
        //

        //拼装 支付记录数据
        $payed_info=array();
        $payed_info['order_no']=$order_cashier_no;
        $total_money=self::orderListSum($order_cashier_no);
        $payed_info['total_money']=floatval($total_money);
        $payed_info['people_money']=floatval($total_money);
        $payed_info['total_benefit']=floatval('0.00');
        //

        //判断 该订单用户 在该时段 是否首单
        $time_type=date::AoPoN_Check('num');//时段标记
        $heaveOrderCheck=$this->timeStampHeaveOrderCheck($uid);
        //

        //用户折扣标记
        $uInfo=$this->rowSelect(USR_INFO,'join,subsidy_type','uid/'.$uid);
        if(!$uInfo){ exit("uInfo fail!"); }
        //var_dump($uInfo);//exit;//

        $join=$uInfo->join;//是:1 否:0 在编
        $subsidy_type=$uInfo->subsidy_type;//早:1 或 早午:2 补贴
        switch($subsidy_type){
            case '1': $subsidy_type=1; break;
            case '2':
                //只包含早午餐,限制3的情况 不补贴晚餐
                if($time_type>0 && $time_type<=2)$subsidy_type=$time_type;
                break;
        }

        if(!$heaveOrderCheck && $join=='1' && $subsidy_type==$time_type){
            $payed_info['people_money']=floatval('1.00');
            $payed_info['total_benefit']=floatval($total_money-1);
        }
        elseif($heaveOrderCheck && $join=='0' && $subsidy_type!=$time_type){
            $payed_info['people_money']=floatval($total_money);
            $payed_info['total_benefit']=floatval('0.00');
        }
        else{
            $payed_info['people_money']=floatval($total_money);
            $payed_info['total_benefit']=floatval('0.00');
        }
        //


        //判断 该订单用户的 补贴钱包余额
        $account=$this->accountCheck($uid);
        $balance=$account->balance;
        $subsidy=$account->subsidy;
        //var_dump($balance);var_dump($subsidy);//
        if($payed_info['total_benefit']>$subsidy){
            $payed_info['people_money']=floatval($total_money);
            $payed_info['total_benefit']=floatval('0.00');
        }
        if($payed_info['people_money']>$balance){exit("people_money fail");}
        //

        //钱包扣款
        self::accountPay($uid,$payed_info['people_money'],$payed_info['total_benefit']);
        //

        //exit;//

        //继续拼装 支付记录数据
        foreach ($cashier_order_list as $k=> $v){
            $payed_info['food_id_list'][$k]=$v->food_id;
        }
        $payed_info['food_id_list']=serialize($payed_info['food_id_list']);
        $payed_info['uid']=$uid;
        $payed_info['cid']=$cid;
        $payed_info['payed']='1';
        $payed_info['time']=TIME;
        $payed_info['time_type']=date::AoPoN_Check('num');
        $payed_info['ip']=tool::get_ip();
        //var_dump($payed_info);exit;//
        //


        //更新已支付记录
        $selectArr=array_slice($selectArray,0,-4);
        //var_dump($selectArr);//exit;//
        $this->rowAddCheck(PAYED,$selectArr,$whereArray,$payed_info);
        //exit('rowAddCheck ok');//
        //

        //最后才给订单 确认
        foreach ($cashier_order_list as $k=> $v){
            $dataArray['payed']='1';
            $this->rowUpdate(ORDER,$dataArray,$whereArray);
        }


        return true;

    }

    function accountRefl($uid,$balance_add,$subsidy_add,$time_recharge){

        $select="balance+".$balance_add.",subsidy+".$subsidy_add;
        $where['uid']=$uid;
        $balanceSum=$this->rowSelectMath(ACCOUNT,$select,$where);
        $balanceSum->time_recharge=$time_recharge;
        //var_dump($balanceSum);exit;//
        $balanceAdd=$this->rowUpdate(ACCOUNT,$balanceSum,$where);
        if(!$balanceAdd){return '$balanceAdd fail';}
        else{return true;}

        return false;
    }


} 
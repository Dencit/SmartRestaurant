<?php
/* Created by User: soma Worker:陈鸿扬  Date: 16/11/14  Time: 19:06 */

namespace Modelers;
use Commons\tool;
use Modelers\baseModel;
use Commons\date as date;

class recharge extends baseModel {

    function __construct(){
        new parent;//可以使用baseModel所有查询方法,包括wpdb的;

    }

    function accountSelect($table,$select,$where,$order){

        $data=$this->resultSelect($table,$select,$where,$order);
        if($data){


            foreach($data as $n=>$v){

                $data[$n]->uid=$this->rowSelect(USR_INFO,'name','uid/'.$v->uid)->name;
                $data[$n]->time_recharge=date::Ymd($v->time_recharge).' '.date::AoPoN_Check('chr',$v->time_recharge);

            }
            return $data;
        }

        return '';

    }


    function rechargesSelect($table,$select,$where,$order){

        $data=$this->resultSelect($table,$select,$where,$order);
        if($data) {


            foreach($data as $n=>$v){

                $data[$n]->uid=@$this->rowSelect(USR_INFO,'name','uid/'.$v->uid)->name;

                if($data[$n]->subsidy_menoy==330.00){
                    $data[$n]->subsidy_menoy="<b style='color:orangered;'>".$data[$n]->subsidy_menoy."</b>";
                    $data[$n]->recharge_menoy= "<b style='color:orangered;'>" .$data[$n]->recharge_menoy."</b>";
                }

                $cid=$this->rowSelect(USR_INFO,'name','uid/'.$v->cid);
                if($cid){$data[$n]->cid=$cid->name;}else{$data[$n]->cid="无";}

                $data[$n]->time=date::Ymd($v->time).' '.date::AoPoN_Check('chr',$v->time);

            }


            return $data;

        }

        return '' ;

    }


    function rechargesTable($table,$select,$where,$order){

        $data=$this->resultSelect($table,$select,$where,$order);
        if($data) {

            foreach($data as $n=>$v){
                $data[$n]->uid=@$this->rowSelect(USR_INFO,'name','uid/'.$v->uid)->name;
                $cid=$this->rowSelect(USR_INFO,'name','uid/'.$v->cid);
                if($cid){$data[$n]->cid=$cid->name;}else{$data[$n]->cid="无";}
                $data[$n]->time=date::Ymd($v->time).' '.date::AoPoN_Check('chr',$v->time);
            }

            $table_data='
            <table>
            <thead>
                <tr>
                    <th>RID</th>
                    <th>商户订单号</th>
                    <th>微信支付单号</th>
                    <th>姓名</th>
                    <th>充值金额</th>
                    <th>补贴金额</th>
                    <th>收银员</th>
                    <th>充值时间</th>
                    <th>ip</th>
                </tr>
            </thead>
            <tbody>';

            foreach($data as $n=>$v){
                $table_data.='
            <tr>
                <th>'.$v->recharge_id.'</th>
                <td>'.'`'.$v->out_trade_no.'</td>
                <td>'.'`'.$v->transaction_id.'</td>
                <td>'.$v->uid.'</td>
                <td>'.$v->recharge_menoy.'</td>
                <td>'.$v->subsidy_menoy.'</td>
                <td>'.$v->cid.'</td>
                <td>'.$v->time.'</td>
                <td>'.$v->ip.'</td>
            </tr>
            ';
            }

            $table_data.='
            </tbody>
            </table>';


            return $table_data;
        }

        return false;

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
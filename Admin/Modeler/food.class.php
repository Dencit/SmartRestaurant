<?php
/* Created by User: soma Worker:陈鸿扬  Date: 16/11/14  Time: 19:06 */

namespace Modelers;
use Commons\tool;
use Modelers\baseModel;
use Commons\date as date;

class food extends baseModel {

    function __construct(){
        new parent;//可以使用baseModel所有查询方法,包括wpdb的;

    }

    function foodSelect($table,$select,$where,$order){

        $data=$this->resultSelect($table,$select,$where,$order);
        if($data){

            $allow='';$type_name='';
            foreach($data as $n=>$v){

                switch($v->allow){
                    case'0': $allow='下架'; break;
                    case'1': $allow='上架'; break;
                }
                $data[$n]->allow=$allow;

                switch($v->type){
                    case'a1': $type_name='早餐'; break;
                    case'p1': $type_name='抓饭'; break;
                    case'p2': $type_name='拌饭'; break;
                    case'p3': $type_name='商务简餐'; break;
                }
                $data[$n]->type=$type_name;

            }
            return $data;
        }

        return '';

    }


    function orderSelect($table,$select,$where,$order){

        $data=$this->resultSelect($table,$select,$where,$order);
        if($data) {


            foreach($data as $n=>$v){

                $data[$n]->uid=@$this->rowSelect(USR_INFO,'name','uid/'.$v->uid)->name;
                $data[$n]->food_id=$this->rowSelect(FOOD,'name','food_id/'.$v->food_id)->name;
                $data[$n]->time=date::Ymd($v->time).' '.date::AoPoN_Check('chr',$v->time);

            }


            return $data;

        }

        return '' ;

    }

    function orderTable($table,$select,$where,$order){

        $data=$this->resultSelect($table,$select,$where,$order);
        if($data) {

            foreach($data as $n=>$v){
                $data[$n]->uid=@$this->rowSelect(USR_INFO,'name','uid/'.$v->uid)->name;
                $data[$n]->food_id=$this->rowSelect(FOOD,'name','food_id/'.$v->food_id)->name;
                $data[$n]->time=date::Ymd($v->time).' '.date::AoPoN_Check('chr',$v->time);
            }


            $table_data='
            <table>
            <thead>
                <tr>
                    <th>OID</th>
                    <th>品名</th>
                    <th>单价</th>
                    <th>数量</th>
                    <th>小计</th>
                    <th>购买者</th>
                    <th>售出时间</th>
                    <th>订单号</th>
                    <th>ip</th>
                </tr>
            </thead>
            <tbody>';

            foreach($data as $n=>$v){
            $table_data.='
            <tr>
                <th>'.$v->order_id.'</th>
                <td>'.$v->food_id.'</td>
                <td>'.$v->price.'</td>
                <td>'.$v->count.'</td>
                <td>'.$v->subtotal_money.'</td>
                <td>'.$v->uid.'</td>
                <td>'.$v->time.'</td>
                <td>'.$v->order_no.'</td>
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

} 
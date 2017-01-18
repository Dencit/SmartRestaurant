<?php
/* Created by User: soma Worker:陈鸿扬  Date: 16/11/14  Time: 19:06 */

namespace Modelers;
use Commons\tool;
use Modelers\baseModel;
use Commons\date as date;

class book extends baseModel {

    function __construct(){
        new parent;//可以使用baseModel所有查询方法,包括wpdb的;

    }

    function booksSelect($table,$select,$where,$order){

        $data=$this->resultSelect($table,$select,$where,$order);
        if($data){


            foreach($data as $n=>$v){

                $data[$n]->uid=@$this->rowSelect(USR_INFO,'name','uid/'.$v->uid)->name;
                $data[$n]->book_time=date::Ymd($v->book_time).' '.date::weekday($v->book_time);

            }

            return $data;
        }

        return '';

    }


    function booksTable($table,$select,$where,$order){

        $data=$this->resultSelect($table,$select,$where,$order);
        if($data) {

            foreach($data as $n=>$v){
                $data[$n]->uid=@$this->rowSelect(USR_INFO,'name','uid/'.$v->uid)->name;
                $data[$n]->book_time=date::Ymd($v->book_time).' '.date::weekday($v->book_time);

                switch($data[$n]->type){
                    case 'a1': $data[$n]->type='早餐'; break;
                    case 'p1': $data[$n]->type='抓饭'; break;
                    case 'p2': $data[$n]->type='拌饭'; break;
                    case 'p3': $data[$n]->type='商务简餐'; break;
                }

            }

            $table_data='
            <table>
            <thead>
                <tr>
                    <th>BID</th>
                    <th>姓名</th>
                    <th>类型</th>
                    <th>预定日期</th>
                    <th>ip</th>
                </tr>
            </thead>
            <tbody>';

            foreach($data as $n=>$v){
                $table_data.='
            <tr>
                <th>'.$v->book_id.'</th>
                <td>'.$v->uid.'</td>
                <td>'.$v->type.'</td>
                <td>'.$v->book_time.'</td>
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


    function payedSelect($table,$select,$where,$order){

        $data=$this->resultSelect($table,$select,$where,$order);
        if($data) {


            foreach($data as $n=>$v){

                $data[$n]->uid=@$this->rowSelect(USR_INFO,'name','uid/'.$v->uid)->name;
                $data[$n]->cid=$this->rowSelect(USR_INFO,'name','uid/'.$v->cid)->name;
                $data[$n]->time=date::Ymd($v->time).' '.date::AoPoN_Check('chr',$v->time);

            }


            return $data;

        }

        return '' ;

    }



    function payedTable($table,$select,$where,$order){

        $data=$this->resultSelect($table,$select,$where,$order);
        if($data) {

            foreach($data as $n=>$v){
                $data[$n]->uid=@$this->rowSelect(USR_INFO,'name','uid/'.$v->uid)->name;
                $data[$n]->cid=$this->rowSelect(USR_INFO,'name','uid/'.$v->cid)->name;
                $data[$n]->time=date::Ymd($v->time).' '.date::AoPoN_Check('chr',$v->time);
            }

            $table_data='
            <table>
            <thead>
                <tr>
                    <th>PID</th>
                    <th>订单号</th>
                    <th>消费者</th>
                    <th>总价</th>
                    <th>补贴</th>
                    <th>实付</th>
                    <th>收银员</th>
                    <th>消费时间</th>
                    <th>ip</th>
                </tr>
            </thead>
            <tbody>';

            foreach($data as $n=>$v){
                $table_data.='
            <tr>
                <th>'.$v->payed_id.'</th>
                <td>'.$v->order_no.'</td>
                <td>'.$v->uid.'</td>
                <td>'.$v->total_money.'</td>
                <td>'.$v->total_benefit.'</td>
                <td>'.$v->people_money.'</td>
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

} 
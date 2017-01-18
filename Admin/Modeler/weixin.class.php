<?php
/* Created by User: soma Worker: 陈鸿扬 Date: 16/8/1  Time: 15:46 */

namespace Modelers;
use Modelers\baseModel;
use Commons\date as date;

class weixin extends baseModel {

    function __construct(){
        new parent;//可以使用baseModel所有查询方法,包括wpdb的;

    }

    function pushSelect($table,$select,$where,$order){

        $data=$this->resultSelect($table,$select,$where,$order);
        $allow='';
        foreach($data as $n=>$v){

            switch($v->allow){
                case'0': $allow='下架'; break;
                case'1': $allow='上架'; break;
            }

            $data[$n]->allow=$allow;

        }


        return $data;

    }


} 
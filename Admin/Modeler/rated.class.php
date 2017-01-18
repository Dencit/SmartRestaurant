<?php
/* Created by User: soma Worker:陈鸿扬  Date: 16/11/14  Time: 19:06 */

namespace Modelers;
use Commons\tool;
use Modelers\baseModel;
use Commons\date as date;

class rated extends baseModel {

    function __construct(){
        new parent;//可以使用baseModel所有查询方法,包括wpdb的;

    }

    function ratedSelect($table,$select,$where,$order){

        $data=$this->resultSelect($table,$select,$where,$order);
        if($data){


            $food_qual='';$serv_qual='';$envi_qual='';
            foreach($data as $n=>$v){

                $data[$n]->uid=@$this->rowSelect(USR_INFO,'name','uid/'.$v->uid)->name;
                $data[$n]->time=date::Ymd($v->time).' '.date::AoPoN_Check('chr',$v->time);

                switch($v->food_qual){
                    case'NULL': $food_qual='未评价'; break;
                    case'A': $food_qual='好评'; break;
                    case'B': $food_qual='良好'; break;
                    case'C': $food_qual='一般'; break;
                    case'D': $food_qual='差评'; break;
                }
                $data[$n]->food_qual=$food_qual;

                switch($v->serv_qual){
                    case'NULL': $serv_qual='未评价'; break;
                    case'A': $serv_qual='好评'; break;
                    case'B': $serv_qual='良好'; break;
                    case'C': $serv_qual='一般'; break;
                    case'D': $serv_qual='差评'; break;
                }
                $data[$n]->serv_qual=$serv_qual;

                switch($v->envi_qual){
                    case'NULL': $envi_qual='未评价'; break;
                    case'A': $envi_qual='好评'; break;
                    case'B': $envi_qual='良好'; break;
                    case'C': $envi_qual='一般'; break;
                    case'D': $envi_qual='差评'; break;
                }
                $data[$n]->envi_qual=$envi_qual;

                if(!empty($v->advise) ){
                    $data[$n]->advise=mb_substr($v->advise,0,10,"UTF-8").'...';
                }else{
                    $data[$n]->advise='无';
                }

            }
            return $data;
        }

        return '';

    }




} 
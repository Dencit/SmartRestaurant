<?php
/* Created by User: soma Worker: 陈鸿扬 Date: 16/8/1  Time: 15:46 */

namespace Modelers;
use Modelers\baseModel;
use Commons\date as date;

class base extends baseModel {

    function __construct(){
        new parent;//可以使用baseModel所有查询方法,包括wpdb的;

    }

    function welcomeSelect($uid){

        $data=$this->rowSelect(USR_INFO,'type,ad_type,name','uid/'.$uid);

        $type='';$ad_type='';

        if($data){

                switch($data->type){
                    //case'0': $type='管理员'; break;
                    case'1': $type='普通'; break;
                    case'2': $type='收银'; break;
                }
                $data->type=$type;

                switch($data->ad_type){
                    //case'0': $ad_type='管理员'; break;
                    case'a': $ad_type='管理员'; break;
                    case'1': $ad_type='普通用户'; break;
                    case'2': $ad_type='收银员'; break;
                    case'3': $ad_type='编辑'; break;
                    case'4': $ad_type='财务'; break;
                }
                $data->ad_type=$ad_type;


            return $data;
        }


    }

    function adminSelect($table,$select,$where,$order){

        $data=$this->resultSelect($table,$select,$where,$order);
        $idcheck='';$join='';$subsidy_type='';$sex='';$type='';$ad_type='';

        if($data){

            foreach($data as $n=>$v){

                switch($v->sex){
                    case'0': $sex='无'; break;
                    case'1': $sex='男'; break;
                    case'2': $sex='女'; break;
                }
                $data[$n]->sex=$sex;

                switch($v->idcheck){
                    case'0': $idcheck='未验证'; break;
                    case'1': $idcheck='已验证'; break;
                }
                $data[$n]->idcheck=$idcheck;

                switch($v->join){
                    case'0': $join='非在编'; break;
                    case'1': $join='在编'; break;
                }
                $data[$n]->join=$join;

                switch($v->subsidy_type){
                    case'0': $subsidy_type='无'; break;
                    case'1': $subsidy_type='早餐'; break;
                    case'2': $subsidy_type='早午餐'; break;
                }
                $data[$n]->subsidy_type=$subsidy_type;

                switch($v->type){
                    //case'0': $type='管理员'; break;
                    case'1': $type='普通'; break;
                    case'2': $type='收银'; break;
                }
                $data[$n]->type=$type;

                switch($v->ad_type){
                    //case'0': $ad_type='管理员'; break;
                    case'a': $ad_type='管理'; break;
                    case'1': $ad_type='普通'; break;
                    case'2': $ad_type='收银'; break;
                    case'3': $ad_type='编辑'; break;
                    case'4': $ad_type='财务'; break;
                }
                $data[$n]->ad_type=$ad_type;

            }

            return $data;

        }

    }


    function memberSelect($table,$select,$where,$order){

        $data=$this->resultSelect($table,$select,$where,$order);
        //var_dump($data);exit;

        $idcheck='';$join='';$subsidy_type='';$sex='';$type='';$ad_type='';

        if($data){

            foreach($data as $n=>$v){

                switch($v->sex){
                    case'0': $sex='无'; break;
                    case'1': $sex='男'; break;
                    case'2': $sex='女'; break;
                }
                $data[$n]->sex=$sex;

                switch($v->idcheck){
                    case'0': $idcheck='未验证'; break;
                    case'1': $idcheck='已验证'; break;
                }
                $data[$n]->idcheck=$idcheck;

                switch($v->join){
                    case'0': $join='非在编'; break;
                    case'1': $join='在编'; break;
                }
                $data[$n]->join=$join;

                switch($v->subsidy_type){
                    case'0': $subsidy_type='无'; break;
                    case'1': $subsidy_type='早餐'; break;
                    case'2': $subsidy_type='早午餐'; break;
                }
                $data[$n]->subsidy_type=$subsidy_type;

                switch($v->type){
                    //case'0': $type='管理员'; break;
                    case'1': $type='普通'; break;
                    case'2': $type='收银'; break;
                }
                $data[$n]->type=$type;

                switch($v->ad_type){
                    //case'0': $ad_type='管理员'; break;
                    //case'a': $ad_type='管理'; break;
                    case'1': $ad_type='普通'; break;
                    case'2': $ad_type='收银'; break;
                    case'3': $ad_type='编辑'; break;
                    case'4': $ad_type='财务'; break;
                }
                $data[$n]->ad_type=$ad_type;

            }


            return $data;
        }

    }

    function memberInfo($data,$rowId=''){
        $infoUp=$data;
        $infoUp['userid']=$this->rowSelect(USR,'userid','uid/'.$rowId)->userid;
        switch($infoUp['join']){
            case '0': $infoUp['join']='非在编';break;
            case '1': $infoUp['join']='在编';break;
        }
        switch($infoUp['ad_type']){
            case 'a': $infoUp['ad_type']='管理';break;
            case '1': $infoUp['ad_type']='普通';break;
            case '2': $infoUp['ad_type']='收银';break;
            case '3': $infoUp['ad_type']='编辑';break;
            case '4': $infoUp['ad_type']='财务';break;
        }
        return $infoUp;
    }



    function cashierSelect($table,$select,$where,$order){

        $data=$this->resultSelect($table,$select,$where,$order);
        $idcheck='';$join='';$subsidy_type='';$sex='';$type='';$ad_type='';

        if($data){

            foreach($data as $n=>$v){

                switch($v->sex){
                    case'0': $sex='无'; break;
                    case'1': $sex='男'; break;
                    case'2': $sex='女'; break;
                }
                $data[$n]->sex=$sex;

                switch($v->idcheck){
                    case'0': $idcheck='未验证'; break;
                    case'1': $idcheck='已验证'; break;
                }
                $data[$n]->idcheck=$idcheck;

                switch($v->join){
                    case'0': $join='非在编'; break;
                    case'1': $join='在编'; break;
                }
                $data[$n]->join=$join;

                switch($v->subsidy_type){
                    case'0': $subsidy_type='无'; break;
                    case'1': $subsidy_type='早餐'; break;
                    case'2': $subsidy_type='早午餐'; break;
                }
                $data[$n]->subsidy_type=$subsidy_type;

                switch($v->type){
                    //case'0': $type='管理员'; break;
                    case'1': $type='普通'; break;
                    case'2': $type='收银'; break;
                }
                $data[$n]->type=$type;

                switch($v->ad_type){
                    //case'0': $ad_type='管理员'; break;
                    //case'a': $ad_type='管理'; break;
                    case'1': $ad_type='普通'; break;
                    case'2': $ad_type='收银'; break;
                    case'3': $ad_type='编辑'; break;
                    case'4': $ad_type='财务'; break;
                }
                $data[$n]->ad_type=$ad_type;

            }

            return $data;


        }

    }



    function systemSelect($table,$select,$where,$order){

        $data=$this->resultSelect($table,$select,$where,$order);

        foreach($data as $n=>$v){

            $data[$n]->time=date::YmdHs($v->time);

        }


        return $data;

    }



} 
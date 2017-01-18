<?php
/* Created byUser: soma Worker: 陈鸿扬 Date: 16/6/18  Time: 20:16 */

namespace Modelers;
use Modelers\baseModel;


class homeModel extends baseModel {

    public static $base;

    function __construct(){

        self::$base=new parent;

    }

//检查式 新增条目
    function rowAddCheck($table,$selectArray,$whereArray,$dataArray,$orderArray=''){

        $base=self::$base;

        $rowSelect=$base->rowSelect($table,$selectArray,$whereArray,$orderArray);
        if($rowSelect){ $have='1'; }else{ $have='0'; }

        if($have=='0'){
            $rowInsert=$base->rowInsert($table,$dataArray);

            if($rowInsert){ return 'insertOk'; }else{ return 'insertFail'; }

        }else{
            $rowUpdate=$base->rowUpdate($table,$dataArray,$whereArray);

            if($rowUpdate){ return 'updateOk'; }else{ return 'updateFail'; }
        }

    }


//加减表中某字段的值,可自定步进值,根据 $selectArray传参类型 返回同类型处理结果
    function fieldNumSUM($table,$selectArray='',$whereArray,$math='1',$num='1',$orderArray=''){
        $base=self::$base;

        //print_r($selectArray);print_r($whereArray);exit;

        $rowSelect=$base->rowSelect($table,$selectArray,$whereArray,$orderArray);
        if(!$rowSelect){
            exit('$rowSelect fail');
        }

        //print_r($rowSelect);exit;

        //加减数据判断 函数
        $dataArray=$this->dataArrayGet($rowSelect,$selectArray,$math,$num);
        //print_r($dataArray);exit;

        $rowUpdate=$base->rowUpdate($table,$dataArray,$whereArray);
        if($rowUpdate){
            return $dataArray; }
        else{ return false; }
    }
////加减数据判断,从上~
    private function dataArrayGet($rowSelect,$selectArray,$math='1',$num='1'){
        $dataArray=array();
        if(is_array($selectArray)){

            //print_r($rowSelect);print_r($selectArray);exit;

            foreach($selectArray as $k=>$v){

                $rowSelectV=$rowSelect->$v;

                //print_r($rowSelectV);exit;

                if($math=='1'){
                    $addField=(int)$rowSelectV + (int)$num;
                }else{
                    $addField=(int)$rowSelectV - (int)$num;
                }
                $dataArray[$v]=$addField;
            }
            //print_r($dataArray); exit;
        }else{

            $rowSelectA=$rowSelect->$selectArray;

            if($rowSelectA=='0'){
                $addField='0';
            }else if($math=='1'){
                $addField=(int)$rowSelectA + (int)$num;
            }else{
                $addField=(int)$rowSelectA - (int)$num;
            }
            $dataArray[$selectArray]=$addField;
            //print_r($dataArray); exit;
        }
        return $dataArray;
    }



}
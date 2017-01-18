<?php
/* Created by User: soma Worker:陈鸿扬  Date: 16/10/21  Time: 09:41 */

namespace Modelers;

use Commons\tool as tool;
use Commons\date as date;

class info extends baseModel {

    function __construct(){
        new parent;//可以使用baseModel所有查询方法,包括wpdb的;

    }

    //used **2 place
    function foodSalesCount(){

        ////更新今日销量
        $query="
        select f.food_id,sum(o.count)as food_count
        from ".ORDER." as o inner join ".FOOD." as f
        on o.food_id=f.food_id
        where o.payed='1' and o.time>".date::todaySide(0)." and o.time<=".date::todaySide(1)."
        group by f.food_id
        ";
        //echo $query;//
        $foodSalesCount=self::$wpDb->get_results( self::$wpDb->prepare($query) );
        //print_r($foodSalesCount);//

        foreach($foodSalesCount as $k=>$v){
            $data['time']=TIME;
            $data['sales']=$v->food_count;
            $where['food_id']=$v->food_id;
            self::$wpDb->update(FOOD,$data,$where);
        }
        ////


        ////获取最新餐品数据
        $selectArray=array('food_id','name','image','price','stock','sales');
        $whereArray['allow']='1';
        $whereArray['stock/>']='0';
        switch(date::AoPoN_Check()){
            default: $whereArray['time_type']='1'; break;
            case '1' : $whereArray['time_type']='1'; break;
            case '2' : $whereArray['time_type']='2'; break;
            case '3' : $whereArray['time_type']='2'; break;
        }

        //var_dump($whereArray);exit;//

        $orderArray['time']='desc';
        $foodList=$this->resultSelect(FOOD,$selectArray,$whereArray,$orderArray);
        //var_dump($foodList);exit('$foodList');//
        ////

        return $foodList;

    }

    //




} 
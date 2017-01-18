<?php
/* Created by User:soma Worker:陈鸿扬 Date: 16/8/15  Time: 09:18 */

namespace Controlers;


use \Commons\tool as tool;//工具类
use \Commons\jump as jump;//跳转类
use \Commons\date as date;//日期类

use \Controlers\urlSerial as I;//序列类
use \Modelers\model as MS;//model装载器
use \Views\view as V;//视图类
use \Commons\forPage as FP;//翻页类
use \Commons\imgMaker as IMG;//写图类


class food extends baseControler {

    private static $adminDesc;//parent object

    private static $MS;

    private static $page;//数据库翻页
    private static $step;//翻页步进值
    private static $start;//翻页起始值

    function __construct(){

        //用户登录状态
        self::$adminDesc=$this->adminDesc();
        //\\

        self::$MS=MS::init('food');

        ////获取翻页值
        $page=I::have('p')-1;$page=$page>0?$page:'0';
        $step=20;
        $start=$step*$page;//$start=$start>0?$start:$step;
        //var_dump($start);//

        self::$page=$page; self::$step=$step; self::$start=$start;
        //\\

    }

    function foods(){

        V::tamplate('index');
        $cArr['content']='change_foods';
        V::asChangeArr($cArr);

        ////搜索处理
        $search='';//搜索字段
        $where=[];//基础条件
        $base_url='?/food/foods';//基础链接
        V::asSign('base_url',$base_url);

        $name=I::have('name') ;
        $type=I::have('type') ;
        $allow=I::have('allow') ;
        if($name){ $where['name/%%']=$name; $search.=DIRECTORY_SEPARATOR.'name-'.$name; }
        if($type){ $where['type/%%']=$type; $search.=DIRECTORY_SEPARATOR.'type-'.$type; }
        if($allow){ $where['allow']=$allow; $search.=DIRECTORY_SEPARATOR.'allow-'.$allow; }
        if($allow=="0"){ $where['allow']=$allow; $search.=DIRECTORY_SEPARATOR.'allow-'.$allow; }

        //var_dump($where);exit;//

        $base_url.=$search;
        //\\

        ////当前页数据
        $select='food_id,image,name,type,type_name,price,stock,sales,allow,time,time_type';

        $foods=self::$MS->foodSelect(FOOD,$select,$where,'time desc limit '.self::$start.','.self::$step);
        //var_dump($USR_INFO);exit;
        if(!$foods){$foods=array();$noneTip='没有了!';}
        else{$noneTip='';}
        V::forList('foods',$foods);
        V::asSign('noneTip',$noneTip);
        //\\

        ////翻页栏
        $countList=count( self::$MS->resultSelect(FOOD,'food_id',$where) );
        FP::getPage($countList,I::have('p'),self::$step,$base_url);
        v::asSign('countList',FP::$countList);
        V::asSign('pages',FP::$pages);
        V::asSign('cur_page',FP::$cur_page);
        V::asSign('previous',FP::$previous);
        V::asSign('next',FP::$next);
        V::forList('plist',FP::$plist);
        //\\

        V::show();

    }

    function foods_edit_ax(){

        $food_id=tool::is_Post('rowId');

        $select='food_id,image,name,type,type_name,price,sales,stock,allow,img_id';
        $where['food_id']=$food_id;
        $rowSelect=self::$MS->rowSelect(FOOD,$select,$where);

        if($rowSelect){

            $rowSelect->image_url='/Upload/food/'.$rowSelect->image;

            tool::jsonResult($rowSelect,'0');

        }

    }

    function foods_submit_ax(){

        $food_id=tool::is_Post('food_id');

        $data=tool::is_Post($_POST);
        unset($data['food_id']);unset($data['image_url']);//排除部分
        //var_dump($data);exit;//
        if( empty($data['img_id']) ){ unset($data['img_id']); }

        $rowUpdate=self::$MS->rowUpdate(FOOD,$data,'food_id/'.$food_id);
        if($rowUpdate){
            $time['time']=time();
            self::$MS->rowUpdate(FOOD,$time,'food_id/'.$food_id);
            tool::jsonResult($_POST,'1','提交成功！','?/food/foods');
        }

        tool::jsonResult($_POST,'0','无修改！');

    }

    function foods_del_ax(){

        $food_id=tool::is_Post('rowId');
        //var_dump($food_id);//

        if(!empty($food_id)){

            $rowSelect=self::$MS->rowSelect(FOOD,'img_id','food_id/'.$food_id);
            $orderSelect=self::$MS->rowSelect(ORDER,'order_id','food_id/'.$food_id);
            //var_dump($orderSelect);//exit;//


            if($rowSelect&&!$orderSelect){
                $img_id=$rowSelect->img_id;

                $upDel=self::$MS->rowDel(UPLOAD,'up_id/'.$img_id);
                $foodDel=self::$MS->rowDel(FOOD,'food_id/'.$food_id);
                if($upDel||$foodDel){
                    tool::jsonResult(array('food_id'=>$food_id),'0','删除成功！');
                }
            }else{

                tool::jsonResult(array('food_id'=>$food_id),'-1','已售出商品不能删除！');

            }

        }

    }

    function foods_add_ax(){

        $data=tool::is_Post($_POST);
        unset($data['food_id']);unset($data['image_url']);//排除部分
        switch($data['type']){
            case'a1': $data['type_name']='早餐'; break;
            case'p1': $data['type_name']='抓饭'; break;
            case'p2': $data['type_name']='拌饭'; break;
            case'p3': $data['type_name']='商务简餐'; break;
        }
        $data['time']=time();
        //var_dump($data);exit;//

        if( empty($data['name']) ) tool::jsonResult($_POST,'-1','品名不能为空 或 等于零！');
        if( empty($data['price']) ) tool::jsonResult($_POST,'-1','单价不能为空 或 等于零！');
        if( empty($data['stock']) ) tool::jsonResult($_POST,'-1','库存不能为空 或 等于零！');
        if( $data['sales']=='' ) tool::jsonResult($_POST,'-1','销量不能为空！');


        $rowInsert=self::$MS->rowInsert(FOOD,$data);
        if($rowInsert){
            tool::jsonResult($_POST,'1','提交成功！','?/food/foods');
        }

        tool::jsonResult($_POST,'0','提交失败！');
    }


    function foods_img_ax(){

        //$up_id=tool::is_Post('up_id');//
        //if(!empty($up_id)){ self::$MS->rowDel(UPLOAD,'up_id/'.$up_id); }//


        $base64Data=tool::is_Post('file'); //var_dump($base64Data);exit;//
        $base_path='../Upload/food/';
        $day_menu=date::Ymd();
        $file_name='foods_'.date::YmdHsi_No();
        $result=IMG::imageWrite($base64Data,$base_path,$day_menu,$file_name);

        $log_data=array_slice($result,2);
        $log_data['type']='1';
        $log_data['action']=I::have('act');
        $log_data['time']=time();
        $log_data['ip']=tool::ip();
        //var_dump($log_data); exit;//

        $rowInsert=self::$MS->rowInsert(UPLOAD,$log_data);
        if($rowInsert){

            $rowSelect=self::$MS->rowSelect(UPLOAD,'up_id','file_name/'.$result['file_name'].',suffix/'.$result['suffix']);
            if($rowSelect){

                $back=array_slice($result,-5,2);
                $back['img_id']=$rowSelect->up_id;
                tool::jsonResult($back,'0','ok');

            }

        }

        exit;
    }


    function orders(){

        V::tamplate('index');
        $cArr['content']='change_orders';
        V::asChangeArr($cArr);


        ////搜索处理
        $search='';//搜索字段
        $where=[];//基础条件
        $base_url='?/food/orders';//基础链接
        V::asSign('base_url',$base_url);

        $where['payed']='1';

        $name=I::have('name') ;
        $order=I::have('order') ;
        $sta=I::have('sta') ;
        $end=I::have('end') ;
        if($name){
            $food=self::$MS->rowSelect(FOOD,'food_id',array("name/%%"=>$name));
            //var_dump($food);//
            if($food){$where['food_id']=$food->food_id;}else{ $where['food_id']=''; }
            $search.=DIRECTORY_SEPARATOR.'name-'.$name;
        }
        if($order){ $where['order_no/%%']=$order; $search.=DIRECTORY_SEPARATOR.'order-'.$order; }
        if($sta){ $where['time/>=']=strtotime($sta); $search.=DIRECTORY_SEPARATOR.'sta-'.$sta; }
        if($end){ $where['time/<']=strtotime($end); $search.=DIRECTORY_SEPARATOR.'end-'.$end; }

        //var_dump($where);//

        $base_url.=$search;
        //\\


        ////导出表格
        if( I::have('table')=='1' ){
            $select='order_id,uid,order_no,food_id,price,count,subtotal_money,time,ip';
            $ORDER=self::$MS->orderTable(ORDER,$select,$where,'time desc limit '.self::$start.','.self::$step);

            echo'<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
            header("Content-type:application/vnd.ms-excel");
            header("Content-Disposition:filename=售出记录[".$sta.":".$end."].xls");
            echo $ORDER;

            exit;
        }
        //\\


        $select='order_id,uid,order_no,food_id,price,count,subtotal_money,time,ip';
        $foods=self::$MS->orderSelect(ORDER,$select,$where,'time desc limit '.self::$start.','.self::$step);
        //var_dump($USR_INFO);exit;

        if(!$foods){$foods=array();$noneTip='没有了!';}
        else{$noneTip='';}

        V::forList('orders',$foods);
        V::asSign('noneTip',$noneTip);

        ////翻页栏
        $countList=count( self::$MS->resultSelect(ORDER,'order_id',$where) );
        FP::getPage($countList,I::have('p'),self::$step,$base_url);
        v::asSign('countList',FP::$countList);
        V::asSign('pages',FP::$pages);
        V::asSign('cur_page',FP::$cur_page);
        V::asSign('previous',FP::$previous);
        V::asSign('next',FP::$next);
        V::forList('plist',FP::$plist);
        //\\

        V::show();

    }




} 
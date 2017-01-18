<?php
/* Created by User:soma Worker:陈鸿扬 Date: 16/8/15  Time: 09:18 */

namespace Controlers;

use \Commons\tool as tool;//工具类
use \Commons\jump as jump;//跳转类

use \Controlers\urlSerial as I;//序列类
use \Modelers\model as model;//model装载器
use \Views\view as V;//视图类
use \Commons\forPage as FP;//翻页类


class book extends baseControler {

    private static $adminDesc;//parent object

    private static $MS;

    private static $page;//数据库翻页
    private static $step;//翻页步进值
    private static $start;//翻页起始值

    function __construct(){

        //用户登录状态
        self::$adminDesc=$this->adminDesc();
        //\\

        self::$MS=model::init('book');

        ////获取翻页值
        $page=I::have('p')-1;$page=$page>0?$page:'0';
        $step=20;
        $start=$step*$page;//$start=$start>0?$start:$step;
        //var_dump($start);//

        self::$page=$page; self::$step=$step; self::$start=$start;
        //\\

    }

    function books(){

        V::tamplate('index');
        $cArr['content']='change_books';
        V::asChangeArr($cArr);


        ////搜索处理
        $search='';//搜索字段
        $where=[];//基础条件
        $base_url='?/book/books';//基础链接
        V::asSign('base_url',$base_url);

        $name=I::have('name') ;
        $type=I::have('type') ;
        $sta=I::have('sta') ;
        $end=I::have('end') ;
        if($name){
            $where['uid']=self::$MS->rowSelect(USR_INFO,'uid',array("name/%%"=>$name))->uid;
            $search.=DIRECTORY_SEPARATOR.'name-'.$name;
        }
        if($type){ $where['type']=$type; $search.=DIRECTORY_SEPARATOR.'type-'.$type; }
        if($sta){ $where['book_time/>=']=strtotime($sta); $search.=DIRECTORY_SEPARATOR.'sta-'.$sta; }
        if($end){ $where['book_time/<']=strtotime($end); $search.=DIRECTORY_SEPARATOR.'end-'.$end; }

        //var_dump($where);exit;//
        $base_url.=$search;
        //\\



        ////导出表格
        if( I::have('table')=='1' ){
            $select='book_id,uid,type,type_name,time_type,book_time,ip';
            $BOOK=self::$MS->booksTable(BOOK,$select,$where,'book_time desc limit '.self::$start.','.self::$step);

            if($BOOK){
                echo'<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
                header("Content-type:application/vnd.ms-excel");
                header("Content-Disposition:filename=预定记录[".$sta.":".$end."].xls");
                echo $BOOK;
            }else{
                exit('没有了！');
            }

            exit;
        }
        //\\



        ////当前页数据
        $select='book_id,uid,type,type_name,time_type,book_time,ip';
        $books=self::$MS->booksSelect(BOOK,$select,$where,'book_time desc limit '.self::$start.','.self::$step);
        //var_dump($books);exit;
        if(!$books){$books=array();$noneTip='没有了!';}
        else{$noneTip='';}
        V::forList('books',$books);
        V::asSign('noneTip',$noneTip);
        //\\

        ////翻页栏
        $countList=count( self::$MS->resultSelect(BOOK,'book_id',$where) );
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

    function payed(){


        V::tamplate('index');
        $cArr['content']='change_payed';
        V::asChangeArr($cArr);

        ////搜索处理
        $search='';//搜索字段
        $where=[];//基础条件
        $base_url='?/book/payed';//基础链接
        V::asSign('base_url',$base_url);

        $where['payed']='1';

        $name=I::have('name') ;
        $order=I::have('order') ;
        $sta=I::have('sta') ;
        $end=I::have('end') ;
        if($name){
            $where['uid']=self::$MS->rowSelect(USR_INFO,'uid',array("name/%%"=>$name))->uid;
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
            $select='payed_id,order_no,food_id_list,uid,cid,total_money,people_money,total_benefit,time,ip';
            $PAYED=self::$MS->payedTable(PAYED,$select,$where,'time desc limit '.self::$start.','.self::$step);

            if($PAYED){
                echo'<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
                header("Content-type:application/vnd.ms-excel");
                header("Content-Disposition:filename=消费记录[".$sta.":".$end."].xls");
                echo $PAYED;
            }else{
                exit('没有了！');
            }

            exit;
        }
        //\\


        ////当前页数据
        $select='payed_id,order_no,food_id_list,uid,cid,total_money,people_money,total_benefit,time,ip';
        $payed=self::$MS->payedSelect(PAYED,$select,$where,'time desc limit '.self::$start.','.self::$step);
        //var_dump($payed);exit;

        if(!$payed){$payed=array();$noneTip='没有了!';}
        else{$noneTip='';}

        V::forList('payed',$payed);
        V::asSign('noneTip',$noneTip);
        //\\

        ////翻页栏
        $countList=count( self::$MS->resultSelect(PAYED,'payed_id',$where) );
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


    function scangun(){

        V::tamplate('index');
        $cArr['content']='change_scangun';
        V::asChangeArr($cArr);

        V::show();

    }






} 
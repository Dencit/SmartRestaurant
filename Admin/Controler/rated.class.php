<?php
/* Created by User:soma Worker:陈鸿扬 Date: 16/8/15  Time: 09:18 */

namespace Controlers;

use \Commons\date as date;
use \Commons\tool as tool;
use \Commons\jump as jump;

use \Controlers\urlSerial as I;
use \Modelers\model as model;//model装载器
use \Views\view as V;

use \Commons\forPage as FP;

class rated extends baseControler {

    private static $adminDesc;//parent object

    private static $MS;

    private static $page;//数据库翻页
    private static $step;//翻页步进值
    private static $start;//翻页起始值

    function __construct(){

        //用户登录状态
        self::$adminDesc=$this->adminDesc();
        //\\

        self::$MS=model::init('rated');

        ////获取翻页值
        $page=I::have('p')-1;$page=$page>0?$page:'0';
        $step=20;
        $start=$step*$page;//$start=$start>0?$start:$step;
        //var_dump($start);//

        self::$page=$page; self::$step=$step; self::$start=$start;
        //\\

    }

    function rateds(){


        V::tamplate('index');
        $cArr['content']='change_rateds';
        V::asChangeArr($cArr);

        ////搜索处理
        $search='';//搜索字段
        $where=[];//基础条件
        $base_url='?/rated/rateds';//基础链接
        V::asSign('base_url',$base_url);


        $name=I::have('name') ;
        $sta=I::have('sta') ;
        $end=I::have('end') ;
        if($name){
            $where['uid']=self::$MS->rowSelect(USR_INFO,'uid',array("name/%%"=>$name))->uid;
            $search.=DIRECTORY_SEPARATOR.'name-'.$name;
        }
        if($sta){ $where['time/>=']=strtotime($sta); $search.=DIRECTORY_SEPARATOR.'sta-'.$sta; }
        if($end){ $where['time/<']=strtotime($end); $search.=DIRECTORY_SEPARATOR.'end-'.$end; }

        //var_dump($where);exit;//

        $base_url.=$search;
        //\\

        ////当前页数据
        $select='rated_id,uid,food_qual,serv_qual,envi_qual,advise,time,ip';

        $rateds=self::$MS->ratedSelect(RATED,$select,$where,'time desc limit '.self::$start.','.self::$step);
        //var_dump($rateds);exit;
        if(!$rateds){$rateds=array();$noneTip='没有了!';}
        else{$noneTip='';}
        V::forList('rateds',$rateds);
        V::asSign('noneTip',$noneTip);
        //\\

        ////翻页栏
        $countList=count( self::$MS->resultSelect(RATED,'rated_id',$where) );
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

    function rateds_edit_ax(){

        $rowId=tool::is_Post('rowId');

        $select='rated_id,uid,food_qual,serv_qual,envi_qual,advise,time,ip';
        $where['rated_id']=$rowId;
        $rowSelect=self::$MS->rowSelect(RATED,$select,$where);

        if($rowSelect){

            $usrInfo=self::$MS->rowSelect(USR_INFO,'name','uid/'.$rowSelect->uid);
            if($usrInfo)$rowSelect->name=$usrInfo->name;

            $rowSelect->time=date::Ymd($rowSelect->time).' '.date::AoPoN_Check('chr',$rowSelect->time);

            if($rowSelect->advise=='')$rowSelect->advise='无';

            tool::jsonResult($rowSelect,'0');

        }

    }



} 
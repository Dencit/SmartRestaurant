<?php
/* Created by User: soma Worker:陈鸿扬  Date: 16/10/17  Time: 12:10 */

namespace Controlers;

use \Commons\tool as tool;//工具类
use \Commons\jump as jump;//跳转类
use \Commons\date as date;//日期类
use Debugs\frameDebug as FD;

use \Controlers\urlSerial as I;
use \Modelers\model as model;//model装载器
use \Views\view as V;

use \Https\weiApi as weiApi;//微信API
use Commons\probability as probability;//概率工具组


class info  extends baseControler {

    private $uid;
    private $mobile;

    private $MS;

    function __construct(){


        //判断有没有 session uid
        $this->uid = isset($_SESSION[PREFIX.'uid']) ? trim($_SESSION[PREFIX.'uid']) : '';
        //exit($this->uid);//
        if($this->uid ==''){
            //$jsonPost['checkUid']='noUid';//
            //tool::jsonExit($jsonPost);//
            jump::head('?/weixin/index');
        }

        //判断有没有 session mobile
        $this->mobile = isset($_SESSION[PREFIX.'mobile']) ? trim($_SESSION[PREFIX.'mobile']) : '';
        if($this->mobile==''){
            //$jsonPost['checkMobile']='noMobile';//
            //tool::jsonExit($jsonPost);//
            jump::head('?/account/login');//
        }

        model::init('info');//一个 controler 对应一个 同名 modeler
        $this->MS = model::set();//加载 index modeler 可同时传参给 构造函数


    }


    function pushNews(){

        V::tamplate('pushNews');

        V::asSign('date',date::Ymd());
        V::asSign('weekday',date::weekday());

        $MS=$this->MS;

    ////组合推送餐品数据
        $select=array('image','name','type');
        $pushNews=$MS->resultByType(FOOD,$select,'-','-','type');
        //$pushFarther=$pushNews;//测试

        //print_r($pushNews);//exit;
        foreach($pushNews as $n=>$v){

            //var_dump($v['type_info']);exit;//
            switch($v['type']){
                default:$type_con='无类型';break;
                case 'a1' :$type_con='早餐';break;
                case 'p1' :$type_con='抓饭';break;
                case 'p2' :$type_con='拌饭';break;
                case 'p3' :$type_con='商务简餐';break;
            }
            $pushNews[$n]['type']=$type_con;

            /*
            $type_info_str='';
            foreach($v['type_info'] as $m=>$a ){
                //var_dump($a);exit;//
                $type_info_str.='
                <dd>
                    <a href="javascript:void(0);">
                        <div class="pushNewsListImg"><img src="'.$a->image.'" alt="'.$a->name.'"></div>
                        <p class="pushNewsListP">'.$a->name.'</p>
                    </a>
                </dd>
                ';
            }
            //var_dump($type_info_str);exit;//
            $pushNews[$n]['type_info']=$type_info_str;
            */

        }
        //print_r($pushNews);exit;
    ////\\组合推送餐品数据

        $AoPoN_Check=date::AoPoN_Check();
        var_dump($AoPoN_Check);

        v::asSign('title','推送消息');

        //V::forList('pushFarther',$pushFarther);//测试
        V::forList('pushNews',$pushNews);

        //v::asSign('nav_active','0');
        V::show();
    }

    function news(){

        $MS=$this->MS;

        $foodList=$MS->foodSalesCount();
        //var_dump($foodList);exit;//
        //exit(date::dateOrderNo());//

        V::tamplate('news');

        V::forList('foods',$foodList);

        v::asSign('title','餐饮下单');
        v::asSign('date',date::Ymd() );
        v::asSign('weekday',date::weekday() );


        V::show();

    }




} 
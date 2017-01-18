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



class info  extends baseControler {

    private $glob_usr;
    private $uid;

    private $mobile;

    private $MS;

    function __construct(){


        //用户登录状态
        $glob_usr=self::homeDesc();
        //var_dump( $glob_usr );exit;//
        $this->glob_usr=$glob_usr; $this->uid=$glob_usr->uid;
        //\\

        $uri=urlRoute::get();
        //var_dump($uri);//exit;//

        //判断有没有 session mobile //才能进入点餐页面
        $this->mobile = isset($_SESSION[PREFIX.'mobile']) ? trim($_SESSION[PREFIX.'mobile']) : '';
        if($this->mobile==''&&$uri[1]=='news'){
            //$jsonPost['checkMobile']='noMobile';//
            //tool::jsonExit($jsonPost);//

            //jump::head('?/account/login');////
        }

        model::init('info');//一个 controler 对应一个 同名 modeler
        $this->MS = model::set();//加载 index modeler 可同时传参给 构造函数

    }


    function pushNews(){

        $aop=tool::isSetRe( I::have('aop') );//早餐 午餐 类型判断
        $date=tool::isSetRe( I::have('date') );
        if(!$aop||!$date){exit('aop or date fail');};

        V::tamplate('pushNews');

        V::asSign('date',date::Ymd( strtotime($date) ));
        V::asSign('weekday',date::weekday( strtotime($date) ));

        $MS=$this->MS;

    ////组合推送餐品数据
        $select=array('image','name','type','allow');
        $pushNews=$MS->resultByType(FOOD,$select,'allow/1','-','type');
        //print_r($pushNews);//exit;//


        if($aop=='1'){
            V::asSign('AoP','早餐');//选项标题
            unset($pushNews[1]);unset($pushNews[2]);unset($pushNews[3]);
        }elseif($aop=='2'){
            V::asSign('AoP','午餐');//选项标题
            unset($pushNews[0]);
        }

        $choose=[];
        foreach($pushNews as $n=>$v){
            //var_dump($v['type_info']);exit;//

            //一级标题
            switch($v['type']){
                default:$type_con='无类型';break;
                case 'a1' :$type_con='早餐';break;
                case 'p1' :$type_con='抓饭';break;
                case 'p2' :$type_con='拌饭';break;
                case 'p3' :$type_con='商务简餐';break;
            }
            $pushNews[$n]['type']=$type_con;

            //选项
            $choose[$n]['type']=$v['type'];
            $choose[$n]['choose_name']=$type_con;
        }
        //print_r($choose);exit;
        //print_r($pushNews);exit;//
    ////\\组合推送餐品数据



        v::asSign('title','推送消息');

        V::forList('pushNews',$pushNews);

        //选项
        V::forList('choose',$choose);

        V::show();
    }

    function pushNews_ax(){

        //var_dump($_POST);exit;//
        $date=tool::is_Post('date');
        if(!$date){ exit('date fail'); }

        $MS=$this->MS;

        $data['type']='food';
        $data=tool::is_Post($data);
        switch($data['type']){
            default:$type_con='无类型';break;
            case 'a1' :$type_con='早餐';break;
            case 'p1' :$type_con='抓饭';break;
            case 'p2' :$type_con='拌饭';break;
            case 'p3' :$type_con='商务简餐';break;
        }
        $data['type_name']=$type_con;

        $data['uid']=$this->uid;
        $data['time']=TIME;
        $data['book_time']=strtotime($date)+1;
        //var_dump(strtotime($date));var_dump( (strtotime($date)+1) );exit;//

        $data['time_type']=date::AoPoN_Check();
        $data['ip']=tool::ip();

        //var_dump($data);exit;//

        switch($data['type']){
            default:

                //二十分钟间隔 提交限制 1200秒
                $haveSomeSecond=$MS->haveSomeSecond(BOOK,$this->uid,'10','time');
                if($haveSomeSecond){
                    tool::jsonResult($_POST,'-1','十分钟内请勿重复提交!');
                }
                $MS->rowInsert(BOOK,$data);
                tool::jsonResult($_POST,'0','预订成功!');
                break;
            case '0':
                //不吃的情况
                tool::jsonResult($_POST,'-1','感谢您的支持!');
                break;
        }

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
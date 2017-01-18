<?php
/* Created by User: soma Worker:陈鸿扬  Date: 16/10/19  Time: 11:09 */

namespace Controlers;

use \Commons\tool as tool;//工具类
use \Commons\jump as jump;
use \Commons\date as date;
use Debugs\frameDebug as FD;

use \Controlers\urlSerial as I;
use \Modelers\model as model;//model装载器
use \Views\view as V;


class account extends baseControler{

    private $glob_usr;
    private $uid;

    private $mobile;
    private $order_cashier_no;

    private $MS;

    function __construct(){

        //用户登录状态
        $glob_usr=self::homeDesc();
        //var_dump( $glob_usr );exit;//
        $this->glob_usr=$glob_usr; $this->uid=$glob_usr->uid;
        //\\

        //收银员 单号
        $this->order_cashier_no=tool::get_session('order_cashier_no');

        model::init('account');//一个 controler 对应一个 同名 modeler
        $this->MS = model::set();//加载 index modeler 可同时传参给 构造函数

    }


    function flowCheck(){
        exit('flowCheck');
    }


    //基本
    function login(){

        $MS=$this->MS;
        $whereArray['uid']=$this->uid;

        $usrInfo=$MS->rowSelect(USR_INFO,'idcheck',$whereArray);
        //var_dump($usrInfo);exit;//
        if(!$usrInfo){ exit('id fail!'); }
        $idChecked=$usrInfo->idcheck;

        //已经手机登陆过的 直接跳过
        if(tool::get_session('mobile')) jump::head('?/info/news');
        //

        V::tamplate('login');

        switch(@$idChecked){
            default:
                $cArr['content']='login_change_1'; break;
                break;
            case '0':
                $cArr['content']='login_change_1'; break;
                break;
            case '1':
                $cArr['content']='login_change_3'; break;
                break;
        }


        V::asChangeArr($cArr);

        V::asSign('uid',$this->uid);
        v::asSign('title','登陆');

        V::show();

    }


    function cashier_login(){

        $MS=$this->MS;
        $whereArray['uid']=$this->uid;

        $idChecked=$MS->rowSelect(USR_INFO,'idcheck',$whereArray)->idcheck;
        //var_dump($idChecked);exit('cashier_login');//
        if($idChecked==0)jump::head('?/account/login/');

        //收银员 免手机号登陆//需 idcheck==1 审核通过才执行
        $order_cashier_no=$this->order_cashier_no;
        //var_dump($order_cashier_no);//exit;//
        if($order_cashier_no && $idChecked=='1'){
            $cashier_mobile=$MS->rowSelect(USR_INFO,'mobile',$whereArray)->mobile;
            //var_dump($cashier_mobile);exit('$cashier_mobile');//

            $kv_array['mobile']=$cashier_mobile;
            tool::mk_session($kv_array);
            //var_dump( tool::get_session('mobile') );//

            //exit ('cashier_login');

            jump::head('?/payment/cashier');exit;
        }
        //

    }


    function idCard_check_ax(){

        $MS=$this->MS;

        $id_card=$_POST['id_card'];

        $userInfo=$MS->idCardCheck($id_card,$this->uid);
        //echo($userInfo->name);exit;//

        if($userInfo){
            $_POST['name']=$userInfo->name;
            $_POST['depart']=$userInfo->depart;

            tool::jsonResult($_POST,'0','该身份有效,请填写该微信绑定的手机号,以激活身份！');
        }else{
            tool::jsonResult($_POST,'-1','该身份未登记或不合法,请联系管理员!');
        }

    }

    function mobile_check_ax(){
        $MS=$this->MS;

        $id_card=$_POST['id_card'];
        $mobile=$_POST['mobile'];

        if( $userInfo=$MS->idCardCheck($id_card,$this->uid) ){

            $dataArray['idcheck']='1';
            $dataArray['mobile']=$mobile;
            $whereArray['idcard']=$id_card;

            $mobileUpdate=$MS->rowUpdate(USR_INFO,$dataArray,$whereArray);

            //$order_cashier_no=$this->order_cashier_no;//

            if($mobileUpdate){
                //tool::jsonResult($_POST,'1','提交成功!','');//
                tool::jsonResult($_POST,'1','提交成功!','?/info/news');
            }else{
                //tool::jsonResult($_POST,'0','请勿重复提交！');//
                tool::jsonResult($_POST,'0','请勿重复提交','?/info/news');
            }

        }else{
            tool::jsonResult($_POST,'-1','该身份未登记,请联系管理员!','?/account/login/step-1');
        }

    }

    function mobileLogin_ax(){

        $order_cashier_no=$this->order_cashier_no;
        //var_dump($order_cashier_no);exit;//

        $MS=$this->MS;

        $whereArray['uid']=$this->uid;
        $userInfo=$MS->rowSelect(USR_INFO,'mobile',$whereArray);
        //var_dump($userInfo->mobile);exit;//

        $jumpto=tool::get_session('jumpTo');//其它页面过期重新授权,预留的回跳记号

        if($_POST['mobile']==$userInfo->mobile){

            $_SESSION[PREFIX.'mobile']=$userInfo->mobile;

            if($order_cashier_no)tool::jsonResult($_POST,'1','','?/payment/cashier');
            else{

                if($jumpto=='perInfo'){
                    tool::get_session('jumpTo',1);
                    tool::jsonResult($_POST,'1','','?/account/perInfo');
                }else{
                    tool::jsonResult($_POST,'1','','?/info/news');
                    //tool::jsonResult($_POST,'1','提交成功!','');//
                }
            }


        }else{

            unset( $_SESSION[PREFIX.'mobile'] );
            tool::jsonResult($_POST,'0','手机号不对!');

        }

    }



    function advise(){


        $MS_INFO=model::init('info');//临时获取info model

        $select=array('image','name');
        $order['time']='desc';

        $food_list=$MS_INFO->foodSalesCount();

        //var_dump($food_list);exit;//

        V::tamplate('advise');
        v::asSign('title','点评建议');

        v::asSign('date',date::Ymd());
        v::asSign('weekday',date::weekday());

        V::forList('food_list',$food_list);

        v::asSign('nav_active','1');
        V::show();
    }

    function advise_ax(){

        $MS=$this->MS;

        //二十分钟间隔 提交限制 1200秒
        $havePostTime=$MS->rowSelect( RATED,'time',array('uid'=>$this->uid),array('time'=>'desc') );
        //var_dump($havePostTime);exit;//
        if($havePostTime && TIME < ($havePostTime->time+1200) ){
            tool::jsonResult($_POST,'-1','二十分钟内请勿重复提交!');
        }

        //执行新增
        $data['food_qual']='foodQu';
        $data['serv_qual']='serveQu';
        $data['envi_qual']='environmentQu';
        $data['advise']='advise_info';
        $data=tool::is_Post($data);
        $data['advise']=tool::filter_mark( strip_tags($data['advise']) );
        $data['uid']=$this->uid;
        $data['time']=TIME;
        $data['ip']=tool::ip();

        $advisePost=$MS->rowInsert(RATED,$data);

        if($advisePost){
            tool::jsonResult($data,'0','点评建议成功!');
        }


    }

    function perInfo(){

        $MS=$this->MS;

        $where['uid']=$this->uid;

        $select=array('name','sex','mobile','photo','idcard','depart');
        $perInfo=$MS->rowSelect(USR_INFO,$select,$where);

        $select_acc=array('balance','subsidy');
        $account=$MS->rowSelect(ACCOUNT,$select_acc,$where);

        V::tamplate('perInfo');

        v::asSign('title','个人信息');

        switch($perInfo->sex){
            case '0' : $perInfo->sex=""; break;
            case '1' :  $perInfo->sex='<i class="icon-man" style="color:#0044cc;"></i>'; break;
            case '2' : $perInfo->sex='<i class="icon-woman" ></i>'; break;
        }


        v::asSignArr($perInfo);
        V::asSignArr($account);

        v::asSign('nav_active','2');

        V::show();

    }

    function conDetail(){

        /*var_dump( strtotime('2016-10-1') );var_dump( strtotime('2016-10-31') );//
        var_dump( date::toMouthSide(0) );var_dump( date::toMouthSide(1) );
        exit;*/

        $MS=$this->MS;

        $select=array('order_no','food_id_list','uid','total_money','time');
        $where['uid']=$this->uid;
        $where['time/>']=date::toMouthSide(0);
        $where['time/<']=date::toMouthSide(1);
        $order['time']='desc';
        $payed_list=$MS->resultSelect(PAYED,$select,$where,$order);

        if(!$payed_list){

            V::tamplate('conDetail');

            V::forList('payed_list',array());
            V::asSign('none','<li style="text-align: center"><h3>本月无消费</h3></li>');

            v::asSign('toMouth', date::Ym_str() );
            v::asSign('total_money_mouth','0.00');
            v::asSign('title','消费详情');
            v::asSign('nav_active','2');

            V::show();
            //exit('none $payed_list');

            exit;
        }

        //echo date::Ymd().'||'.date::weekday().'||'.date::Hs();exit;//

        $payed_list_out=array();

        $total_money_mouth=floatval('0.00');
        foreach($payed_list as $k=>$v){

            $time=$v->time;
            $v->Ymd=date::Ymd($time);
            $v->weekday=date::weekday($time);
            $v->Hs=date::Hs($time);

            $food_id_seri=unserialize($v->food_id_list);

            $food_data='';
            foreach($food_id_seri as $m=>$n){
                $food_data.=$MS->rowSelect(FOOD,array('name'),array("food_id"=>$n))->name."<br/>";
            }
            $v->food_id_seri=$food_data;

            $payed_list_out[$k]=$v;
            $total_money_mouth+=floatval($v->total_money);
        }

        //var_dump($payed_list_out);exit;//

        V::tamplate('conDetail');

        V::asSign('none','');

        V::forList('payed_list',$payed_list_out);

        v::asSign('toMouth', date::Ym_str() );
        v::asSign('total_money_mouth', $total_money_mouth );
        v::asSign('title','消费详情');
        v::asSign('nav_active','2');

        V::show();

    }



} 
<?php
/* Created by User: soma Worker:陈鸿扬  Date: 16/11/10  Time: 16:48 */

namespace Controlers;

use \Commons\tool as tool;//工具类
use \Commons\jump as jump;
use \Commons\date as date;

use \Controlers\urlSerial as I;
use \Modelers\model as model;
use \NoSql\redis as redis;//noSql redis类

use \Apis\weicoApi as weicoApi;//微信功能接口

class wxpay extends baseControler {

    private static $api;

    private $glob_usr;
    private $uid;

    private $MS;

    function __construct(){

        self::$api = new weicoApi;


        model::init('wxpay');//一个 controler 对应一个 同名 modeler
        $this->MS = model::set();//加载 index modeler 可同时传参给 构造函数

        ////wxpay公用类 //无命名空间调用
        require_once LIBRARY."/Thirds/wxpayApi/lib/WxPay.Exception.php";
        require_once LIBRARY."/Thirds/wxpayApi/lib/WxPay.Config.php";
        require_once LIBRARY."/Thirds/wxpayApi/lib/WxPay.Data.php";
        require_once LIBRARY."/Thirds/wxpayApi/lib/WxPay.Api.php";
        require_once LIBRARY."/Thirds/wxpayApi/lib/log.php";
        //\\

    }

    function printf_info($data)
    {
        foreach($data as $key=>$value){
            echo "<font color='#00ff55;'>$key</font> : $value <br/>";
        }
    }

    function jsApi(){


        //用户登录状态
        $glob_usr=self::homeDesc();
        //var_dump( $glob_usr );exit;//
        $this->glob_usr=$glob_usr; $this->uid=$glob_usr->uid;
        //\\

        $MS=$this->MS;

        ////处理post 拼接充值记录 与 recharge表 字段对应
        $rechData['text']='text';
        $rechData['recharge_menoy']='money';
        $rechData=tool::is_Post($rechData);
        //var_dump($rechData);exit;//

        if(empty($rechData['text'])&&$rechData['recharge_menoy']=='on'){
            tool::jsonResult($rechData,'-1','请输入金额！');
        }
        else if(!empty($rechData['text'])&&$rechData['recharge_menoy']=='on'){
            $rechData['recharge_menoy']=$rechData['text'];
        }
        unset($rechData['text']);

        $fee_menoy=floatval($rechData['recharge_menoy']*100);
        //var_dump($fee_menoy);exit;//
        //\\


        ////wxpay 支付类 //无命名空间调用
        require_once LIBRARY."/Thirds/wxpayApi/lib/WxPay.JsApiPay.php";
        //\\

        //①、获取用户openid
        $tools = new \JsApiPay();
        //$openId = $tools->GetOpenid();//


/*
        $uid=$this->uid;//var_dump($uid);exit;//
        $USR=$MS->rowSelect(USR,'userid','uid/'.$uid);
        //var_dump($USR);//
        if($USR){
            //这里获取的是 面向整个企业号的 openid 不是面向某个应用id的 openid
            $corp_userid=$USR->userid;
            $access_token=$access_token = redis::get(PREFIX."access_token");
            $uid2openid=self::$api->convert_to_openid($access_token,array("userid"=>$corp_userid));
            $corp_openid=$uid2openid->openid;
        }
        //var_dump($corp_userid);var_dump($corp_openid);exit;//
*/

/*
        $uid=$this->uid;//var_dump($uid);exit;//
        $USR=$MS->rowSelect(USR,'openid','uid/'.$uid);
        //var_dump($USR);exit;//
        if($USR){
            $corp_openid=$USR->openid;
        }
        //var_dump($corp_openid);//exit;//
*/


        //var_dump( (int)$this->glob_usr->openId );exit;
        $corp_openid=$this->glob_usr->openId;


        //②、统一下单
        $input = new \WxPayUnifiedOrder();
        $input->SetBody("智慧餐厅个人钱包充值");
        $input->SetAttach("个人充值");
        $input->SetOut_trade_no(\WxPayConfig::MCHID.date("YmdHis"));
        //$input->SetTotal_fee("1");//
        $input->SetTotal_fee($fee_menoy);//$fee_menoy
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetGoods_tag("钱包充值");
        $input->SetNotify_url(HTTP_BASE."/Api/wxpay/result/uid-".$this->uid);
        $input->SetTrade_type("JSAPI");
        $input->SetOpenid( urldecode($corp_openid) );
        //var_dump($input);exit;//

        $order = \WxPayApi::unifiedOrder($input);
        $jsApiParameters = $tools->GetJsApiParameters($order);
        exit($jsApiParameters);//

    }



    //
    function result(){
        $MS=$this->MS;

        ////wxpay 回调类 //无命名空间调用
        require_once LIBRARY."/Thirds/wxpayApi/lib/WxPay.Notify.php";
        require_once LIBRARY."/Thirds/wxpayApi/lib/WxPay.PayNotifyCallBack.php";
        //\\

        ////初始化日志
        $logHandler= new \CLogFileHandler("./Logs/".date('Y-m-d').'.log');
        $log = \Log::Init($logHandler, 15);
        //\\

        ////接收http回调xml
        $notify = new \PayNotifyCallBack();
        //$notify->Handle(true);//微信回调数据成功合格,并且业务逻辑处理完的时候 直接打印出来给验证
        $xml=file_get_contents("php://input"); $xmlArr=$notify->FromXml($xml);
        //$log::DEBUG("xmlArr :".json_encode( $xmlArr ) );//
        $notify_check=$notify->Queryorder($xmlArr['transaction_id']);
        //$log::DEBUG("notify_check :".json_encode( $notify_check ) );//
        if(!$notify_check){ $log::DEBUG("return_code : fail!");exit;}//不对则终止
        //\\

        $uid=tool::isSetRe( I::have('uid') );

        ////检查单号 预防重复回调
        $RECHARGE_HAD=$MS->rowSelect(RECHARGE,'uid,out_trade_no','out_trade_no/'.$xmlArr['out_trade_no'].',uid/'.$uid);
        //$log::DEBUG("RECHARGE_HAD:".json_encode($RECHARGE_HAD));//
        if($RECHARGE_HAD){$log::DEBUG("RECHARGE_HAD");exit;}
        //\\


        ////拼接充值记录 与 recharge表 字段对应
        $rechData['sign']=$xmlArr['sign'];
        $rechData['openid']=$xmlArr['openid'];
        $rechData['out_trade_no']=$xmlArr['out_trade_no'];
        $rechData['transaction_id']=$xmlArr['transaction_id'];
        $rechData['return_code']=$xmlArr['return_code'];

        $rechData['recharge_menoy']=floatval( $xmlArr['cash_fee']/100 );
        //$rechData['recharge_menoy']='22';//测试用
        //$rechData['recharge_menoy']='44';//测试用
        $rechData['uid']=$uid;
        $rechData['subsidy_menoy']='0';
        $rechData['time']=strtotime($xmlArr['time_end']);
        $rechData['ip']=tool::ip();
        $log::DEBUG("rechData:".json_encode( $rechData ) );//
        //\\

        //充值公用变量
        $balance_add=$rechData['recharge_menoy'];//要给钱包加的值
        $subsidy_add=$rechData['subsidy_menoy'];//首充补贴
        $subsidy_type='0';//用户补贴记号//非在编为零
        $time_recharge=$rechData['time'];//更新时间值 都用这里的 引用变量
        //

        //判断是否在编会员
        $USR_INFO=$MS->rowSelect(USR_INFO,"`join`",array('uid'=>$uid));
        if(!$USR_INFO){$log::DEBUG('$USR_INFO fail');exit;}
        $is_join=$USR_INFO->join; //$log::DEBUG('is join:'.$is_join);//

        //不是在编会员
        if($is_join=='0'){

            ////修改用户补贴记号
            $subsidySign=$MS->rowUpdate(USR_INFO, array('subsidy_type' => $subsidy_type), "uid/" . $this->uid);
            if(!$subsidySign){ $log::DEBUG('$subsidySign had 0!'); }
            //\\

            ////正常充值
            $accountRefl=$MS->accountRefl($uid,$balance_add,$subsidy_add,$time_recharge);
            //$log::DEBUG("accountRefl:".json_encode($accountRefl) );//
            if(!$accountRefl){ $log::DEBUG("accountRefl fail!");exit; }
            //\\

            ////开始写充值记录
            $rechargeSet=$MS->rowInsert(RECHARGE,$rechData);
            if(!$rechargeSet){ $log::DEBUG('$rechargeSet fail!');exit; }

            //tool::jsonResult($rechData,'0','非在编人员,充值成功!','?/payment/recharge/step-1');
            $log::DEBUG("[uid:".$uid."] out join member recharge success!");//非在编人员,充值成功
            $notify->Handle(true);exit;//微信回调数据成功合格,并且业务逻辑处理完的时候 直接打印出来给验证
            //\\
        }

        //是在编会员
        if($is_join=='1'){

            //当月首充补贴处理 //月底 补贴自动清零时 处理 USR_INFO、ACCOUNT、表 LOG表记录日志

            //用户补贴标记 预定
            if($balance_add>='22'&&$balance_add<'44'){ $subsidy_type='1'; }
            elseif($balance_add>='44'){ $subsidy_type='2'; }

            //当月首充判断 //条件：充值>=22 的最新记录
            $where['uid']=$uid;
            $where['time/>=']=date::toMouthSide(0);
            $where['time/<']=date::toMouthSide(1);
            $where['recharge_menoy/>=']='22';
            $where['subsidy_menoy']='330';
            //var_dump($where);//exit;//
            $recharged=$MS->rowSelect(RECHARGE,'*',$where,'time desc');

            //是否当月首充 >=22
            if(!$recharged&&$balance_add>='22'){
                //首充
                $rechData['subsidy_menoy']='330';//首充补贴
                if($balance_add>='22') {

                    ////修改用户补贴记号
                    $subsidySign=$MS->rowUpdate(USR_INFO, array('subsidy_type' => $subsidy_type), "uid/" . $uid);
                    if(!$subsidySign){ $log::DEBUG("subsidySign had 1 !"); }
                    //\\

                }
            }else{
                //非首充 //因为首充“>=22”已经自动加补贴330 和 修改用户补贴记号为“1”只补贴早餐, 再充值时除非“>=44”,否则不修改补贴记号为2
                $rechData['subsidy_menoy']='0';//补贴
                if($balance_add>='44'){

                    //exit($balance_add);//

                    ////修改用户补贴记号
                    $subsidySign=$MS->rowUpdate(USR_INFO, array('subsidy_type' => $subsidy_type), "uid/" . $uid);
                    if(!$subsidySign){  $log::DEBUG("subsidySign had!'"); }
                    //\\

                }
            }


            $subsidy_add=$rechData['subsidy_menoy'];//重新引用 充值预存变量的 首充补贴

            //$log::DEBUG("subsidy_add".$subsidy_add);//
            //$log::DEBUG("balance_add".$balance_add);//


            ////正常充值
            $accountRefl=$MS->accountRefl($uid,$balance_add,$subsidy_add,$time_recharge);
            //$log::DEBUG("accountRefl:".json_encode($accountRefl) );//
            if(!$accountRefl){ $log::DEBUG("accountRefl fail!");exit; }
            //\\

            ////开始写充值记录
            $rechargeSet=$MS->rowInsert(RECHARGE,$rechData);
            //$log::DEBUG("rechData".json_encode($rechargeSet) );//
            if(!$rechargeSet){ $log::DEBUG("rechargeSet fail!");exit; }

            //tool::jsonResult($rechData,'0','在编人员,充值成功!','?/payment/recharge/step-1');
            $log::DEBUG("[uid:".$uid."] in join member recharge success!");
            $notify->Handle(true);exit;//微信回调数据成功合格,并且业务逻辑处理完的时候 直接打印出来给验证
            //\\


        }



    }



} 
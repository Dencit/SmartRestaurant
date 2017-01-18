<?php
/* Created by User: soma Worker:陈鸿扬  Date: 16/10/19  Time: 11:04 */

namespace Controlers;

use \Commons\tool as tool;//工具类
use \Commons\jump as jump;
use \Commons\date as date;//日期类
//use \Thirds\qrcode as QRcode;
use Debugs\frameDebug as FD;

use \Controlers\urlSerial as I;
use \Controlers\urlRoute as U;
use \Modelers\model as model;//model装载器
use \Views\view as V;



class payment extends baseControler {

    private $glob_usr;
    private $uid;


    private $mobile;
    private $order_no;

    private $MS;

    function __construct(){

        //用户登录状态
        $glob_usr=self::homeDesc();
        //var_dump( $glob_usr );exit;//
        $this->glob_usr=$glob_usr; $this->uid=$glob_usr->uid;
        //\\

        ////判断有没有 session mobile //
        $U=U::get();
        //var_dump($U[1]);exit;
        if($U[1]!="cashier"&&$U[1]!="cashier_ax"){

            //判断有没有 session mobile
            $this->mobile = isset($_SESSION[PREFIX.'mobile']) ? trim($_SESSION[PREFIX.'mobile']) : '';


            //if($this->mobile==''){ jump::head('?/account/login');}////

        }
        //\\

        model::init('payment');//一个 controler 对应一个 同名 modeler
        $this->MS = model::set();//加载 index modeler 可同时传参给 构造函数

        //session_unset();session_destroy();//

    }


//支付

    function pay_ax(){

        $MS=$this->MS;

        ////预防 空订单提交
        foreach($_POST as $k=>$v){
            if($v['count']=='0') tool::jsonResult($_POST,'-1','','?/info/news/');
        }
        //\\

        ////检查用户 取消支付 或 支付失败 的订单//清除
        $dataArray['uid']=$this->uid;
        $dataArray['order_no']=date::dateOrderNo();
        $dataArray['time']=TIME;
        $time_type=date::AoPoN_Check('num');//早午晚检查
        //var_dump( $time_type );exit;//


        //if($time_type=='3' || $time_type=='0')tool::jsonResult($_POST,'-1','已过用餐时间！');
        $dataArray['time_type']=$time_type;

        $dataArray['ip']=tool::get_ip();
        $whereArray['uid']=$this->uid;
        $whereArray['payed']='0';
        $MS->rowDel(ORDER,$whereArray);
        //exit;//
        //\\

        ////下单 统计
        //查FOOD表 对单品做小计 入ORDER表
        foreach($_POST as $k=>$v){

            $food_price=$MS->rowSelect(FOOD,'price',array("food_id"=>$v['food_id']))->price;
            //var_dump($food_price);exit;//

            $dataArray['food_id']=$v['food_id'];
            $dataArray['price']=$food_price;
            $dataArray['count']=$v['count'];
            $dataArray['subtotal_money']=$food_price*$v['count'];
            $dataArray['subtotal_benefit']=floatval(0.00);

            //print_r($dataArray);//
            $orderInsert=$MS->rowInsert(ORDER,$dataArray);
            if(!$orderInsert){ "\$orderInsert ".$k." fail!"; }

        }
        //exit('order food subtotal ok');//
        //总价
        $total_money=$MS->orderListSum($dataArray['order_no']);
        $people_money=$total_money;//默认个人支出
        $total_benefit=floatval(0.00);//默认补贴支出
        //var_dump( $total_money );//
        //\\


        ////折扣处理

        //时段内是否首单
        $heaveOrderCheck=$MS->timeStampHeaveOrderCheck($this->uid);
        //var_dump($heaveOrderCheck);//

        //用户折扣标记
        $uInfo=$MS->rowSelect(USR_INFO,'join,subsidy_type','uid/'.$this->uid);
        if(!$uInfo){ return "\$uInfo fail!";}
        //var_dump($uInfo);//exit;//
        $join=$uInfo->join;//是:1 否:0 在编
        $subsidy_type=$uInfo->subsidy_type;//早:1 或 早午:2 补贴
        switch($subsidy_type){
            case '1': $subsidy_type=1; break;
            case '2':
                //只包含早午餐,限制3的情况 不补贴晚餐
                if($time_type>0 && $time_type<=2)$subsidy_type=$time_type;
                break;
        }


        if(!$heaveOrderCheck && $join=='1' && $subsidy_type==$time_type){
            $people_money=floatval(1);
            $total_benefit=floatval($total_money-1);
        }
        elseif($heaveOrderCheck && $join=='0' && $subsidy_type!=$time_type){
            $people_money=$total_money;
            $total_benefit=floatval(0.00);
        }
        else{
            $people_money=$total_money;
            $total_benefit=floatval(0.00);
        }

        //var_dump( $total_money );var_dump( $people_money);var_dump( $total_benefit);//
        //exit;//

        //\\


        ////检查钱包余额

        $account=$MS->accountCheck($this->uid);
        $balance=$account->balance;
        $subsidy=$account->subsidy;
        //var_dump($balance);var_dump($subsidy);//

        //先处理补贴钱包
        $kv_arr['order_tip']=" ";//默认空
        if($total_benefit>$subsidy){
            //在每餐首单 有补贴权利的情况下 补贴钱包没钱
            $people_money=floatval($people_money+$total_benefit);
            $total_benefit=floatval('0.00');

            $kv_arr['order_tip']="<p><b>本月补贴额度已用完,自动计入个人消费!</b></p>";//等待 tool::mk_session($kv_arr) 一并执行
        }

        if($people_money>$balance){ tool::jsonResult($_POST,'1','个人钱包余额不足,请充值!','?/payment/recharge/'); }

        //var_dump($people_money);var_dump($total_benefit);//
        //exit;//

        ////

        $kv_arr['total_money']=floatval($total_money);
        $kv_arr['people_money']=floatval($people_money);
        $kv_arr['total_benefit']=floatval($total_benefit);
        $kv_arr['order_no']=$dataArray['order_no'];
        tool::mk_session($kv_arr);
        //var_dump($_SESSION);exit;//

        //tool::jsonResult($_POST,'1','','');//
        tool::jsonResult($_POST,'1','','?/payment/pay/');
    }

    //二维码支付 页面
    function pay(){

        $no_array=array('total_money','people_money','total_benefit','order_no','order_tip');
        $get_session=tool::get_session($no_array);
        //var_dump( $get_session );//

        //var_dump($_SESSION);exit;//
        if($get_session && $get_session->order_no!=''){

            //$order_url=HTTP_BASE."/Home/?/payment/cashier/order_no-".$get_session->order_no;//路径形式
            $order_url=$get_session->order_no;//订单号形式

            $qrcode_url="/Library/Thirds/qrcode.php?order_no=".$order_url;

            V::tamplate('pay');

            v::asSign('title','结账');

            V::asSign('order_tip',$get_session->order_tip);
            V::asSign('total_money',$get_session->total_money);
            v::asSign('people_money',$get_session->people_money);
            V::asSign('total_benefit',$get_session->total_benefit);
            v::asSign('order_no',$get_session->order_no);//页面隐藏单号
            v::asSign('qrcode_url',$qrcode_url);//二维码链接

            V::show();
        }else{
            jump::head('?/info/news');exit;//没有订单记录则 跳出
        }

    }

    //异步检查 订单是否通过
    function checkPay_ax(){

        $order_no=tool::get_session('order_no');
        if(!$order_no)exit;

        $MS=$this->MS;

        $whereArray['order_no']=$order_no;
        $whereArray['payed']='1';
        $orderArray['order_id']='desc';


        //支付判断
        $payCheck=$MS->rowSelect(ORDER,'payed',$whereArray,$orderArray);
        //超时判断
        $time_ax=tool::setTimeOut('time_ax',2000,120000);//1分钟超时

        if($payCheck){
            tool::jsonResult($time_ax,'1','','?/payment/paySuccess/');
        }
        elseif(!$time_ax){
            //未扫码 则 清除订单session
            $no_array=array('total_money','people_money','total_benefit','order_no','order_tip');
            tool::get_session($no_array,'1');

            tool::jsonResult($time_ax,'1','1分钟内未扫码,自动取消！','?/info/news/');
        }
        else{tool::jsonResult($time_ax,'-1','');}


    }

    //收银员 确认订单 准备扣款//前台微信扫码 跳转页
    // /Home/?/payment/cashier/
    function cashier(){

        $kv_array['order_cashier_no']=tool::isSetRe( I::have('order_no') );
        tool::mk_session($kv_array);

        $MS=$this->MS;

        //收银权限检查
        if(! $MS->cashierAuthCheck($this->uid) ) jump::alertTo('没有收银权限!','?/weixin/index/');


        $order_cashier_no=tool::get_session('order_cashier_no');
        //var_dump($order_cashier_no);exit;//
        if(!$order_cashier_no) jump::alert('单号为空!','?/weixin/index/');


        //一系列数据更新操作
        $updateCashierOrder=$MS->updateCashierOrder($order_cashier_no,$this->uid);
        //

        //exit('$updateCashierOrder');//

        $selectArray=array('total_money','total_benefit');
        $whereArray['order_no']=$order_cashier_no;
        $whereArray['payed']='1';
        $payed_order=$MS->rowSelect(PAYED,$selectArray,$whereArray);

        if($updateCashierOrder){

            V::tamplate('cashierSuccess');

            v::asSign('title','结账成功');
            V::asSign('total_money',floatval($payed_order->total_money));
            V::asSign('people_money',floatval($payed_order->total_money-$payed_order->total_benefit));
            V::asSign('total_benefit',floatval($payed_order->total_benefit));
            v::asSign('order_no',floatval($order_cashier_no));//页面隐藏单号

            V::show();
        }


    }

    //收银员 确认订单 准备扣款//后台扫码枪 异步页
    // /Home/?/payment/cashier_ax/
    function cashier_ax(){

        $MS=$this->MS;

        //$kv_array['order_cashier_no']=tool::isSetRe( I::have('order_no') );
        $kv_array['order_cashier_no']=tool::is_Post('order_no');
        tool::mk_session($kv_array);


        //收银权限检查
        if(! $MS->cashierAuthCheck2($this->uid) ) {
            tool::jsonResult($kv_array['order_cashier_no'],'-1','没有收银权限！');
        }

        $order_cashier_no=tool::get_session('order_cashier_no');
        //var_dump($order_cashier_no);exit;//
        if(!$order_cashier_no){
            tool::jsonResult($kv_array['order_cashier_no'],'-1','单号为空！');
        }


        //一系列数据更新操作
        $updateCashierOrder=$MS->updateCashierOrder($order_cashier_no,$this->uid);
        //exit('$updateCashierOrder');//
        //

        $selectArray=array('total_money','total_benefit');
        $whereArray['order_no']=$order_cashier_no;
        $whereArray['payed']='1';
        $payed_order=$MS->rowSelect(PAYED,$selectArray,$whereArray);
        if($updateCashierOrder&&$payed_order){
            tool::jsonResult($kv_array['order_cashier_no'],'0','收银成功！');
        }


    }

    function paySuccess(){

        $no_array=array('total_money','people_money','total_benefit','order_no','order_tip');
        $get_session=tool::get_session($no_array);
        //var_dump( $get_session );//
        if(!$get_session){jump::head('?/weixin/index');}
        //exit;//

        //清除指定session 页面刷新或后退 即 跳转首页
        tool::get_session($no_array,1);
        //

        V::tamplate('paySuccess');

        v::asSign('title','结账成功');
        V::asSign('total_money',$get_session->total_money);
        V::asSign('people_money',$get_session->people_money);
        V::asSign('total_benefit',$get_session->total_benefit);
        v::asSign('order_no',$get_session->order_no);//页面隐藏单号

        V::show();

    }



    function recharge(){

        $step=I::have('step');//
        $step=tool::isSetRe($step);//
        //exit('$step:'.$step);//

        $MS=$this->MS;

        $where['uid']=$this->uid;

        $select_acc=array('balance','subsidy');
        $account=$MS->rowSelect(ACCOUNT,$select_acc,$where);





        V::tamplate('recharge');
        v::asSign('title','余额充值');

        V::asSignArr($account);

        switch($step){
            default:$cArr['content']=''; break;
            case '1': $cArr['content']='recharge_change_1'; break;
        }
        V::asChangeArr($cArr);


        v::asSign('nav_active','2');

        V::show();

    }

    function recharge_ax(){

        exit('closed');//

        $MS=$this->MS;

        //var_dump($_POST);exit;//

        ////处理post 拼接充值记录 与 recharge表 字段对应
        $rechData['text']='text';
        $rechData['recharge_menoy']='money';
        $rechData=tool::is_Post($rechData);
        //var_dump($rechData);exit;//
        if(empty($rechData['text'])&&$rechData['recharge_menoy']=='on'){
            tool::jsonResult($rechData,'-1','请输入金额！');
        }
        if(!empty($rechData['text'])&&$rechData['recharge_menoy']=='on'){
            $rechData['recharge_menoy']=$rechData['text'];
        }
        unset($rechData['text']);
        $rechData['uid']=$this->uid;
        $rechData['subsidy_menoy']='0';
        $rechData['time']=TIME;
        $rechData['ip']=tool::ip();
        //var_dump($rechData);exit;//
        //\\

        //充值公用变量
        $balance_add=$rechData['recharge_menoy'];//要给钱包加的值
        $subsidy_add=$rechData['subsidy_menoy'];//首充补贴
        $subsidy_type='0';//用户补贴记号//非在编为零
        $time_recharge=$rechData['time'];//更新时间值 都用这里的 引用变量
        //

        //判断是否在编会员
        $is_join=$MS->rowSelect(USR_INFO,'*',array('uid'=>$this->uid))->join;
        //var_dump($is_join);exit;//

        //不是在编会员
        if($is_join=='0'){


            ////修改用户补贴记号
            $subsidySign=$MS->rowUpdate(USR_INFO, array('subsidy_type' => $subsidy_type), "uid/" . $this->uid);
            //var_dump($subsidySign);//
            //\\

            ////正常充值
            $accountRefl=$MS->accountRefl($this->uid,$balance_add,$subsidy_add,$time_recharge);
            if(!$accountRefl){ exit('$accountRefl fail!'); }
            //\\

            ////开始写充值记录
            $rechargeSet=$MS->rowInsert(RECHARGE,$rechData);
            if(!$rechargeSet){ exit('$rechargeSet fail!'); }

            tool::jsonResult($rechData,'0','非在编人员,充值成功!','?/payment/recharge/step-1');
            //\\
        }

        //是在编会员
        if($is_join=='1'){

            //当月首充补贴处理 //月底 补贴自动清零时 处理 USR_INFO、ACCOUNT、表 LOG表记录日志

            //用户补贴标记 预定
            if($balance_add>='22'&&$balance_add<'44'){ $subsidy_type='1'; }
            elseif($balance_add>='44'){ $subsidy_type='2'; }

            //当月首充判断 //条件：充值>=22 的最新记录
            $where['uid']=$this->uid;
            $where['time/>=']=date::toMouthSide(0);
            $where['time/<']=date::toMouthSide(1);
            $where['recharge_menoy/>=']='22';
            $where['subsidy_menoy']='330';
            //var_dump($where);//exit;//
            $recharged=$MS->rowSelect(RECHARGE,'*',$where,'time desc');
            //var_dump($recharged);exit;//

            //是否当月首充 >=22
            if(!$recharged&&$balance_add>='22'){
                //首充
                $rechData['subsidy_menoy']='330';//首充补贴
                if($balance_add>='22') {

                    ////修改用户补贴记号
                    $subsidySign=$MS->rowUpdate(USR_INFO, array('subsidy_type' => $subsidy_type), "uid/" . $this->uid);
                    if(!$subsidySign){return '$subsidySign fail!';}
                    //\\

                }
            }else{
                //非首充 //因为首充“>=22”已经自动加补贴330 和 修改用户补贴记号为“1”只补贴早餐, 再充值时除非“>=44”,否则不修改补贴记号为2
                $rechData['subsidy_menoy']='0';//补贴
                if($balance_add>='44'){

                    //exit($balance_add);//

                    ////修改用户补贴记号
                    $subsidySign=$MS->rowUpdate(USR_INFO, array('subsidy_type' => $subsidy_type), "uid/" . $this->uid);

                    if(!$subsidySign){return '$subsidySign fail!';}
                    //\\

                }
            }


            $subsidy_add=$rechData['subsidy_menoy'];//重新引用 充值预存变量的 首充补贴



            ////正常充值
            $accountRefl=$MS->accountRefl($this->uid,$balance_add,$subsidy_add,$time_recharge);
            if(!$accountRefl){return '$accountRefl fail!';}
            //\\

            ////开始写充值记录
            $rechargeSet=$MS->rowInsert(RECHARGE,$rechData);
            if(!$rechargeSet){exit('$rechargeSet fail!');}

            tool::jsonResult($rechData,'0','在编人员,充值成功!','?/payment/recharge/step-1');

            //\\


        }



    }

    function rechargeBack(){



    }


} 
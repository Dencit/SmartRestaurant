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


class recharge extends baseControler {

    private static $adminDesc;//parent object

    private static $MS;

    private static $page;//数据库翻页
    private static $step;//翻页步进值
    private static $start;//翻页起始值

    function __construct(){

        //用户登录状态
        self::$adminDesc=$this->adminDesc();
        //\\

        self::$MS=model::init('recharge');

        ////获取翻页值
        $page=I::have('p')-1;$page=$page>0?$page:'0';
        $step=20;
        $start=$step*$page;//$start=$start>0?$start:$step;
        //var_dump($start);//

        self::$page=$page; self::$step=$step; self::$start=$start;
        //\\

    }

    function account(){


        V::tamplate('index');
        $cArr['content']='change_account';
        V::asChangeArr($cArr);


        ////搜索处理
        $search='';//搜索字段
        $where=[];//基础条件
        $base_url='?/recharge/account';//基础链接
        V::asSign('base_url',$base_url);

        $name=I::have('name') ;
        $sta=I::have('sta') ;
        $end=I::have('end') ;
        if($name){
            $where['uid']=self::$MS->rowSelect(USR_INFO,'uid',array("name/%%"=>$name))->uid;
            $search.=DIRECTORY_SEPARATOR.'name-'.$name;
        }
        if($sta){ $where['time_recharge/>=']=strtotime($sta); $search.=DIRECTORY_SEPARATOR.'sta-'.$sta; }
        if($end){ $where['time_recharge/<']=strtotime($end); $search.=DIRECTORY_SEPARATOR.'end-'.$end; }

        //var_dump($where);exit;//
        $base_url.=$search;
        //\\

        ////当前页数据
        $select='acc_id,uid,balance,subsidy,time_recharge,time_pay';
        $account=self::$MS->accountSelect(ACCOUNT,$select,$where,'time_recharge desc limit '.self::$start.','.self::$step);
        //var_dump($account);exit;
        if(!$account){$account=array();$noneTip='没有了!';}
        else{$noneTip='';}
        V::forList('account',$account);
        V::asSign('noneTip',$noneTip);
        //\\

        ////翻页栏
        $countList=count( self::$MS->resultSelect(ACCOUNT,'acc_id',$where) );
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


    function account_edit_ax(){

        $rowId=tool::is_Post('rowId');

        $select='acc_id,uid,balance,subsidy,time_recharge,time_pay';
        $where['acc_id']=$rowId;
        $rowSelect=self::$MS->rowSelect(ACCOUNT,$select,$where);

        if($rowSelect){

            $usrInfo=self::$MS->rowSelect(USR_INFO,'name','uid/'.$rowSelect->uid);
            if($usrInfo)$rowSelect->name=$usrInfo->name;

            $rowSelect->time_recharge=date::Ymd($rowSelect->time_recharge).' '.date::AoPoN_Check('chr',$rowSelect->time_recharge);

            tool::jsonResult($rowSelect,'0');

        }

    }

    function account_submit_ax(){

        $rowId=tool::is_Post('acc_id');

        $data=tool::is_Post($_POST);
        unset($data['acc_id']);unset($data['name']);unset($data['time_recharge']);//排除部分
        //var_dump($data);exit;//

        $rowUpdate=self::$MS->rowUpdate(ACCOUNT,$data,'acc_id/'.$rowId);
        if($rowUpdate){
            tool::jsonResult($_POST,'1','提交成功！','?/recharge/account');
        }

        tool::jsonResult($_POST,'0','无修改！');

    }

    function account_money_add_ax(){

        //var_dump($rechData);exit;//

        $MS=self::$MS;

        ////处理post 拼接充值记录 与 recharge表 字段对应

        $rechData['acc_id']='rowId';
        $rechData['recharge_menoy']='money_add';
        $rechData=tool::is_Post($rechData);
        //var_dump($rechData);exit;//
        if( empty( $rechData['recharge_menoy'] ) ){
            tool::jsonResult($rechData,'-1','请输入金额！');
        }

        $account=$MS->rowSelect(ACCOUNT,'uid','acc_id/'.$rechData['acc_id']);
        if(!$account){
            tool::jsonResult($rechData,'-1','无此用户!');
        }

        $uid=$account->uid;
        unset($rechData['acc_id']);

        $rechData['uid']=$uid;
        $rechData['cid']=self::$adminDesc->uid;
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
        $is_join=$MS->rowSelect(USR_INFO,'*',array('uid'=>$uid))->join;
        //var_dump($is_join);exit;//

        //不是在编会员
        if($is_join=='0'){

            ////修改用户补贴记号
            $subsidySign=$MS->rowUpdate(USR_INFO, array('subsidy_type' => $subsidy_type), "uid/" . $uid);
            //var_dump($subsidySign);//
            //\\

            ////正常充值
            $accountRefl=$MS->accountRefl($uid,$balance_add,$subsidy_add,$time_recharge);
            if(!$accountRefl){ exit('$accountRefl fail!'); }
            //\\

            ////开始写充值记录
            $rechargeSet=$MS->rowInsert(RECHARGE,$rechData);
            if(!$rechargeSet){ exit('$rechargeSet fail!'); }

            tool::jsonResult($rechData,'0','非在编人员,充值成功!','?/recharge/account');
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
            //var_dump($recharged);exit;//

            //是否当月首充 >=22
            if(!$recharged&&$balance_add>='22'){
                //首充
                $rechData['subsidy_menoy']='330';//首充补贴
                if($balance_add>='22') {

                    ////修改用户补贴记号
                    $subsidySign=$MS->rowUpdate(USR_INFO, array('subsidy_type' => $subsidy_type), "uid/" . $uid);
                    //if(!$subsidySign){return '$subsidySign fail!';}
                    //\\

                }
            }else{
                //非首充 //因为首充“>=22”已经自动加补贴330 和 修改用户补贴记号为“1”只补贴早餐, 再充值时除非“>=44”,否则不修改补贴记号为2
                $rechData['subsidy_menoy']='0';//补贴
                if($balance_add>='44'){

                    //exit($balance_add);//

                    ////修改用户补贴记号
                    $subsidySign=$MS->rowUpdate(USR_INFO, array('subsidy_type' => $subsidy_type), "uid/" . $uid);

                    //if(!$subsidySign){return '$subsidySign fail!';}
                    //\\

                }
            }


            $subsidy_add=$rechData['subsidy_menoy'];//重新引用 充值预存变量的 首充补贴


            ////正常充值
            $accountRefl=$MS->accountRefl($uid,$balance_add,$subsidy_add,$time_recharge);
            if(!$accountRefl){return '$accountRefl fail!';}
            //\\

            ////开始写充值记录
            $rechargeSet=$MS->rowInsert(RECHARGE,$rechData);
            if(!$rechargeSet){exit('$rechargeSet fail!');}

            tool::jsonResult($rechData,'0','在编人员,充值成功!','?/recharge/account');

            //\\


        }



    }


    function recharges(){

        V::tamplate('index');
        $cArr['content']='change_recharges';
        V::asChangeArr($cArr);


        ////搜索处理
        $search='';//搜索字段
        $where=[];//基础条件
        $base_url='?/recharge/recharges';//基础链接
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


        ////导出表格
        if( I::have('table')=='1' ){
            $select='recharge_id,uid,cid,recharge_menoy,subsidy_menoy,out_trade_no,transaction_id,time,ip';
            $RECHARGE=self::$MS->rechargesTable(RECHARGE,$select,$where,'time desc limit '.self::$start.','.self::$step);

            if($RECHARGE){
                echo'<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
                header("Content-type:application/vnd.ms-excel");
                header("Content-Disposition:filename=充值记录[".$sta.":".$end."].xls");
                echo $RECHARGE;
            }
            else{
                exit('没有了！');
            }

            exit;
        }
        //\\


        ////当前页数据
        $select='recharge_id,uid,cid,recharge_menoy,subsidy_menoy,out_trade_no,transaction_id,time,ip';
        $recharges=self::$MS->rechargesSelect(RECHARGE,$select,$where,'time desc limit '.self::$start.','.self::$step);
        //var_dump($recharges);exit;
        if(!$recharges){$recharges=array();$noneTip='没有了!';}
        else{$noneTip='';}
        V::forList('recharges',$recharges);
        V::asSign('noneTip',$noneTip);
        //\\

        ////翻页栏
        $countList=count( self::$MS->resultSelect(RECHARGE,'recharge_id',$where) );
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
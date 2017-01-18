<?php
/* Created by User: soma Worker:陈鸿扬  Date: 16/11/9  Time: 09:57 */

namespace Controlers;

use \Commons\tool as tool;//工具类
use \Commons\jump as jump;//跳转类
use \Commons\date as date;//日期类
use \Commons\encryp as cryp;//加密类
use Debugs\frameDebug as FD;

use \Controlers\urlSerial as I;
use \Modelers\model as model;//model装载器
use \Views\view as V;

class system extends baseControler {

    static $pw;
    static $MS;

    function __construct(){

    self::$pw=I::have('pw');
    self::$MS=model::init('system');

    }

    //获取密码
    function getPw(){

        exit;//不获取时打开
        echo( cryp::passWord() );

    }

    //月底补贴清零动作
    //http://smrt.host.com/Home/?/system/monthOpera/pw-6aecc1265f6bc211879f44dc7472c460
    function monthOpera(){

        $pw=tool::isSetRe(self::$pw);
        //var_dump(self::$pw);exit;//
        $cryp=cryp::comparePw($pw);//密码比对
        //var_dump($cryp);exit;//

        if($cryp){

            $uids=self::$MS->resultSelect(USR_INFO,'uid');
            if(!$uids){ exit('$uids fail!'); }
            //var_dump($uids);//

            $where['time/>']=$toMouthStart=date::toMouthSide(0);
            $where['time/<=']=$toMouthEnd=date::toMouthSide(1);
            $where['type']='1';
            $logs=self::$MS->rowSelect(LOG,'type',$where);
            if(!$logs){ $logs=false; }
            //var_dump($logs);exit;//

            if(!$logs){
                foreach($uids as $n=>$v){
                    self::$MS->rowUpdate(USR_INFO,'subsidy_type/0','uid/'.$v->uid);
                    self::$MS->rowUpdate(ACCOUNT,'subsidy/0','uid/'.$v->uid);
                }

                self::$MS->rowInsert(LOG,'type/1,type_name/补贴清零,desc/服务器定时事件,time/'.TIME.',ip/'.tool::ip() );

                echo 'ok,log success !'; exit;
            }

            echo 'fail,log had !'; exit;
        }


        echo 'fail,password wrong !'; exit;
    }

} 
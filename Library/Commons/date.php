<?php
/* Created by User: soma Worker:陈鸿扬  Date: 16/10/21  Time: 15:33 */
namespace Commons ;

use NoSql\redis;

class date{

    static function YmdHsi_No($time=''){

        if($time==''||$time=='now'){$time=time();};
        return date('Ymd_Hsi',$time);

    }

    static function YmdHs($time=''){

        if($time==''||$time=='now'){$time=time();};
        return date('Y-m-d H:s',$time);

    }

    //年月日 今天日期格式化
    static function Ymd($time=''){

        if($time==''||$time=='now'){$time=time();};
        return date('Y-m-d',$time);

    }

    //年月 今天日期格式化
    static function Ym($time='',$add=''){

        if($time==''||$time=='now'){$time=time();};

        if($add!=''){ return date('Y-m',$time); }
        else{ return date('Y-m',$time); }

    }

    //n年n月 今天日期格式化
    static function Ym_str($time='',$add=''){

        if($time==''||$time=='now'){$time=time();};

        if($add!=''){ return date('Y-m',$time); }
        else{ return date('Y年m月',$time); }

    }

    //年月 今天日期格式化 如 01、02、03
    static function m($time=''){

        if($time==''||$time=='now'){$time=time();};
        return date('m',$time);

    }

    //年月 今天日期格式化 如 1、2、3
    static function n($time=''){

        if($time==''||$time=='now'){$time=time();};
        return date('n',$time);

    }

    //今天星期几 格式化
    static function weekday($time=''){
        if($time==''||$time=='now'){$time=time();};
        $weekarray=array("日","一","二","三","四","五","六");

        $w=date('w',$time);

        return '星期'.$weekarray[$w];

    }

    //时分  现在时间格式化
    static function Hs($time=''){
        if($time==''||$time=='now'){$time=time();};
        return date('H:s',$time);
    }

    static function dateOrderNo($id=''){
        if($id==''||!is_numeric($id) ){$id='';};
        return date('YmdwHims',time()).$id.rand(1000,9999);

    }

    //格式化dateFormat
    static function dateFormat($time=''){

    }

    //今月区间
    static function toMouthSide($int=''){

        switch($int){
            case 0 || '':
                $mouthStampStart= mktime( 0,0,0,date('m'),1,date('Y') );
                return $mouthStampStart;  break;
            case 1:
                $mouthStampEnd=mktime(0,0,0,date('m'),date('t'),date('Y') )+86400;
                return $mouthStampEnd; break;
        }

        return false;
    }

    //今天区间
    static function todaySide($int=''){

        $dayStamp=self::Ymd(time());

        switch($int){
            default: return strtotime($dayStamp); break;
            case 0 : return strtotime($dayStamp); break;
            case 1 : return strtotime($dayStamp)+86400; break;
        }

    }

    //一天区间 判断
    static function oneDayOneChance(){

        if(time() >= self::todaySide(0) && time() <= self::todaySide(1)) return true;
        else return false;

    }


    //上下午判断
    static function AMorPM(){

        $aop=date('A',time());

        return $aop;

    }

    //早中晚 判断
    static function AoPoN_Check($return='',$time=''){

        if($time==''||$time=='now') $time=time();

        $dayStamp=self::Ymd( $time );

        $dayStart=strtotime($dayStamp);//凌晨0点
        $dayMornStart=$dayStart+21600;//早上6点
        $dayNoonStart=$dayStart+39600;//中午11点
        $dayNightStart=$dayStart+64800;//傍晚6点
        $dayEnd=$dayStart+86400;//深夜24点

        //num=数字格式；str=字符串格式；side=数组区间格式；
        $return_type=[];
        switch($return){
            default:
                $return_type[0]='0'; $return_type[1]='1'; $return_type[2]='2'; $return_type[3]='3';
            case 'num':
                $return_type[0]='0'; $return_type[1]='1'; $return_type[2]='2'; $return_type[3]='3';
                break;
            case 'chr':
                $return_type[0]='凌晨'; $return_type[1]='早上'; $return_type[2]='中午'; $return_type[3]='晚上';
                break;
            case 'str':
                $return_type[0]='0'; $return_type[1]='Morning'; $return_type[2]='Noon'; $return_type[3]='Night';
                break;
            case 'side':
                $return_type[0]=array($dayStart,$dayMornStart);
                $return_type[1]=array($dayMornStart,$dayNoonStart);
                $return_type[2]=array($dayNoonStart,$dayNightStart);
                $return_type[3]=array($dayNightStart,$dayEnd);
                break;
        }

        if($time>=$dayMornStart&&$time<$dayNoonStart) return $return_type[1];
        else if($time>=$dayNoonStart&&$time<$dayNightStart) return $return_type[2];
        else if($time>=$dayNightStart&&$time<$dayEnd) return $return_type[3];
        else return $return_type[0];

    }

    static function AoPoN_Side($time=''){

        if($time==''||$time=='now') $time=time();



    }



}
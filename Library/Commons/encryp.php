<?php
/* Created by User: soma Worker:陈鸿扬  Date: 16/11/9  Time: 10:39 */

namespace Commons;


class encryp {

    protected static $saltArr;

    protected static $truePw='[PASS_WORD]';

    protected static function __init(){
        if(self::$saltArr===null){

            //重复调用 只执行一次
            //随机盐
            self::$saltArr=array(
                "BangJu2016",
                "BJ2016",
                "BANGJU2016",
                "bj2016",
                "bangju2016++",
                "bangju2016--",
                "BangJu2016-888",
                "BJ2016-888",
                "BANGJU2016-888",
                "bj2016-888"
            );

            //

        }


    }

    static function md5Loop($second,$pw){
        $second--;
        //var_dump( $second );//
        if($second>0){
            $pwMd5=md5($pw);
            //var_dump( $pwMd5 );//
            return self::md5Loop($second,$pwMd5);
        }else{
            //var_dump( $pw );//
            return $pw;
        }
    }

    static function passWord(){
        self::__init();

        $rnd=number_format( rand(0,9) );
        $pw=self::$truePw.self::$saltArr[$rnd];

        //return $string;

        return self::md5Loop(1000,$pw) ;
        //return true;
    }

    static function comparePw($md5Word){
        self::__init();

        $md5Word=strval($md5Word);

        //密码比对
        foreach(self::$saltArr as $n=>$v){

            $pw=self::$truePw.$v;
            $pwMd5=self::md5Loop(1000,$pw);

            if($md5Word==$pwMd5){ return true;}

        }

        return false;
    }

} 
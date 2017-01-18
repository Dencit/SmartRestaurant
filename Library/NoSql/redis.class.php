<?php
/* Created by User: soma Worker:陈鸿扬  Date: 16/10/28  Time: 09:49 */

namespace NoSql;


class redis {

    private static $redis;

    function __construct(){

        $redis = new \redis();
        $redis->connect('127.0.0.1', 6379);
        $redis->auth("[PASS_WORD]");
        $redis->select(1);
        self::$redis=$redis;

    }

    static function init(){

        $redis = new \redis();
        $redis->connect('127.0.0.1', 6379);
        $redis->auth("[PASS_WORD]");
        $redis->select(1);
        self::$redis=$redis;

    }


/*$key_array['a']='aa';$key_array['b']='bb';$key_array['c']='cc';
redis::set($key_array);*/

    static function set($key,$val=''){

        self::init();

        if(is_array($key)&&$val==''){
            foreach ($key as $k=>$v){
                self::$redis->set($k,$v);
            }
        }else{
            self::$redis->set($key,$val);
        }

    }


/*$no_array[0]='a';$no_array[1]='b';$no_array[2]='c';
var_dump( redis::get($no_array) );*/

    static function get($key){

        self::init();

        if(is_array($key)){
            $reArr=array();
            foreach($key as $k=>$v){
                $reArr[$v]=self::$redis->get($v);
            }
            return $reArr;
        }else{
            return self::$redis->get($key);
        }

    }

    static function ttl($key){

        self::init();

        return self::$redis->ttl($key);

    }

    static function expire($key,$time_out){

        self::init();

        self::$redis->expire($key,$time_out);

    }

    static function exists($key){

        self::init();

        return self::$redis->exists($key);

    }


} 
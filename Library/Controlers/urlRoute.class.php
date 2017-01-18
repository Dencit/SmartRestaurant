<?php
/* Created by User: soma Worker: 陈鸿扬 Date: 16/7/29  Time: 20:08 */

namespace Controlers;
use stdClass;
use Debugs\frameDebug as FD;

//url路由 抓取地址栏 路由参数 数组
class urlRoute {


    private static $this_scr_name;
    private static $this_req_uri;

    function __construct(){

        self::$this_scr_name=$_SERVER['SCRIPT_NAME'];
        self::$this_req_uri=$_SERVER['REQUEST_URI'];

    }

    private static function trimall($str)//删除空格
    {
        $qian=array(" ","　","\t","\n","\r");
        $hou=array("","","","","");
        return str_replace($qian,$hou,$str);
    }

    static function get($path_mode=null){

        if( !$path_mode ){$path_mode=PATH_URI;}//兼容全局变量设置

        switch($path_mode){
            case 'Normal'&&'':

                return false;

                break;
            case 'Path_Info':


                $this_scr_name=urlencode(self::$this_scr_name);
                $this_req_uri=urlencode(self::$this_req_uri);
                //echo $this_scr_name.'%2F%3F%2F'.'   ||   '.$this_req_uri.'<br/><br/>';//

                $this_repl=self::trimall( preg_replace('/'.$this_scr_name.'|^%+[\w]+%2F%3F%2F|%2F%3F%2F|%3F%2F|%2F$/','',$this_req_uri) );//截取/index.php?/后面的序列
                //print_r( $this_repl );echo'<br/><br/>';//

                //处理混合序列的情况
                $this_repl=self::trimall( preg_replace('/%26/','%2F',$this_repl) );//把序列中的“&”变成“/”
                //print_r( $this_repl );echo'<br/><br/>';//
                $this_repl=self::trimall( preg_replace('/%2F%2F/','%2F',$this_repl) );//把第一个序列中多出来的"/"合并
                //print_r( $this_repl );echo'<br/><br/>';//
                $this_repl=self::trimall( preg_replace('/%2F%3D%2F/','%2F',$this_repl) );//把多出来的"="合并
                //print_r( $this_repl );echo'<br/><br/>';//
                //#处理混合序列的情况

                $this_repl=explode("%2F",$this_repl);
                //print_r( $this_repl );echo'<br/><br/>';exit;//

                return $this_repl;


                break;

            case 'Rewrite':


                $this_scr_name=urlencode(self::$this_scr_name);
                $this_req_uri=urlencode(self::$this_req_uri);
                //echo $this_scr_name.'%2F%3F%2F'.'   ||   '.$this_req_uri.'<br/><br/>';//

                $this_repl=self::trimall( preg_replace('/'.$this_scr_name.'|^%+[\w]+%2F%3F%2F|%2F%3F%2F|%3F%2F|%2F$/','',$this_req_uri) );//截取/index.php?/后面的序列
                //print_r( $this_repl );echo'<br/><br/>';//

                //处理混合序列的情况
                $this_repl=self::trimall( preg_replace('/%26/','%2F',$this_repl) );//把序列中的“&”变成“/”
                //print_r( $this_repl );echo'<br/><br/>';//
                $this_repl=self::trimall( preg_replace('/(^%2F\w+%2F)+?/','',$this_repl) );//把序列中第一次出现的“/folder/”去掉 只获取路由序列
                //print_r( $this_repl );echo'<br/><br/>';//
                $this_repl=self::trimall( preg_replace('/%2F%2F/','%2F',$this_repl) );//把第一个序列中多出来的"/"合并
                //print_r( $this_repl );echo'<br/><br/>';//
                $this_repl=self::trimall( preg_replace('/%2F%3D%2F/','%2F',$this_repl) );//把多出来的"="合并
                //print_r( $this_repl );echo'<br/><br/>';//
                //#处理混合序列的情况

                $this_repl=explode("%2F",$this_repl);
                //print_r( $this_repl );echo'<br/><br/>';exit;//

                return $this_repl;

                break;
        }


    }


} 
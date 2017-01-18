<?php
/* Created by User: soma Worker: 陈鸿扬 Date: 16/7/28  Time: 17:15 */

namespace Controlers;
use stdClass;
use Debugs\frameDebug as FD;
use Commons\rootProj as rootProj;

use Controlers\urlRoute as urlRoute;


class controler{

    //private static $FD;
    private static $from;

    private static $rootProj;
    private static $urlRoute;

    private static $folderRootUrl;//网站根目录开始 预先指定的 项目 路径
    private static $libraryRootUrl;//网站根目录开始 预先指定的 控制器相关库 路径
    private static $controlerRootUrl;//网站根目录开始 预先指定的 控制器 路径

    private static $baseClassUrl;
    private static $thisClassUrl;

    public  $urlCont;//公用 控制器 变量
    public  $urlFunc;//公用 方法页 变量

    private static $this_scr_name;
    private static $this_req_uri;

    function __construct(){

        //self::$FD=new FD;
        self::$from="FROM::Library/Controlers/controler.class::";

        self::$rootProj=new rootProj(ROOT_PROJECT,ROOT_CONTROLER);//【项目夹，目标文件夹】传入调用页 全局变量或变量
        self::$urlRoute=new urlRoute;

        self::$this_scr_name=$_SERVER['SCRIPT_NAME'];
        self::$this_req_uri=$_SERVER['REQUEST_URI'];

    }

    private function trimall($str)//删除空格
    {
        $qian=array(" ","　","\t","\n","\r");
        $hou=array("","","","","");
        return str_replace($qian,$hou,$str);
    }

    private function getPage($controler,$function)
    {

        $controlerRootUrl=self::$rootProj->getFolder();//得到绝对路径
        //exit($controlerRootUrl);//

        $thisClassUrl=
            $controlerRootUrl.DIRECTORY_SEPARATOR
            .$controler.'.class.php';
        //exit($thisClassUrl);//

        if(!file_exists($thisClassUrl)){FD::frameDebugExit('没有'.$controler.'控制类!');}
        require_once($thisClassUrl);
        self::$thisClassUrl=$thisClassUrl;

        $controler=$this->trimall("\\Controlers\\".$controler);
        //print_r( $controler );exit;//
        $controler=new $controler ;

        if( method_exists( $controler,$function ) ){
            $controler->$function();
        }else{
            FD::frameDebugExit(self::$from.'没有或未定义'.$function.'方法');
        }



    }


    function uri($path_mode)
    {

        switch($path_mode){

            case 'Normal'&&'':

                $url_cont=isset($_GET['c'])?$this->trimall($_GET['c']):'';
                $url_func=isset($_GET['f'])?$this->trimall($_GET['f']):'';

                break;

            case 'Path_Info':

                $this_repl=self::$urlRoute->get('Path_Info');//路由数组
                //var_dump($this_repl);//

                $url_cont=!empty($this_repl[0])?$this->trimall($this_repl[0]):'';
                $url_func=!empty($this_repl[1])?$this->trimall($this_repl[1]):'';

                $this->urlCont=$url_cont;//公用 控制器 变量
                $this->urlFunc=$url_func;//公用 方法页 变量

                break;
            case 'Rewrite':

                $this_repl=self::$urlRoute->get('Rewrite');//路由数组
                //var_dump($this_repl);//

                $url_cont=!empty($this_repl[0])?$this->trimall($this_repl[0]):'';
                $url_func=!empty($this_repl[1])?$this->trimall($this_repl[1]):'';

                $this->urlCont=$url_cont;//公用 控制器 变量
                $this->urlFunc=$url_func;//公用 方法页 变量

                break;
        }


        if( empty($url_cont)){ $url_state='ec0';}
        elseif( empty($url_func)){ $url_state='ef0';}
        else{$url_state='ok';};

        switch($url_state){
            case 'ec0':
                FD::frameDebugExit(self::$from.'控制类为空!');
                break;
            case 'ef0':
                FD::frameDebugExit(self::$from.'方法为空!');
                break;
            case 'ok':
                $this->getPage($url_cont,$url_func);
                break;
        }


    }



} 
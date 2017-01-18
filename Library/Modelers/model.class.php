<?php
/* Created by User: soma Worker: 陈鸿扬 Date: 16/7/30  Time: 13:15 */

namespace Modelers;
use Debugs\frameDebug as FD;
use Commons\rootProj as rootProj;

//model装载类
class model {

    private static $from;

    private static $index;
    private static $rootProj;

    //private static $model_root_url;//网站根目录开始 预先指定的 控制器 路径

    function __construct($index){


        self::$from="FROM::Library/Modelers/model.class::";

        self::$index=$index;
        self::$rootProj=new rootProj(ROOT_PROJECT,ROOT_MODELER);//【项目夹，目标文件夹】传入调用页 全局变量或变量

    }

    private static function trimall($str)//删除空格
    {
        $qian=array(" ","　","\t","\n","\r");
        $hou=array("","","","","");
        return str_replace($qian,$hou,$str);
    }


    static function init($index='',$set=''){
        self::$from="FROM::Library/Modelers/model.class::";

        if(empty($index)){
            self::$index='baseModel';
            self::$rootProj=new rootProj('Library','Modelers');//【项目夹，目标文件夹】传入调用页 全局变量或变量
        }
        else{
            self::$index=$index;
            self::$rootProj=new rootProj(ROOT_PROJECT,ROOT_MODELER);//【项目夹，目标文件夹】传入调用页 全局变量或变量
        }

        if($set==''){
            return self::set();
        }else{
            return self::set($set);
        }

    }


    static function set($newParam='')//new classs时 传给构造函数的 初始化参数
    {

        $index=self::$index;

        $modelFolder=self::$rootProj->getFolder();//得到绝对路径
        //var_dump( $modelFolder );//

        $index_req_url=
            $modelFolder.DIRECTORY_SEPARATOR
            .$index.'.class.php';
        //var_dump($index_req_url);//

        require_once($index_req_url);
        $index_class=self::trimall('\\Modelers\\'.$index);
        //var_dump($index_class);//

        $index=new $index_class($newParam);

        return $index;

    }



} 
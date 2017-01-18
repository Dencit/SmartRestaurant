<?php
/* Created by User: soma Worker: 陈鸿扬 Date: 16/7/30  Time: 16:32 */

namespace Commons;
use Debugs\frameDebug as FD;

class rootProj {


    private static $in_Proj_url;
    private static $to_folder_url;

    //public  static $ts;

    function __construct($in_Proj_url,$to_folder_url)//【项目夹，目标文件夹】传入调用页 全局变量或变量
    {


        self::$in_Proj_url=$in_Proj_url;
        self::$to_folder_url=$to_folder_url;

        //self::$ts='123';

    }

    public function getFolder()//获取 文件夹绝对路径
    {

        $in_Proj_url=self::$in_Proj_url;
        $to_folder_url=self::$to_folder_url;

        $webRoot=realpath($_SERVER['DOCUMENT_ROOT']);//网站根目录
        if( empty($in_Proj_url) ){
            $to_folder=$webRoot.DIRECTORY_SEPARATOR.$to_folder_url;
        }else{
            $to_folder=$webRoot.DIRECTORY_SEPARATOR.$in_Proj_url.DIRECTORY_SEPARATOR.$to_folder_url;
        }

        //exit($to_folder);//

        if(!is_dir($to_folder)){
            FD::frameDebugExit('网站根目录没有  ~ '.$to_folder_url.' ~  目录');
        }
        else{

            return $to_folder;

        }
    }

} 
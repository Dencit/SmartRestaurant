<?php
/* Created by User: soma Worker: 陈鸿扬 Date: 16/7/28  Time: 16:31 */

namespace Debugs;
use stdClass;

class frameDebug{


    function __construct(){


    }

    private static function frameDebugMsg($ecorrMsg)
    {
        $rnd=rand(0,5);

        $faceArr=array('(っ⊙＿⊙)っ','(っ°Д°)っ ','(っ°○°)っ','(っ▔皿▔)っ','(っ⊙▂⊙)っ','(っ°Д°)っ');
        $face=urlencode('_'.$faceArr[$rnd].'_');

        $errLog=new stdClass();
        $errLog->$face=urlencode('?!?!?!');
        $errLog->errMsg='#####_____'.urlencode($ecorrMsg).'_____#####';//php5.4版本下中文转码
        $errLog->logTime=date('Y-m-d H:s:i',time());
        exit( urldecode( json_encode( $errLog ) ) );
    }

    public static function frameDebugExit($ecorrMsg)
    {
        switch(FRAM_DEBUG){
            case 'on':
                self::frameDebugMsg($ecorrMsg);
                break;
            case 'off':
                self::frameDebugMsg('fail');
                break;
        }
    }

} 
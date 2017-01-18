<?php
/* Created by User: soma Worker: 陈鸿扬 Date: 2016/5/26 Time: 16:37 */
class timeInterval
{
    //打开概率设置文件，获取顺序数组，只有值。
    public static function fileGetVal($url){
        $arr=array();
        $fgc=file_get_contents($url);
        $fgc_ex=explode(",",$fgc);
        foreach($fgc_ex as $k=>$v){
            $fgc_exx=explode("=>",$v);
            $arr[$k]=$fgc_exx[1];
        }
        return $arr;
    }

    //打开概率设置文件，获取顺序数组，只有键。
    public static function fileGetKey($url){
        $arr=array();
        $fgc=file_get_contents($url);
        $fgc_ex=explode(",",$fgc);
        foreach($fgc_ex as $k=>$v){
            $fgc_exx=explode("=>",$v);
            $arr[$k]=$fgc_exx[0];
        }
        return $arr;
    }

    public static function dataMerge($fileGetStr,$postData){
        $arr=array();
        foreach($fileGetStr as $k=>$v){
            $arr[$v]=$postData[$k];
        }
        return $arr;
    }

    public static function Arr2Str($fileRefresh){
        $str='';
        foreach($fileRefresh as $k=>$v){
            $str .= $k.":".$v.",";
        }
        return mb_substr($str,0,-1,'UTF-8');
    }

    public static function fileWrite($url,$str){
        $link=@fopen($url,"w");
        if($link!=false){
            fwrite($link,$str);
            fclose($link);
            return true;
        }else{
            return false;
        }
    }

    public static function fileAdd($url,$str){
        $l=fopen($url,"a+");
        fwrite($l,$str);
        fclose($l);
        return true;
    }

}
<?php
/* Created by User: soma Worker: 陈鸿扬 Date: 2016/5/26 Time: 16:37 */
namespace Commons;

class probability
{
    function __construct(){



    }

    //打开概率设置文件，获取顺序数组，只有值
    static function fileGetVal($url){
        $arr=array();
        $fgc=file_get_contents($url);
        $fgc_ex=explode(",",$fgc);
        foreach($fgc_ex as $k=>$v){
            $fgc_exx=explode(":",$v);
            $arr[$k]=$fgc_exx[1];
        }
        return $arr;
    }
    //从数组中 获取概率 生成中奖区间数组
    static function setInterval($fileGet){
        $arr=array();
        foreach($fileGet as $k=>$v){
            $arr[$k]=array_sum( array_slice($fileGet,0,$k+1) );
        }
        return $arr;
    }
    //从中奖区间数组中 获取礼品标记
    static function getSign($rnd,$setInt){
        $arr=array();
        foreach ($setInt as $k => $v) {
            if ($rnd <= $v) {
                $arr=array("inv"=>$v,"s"=>$k);
                break;
            }
        }
        return $arr;
    }
//
    //打开概率设置文件，获取顺序数组，只有键
    static function fileGetKey($url){
        $arr=array();
        $fgc=file_get_contents($url);
        $fgc_ex=explode(",",$fgc);
        foreach($fgc_ex as $k=>$v){
            $fgc_exx=explode(":",$v);
            $arr[$k]=$fgc_exx[0];
        }
        return $arr;
    }

    static function dataMerge($fileGetStr,$postData){
        $arr=array();
        foreach($fileGetStr as $k=>$v){
            $arr[$v]=$postData[$k];
        }
        return $arr;
    }

    static function Arr2Str($fileRefresh){
        $str='';
        foreach($fileRefresh as $k=>$v){
            $str .= $k.":".$v.",";
        }
        return mb_substr($str,0,-1,'UTF-8');
    }

    static function fileWrite($url,$str){
        $link=@fopen($url,"w");
        if($link!=false){
            fwrite($link,$str);
            fclose($link);
            return true;
        }else{
            return false;
        }
    }

    static function fileAdd($url,$str){
        $l=fopen($url,"a+");
        fwrite($l,$str);
        fclose($l);
        return true;
    }

}
<?php
/* Created by User: soma Worker:陈鸿扬  Date: 16/11/11  Time: 17:17 */

namespace Commons ;

class forPage {

    static $countList;//计算条数
    static $pages;//总页数
    static $cur_page;//当前页码
    static $previous;//上一页页码
    static $next;//下一页页码
    static $plist;//页码数组 带高亮标记

    static function getPage($countList,$num,$step,$base_url=null){

        self::$countList=$countList;

        $pages=ceil($countList/$step);$pages=$pages<1?1:$pages;
        $cur_page=self::currPageNum($num,$pages);
        $previous=self::prevNumMake($cur_page);
        $next=self::nextNumMake($cur_page,$pages);
        $plist=self::pageNumList($cur_page,$pages,'active');

        if($base_url!=null)$base_url.=DIRECTORY_SEPARATOR.'p-';

        self::$pages=$pages;
        self::$cur_page=$cur_page;
        self::$previous=$base_url.$previous;
        self::$next=$base_url.$next;

        foreach($plist as $n=>$v){
            $plist[$n]['page_url']=$base_url.$v['page_num'];
        }

        //var_dump($plist);exit;

        self::$plist=$plist;

    }


    static function currPageNum($seriPageNum,$pages,$math=null){

        if($seriPageNum<0||!is_numeric($seriPageNum)){
            $seriPageNum=1;
        }

        if($seriPageNum>$pages){
            $seriPageNum=$pages;
        }

        if($math!=null){
            $seriPageNum+=$math;
        }

        return $seriPageNum;
    }

    static function pageNumList($cur_page,$pages,$on_type){

        $pagesArr=[];
        for($n=0;$n<$pages;$n++){
            $i=$n+1;
            //echo $i."||||".$cur_page."</br>";
            if($i==$cur_page){ $pagesArr[$n]['page_num']=$i; $pagesArr[$n]['on']=$on_type; }
            else { $pagesArr[$n]['page_num']=$i; $pagesArr[$n]['on']=''; }
        }

        return $pagesArr;

    }

    static function prevNumMake($cur_page){
        $previous=number_format($cur_page-1);
        return $previous>0?$previous:1;
    }

    static function nextNumMake($cur_page,$pages){
        $next=number_format($cur_page+1);
        return $next<=$pages?$next:$pages;
    }

} 
<?php
/* Created by User: soma Worker: 陈鸿扬 Date: 2016/3/23 Time: 2:18*/

namespace Views;
use Debugs\frameDebug as FD;

class view{

    private static $view;

    private static $childView;

    function __construct(){
    }

    private static function msg($type){
        switch ($type){
            case 'from':
                return "FROM::Library/Views/view.class::";
                break;

        }
    }

    private static function htmlFiltrationRep($str){
        $str = trim($str);
        $str=preg_replace("{\t}","<_t_>",$str);
        $str=preg_replace("{\r\n}","<_r_n_>",$str);
        $str=preg_replace("{\r}","<_r_>",$str);
        $str=preg_replace("{\n}","<_n_>",$str);
        $str=preg_replace("/>\s*/",">",$str);
        $str=preg_replace("/\s*</","<",$str);
        return $str;
    }

    private static function htmlFiltrationBack($str){
        $str = trim($str);
        $str=preg_replace("{<_t_>}","\t",$str);
        $str=preg_replace("{<_r_n_>}","\r\n",$str);
        $str=preg_replace("{<_r_>}","\r",$str);
        $str=preg_replace("{<_n_>}","\n",$str);
        return $str;
    }

    private static function htmlFiltrationZip($str){
        $str = trim($str);
        $str=preg_replace("/\n+/","\n",$str);
        $str=preg_replace("/>\n/",">",$str);
        $str=preg_replace("/\n</","<",$str);
        $str=preg_replace("/>\s*/",">",$str);
        $str=preg_replace("/\s*</","<",$str);
        return $str;
    }


    static function tamplate($type){//加载 include 嵌套标签指向文件 到当前模板

        $url="Tamplate/".$type.".html";

        //
        if(!file_exists($url)){
            FD::frameDebugExit(self::msg('from').' tamplate():: 找不到 '.$url.' 主模版文件');
        }
        //

        $file=file_get_contents("$url","1");

        //include模板标签 过滤
        $match_patterns="/{{include::\"([^>]+?)\"}}/";
        preg_match_all($match_patterns, $file ,$include_path_group);
        //var_dump($include_path_group);exit;//

        if( !empty($include_path_group[0]) ){//include模板标签 非空判断

            foreach($include_path_group[1] as $k=>$v){
                $include_path_arr[]=$v;
            }
            //var_dump($include_path_arr);exit;//

            foreach($include_path_arr as $k=>$v){
                $patterns[]="/{{[include::]+\"($v)\"}}/i";

                $path="Tamplate/".$v.".html";
                $path_file=file_get_contents("$path","1");

                //print_r($path_file);exit;
                $replace[]="$path_file";
            }

            $filestr=preg_replace($patterns,$replace,$file);
            //var_dump($filestr);exit;//


            self::$view=$filestr;

        }else{

            self::$view=$file;

        }


    }

    static function asChange($sign,$type){//实时替换 可变模板 标记

        if(self::$view==''){
            FD::frameDebugExit(self::msg('from').'未执行tamplate函数');
        }

        if(empty($sign)){
            $patterns= "/{{[change::]+\"([^>]+?)\"}}/i";
            $replace='';
            $filestr = preg_replace($patterns, $replace, self::$view);
            self::$view = $filestr;
        }
        else{
            $patterns="/{{[change::]+\"($sign)\"}}/i";
            preg_match_all($patterns,self::$view,$return);
            if(empty($return[1])){
                FD::frameDebugExit(self::msg('from').' {{change}}标签无匹配');
            }
        }


        if(empty($type)){ $file=''; }
        else{
            $url="Tamplate/".$type.".html";
            if(!file_exists($url)){
                FD::frameDebugExit(self::msg('from').' asChange():: 找不到 '.$url.' 模版文件');
            }
            $file=file_get_contents("$url","1");
        }

        $replace=$file;

        $filestr=preg_replace($patterns,$replace,self::$view);
        //var_dump($filestr);exit;//

        self::$view=$filestr;
    }


    static function asChangeArr($arr=array()){//实时替换 可变模板 标记//数组方式

        if(self::$view==''){
            FD::frameDebugExit(self::msg('from').'未执行tamplate函数');
        }

        if(empty($arr)){
            $patterns[] = "/{{[change::]+\"([^>]+?)\"}}/i";
            $replace='';
            $filestr = preg_replace($patterns, $replace, self::$view);
            self::$view = $filestr;
        }

        if(is_array($arr) && self::$view!='') {

            foreach($arr as $k=>$v){

                $sign=$k; $type=$v;

                ////表达式匹配
                $pattern="/{{[change::]+\"($sign)\"}}/i";
                if( !empty($sign) ){
                    preg_match_all($pattern,self::$view,$return);
                    if(empty($return[1])){
                        FD::frameDebugExit(self::msg('from').'asChangeArr():: {{change}}标签无匹配');
                    }
                }
                $patterns[]=$pattern;

                ////路径模板匹配
                $url = "Tamplate/" . $type . ".html";
                //模板文件名非空 但找不到模板文件
                if(!file_exists($url) && $v!==''){
                    FD::frameDebugExit(self::msg('from').' asChangeArr():: 找不到 '.$url.' 模版文件');
                }
                //模板文件名为空时,将标记部分模板清除
                if($type==''){ $file=''; }
                else{ $file = file_get_contents("$url", "1"); }
                $replace[] = "$file";
            }

            $filestr = preg_replace($patterns, $replace, self::$view);
            //var_dump($filestr);exit;//
            self::$view = $filestr;

        }

        return false;
    }


////列表循环

    //forList获取模板标记
    private static function forListSign($listView,$match_patterns){
        preg_match_all($match_patterns,$listView,$sign_group);
        //如果没有匹配结果
        if(empty($sign_group[1])){

            //var_dump($match_patterns);
            //var_dump($listView);exit;

            FD::frameDebugExit(self::msg('from').' {{forList}}标签无匹配 或 未执行tamplate函数');
        }
        //
        return $sign_group;
    }

    private static function forInfo($match_group,$sign,$arr){

        $stringList='';

        //缺少数组为 字符串或空 时的判断

        //

        foreach($arr as $k=>$v){
            unset($patterns);
            unset($replace);
            foreach($v as $m=>$n){

                ////当 $n 是数组 时,正则替换会报错 ，需要过滤掉或者 显示debug信息

                //报错情况
                /*if(is_array($n) || is_object($n)){
                    FD::frameDebugExit(self::msg('from').' 标签替换错误 不可以是数组或对象 ['. print_r($n).']' );
                }*/
                //

                //处理成二级列表规则的情况
                if(is_array($n) || is_object($n)){

                    $patterns[]="/\[\[+[forList::]+\"$sign+_child\"+\]\](.*?|\W+|\w+)\[\[+\/+[forList::]+\"$sign+_child\"+\]\]/i";
                    $child=self::forChild($sign.'_child',$n);
                    $replace[]=$child;

                }else{
                    $patterns[]="/\[\[+($m)+\]\]/";
                    $replace[]=$n;
                }
                //

                continue;

                ////

            }

            $stringList.=preg_replace($patterns,$replace,$match_group[1])[0];

        }
        //var_dump($stringList);exit;//

        return $stringList;

    }

    private static function forChild($sign,$arr=array()){

        //获取视图缓存
        $listView=self::htmlFiltrationRep(self::$view);//过滤html换号符//空格精简//方便正则匹配
        //$listView=self::$view;
        //var_dump( $listView );//exit;//

        //设置 {{type}} 记号范围
        $match_patterns="/\[\[+[forList::]+\"$sign\"+\]\](.*?|\W+|\w+)\[\[+\/+[forList::]+\"$sign\"+\]\]/i";

        //获取forlist模板区域
        $match_group=self::forListSign($listView,$match_patterns);
        //var_dump($match_group);exit;//

        //模板区域 单列字段 替换
        $stringList=self::forInfo($match_group,$sign,$arr);
        //var_dump($stringList);exit;//

        return $stringList;
    }

    static function forList($sign,$arr=array()){

        //var_dump($sign);var_dump($arr);echo '<br>';

        //获取视图缓存
        $listView=self::htmlFiltrationRep(self::$view);//过滤html换号符//空格精简//方便正则匹配
        //$listView=self::$view;
        //var_dump( $listView );//exit;//

        //设置 {{type}} 记号范围
        $match_patterns="/{{[forList::]+\"$sign\"+}}(.*?|\W+|\w+){{\/+[forList::]+\"$sign\"+}}/i";

        //获取forlist模板区域
        $match_group=self::forListSign($listView,$match_patterns);
        //var_dump($match_group);exit;//

        //模板区域 单列字段 替换
        $stringList=self::forInfo($match_group,$sign,$arr);
        //var_dump($stringList);exit;//

        //folist区域整块替换
        $fileStr = preg_replace($match_patterns,$stringList, $listView);

        $fileStr=self::htmlFiltrationBack($fileStr);
        //print_r( $fileStr );exit;//

        self::$view =$fileStr;

        return $stringList;//单独返回前途完数据的列表部分//用于二级目录回调
    }

////\\列表循环


    static function asSign($sign,$value){//变量填充 当前模板中 单个标记
        $assign[$sign]=$value;
        //var_dump(self::$view);exit;

        if($assign!=''&& self::$view!=''){

            foreach($assign as $k=>$v){
                $patterns[]="/{{\s+($k)\s+}}|{{($k)}}/i";
                $replace[]="$v";
            }

        }else{
            exit('asSign fail!');
        }

        $filestr=preg_replace($patterns,$replace,self::$view);
        //var_dump($filestr);exit;//
        self::$view=$filestr;
    }

    static function asSignArr($arr=array()){//变量填充 当前模板中 单个标记//数组方式

        foreach($arr as $k=>$v){
            $asArray[$k]=$v;
        }

        if($asArray!='' && self::$view!=''){

            foreach($asArray as $k=>$v){
                $patterns[]="/{{\s+($k)\s+}}|{{($k)}}/";
                $replace[]="$v";
            }

        }

        $filestr=preg_replace($patterns,$replace,self::$view);
        //var_dump($filestr);exit;//
        self::$view=$filestr;

    }



    static function show(){//输出视图

        $fileStr=self::htmlFiltrationZip(self::$view);
        echo $fileStr;

    }


}


?>
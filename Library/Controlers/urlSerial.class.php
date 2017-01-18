<?php
/* Created by User: soma Worker: 陈鸿扬 Date: 16/7/28  Time: 17:15 */

namespace Controlers;
use stdClass;
use Debugs\frameDebug as FD;
use Controlers\urlRoute as urlRoute;

//url序列 抓取地址栏 序列参数 数组
class urlSerial {

    private static $from;

    private static $this_scr_name;
    private static $this_req_uri;

    private static $iGet;

    function __construct(){

    }

    private static function trimall($str)//删除空格
    {
        $qian=array(" ","　","\t","\n","\r");
        $hou=array("","","","","");
        return str_replace($qian,$hou,$str);
    }

//get 旧方法
    static function get($mode=null){

        self::$from="FROM::Library/Controlers/urlSerial.class::";

        self::$this_scr_name=$_SERVER['SCRIPT_NAME'];
        self::$this_req_uri=$_SERVER['REQUEST_URI'];


        switch(PATH_URI){
            case 'Normal'&&'':

                FD::frameDebugExit(self::$from.'当前不是 Path_Info 模式!');

                break;
            case 'Path_Info':

                $this_repl=urlRoute::get('Path_Info');//路由数组
                //print_r($this_repl);echo"<br/>";//
                $this_serial=array_slice($this_repl,'2');//路由数组截掉 控制器和模型 数组
                //print_r($this_serial);echo '<br/><br/>';//

                if($mode==null){$mode='-';}//默认是‘-’ ，微信token序列 则不要使用'-'或者'_'
                $this_mode=urlencode($mode);
                $mode_arr=array( urlencode('+'),urlencode('='),urlencode('-'),urlencode('_'));//序列分隔符

                //print_r( $this_mode );echo '<br/><br/>';//
                //print_r( $mode_arr );echo '<br/><br/>';//

                if( !in_array($this_mode,$mode_arr) ){
                    FD::frameDebugExit(self::$from.'URL序列分隔符错误!不是 “ '.$mode.' ” 号');
                }

                $serial=new stdClass();
                foreach($this_serial as $k=>$v){

                    $equl_replace=preg_replace('/%3D/',$this_mode,$v);//过滤序列中的等于号为 自定义符号
                    //print_r( $equl_replace.'<br/><br/>');//

                    $serial_match=preg_match('/'.$this_mode.'/',$equl_replace);//检查自定义符号序列
                    //var_dump($serial_match);echo '<br/><br/>';//

                    if($serial_match){
                        $vv=explode($this_mode,$equl_replace);//%3D//
                        //print_r($vv);echo '<br/><br/>';//

                        $v1='';

                        if($vv[0]==''){continue;}//跳过异常序列

                        ////处理变量分隔符 重复的 情况
                        if( count($vv)>2 ){
                            for( $i=1;$i<count($vv);$i++ ){
                                if($i=='1')$v1.=$vv[$i];
                                else $v1.=$this_mode.$vv[$i];
                            }
                            $serial->$vv[0]=urldecode( urldecode( $v1 ));
                        }else{

                            if($vv[1]=='0' ){ $serial->$vv[0]="0"; }//对零转义
                            else{ $serial->$vv[0]=urldecode( urldecode( $vv[1] )); }

                        }
                        //\\

                    }
                    else{

                        FD::frameDebugExit(self::$from.'第'.($k+1).'个序列单元不符合格式');

                    }

                }

                //print_r($serial);//exit;//

                self::$iGet=$serial;

                return $serial;

                break;
            case 'Rewrite':

                $this_repl=urlRoute::get('Rewrite');//路由数组
                //print_r($this_repl);echo"<br/>";//
                $this_serial=array_slice($this_repl,'2');//路由数组截掉 控制器和模型 数组
                //print_r($this_serial);echo '<br/><br/>';//

                if($mode==null){$mode='-';}//默认是‘-’ ，微信token序列 则不要使用'-'或者'_'
                $this_mode=urlencode($mode);
                $mode_arr=array( urlencode('+'),urlencode('='),urlencode('-'),urlencode('_'));//序列分隔符

                //print_r( $this_mode );echo '<br/><br/>';//
                //print_r( $mode_arr );echo '<br/><br/>';//

                if( !in_array($this_mode,$mode_arr) ){
                    FD::frameDebugExit(self::$from.'URL序列分隔符错误!不是 “ '.$mode.' ” 号');
                }

                $serial=new stdClass();
                foreach($this_serial as $k=>$v){

                    $equl_replace=preg_replace('/%3D/',$this_mode,$v);//过滤序列中的等于号为 自定义符号
                    //print_r( $equl_replace.'<br/><br/>');//

                    $serial_match=preg_match('/'.$this_mode.'/',$equl_replace);//检查自定义符号序列
                    //var_dump($serial_match);echo '<br/><br/>';//

                    if($serial_match){
                        $vv=explode($this_mode,$equl_replace);//%3D//
                        //print_r($vv);echo '<br/><br/>';//

                        $v1='';

                        if($vv[0]==''){continue;}//跳过异常序列

                        ////处理变量分隔符 重复的 情况
                        if( count($vv)>2 ){
                            for( $i=1;$i<count($vv);$i++ ){
                                if($i=='1')$v1.=$vv[$i];
                                else $v1.=$this_mode.$vv[$i];
                            }
                            $serial->$vv[0]=urldecode( urldecode( $v1 ));
                        }else{

                            if($vv[1]=='0' ){ $serial->$vv[0]="0"; }//对零转义
                            else{ $serial->$vv[0]=urldecode( urldecode( $vv[1] )); }

                        }
                        //\\

                    }
                    else{

                        FD::frameDebugExit(self::$from.'第'.($k+1).'个序列单元不符合格式');

                    }

                }

                //print_r($serial);//exit;//

                self::$iGet=$serial;

                return $serial;

                break;
        }

    }
//

//get 新方法
    static function init($mode=null){

        self::$from="FROM::Library/Controlers/urlSerial.class::";

        self::$this_scr_name=$_SERVER['SCRIPT_NAME'];
        self::$this_req_uri=$_SERVER['REQUEST_URI'];


        switch(PATH_URI){
            case 'Normal'&&'':

                FD::frameDebugExit(self::$from.'当前不是 Path_Info 模式!');

                break;
            case 'Path_Info':

                $this_repl=urlRoute::get('Path_Info');//路由数组
                //print_r($this_repl);echo"<br/>";//
                $this_serial=array_slice($this_repl,'2');//路由数组截掉 控制器和模型 数组
                //print_r($this_serial);echo '<br/><br/>';//

                if($mode==null){$mode='-';}//默认是‘-’ ，微信token序列 则不要使用'-'或者'_'
                $this_mode=urlencode($mode);
                $mode_arr=array( urlencode('+'),urlencode('='),urlencode('-'),urlencode('_'));//序列分隔符

                //print_r( $this_mode );echo '<br/><br/>';//
                //print_r( $mode_arr );echo '<br/><br/>';//

                if( !in_array($this_mode,$mode_arr) ){
                    FD::frameDebugExit(self::$from.'URL序列分隔符错误!不是 “ '.$mode.' ” 号');
                }

                $serial=new stdClass();
                foreach($this_serial as $k=>$v){

                    $equl_replace=preg_replace('/%3D/',$this_mode,$v);//过滤序列中的等于号为 自定义符号
                    //print_r( $equl_replace.'<br/><br/>');//

                    $serial_match=preg_match('/'.$this_mode.'/',$equl_replace);//检查自定义符号序列
                    //var_dump($serial_match);echo '<br/><br/>';//

                    if($serial_match){
                        $vv=explode($this_mode,$equl_replace);//%3D//
                        //print_r($vv);echo '<br/><br/>';//
                        $v1='';

                        if($vv[0]==''){continue;}//跳过异常序列

                        ////处理变量分隔符 重复的 情况
                        if( count($vv)>2 ){
                            for( $i=1;$i<count($vv);$i++ ){
                                if($i=='1')$v1.=$vv[$i];
                                else $v1.=$this_mode.$vv[$i];
                            }
                            $serial->$vv[0]=urldecode( urldecode( $v1 ));
                        }else{

                            if($vv[1]=='0' ){ $serial->$vv[0]="0"; }//对零转义
                            else{ $serial->$vv[0]=urldecode( urldecode( $vv[1] )); }

                        }
                        //\\

                        //var_dump($vv);echo '<br/><br/>';//
                    }
                    else{

                        FD::frameDebugExit(self::$from.'第'.($k+1).'个序列单元不符合格式');

                    }

                }

                //print_r($serial);//exit;//

                self::$iGet=$serial;

                return $serial;

                break;
            case 'Rewrite':

                $this_repl=urlRoute::get('Rewrite');//路由数组
                //print_r($this_repl);echo"<br/>";//
                $this_serial=array_slice($this_repl,'2');//路由数组截掉 控制器和模型 数组
                //print_r($this_serial);echo '<br/><br/>';//

                if($mode==null){$mode='-';}//默认是‘-’ ，微信token序列 则不要使用'-'或者'_'
                $this_mode=urlencode($mode);
                $mode_arr=array( urlencode('+'),urlencode('='),urlencode('-'),urlencode('_'));//序列分隔符

                //print_r( $this_mode );echo '<br/><br/>';//
                //print_r( $mode_arr );echo '<br/><br/>';//

                if( !in_array($this_mode,$mode_arr) ){
                    FD::frameDebugExit(self::$from.'URL序列分隔符错误!不是 “ '.$mode.' ” 号');
                }

                $serial=new stdClass();
                foreach($this_serial as $k=>$v){

                    $equl_replace=preg_replace('/%3D/',$this_mode,$v);//过滤序列中的等于号为 自定义符号
                    //print_r( $equl_replace.'<br/><br/>');//

                    $serial_match=preg_match('/'.$this_mode.'/',$equl_replace);//检查自定义符号序列
                    //var_dump($serial_match);echo '<br/><br/>';//

                    if($serial_match){
                        $vv=explode($this_mode,$equl_replace);//%3D//
                        //print_r($vv);echo '<br/><br/>';//
                        $v1='';

                        if($vv[0]==''){continue;}//跳过异常序列

                        ////处理变量分隔符 重复的 情况
                        if( count($vv)>2 ){
                            for( $i=1;$i<count($vv);$i++ ){
                                if($i=='1')$v1.=$vv[$i];
                                else $v1.=$this_mode.$vv[$i];
                            }
                            $serial->$vv[0]=urldecode( urldecode( $v1 ));
                        }else{

                            if($vv[1]=='0' ){ $serial->$vv[0]="0"; }//对零转义
                            else{ $serial->$vv[0]=urldecode( urldecode( $vv[1] )); }

                        }
                        //\\

                        //var_dump($vv);echo '<br/><br/>';//
                    }
                    else{

                        FD::frameDebugExit(self::$from.'第'.($k+1).'个序列单元不符合格式');

                    }

                }

                //print_r($serial);//exit;//

                self::$iGet=$serial;

                return $serial;

                break;
        }

    }
//

    static function have($key,$mode=null){

        self::init($mode);

        return @self::$iGet->$key;

    }

} 
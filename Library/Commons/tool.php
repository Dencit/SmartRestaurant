<?php
namespace Commons ;

class tool
{

    function __construct(){



    }

    static function isSetRe($data){

        $is_data=isset($data);
        $n_data=$is_data?$data:'';

        return $n_data;

    }

    //处理值get值 防报错
    static function is_Get($string){

        //获取 $string字符串 对应的 $_POST 键值
        if(is_string($string)){
            $is_get=@$_GET[$string];
            $value=isset($is_get)?$is_get:null;
            return $value;
        }

        //获取 $string键值对中 值名与$_POST键名 对应的 值
        if(is_array($string)||is_object($string)){
            $new_arr=[];
            foreach($string as $key=>$val){
                $is_get=@$_GET[$val];
                $new_arr[$key]=isset($is_get)?$is_get:null;
            }

            //var_dump($new_arr);//
            return $new_arr;
        }

        return false;
    }


    /*$data['food_qual']='foodQu';
    $data['serv_qual']='serveQu';
    $data['envi_qual']='environmentQu';
    $data['advise']='advise_info';
    $data=tool::is_Post($data);
    var_dump($data);*/

    //处理值post值 防报错
    static function is_Post($string){

        //获取 $string字符串 对应的 $_POST 键值
        if(is_string($string)){
            $is_post=@$_POST[$string];
            $value=isset($is_post)?$is_post:$string;
            return $value;
        }

        //获取 $string键值对中 值名与$_POST键名 对应的 值
        if(is_array($string)||is_object($string)){
            $new_arr=[];
            foreach($string as $key=>$val){
                $is_post=@$_POST[$val];
                $new_arr[$key]=isset($is_post)?$is_post:$val;
            }

            //var_dump($new_arr);//
            return $new_arr;
        }

        return false;
    }


    /*
    $kv_array['one']=1;
    $kv_array['two']=2;
    $kv_array['third']=3;
    tool::mk_session($kv_array);
    */
    static function mk_session($kv_array='',$unset=''){
        if(is_array($kv_array)){
            foreach($kv_array as $k=>$v){
                //echo $k.'||'.$v.'<br/>';

                if($unset!='') unset($_SESSION[PREFIX.$k]);
                else $_SESSION[PREFIX.$k]=$v;
            }
            return true;
        }

        return false;
    }

    /*
    $no_array[0]='one';
    $no_array[1]='two';
    $no_array[2]='third';
    var_dump( tool::get_session($no_array) );
    */
    static  function get_session($no_array='',$unset=''){

        if(is_array($no_array)){
            $kv_array=new \stdClass();
            foreach($no_array as $k=>$v){
                if($unset!=''){ unset( $_SESSION[PREFIX.$v]) ; }
                elseif(!isset($_SESSION[PREFIX.$v])) continue;
                else $kv_array->$v=$_SESSION[PREFIX.$v];
            }
            if($unset!='') return true;
            elseif( end($kv_array)==null ) return false;
            else return $kv_array;
        }elseif( is_string($no_array) ){

            if($unset!=''){ unset( $_SESSION[PREFIX.$no_array] ); }
            elseif( !isset($_SESSION[PREFIX.$no_array]) ) return false;
            else return $_SESSION[PREFIX.$no_array];

        }

        return false;
    }

    static function setTimeOut($type,$step,$end){

        $step=floatval($step);$end=floatval($end);

        $time_ax=self::get_session($type);
        $time_ax+=$step;
        self::mk_session(array($type=>$time_ax));
        $time_ax=self::get_session($type);

        if($time_ax>=$end){
            tool::get_session($type,'1');
            return false;
        }

        return $time_ax;
    }

    static function ip()
    {
        if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        } else if (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } else if (!empty($_SERVER["REMOTE_ADDR"])) {
            $ip = $_SERVER["REMOTE_ADDR"];
        } else {
            $ip = '';
        }
        preg_match("/[\d\.]{7,15}/", $ip, $ips);
        $ip = isset($ips[0]) ? $ips[0] : 'unknown';
        return $ip;
    }

    static function get_ip()
    {
        if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        } else if (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } else if (!empty($_SERVER["REMOTE_ADDR"])) {
            $ip = $_SERVER["REMOTE_ADDR"];
        } else {
            $ip = '';
        }
        preg_match("/[\d\.]{7,15}/", $ip, $ips);
        $ip = isset($ips[0]) ? $ips[0] : 'unknown';
        return $ip;
    }

    static function conStr($Str)
    {
        return mb_convert_encoding($Str,'UTF-8','auto');
    }

    static function jsonSet($arr=array()){
        $json_obj=json_encode($arr);
        return $json_obj;
    }

    static function jsonExit($arr=array()){
        $json_obj=json_encode($arr);
        exit($json_obj);
    }

    static function jsonResult($arr=array(),$errcode='',$errmsg='',$redirect=''){

        if($errcode!=''){ $result['errcode']=$errcode; }
        if($errmsg!=''){$result['errmsg']=$errmsg;}
        if($redirect!=''){$result['redirect']=$redirect;}

        $result['data']=$arr;
        $json_obj=json_encode($result);
        exit($json_obj);
    }

    //处理 get_results 返回的数组中包含std对象的情况
    static function std2arr($giftCount,$count){
        $arr=array();
        foreach($giftCount as $k=>$v){
            $arr[]=$giftCount[$k]->$count;
        }
        return $arr;
    }

    //比较两个数组间 同值的情况 有多少次
    static function arrayContrast ($firstArr,$secondArr,$count='0'){
        foreach($firstArr as $k=>$v){
            if($v==$secondArr[$k]){
                $count+=1;
            }
        }
        return $count;
    }

    //过滤符号
    static function filter_mark($text){
        if(trim($text)=='')return '';
        $text=preg_replace("/[[:punct:]\s]/",' ',$text);
        $text=urlencode($text);
        $text=preg_replace("/(%7E|%60|%21|%40|%23|%24|%25|%5E|%26|%27|%2A|%28|%29|%2B|%7C|%5C|%3D|\-|_|%5B|%5D|%7D|%7B|%3B|%22|%3A|%3F|%3E|%3C|%2C|\.|%2F|%A3%BF|%A1%B7|%A1%B6|%A1%A2|%A1%A3|%A3%AC|%7D|%A1%B0|%A3%BA|%A3%BB|%A1%AE|%A1%AF|%A1%B1|%A3%FC|%A3%BD|%A1%AA|%A3%A9|%A3%A8|%A1%AD|%A3%A4|%A1%A4|%A3%A1|%E3%80%82|%EF%BC%81|%EF%BC%8C|%EF%BC%9B|%EF%BC%9F|%EF%BC%9A|%E3%80%81|%E2%80%A6%E2%80%A6|%E2%80%9D|%E2%80%9C|%E2%80%98|%E2%80%99|%EF%BD%9E|%EF%BC%8E|%EF%BC%88)+/",' ',$text);
        $text=urldecode($text);
        return trim($text);
    }


    //
    static function writeLogText($data){

        $dataText='';

        if(is_array($data)){
            foreach($data as  $k=>$v){
                if(end($data)){
                    $dataText.=$k.':'.$v.',';
                }else{
                    $dataText.=$k.':'.$v;
                }
            }
        }else{
            $dataText=$data;
        }

        $filename ="writeLogText.text";
        $filePath = CACHE."/".$filename;
        $rs = @file_put_contents($filePath,$dataText);

        if($rs && @chmod($fpath,0260)){
            return true;
        }

    }

    static function clearLogText($data){

    }


}
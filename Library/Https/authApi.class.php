<?php
/* Created by User: soma Worker: 陈鸿扬 Date: 2016/6/2 Time: 11:21 */
namespace Https;

class authApi{

    private static $account = "[NAME]";
    private static $passwd = "[PASS_WORD]";

    function __construct(){

    }

//有限授权
     static function usrAuth($redirect_uri = '',$scope = 'snsapi_userinfo'){
        $time = time();
        $sign = "OAuth2".self::$account.self::$passwd.$time;
        $url=OAUTH2_URI."/?acc=".self::$account."&time=".$time."&sign=".md5($sign)."&scope=".$scope."&state=".urlencode($redirect_uri);
        header("location:".$url);
        exit;
    }


//获取全局access_token
     static function globeAccessToken(){
        $time = time();
        $sign = "access".self::$account.self::$passwd.$time."token";
        $url = ACCESS_TOKEN."/?acc=".self::$account."&time=".$time."&sign=".md5($sign);
        $data = self::http($url);
        if($data){
            $globeAccessToken = json_decode($data)->access_token;
            return $globeAccessToken;
        }
    }

//http curl get请求函数
     static function http($url){
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $data = curl_exec($ch);
        $error = curl_error($ch);

        //关闭URL请求
        curl_close($ch);
        if($error){
            echo $error;
            return false;
        }
        return $data;
    }

//http curl post请求函数
    static function http_post($url,$post_array_data){
        $ch = curl_init();

        //设置抓取的url
        curl_setopt($ch,CURLOPT_URL,$url);
        //设置头文件的信息作为数据流输出
        curl_setopt($ch,CURLOPT_HEADER,TRUE);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
        //设置过期时间
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        //设置post方式提交
        curl_setopt($ch,CURLOPT_POST,TRUE);
        //设置post数据
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_array_data);
        //执行命令

        $data = curl_exec($ch);
        $error = curl_error($ch);

        //关闭URL请求
        curl_close($ch);
        if($error){
            echo $error;
            return false;
        }
        return $data;
    }


//http curl_request 整合函数//
    static function curl_request($url,$post='',$cookie='', $returnCookie=0){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)');
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        curl_setopt($curl, CURLOPT_REFERER, "http://XXX");
        if($post) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));
        }
        if($cookie) {
            curl_setopt($curl, CURLOPT_COOKIE, $cookie);
        }
        curl_setopt($curl, CURLOPT_HEADER, $returnCookie);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $data = curl_exec($curl);
        if (curl_errno($curl)) {
            return curl_error($curl);
        }
        curl_close($curl);

        if($returnCookie){
            list($header, $body) = explode("\r\n\r\n", $data, 2);
            preg_match_all("/Set\-Cookie:([^;]*);/", $header, $matches);
            $info['cookie']  = substr($matches[1][0], 1);
            $info['content'] = $body;
            return $info;
        }else{
            return $data;
        }
    }



}
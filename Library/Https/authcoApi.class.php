<?php
/* Created by User: soma Worker: 陈鸿扬 Date: 2016/6/2 Time: 11:21 */
namespace Https;

class authcoApi{

    private static $account = "[NAME]";
    private static $passwd = "[PASS_WORD]";

    function __construct(){

    }

//pc端 用户扫码授权 授权链接
    static function usrCodeAuth($final_redirect_uri,$user_type='all'){

        $time = time();
        $sign = "loginAuth_".self::$account.self::$passwd.$time;
        $url=API_URL."/login/&acc=".self::$account."&time=".$time."&sign=".md5($sign)."&user_type=".$user_type."&final_redirect_uri=".urlencode($final_redirect_uri);
        //echo($url);exit;//

        header("location:".$url);
    }

//pc端 用户扫码授权 授权码
    static function usrCodeInfo($auth_code,$access_token){

        $url="https://qyapi.weixin.qq.com/cgi-bin/service/get_login_info?access_token=".$access_token;
        //var_dump($url);exit;//
        $data=array("auth_code"=>$auth_code);
        $result=json_decode( self::http_post($url,$data) );
        //var_dump($result);exit;//

        if(@$result->errcode=='40029'){
            return 'code_fail';
        }else if(!@$result->user_info->userid){
            return 'user_fail';
        }

        return $result;

        /*
         *通讯录用户
        object(stdClass)#13 (3) {
          ["usertype"]=>
          int(5)
          ["user_info"]=>
          object(stdClass)#14 (3) {
            ["userid"]=>
            string(3) "002"
            ["name"]=>
            string(9) "陈鸿扬"
            ["avatar"]=>
            string(82) "http://shp.qpic.cn/bizmp/DlJnhX4UZ8BE1nDtDLnzFt9WwhicniaUdnL5X77cK4m1Wlp3gxTJ37xg/"
          }
          ["corp_info"]=>
          object(stdClass)#15 (1) {
            ["corpid"]=>
            string(18) "wx6c13a09936584ffd"
          }
        }
         *
         * */

        /*
         * 通讯录管理者
         * object(stdClass)#8 (4) {
              ["usertype"]=>
              int(4)
              ["user_info"]=>
              object(stdClass)#9 (3) {
                ["userid"]=>
                string(3) "002"
                ["name"]=>
                string(9) "陈鸿扬"
                ["avatar"]=>
                string(82) "http://shp.qpic.cn/bizmp/DlJnhX4UZ8BE1nDtDLnzFt9WwhicniaUdnL5X77cK4m1Wlp3gxTJ37xg/"
              }
              ["redirect_login_info"]=>
              object(stdClass)#10 (2) {
                ["login_ticket"]=>
                string(32) "4a2da8bfd643b1da2e3d0d215ca39ab5"
                ["expires_in"]=>
                int(36000)
              }
              ["corp_info"]=>
              object(stdClass)#11 (1) {
                ["corpid"]=>
                string(18) "wx6c13a09936584ffd"
              }
            }
         * */

    }


//移动端 有限授权
     static function usrAuth($final_redirect_uri= '',$scope ='snsapi_base'){
        $time = time();
        $sign = "OAuth2".self::$account.self::$passwd.$time;
        $url=OAUTH2_URI."/&acc=".self::$account."&time=".$time."&sign=".md5($sign)."&scope=".$scope."&final_redirect_uri=".urlencode($final_redirect_uri);

        //echo($url);exit;//
        header("location:".$url);
        exit;
    }


//获取全局access_token
     static function globeAccessToken(){
        $time = time();
        $sign = "access".self::$account.self::$passwd.$time."token";
        $url = ACCESS_TOKEN."/&acc=".self::$account."&time=".$time."&sign=".md5($sign);
        $data = self::http($url);

        //var_dump($data);exit;

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
        curl_setopt($ch,CURLOPT_HEADER,false);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
        //设置过期时间
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        //设置post方式提交
        curl_setopt($ch,CURLOPT_POST,TRUE);
        //设置post数据
        $post_json_data=json_encode($post_array_data);
        $post_json_data=urldecode($post_json_data);//对付 包含用urlencode的中文数据

        //var_dump($post_json_data);//

        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_json_data);
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
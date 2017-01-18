<?php
/* Created by User: soma Worker:陈鸿扬  Date: 16/10/28  Time: 09:33 */

namespace Controlers;

use \Commons\tool as tool;//工具类
use \Commons\jump as jump;
use \Controlers\urlSerial as I;

use Https\weicoApi;
use \NoSql\redis as redis;//noSql redis类
use \Apis\weicoApi as weiApi;//企业号接口类


class weixinco extends baseControler {

    private static $key;
    private static $refresh;
    private static $api;

    private static $acc_arr;

    private static $passwd_arr;


    function __construct(){

        /* 接口访问账号和密匙 */

        //账号
        self::$acc_arr["name"] = "[NAME]";
        //账号对应密匙
        self::$passwd_arr["name"] = "[PASS_WORD]";

        self::$api = new weiApi;

        redis::init();

    }

    function init(){

        self::$key=I::have('k');
        //var_dump(self::$key);//
        self::$refresh = I::have('r');
        //var_dump(self::$refresh);//

        if(self::$key !="BangJu888"){ exit('fail'); };

        /* access_token */
        $access_token = redis::get(PREFIX."access_token");
        $access_token_ttl = redis::ttl(PREFIX."access_token");//读取expire 设置的过期时间

        if(!$access_token || $access_token_ttl < 300 || self::$refresh=='refresh'){
            $data = self::$api->access_token();
            if(!$data){
                //发送警报给管理员
            }else{
                $data = json_decode($data);
            }
            if(!isset($data->access_token) || !isset($data->expires_in)){
                //发送警报给管理员
            }else {
                redis::set(PREFIX."access_token", $data->access_token);
                redis::expire(PREFIX."access_token", ($data->expires_in - 10));
                echo "[ ".$access_token_ttl." ] [ ".date('Y-m-d h:i:s')." ] [ access_token OK ] ";
            }
        }else{
            echo "[ ".$access_token_ttl." ] [ ".date('Y-m-d h:i:s')." ] [ access_token NOT Refresh ] ";
        }


        /* jsapi_ticket */
        $jsapi_ticket = redis::get(PREFIX."jsapi_ticket");
        $jsapi_ticket_ttl = redis::ttl(PREFIX."jsapi_ticket");

        if(!$jsapi_ticket || $jsapi_ticket_ttl < 300 || self::$refresh=='refresh'){
            $data = self::$api->jsapi_ticket($access_token);
            if(!$data){
                //发送警报给管理员
            }else{
                $data = json_decode($data);
            }
            if(!isset($data->ticket) || !isset($data->expires_in)){
                //发送警报给管理员
            }else {
                redis::set(PREFIX."jsapi_ticket", $data->ticket);
                redis::expire(PREFIX."jsapi_ticket", ($data->expires_in - 10));
                echo " [ jsapi_ticket OK ] ";
            }
        }else{
            echo " [ jsapi_ticket NOT Refresh ] ";
        }


        /* api_ticket */
        $api_ticket = redis::get(PREFIX."api_ticket");
        $api_ticket_ttl = redis::ttl(PREFIX."api_ticket");

        if(!$api_ticket || $api_ticket_ttl < 300 || self::$refresh=='refresh'){
            $data = self::$api->api_ticket($access_token);
            if(!$data){
                //发送警报给管理员
            }else{
                $data = json_decode($data);
            }
            if(!isset($data->ticket) || !isset($data->expires_in)){
                //发送警报给管理员
            }else {
                redis::set(PREFIX."api_ticket", $data->ticket);
                redis::expire(PREFIX."api_ticket", ($data->expires_in - 10));
                echo " [ api_ticket OK ] \n";
            }
        }else{
            echo " [ api_ticket NOT Refresh ] \n";
        }

    }

    function login(){

        $acc = isset($_GET["acc"]) ? trim($_GET["acc"]) : "";
        $time = isset($_GET["time"]) ? trim($_GET["time"]) : "";
        $sign = isset($_GET["sign"]) ? trim($_GET["sign"]) : "";

        $user_type = isset($_GET['user_type']) ? trim($_GET['user_type']) : '';
        $final_redirect_uri = isset($_GET['final_redirect_uri']) ? trim($_GET['final_redirect_uri']) : '';


        $debug = isset($_GET['debug']) ? trim($_GET['debug']) : '';//$debug=1;//调试用//
        //$redirect_uri api内跳转地址
        if($debug){ $redirect_uri = API_URL.'/authCode/&debug=1'; }
        else{ $redirect_uri = API_URL.'/authCode/'; }


        ////检查序列
        $rsp = new \stdClass();
        if(!$acc || !$time || !$sign || !$user_type || !$final_redirect_uri){
            $rsp->errcode = -1; $rsp->errmsg = "Parameter missing!"; echo json_encode($rsp); exit;
        }
        if(!isset(self::$acc_arr[$acc])){
            if($debug){ $rsp->errcode = -2; $rsp->errmsg = "No account!"; echo json_encode($rsp); }
            else{ header("location:".urldecode($final_redirect_uri)); } exit;
        }
        if(strlen($time) != 10){
            if($debug){ $rsp->errcode = -3; $rsp->errmsg = "time parameter error!"; echo json_encode($rsp); }
            else{ header("location:".urldecode($final_redirect_uri)); } exit;
        }
        if(abs($time - TIME) > 300){
            if($debug){  $rsp->errcode = -4; $rsp->errmsg = "time parameter is not match with the server time!"; echo json_encode($rsp);}
            else{ header("location:".urldecode($final_redirect_uri)); } exit;
        }
        if(md5("loginAuth_".$acc.self::$passwd_arr[$acc].$time) != $sign){
            if($debug){ $rsp->errcode = -5; $rsp->errmsg = "sign is not match!"; echo json_encode($rsp); }
            else{ header("location:".urldecode($final_redirect_uri)); } exit;
        }
        if (!in_array($user_type,array('all','admin','member'))){
            if($debug){ $rsp->errcode = -6; $rsp->errmsg = 'usertype is invalid!'; echo json_encode($rsp); }
            else{ header("location:".urldecode($final_redirect_uri)); } exit;
        }
        if (!$final_redirect_uri){
            if($debug){ $rsp->errcode = -7; $rsp->errmsg = 'state is empty.state must be an url!'; echo json_encode($rsp); }
            else{ header("location:".urldecode($final_redirect_uri)); } exit;
        }
        //\\


        ////标记
        $state = PREFIX.'loginAuth_'.md5($acc.$final_redirect_uri.$user_type.TIME.rand(0,100000));
        while ( redis::exists($state) ){
            $state = PREFIX.'loginAuth_'.md5($acc.$final_redirect_uri.$user_type.TIME.rand(0,100000));
        }
        redis::set($state,$final_redirect_uri);
        redis::expire($state,300);
        //最终跳转地址
        setcookie(PREFIX."loginAuth_back",$final_redirect_uri);
        //\\

        self::$api->get_auth_code($redirect_uri,$user_type,$state);
    }

    function authCode(){

        $state = isset($_GET['state']) ? trim($_GET['state']) : '';
        $debug = isset($_GET['debug']) ? trim($_GET['debug']) : ''; //$debug=1;//调试用//

        $auth_code=tool::is_Get('auth_code');
        $expires_in=tool::is_Get('expires_in');
        //var_dump($auth_code);var_dump($expires_in);

        $loginAuth_back = isset($_COOKIE[PREFIX."loginAuth_back"]) ? trim($_COOKIE[PREFIX."loginAuth_back"]) : "";

        $rsp = new \stdClass();
        if (!$auth_code){
            if($debug){ $rsp->errcode = -11; $rsp->errmsg = 'auth_code is empty from weixin server.'; echo json_encode($rsp);}
            else{ header("location:".urldecode($loginAuth_back)); } exit;
        }
        if (!$expires_in){
            if($debug){ $rsp->errcode = -12; $rsp->errmsg = 'expires_in is empty from weixin server.'; echo json_encode($rsp);}
            else{ header("location:".urldecode($loginAuth_back)); exit; }
        }
        if (!$state){
            if($debug){ $rsp->errcode = -13; $rsp->errmsg = 'state is empty from weixin server.'; echo json_encode($rsp); }
            else{ header("location:".urldecode($loginAuth_back)); } exit;
        }
        if (!redis::exists($state)){
            if($debug){ $rsp->errcode = -14; $rsp->errmsg = 'state is not match or request timeout from weixin server.'; echo json_encode($rsp); }
            else{ header("location:".urldecode($loginAuth_back)); } exit;
        }

        $back=urldecode($loginAuth_back);
        $access_token = redis::get(PREFIX."access_token");

        //var_dump($back);exit;//

        header("location:".$back."&auth_code=".$auth_code."&access_token=".$access_token);

        exit;

    }


    function oauth(){

        $acc = isset($_GET["acc"]) ? trim($_GET["acc"]) : "";
        $time = isset($_GET["time"]) ? trim($_GET["time"]) : "";
        $sign = isset($_GET["sign"]) ? trim($_GET["sign"]) : "";

        $scope = isset($_GET['scope']) ? trim($_GET['scope']) : '';
        $final_redirect_uri = isset($_GET['final_redirect_uri']) ? trim($_GET['final_redirect_uri']) : '';
        $debug = isset($_GET['debug']) ? trim($_GET['debug']) : ''; //$debug=1;//调试用//

        //$debug=1;//调试用//

        if($debug){
            $redirect_uri = urlencode(API_URL.'/userId/&debug=1');
        }else{
            $redirect_uri = urlencode(API_URL.'/userId/');
        }

        $rsp = new \stdClass();
        if(!$acc || !$time || !$sign || !$scope || !$final_redirect_uri){
            $rsp->errcode = -1;
            $rsp->errmsg = "Parameter missing!";
            echo json_encode($rsp);
            exit;
        }

        //var_dump(self::$acc_arr[$acc]);//

        if(!isset(self::$acc_arr[$acc])){
            if($debug){
                $rsp->errcode = -2;
                $rsp->errmsg = "No account!";
                echo json_encode($rsp);
            }else{
                header("location:".urldecode($final_redirect_uri));
            }
            exit;
        }
        if(strlen($time) != 10){
            if($debug){
                $rsp->errcode = -3;
                $rsp->errmsg = "time parameter error!";
                echo json_encode($rsp);
            }else{
                header("location:".urldecode($final_redirect_uri));
            }
            exit;
        }
        if(abs($time - TIME) > 300){
            if($debug){
                $rsp->errcode = -4;
                $rsp->errmsg = "time parameter is not match with the server time!";
                echo json_encode($rsp);
            }else{
                header("location:".urldecode($final_redirect_uri));
            }
            exit;
        }
        if(md5("OAuth2".$acc.self::$passwd_arr[$acc].$time) != $sign){
            if($debug){
                $rsp->errcode = -5;
                $rsp->errmsg = "sign is not match!";
                echo json_encode($rsp);
            }else{
                header("location:".urldecode($final_redirect_uri));
            }
            exit;
        }
        if (!in_array($scope,array('snsapi_base','snsapi_userinfo'))){
            if($debug){
                $rsp->errcode = -6;
                $rsp->errmsg = 'scope is invalid!';
                echo json_encode($rsp);
            }else{
                header("location:".urldecode($final_redirect_uri));
            }
            exit;
        }
        if (!$final_redirect_uri){
            if($debug){
                $rsp->errcode = -7;
                $rsp->errmsg = 'state is empty.state must be an url!';
                echo json_encode($rsp);
            }else{
                header("location:".urldecode($final_redirect_uri));
            }
            exit;
        }


        $state = PREFIX.'OAuth_'.md5($acc.$final_redirect_uri.$scope.TIME.rand(0,100000));
        while ( redis::exists($state) ){
            $state = PREFIX.'OAuth_'.md5($acc.$final_redirect_uri.$scope.TIME.rand(0,100000));
        }


        redis::set($state,$final_redirect_uri);
        redis::expire($state,60);
        setcookie(PREFIX."OAuth_back",$final_redirect_uri);

        //var_dump($redirect_uri);exit;//

        self::$api->get_code($scope,$state,$redirect_uri);//成功则去到 openid page

    }

    static function userId(){

        $code = isset($_GET['code']) ? trim($_GET['code']) : '';
        $state = isset($_GET['state']) ? trim($_GET['state']) : '';
        $debug = isset($_GET['debug']) ? trim($_GET['debug']) : ''; //$debug=1;//调试用//

        $oAuth_back = isset($_COOKIE[PREFIX."OAuth_back"]) ? trim($_COOKIE[PREFIX."OAuth_back"]) : "";

        $rsp = new \stdClass();
        if (!$state){
            if($debug){
                $rsp->errcode = -11;
                $rsp->errmsg = 'state is empty from weixin server.';
                echo json_encode($rsp);
            }else{
                header("location:".urldecode($oAuth_back));
            }
            exit;
        }

        if (!$code){
            if($debug){
                $rsp->errcode = -12;
                $rsp->errmsg = 'code is empty from weixin server.';
                echo json_encode($rsp);
            }else{
                header("location:".urldecode($oAuth_back));
            }
            exit;
        }


        if (!redis::exists($state)){
            if($debug){
                $rsp->errcode = -13;
                $rsp->errmsg = 'state is not match or request timeout from weixin server.';
                echo json_encode($rsp);
            }else{
                header("location:".urldecode($oAuth_back));
            }
            exit;
        }
        $back = urldecode( redis::get($state) );

        $access_token = redis::get(PREFIX."access_token");
        //var_dump($access_token);exit;//


        //获取用户信息//企业成员和非企业成员用户 返回不同标识
        $corp_data = self::$api->get_usersign_co($access_token,$code);
        //var_dump($corp_data);exit;//
        if (!$corp_data){ header("location:$back");exit; }


        $data_serialize='';


        if( isset($corp_data->UserId) ){
            //企业成员用户标识
            $open_Data = self::$api->convert_to_openid($access_token,array("userid"=>$corp_data->UserId) );
            //var_dump($open_Data);exit;//
            $data_serialize="userId=".$corp_data->UserId."&openId=".$open_Data->openid."&access_token=".$access_token;
            //var_dump($open_Data);var_dump($data_serialize);exit;//

        }
        elseif( isset($corp_data->OpenId) ){
            //非企业成员用户标识
            $data_serialize="userId=&openId=".$corp_data->openid."&access_token=".$access_token;
            //var_dump($open_Data);var_dump($data_serialize);exit;//
        }


        if(isset($corp_data->UserId)) {
            if (strpos($back, '?')) {

                if($debug) echo("location:$back&".$data_serialize);
                else header("location:$back&".$data_serialize);

            } else {

                if($debug) echo("location:$back?".$data_serialize);
                else header("location:$back?".$data_serialize);

            }
        }else{
            header("location:$back");
        }


    }

    function jsApi(){

        $url = isset($_GET['url']) ? trim($_GET['url']) : (isset($_POST['url']) ? trim($_POST['url']) : '');
        $package = new \stdClass();
        if (!$url){
            $package->errcode = -1;
            $package->errmsg = 'jsApi:: url is empty.';
            echo json_encode($package);
            exit;
        }
        $url_arr = parse_url($url);
        if(!$url_arr){
            $package->errcode = -2;
            $package->errmsg = 'api:: url is invalid.';
            echo json_encode($package);
            exit;
        }
        if(!isset($url_arr["scheme"]) || !isset($url_arr["host"])){
            $package->errcode = -3;
            $package->errmsg = 'api:: url is invalid!';
            echo json_encode($package);
            exit;
        }
        header("Access-Control-Allow-Credentials:true");
        header("Access-Control-Allow-Origin:".$url_arr["scheme"]."://".$url_arr["host"]);

        $jsapi_ticket = redis::get(PREFIX."jsapi_ticket");

        //随机字符串
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < 16; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }

        //这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$jsapi_ticket&noncestr=$str&timestamp=".TIME."&url=$url";
        $signature = sha1($string);

        $package->noncestr = $str;
        $package->jsapi_ticket = $jsapi_ticket;
        $package->timestamp = TIME;
        $package->url = $url;
        $package->signature = $signature;
        echo json_encode($package);

    }


    static function access_token(){

        //exit('ok');//

        $acc = isset($_GET["acc"]) ? trim($_GET["acc"]) : "";
        $time = isset($_GET["time"]) ? trim($_GET["time"]) : "";
        $sign = isset($_GET["sign"]) ? trim($_GET["sign"]) : "";

        //var_dump($acc);var_dump($time);var_dump($sign);exit;

        $rsp = new \stdClass();
        if(!$acc || !$time || !$sign){
            $rsp->errcode = -1;
            $rsp->errmsg = "Parameter missing!";
            echo json_encode($rsp);
            exit;
        }
        if(!isset(self::$acc_arr[$acc])){
            $rsp->errcode = -2;
            $rsp->errmsg = "No account!";
            echo json_encode($rsp);
            exit;
        }
        if(strlen($time) != 10){
            $rsp->errcode = -3;
            $rsp->errmsg = "time parameter error!";
            echo json_encode($rsp);
            exit;
        }
        if(abs($time - TIME) > 300){
            $rsp->errcode = -4;
            $rsp->errmsg = "time parameter is not match with the server time!";
            echo json_encode($rsp);
            exit;
        }
        if(md5("access".$acc.self::$passwd_arr[$acc].$time."token") != $sign){
            $rsp->errcode = -5;
            $rsp->errmsg = "sign is not match";
            echo json_encode($rsp);
            exit;
        }

        $access_token = redis::get(PREFIX."access_token");
        if($access_token){
            $expires_in = redis::ttl(PREFIX."access_token") - 300;

            $expires_in = ($expires_in <= 0) ? 5 : $expires_in;
            $rsp->errcode = 1;
            $rsp->errmsg = "success";
            $rsp->access_token = $access_token;
            $rsp->expires_in = $expires_in;
            echo json_encode($rsp);
            exit;
        }else{
            $rsp->errcode = 0;
            $rsp->errmsg = "fail";
            echo json_encode($rsp);
            exit;
        }

    }


    static function api_ticket(){

        $acc = isset($_GET["acc"]) ? trim($_GET["acc"]) : "";
        $time = isset($_GET["time"]) ? trim($_GET["time"]) : "";
        $sign = isset($_GET["sign"]) ? trim($_GET["sign"]) : "";

        $rsp = new \stdClass();
        if(!$acc || !$time || !$sign){
            $rsp->errcode = -1;
            $rsp->errmsg = "Parameter missing!";
            echo json_encode($rsp);
            exit;
        }
        if(!isset(self::$acc_arr[$acc])){
            $rsp->errcode = -2;
            $rsp->errmsg = "No account!";
            echo json_encode($rsp);
            exit;
        }
        if(strlen($time) != 10){
            $rsp->errcode = -3;
            $rsp->errmsg = "time parameter error!";
            echo json_encode($rsp);
            exit;
        }
        if(abs($time - TIME) > 300){
            $rsp->errcode = -4;
            $rsp->errmsg = "time parameter is not match with the server time!";
            echo json_encode($rsp);
            exit;
        }
        if(md5("api".$acc.self::$passwd_arr[$acc].$time."ticket") != $sign){
            $rsp->errcode = -5;
            $rsp->errmsg = "sign is not match";
            echo json_encode($rsp);
            exit;
        }

        $api_ticket = redis::get(PREFIX."api_ticket");
        if($api_ticket){
            $expires_in = redis::ttl(PREFIX."api_ticket") - 300;
            $expires_in = ($expires_in <= 0) ? 5 : $expires_in;
            $rsp->errcode = 1;
            $rsp->errmsg = "success";
            $rsp->api_ticket = $api_ticket;
            $rsp->expires_in = $expires_in;
            echo json_encode($rsp);
            exit;
        }else{
            $rsp->errcode = 0;
            $rsp->errmsg = "fail";
            echo json_encode($rsp);
            exit;
        }

    }


    static function js_api_ticket(){

        $acc = isset($_GET["acc"]) ? trim($_GET["acc"]) : "";
        $time = isset($_GET["time"]) ? trim($_GET["time"]) : "";
        $sign = isset($_GET["sign"]) ? trim($_GET["sign"]) : "";

        $rsp = new \stdClass();
        if(!$acc || !$time || !$sign){
            $rsp->errcode = -1;
            $rsp->errmsg = "Parameter missing!";
            echo json_encode($rsp);
            exit;
        }
        if(!isset(self::$acc_arr[$acc])){
            $rsp->errcode = -2;
            $rsp->errmsg = "No account!";
            echo json_encode($rsp);
            exit;
        }
        if(strlen($time) != 10){
            $rsp->errcode = -3;
            $rsp->errmsg = "time parameter error!";
            echo json_encode($rsp);
            exit;
        }
        if(abs($time - TIME) > 300){
            $rsp->errcode = -4;
            $rsp->errmsg = "time parameter is not match with the server time!";
            echo json_encode($rsp);
            exit;
        }
        if(md5("jsapi".$acc.self::$passwd_arr[$acc].$time."ticket") != $sign){
            $rsp->errcode = -5;
            $rsp->errmsg = "sign is not match";
            echo json_encode($rsp);
            exit;
        }

        $jsapi_ticket = redis::get(PREFIX."jsapi_ticket");
        if($jsapi_ticket){
            $expires_in = redis::ttl(PREFIX."jsapi_ticket") - 300;
            $expires_in = ($expires_in <= 0) ? 5 : $expires_in;
            $rsp->errcode = 1;
            $rsp->errmsg = "success";
            $rsp->jsapi_ticket = $jsapi_ticket;
            $rsp->expires_in = $expires_in;
            echo json_encode($rsp);
            exit;
        }else{
            $rsp->errcode = 0;
            $rsp->errmsg = "fail";
            echo json_encode($rsp);
            exit;
        }

    }



} 
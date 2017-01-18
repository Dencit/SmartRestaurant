<?php

namespace Apis;

class weicoApi{
    /*
     * 获取access_token,每日限额2000
     */

    //参考//https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=id&corpsecret=secrect
    public function access_token(){
        $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=".corpId."&corpsecret=".corpSecret;
        $data = $this->http_get($url);
        return $data;
    }


    /*
     *获取jsapi_ticket,
     */

    //参考//https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token=ACCESS_TOKE
    public function jsapi_ticket($access_token = ""){
        $url = "https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token=$access_token&type=jsapi";
        $data = $this->http_get($url);
        return $data;
    }

    /*
     * 卡券ticket
     */

    //参考//https://qyapi.weixin.qq.com/cgi-bin/ticket/get?access_token=ACCESS_TOKEN&type=wx_card
    public function api_ticket($access_token = ""){
        $url = "https://qyapi.weixin.qq.com/cgi-bin/ticket/get?access_token=$access_token&type=wx_card";
        $data = $this->http_get($url);
        return $data;
    }


    //----------------------


    /*
     * pc扫码授权登录
     *
     * */

    public function get_auth_code($redirect_uri='',$user_type='all',$state = 'state'){

        $redirect_uri=urlencode($redirect_uri."&state=".$state);
        $uri="https://qy.weixin.qq.com/cgi-bin/loginpage?corp_id=".corpId."&redirect_uri=".$redirect_uri."&state=".$state."&usertype=".$user_type;

        echo "<script> location.replace('".$uri."'); </script>";
    }

    /*
     *
     * pc端获取用户详细信息
     *
     * */




    /*
     * 网页授权获取code
     */

    //参考//https://open.weixin.qq.com/connect/oauth2/authorize?appid=CORPID&redirect_uri=REDIRECT_URI&response_type=code&scope=SCOPE&state=STATE#wechat_redirect
    public function get_code($scope = 'snsapi_base',$state = 'state',$redirect_uri = ''){
        $uri = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".corpId."&redirect_uri=$redirect_uri&response_type=code&scope=$scope&state=$state#wechat_redirect";
        header("location:$uri");
        exit;
    }


    //{"auth_code":"xxxxx"}
/*    public function get_userInfo($access_token='',$post_array_data=''){
        $url = "https://qyapi.weixin.qq.com/cgi-bin/service/get_login_info?access_token=".$access_token;
        //var_dump($post_json_data);exit;//
        $data = $this->http_post($url,$post_array_data );
        return json_decode($data);
    }*/


    /*
     * 获取 UserId,DeviceId
     */
    //参考//https://qyapi.weixin.qq.com/cgi-bin/user/getuserinfo?access_token=ACCESS_TOKEN&code=CODE
    public function get_usersign_co($access_token='',$code = ''){
        $url = "https://qyapi.weixin.qq.com/cgi-bin/user/getuserinfo?corpSecret=".corpSecret."&access_token=".$access_token."&code=".$code;
        $data = $this->http_get($url);
        return json_decode($data);
    }


    /*
     * userid 转 openid
     */
    //参考//https://qyapi.weixin.qq.com/cgi-bin/user/convert_to_openid?access_token=ACCESS_TOKEN
    //$post_array_data {"userid": "zhangsan","agentid": 1}

    public function convert_to_openid($access_token='',$post_array_data=''){
        $url = "https://qyapi.weixin.qq.com/cgi-bin/user/convert_to_openid?access_token=".$access_token;
        //var_dump($post_json_data);exit;//
        $data = $this->http_post($url,$post_array_data );
        return json_decode($data);
    }


    /*
     *  openid 转 userid
     */
    //参考//https://qyapi.weixin.qq.com/cgi-bin/user/convert_to_userid?access_token=ACCESS_TOKEN
    //$post_array_data {"openid": "oDOGms-6yCnGrRovBj2yHij5JL6E"}

    public function convert_to_userid($access_token='',$post_array_data=''){
        $url = "https://qyapi.weixin.qq.com/cgi-bin/user/convert_to_userid?access_token=".$access_token;
        //var_dump($post_json_data);exit;//
        $data = $this->http_post($url,$post_array_data );
        return json_decode($data);
    }



//http curl get请求函数
    static function http_get($url){
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
<?php
/* Created by User: soma Worker: 陈鸿扬 Date: 2016/6/2 Time: 11:21 */
namespace Https;
use Commons\tool;
use Https\authcoApi;

class weicoApi extends authcoApi
{
    function __construct(){
        new parent;

    }

    // userid 转 openid

    //参考//https://qyapi.weixin.qq.com/cgi-bin/user/convert_to_openid?access_token=ACCESS_TOKEN
    //$post_array_data {"userid": "zhangsan","agentid": 1}

    static function convert_to_openid($access_token='',$post_array_data=''){
        $url = "https://qyapi.weixin.qq.com/cgi-bin/user/convert_to_openid?access_token=".$access_token;
        //var_dump($post_json_data);exit;//
        $data = self::http_post($url,$post_array_data );
        return json_decode($data);

        /*
         * object(stdClass)#16 (4) {
              ["errcode"]=>
              int(0)
              ["errmsg"]=>
              string(2) "ok"
              ["openid"]=>
              string(28) "ojex4s3Ujf3FvtMCR5A7mLE9Gv-0"
              ["appid"]=>
              string(18) "wxca53ddb3e1b51804"
            }
         *
         */
    }


    //openid 转 userid

    //参考//https://qyapi.weixin.qq.com/cgi-bin/user/convert_to_userid?access_token=ACCESS_TOKEN
    //$post_array_data {"openid": "oDOGms-6yCnGrRovBj2yHij5JL6E"}

    static function convert_to_userid($access_token='',$post_array_data=''){
        $url = "https://qyapi.weixin.qq.com/cgi-bin/user/convert_to_userid?access_token=".$access_token;
        //var_dump($post_json_data);exit;//
        $data = self::http_post($url,$post_array_data );
        return json_decode($data);

        /*
         * object(stdClass)#17 (3) {
              ["errcode"]=>
              int(0)
              ["errmsg"]=>
              string(2) "ok"
              ["userid"]=>
              string(3) "002"
            }
         * */
    }


////组装用户信息//基本信息//PC端
    static function userInfo($usrCodeInfo,$user2openid){

        $usertype=$usrCodeInfo->usertype;
        $user_info=$usrCodeInfo->user_info;
        $corpid=$usrCodeInfo->corp_info->corpid;

        if(isset($user_info->userid) && isset($user2openid->openid) )
        {
            $info['usertype']=$usertype;
            $info['userid']=$user_info->userid;
            $info['name']=$user_info->name;
            $info['headimgurl']=$user_info->avatar;
            $info['corpid']=$corpid;

            $info['openid']=$user2openid->openid;
            if( isset($user2openid->appid) ){
                $info['appid']=$user2openid->appid;
            }

            $info['acctype']='1';
            $info['time']=time();
            $info['ip']=tool::ip();

            return $info;

        }else{
            exit("userInfo fail");
        }

    }
////\\

////组装用户信息//基本信息//移动端

    //https://qyapi.weixin.qq.com/cgi-bin/user/get?access_token=ACCESS_TOKEN&userid=USERID
    //获取用户详细信息
    static function usrInfoAddGet($userid = '',$access_token = ''){
        $url="https://qyapi.weixin.qq.com/cgi-bin/user/get?access_token=".$access_token."&userid=".$userid;
        $data = self::http($url);
        if ($data){
            $data = json_decode($data);
        }
        return $data;
    }

    //组装用户信息//基本信息
    static function usrInfoBase($usrArray,$usrInfoAddGet){

        $usrArray['name']=$usrInfoAddGet->name;
        $usrArray['headimgurl']=$usrInfoAddGet->avatar;

        if( empty($usrArray['userid']) ) { $usrArray['acctype'] = 0; }
        else { $usrArray['acctype'] = 1; }
        $usrArray['time'] = time();
        $ipGet = tool::get_ip();
        $usrArray['ip'] = $ipGet;

        return $usrArray;
    }

    //https://qyapi.weixin.qq.com/cgi-bin/department/list?access_token=ACCESS_TOKEN&id=ID
    static  function departGet($id='',$access_token=''){
        $url="https://qyapi.weixin.qq.com/cgi-bin/department/list?access_token=".$access_token."&id=".$id;
        $data = self::http($url);
        if ($data){
            $data = json_decode($data);
        }
        return $data;
    }

    //组装用户信息//详细信息
    static function usrInfoAdd($usrInfoAddGet,$departGet=''){

        $infoData['name']=$usrInfoAddGet->name;
        $infoData['sex']=$usrInfoAddGet->gender;
        $infoData['mobile']=$usrInfoAddGet->mobile;
        $infoData['photo']=$usrInfoAddGet->avatar;
        $infoData['status']=$usrInfoAddGet->status;

        $infoData['weixinid']=tool::isSetRe($usrInfoAddGet->weixinid);

        $index=count($usrInfoAddGet->department)-1;
        $depart=$usrInfoAddGet->department[$index];


        if($departGet!=''){
            $department=$departGet->department;
            foreach($department as $n=>$v){
                if($v->id==$depart){
                    $infoData['depart']=$v->name;
                }

            }
        }


        foreach( $usrInfoAddGet->extattr->attrs as $n=>$v){
            switch($usrInfoAddGet->extattr->attrs[$n]->name){
                case '编制':
                    $value=$usrInfoAddGet->extattr->attrs[$n]->value;
                    switch($value){
                        default:$infoData['join']='0';break;
                        case'非在编':$infoData['join']='0';break;
                        case'在编':$infoData['join']='1';break;
                    }
                    break;
                case '身份证':
                    $infoData['idcard']=$usrInfoAddGet->extattr->attrs[$n]->value;
                    break;
                case '身份':
                    $value=$usrInfoAddGet->extattr->attrs[$n]->value;
                    switch($value){
                        default:$infoData['ad_type']='1';break;
                        case'管理':$infoData['ad_type']='a';break;
                        case'普通':$infoData['ad_type']='1';break;
                        case'收银':$infoData['ad_type']='2';break;
                        case'编辑':$infoData['ad_type']='3';break;
                        case'财务':$infoData['ad_type']='4';break;
                    }
                    break;
            }
        }

        return $infoData;


    }


    //https://qyapi.weixin.qq.com/cgi-bin/user/update?access_token=ACCESS_TOKEN
    //更新成员信息
    static function usrInfoUpdate($post_array_data,$access_token = ''){
        $url = "https://qyapi.weixin.qq.com/cgi-bin/user/update?access_token=".$access_token;
        //var_dump($post_array_data);exit;//
        $data = self::http_post($url,$post_array_data );
        return json_decode($data);
    }
    //组装成员信息//详细信息
    static function usrInfoUpdateBase($infoUp){
        //var_dump($access_token);exit;//
        $post_array_data=new \stdClass();
        $post_array_data->userid=$infoUp['userid'];
        $post_array_data->name=urlencode($infoUp['name']);
        //$post_array_data->department=array();
        //$post_array_data->department[0]=$infoUp['depart'];
        $post_array_data->mobile=$infoUp['mobile'];
        $post_array_data->gender=$infoUp['sex'];
        $post_array_data->extattr=new \stdClass();
        $post_array_data->extattr->attrs=array();
        $post_array_data->extattr->attrs[0]=new \stdClass();
        $post_array_data->extattr->attrs[0]->name=urlencode('身份证');
        $post_array_data->extattr->attrs[0]->value=$infoUp['idcard'];
        $post_array_data->extattr->attrs[1]=new \stdClass();
        $post_array_data->extattr->attrs[1]->name=urlencode('身份');
        $post_array_data->extattr->attrs[1]->value=urlencode($infoUp['ad_type']);
        $post_array_data->extattr->attrs[2]=new \stdClass();
        $post_array_data->extattr->attrs[2]->name=urlencode('编制');
        $post_array_data->extattr->attrs[2]->value=urlencode($infoUp['join']);
        return $post_array_data;
    }


    /*static function qrCode($access_token,$scene_id){
        $url="https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=".$access_token;

        $post_array_data=array(
            "expire_seconds"=>'1800',
            "action_name"=>"QR_SCENE",
            "action_info"=>
                array(
                "scene"=>
                    array(
                    "scene_id"=>$scene_id
                )
            )
        );

        $post_array_data=json_encode($post_array_data);

        //var_dump($post_array_data);exit;//

        $post=self::http_post($url,$post_array_data);

        var_dump($post);exit;

        return $post;
    }*/

    //https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token=ACCESS_TOKEN
    static function sendMsg($post_array_data='',$access_token = ''){

        $url = "https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token=".$access_token;
        //var_dump($post_json_data);exit;//
        $data = self::http_post($url,$post_array_data );
        return json_decode($data);

    }

    //------------------------------------



    //用户在关注了公众号之后获取其nickname、headimgurl等信息
    static function subscribe($openid = '',$globeAccessToken=""){
        //return $globl_access_token;
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$globeAccessToken."&openid=".$openid."&lang=zh_CN";
        $data = self::http($url);
        if($data){
            $data = json_decode($data);
        }
        return $data;
    }


    //获限图片文件并保存在服务器
    static function getMedia($media_id = "",$openid = "",$globeAccessToken=""){
        if(!$openid){ exit('!$openid'); }
        if(!$media_id){ exit('!$media_id'); }
        if(!$globeAccessToken ){ exit('!$globeAccessToken'); }

        $url = "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token=$globeAccessToken&media_id=$media_id";
        $data = self::http($url);

        //exit($data);

        if($data){

            if(json_decode($data)){

                exit('fail');

            }else{

                $filename = $openid.'_'.date("YmdHis",TIME).".jpg";
                $fpath = PUBLIC_FILE."/Photos/".$filename;
                $rs = @file_put_contents($fpath,$data);

                if($rs && @chmod($fpath,0660)){
                    return ($filename);
                }

            }
        }
        return $data;
    }



}
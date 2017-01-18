<?php
/* Created by User: soma Worker: 陈鸿扬 Date: 16/7/28  Time: 09:37 */

namespace Controlers;

use \Commons\tool as tool;//工具类
use \Commons\jump as jump;

use \Modelers\model as model;//model装载器
use \Https\weiApi as weiApi;

use Commons\probability as probability;//概率工具组


class index extends baseControler {

    private $uid;


    function __construct(){
        //初始化
        new parent;

        //判断有没有 session uid
        $this->uid = isset($_SESSION[PREFIX.'uid']) ? trim($_SESSION[PREFIX.'uid']) : '';
        //exit($this->uid);//

        if($this->uid ==''){
            $jsonPost['checkUid']='noUid';
            tool::jsonExit($jsonPost);
        }

        model::init('index');//一个 controler 对应一个 同名 modeler
        //model::set();//加载 index modeler 可同时传参给 构造函数

    }





    //ajax 部分

    function test(){

        //短信调用
        $aliDayu='\Thirds\aliDayu';//命名空间引用
        $code=rand(100000,999999);
        $aliDayu::SmsInit("邦聚网络","{code:'".$code."',product:'邦聚网络'}","SMS_12355379");//短信模板初始化//一次通用
        $aliDayu::SmsNumSend('18588891945','');//目标号码//第二个参数设置“1”才发送，默认只打印参数

    }

    function welcome(){

        $uid=$this->uid;

        $MS=model::set();
        $MS->indexModel($uid);

        //在构造函数 初始化时 执行批量查询，结果保存为静态变量
        echo '[批量查询]'
            .'<br/>'.$MS::$uid
            .'<br/>'.$MS::$nickname
            .'<br/>'.$MS::$headimgurl
        ;
        //
        $whereArray['uid']='4';
        $user=$MS->rowSelect(USR,'*',$whereArray);
        var_dump($user);
        //
        $I=self::$serial->get();
        $type_get=$I->name;//
        var_dump( $type_get.' say :: hello world!' );
        //

    }

    function usrState(){

        $uid=$this->uid;

        $MS=model::set();
        $MS->indexModel($uid);

        if($MS::$reced !='0'){
            $jsonPost['indexReturn']='reced';
            $jsonPost['reced']='1';
            $jsonPost['uid']=$MS::$uid;
            $jsonPost['nick']=$MS::$nickname;
            $jsonPost['head']=$MS::$headimgurl;
            tool::jsonExit($jsonPost);
        }

        $jsonPost['indexReturn']='ok';
        $jsonPost['reced']='0';
        $jsonPost['uid']=$MS::$uid;
        $jsonPost['nick']=$MS::$nickname;
        $jsonPost['head']=$MS::$headimgurl;
        tool::jsonExit($jsonPost);

    }



    function usrLottery(){

        $uid=$this->uid;

        //$probability=new probability();

        $MS=model::set();
        $MS->indexModel($uid);

            $rnd=rand(1,1000);
            $fileGet=probability::fileGetVal(CACHE.'/probability.php');
            $setInt=probability::setInterval($fileGet);
            //print_r($setInt);
            $item=probability::getSign($rnd,$setInt);
            //print_r($item);exit;


            if($MS::$gift!=0 ){
                $jsonPost['index2Return']='haveGift';
                $jsonPost['rnd']=$rnd;
                $jsonPost['interval']=$item['inv'];
                $jsonPost['item']=$item['s'];
                tool::jsonExit($jsonPost);
            }

            $gift_count=$MS::$wpDb->get_row($MS::$wpDb->prepare("select * from ".GIFT_COUNT." WHERE gid=%s ",$item['s']));

            if($gift_count){
                $g_count=$gift_count->count;
            }else{
                exit('$gift_count bug');
            }

            if($g_count==0&&$item!='1'){

                $jsonPost['index2Return']='endGift';
                $jsonPost['rnd']=$rnd;
                $jsonPost['interval']=$item['inv'];
                $jsonPost['item']=$item['s'];
                tool::jsonExit($jsonPost);
            }

            $jsonPost['index2Return']='emptyGift';
            $jsonPost['rnd']=$rnd;
            $jsonPost['interval']=$item['inv'];
            $jsonPost['item']=$item['s'];
            tool::jsonExit($jsonPost);

    }



    function shareState(){

        $uid=$this->uid;
        $sid=isset($_POST['sid'])?$_POST['sid']:'';

        if($sid==''){
            $jsonPost['checkSid']='noSid';
            tool::jsonExit($jsonPost);
        }

        $MS=model::set();
        $MS->shareModel($uid);

        $jsonPost['shareReturn']='shareStatuOk';
        $jsonPost['uid']=$MS::$uid;
        $jsonPost['nick']=strip_tags($MS::$nickname);
        $jsonPost['head']=$MS::$headimgurl;
        $jsonPost['mobile']=$MS::$mobile;
        tool::jsonExit($jsonPost);


    }



} 
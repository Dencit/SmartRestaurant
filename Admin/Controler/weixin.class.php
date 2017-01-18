<?php
/* Created by User:soma Worker:陈鸿扬 Date: 16/8/15  Time: 09:18 */

namespace Controlers;

use Commons\date;
use \Commons\tool as tool;
use \Commons\jump as jump;

use \Controlers\urlSerial as I;
use \Modelers\model as model;//model装载器
use \Views\view as V;
use \Commons\forPage as FP;

use \Https\weicoApi as weicoApi;//微信功能接口
use \Https\authcoApi as authcoApi;//微信授权接口

class weixin extends baseControler {

    private static $adminDesc;//parent object

    private static $MS;

    private static $page;//数据库翻页
    private static $step;//翻页步进值
    private static $start;//翻页起始值

    function __construct(){

        //用户登录状态
        self::$adminDesc=$this->adminDesc();
        //\\

        self::$MS=model::init('weixin');

        ////获取翻页值
        $page=I::have('p')-1;$page=$page>0?$page:'0';
        $step=10;
        $start=$step*$page;//$start=$start>0?$start:$step;

        self::$page=$page; self::$step=$step; self::$start=$start;
        //\\

    }

    function pushNews(){

        V::tamplate('index');
        $cArr['content']='change_pushNews';
        V::asChangeArr($cArr);

        $select='food_id,image,name,type,type_name,price,stock,sales,allow,time,time_type';
        $pushNews=self::$MS->pushSelect(FOOD,$select,'allow/1','time desc limit '.self::$start.','.self::$step);
        //var_dump($USR_INFO);exit;

        if(!$pushNews){$pushNews=array();$noneTip='没有了!';}
        else{$noneTip='';}

        V::forList('pushNews',$pushNews);
        V::asSign('noneTip',$noneTip);

        ////翻页栏
        $countList=count( self::$MS->resultSelect(FOOD,'food_id','allow/1') );
        FP::getPage($countList,I::have('p'),self::$step);
        v::asSign('countList',FP::$countList);
        V::asSign('pages',FP::$pages);
        V::asSign('cur_page',FP::$cur_page);
        V::asSign('previous',FP::$previous);
        V::asSign('next',FP::$next);
        V::forList('plist',FP::$plist);
        //\\

        V::show();

    }


    function pushNews_ax(){

        //var_dump($_POST);


        $date=tool::isSetRe( $_POST['date'] );
        $aop=tool::isSetRe( $_POST['type'] );
        $picurl='';$aop_type='';
        if($aop==1){ $aop_type="早餐"; $picurl=urlencode(HTTP_BASE."/Static/images/breakfast.jpg"); }
        if($aop==2){ $aop_type="午餐"; $picurl=urlencode(HTTP_BASE."/Static/images/lunch.jpg"); }

        $title=urlencode( '预定 '.$date.' '.date::weekday( strtotime($date) )." 的".$aop_type );

        $description=tool::isSetRe( $_POST['desc'] );
        $description=urlencode( $description );

        //
        $url=HTTP_BASE."/Home/?/info/pushnews/date-".$date."/aop-".$aop;

        $access_token=weicoApi::globeAccessToken();

        $post_data=new \stdClass();
        $post_data->touser='@all';
        $post_data->msgtype='news';
        $post_data->agentid=AGENT_ID;
        $post_data->news=new \stdClass();
        $post_data->news->articles=[];
        $post_data->news->articles[0]['title']=$title;
        $post_data->news->articles[0]['description']=$description;
        $post_data->news->articles[0]['url']=$url;
        $post_data->news->articles[0]['picurl']=$picurl;
        $post_data->safe='0';

        //var_dump( urldecode(json_encode( $post_data )) ); exit;//

        $result=weicoApi::sendMsg($post_data,$access_token);
        //var_dump($result);//

        if($result->errmsg=='ok'){
            tool::jsonResult('','0','推送成功！');
        }

    }

    function pushNews_down_ax(){

        $food_id=tool::is_Post('rowId');
        //var_dump($food_id);//

        if(!empty($food_id)){

            $rowUpdate=self::$MS->rowUpdate(FOOD,'allow/0,time/'.time(),'food_id/'.$food_id);
            if($rowUpdate){

                tool::jsonResult(array('food_id'=>$food_id),'0','下架成功！');

            }

        }

    }


    function keyWords(){

        V::tamplate('index');

        $cArr['content']='change_keyWords';
        V::asChangeArr($cArr);
        $lArr[0]=array('style'=>'','id'=>'1','scene'=>'1','reply'=>'1','scene_type'=>'被添加自动回复','reply_type'=>'文本','link'=>'https://www.baidu.com');
        $lArr[1]=array('style'=>'','id'=>'2','scene'=>'2','reply'=>'1','scene_type'=>'消息自动回复','reply_type'=>'文本','link'=>'https://www.baidu.com');
        $lArr[2]=array('style'=>'','id'=>'3','scene'=>'3','reply'=>'1','scene_type'=>'关键词自动回复','reply_type'=>'文本','link'=>'https://www.baidu.com');
        $lArr[3]=array('style'=>'','id'=>'4','scene'=>'3','reply'=>'2','scene_type'=>'关键词自动回复','reply_type'=>'图片','link'=>'https://www.baidu.com');
        $lArr[4]=array('style'=>'','id'=>'5','scene'=>'3','reply'=>'3','scene_type'=>'关键词自动回复','reply_type'=>'音频','link'=>'https://www.baidu.com');
        $lArr[5]=array('style'=>'','id'=>'6','scene'=>'3','reply'=>'4','scene_type'=>'关键词自动回复','reply_type'=>'视频','link'=>'https://www.baidu.com');
        $lArr[6]=array('style'=>'','id'=>'7','scene'=>'3','reply'=>'5','scene_type'=>'关键词自动回复','reply_type'=>'图文','link'=>'https://www.baidu.com');

        V::forList('keyWords',$lArr);

        V::show();

    }

    function menu(){

        V::tamplate('index');

        $cArr['content']='change_menu';
        V::asChangeArr($cArr);

        $lArr[0]=array('style'=>'info','id'=>'1','type'=>'主导航','menu'=>'导航1','link'=>'https://www.baidu.com');
        $lArr[1]=array('style'=>'','id'=>'2','type'=>'子导航','menu'=>'菜单1','link'=>'https://www.baidu.com');
        $lArr[2]=array('style'=>'info','id'=>'3','type'=>'主导航','menu'=>'导航2','link'=>'https://www.baidu.com');
        $lArr[3]=array('style'=>'','id'=>'4','type'=>'子导航','menu'=>'菜单2','link'=>'https://www.baidu.com');
        $lArr[4]=array('style'=>'info','id'=>'5','type'=>'主导航','menu'=>'导航3','link'=>'https://www.baidu.com');
        $lArr[5]=array('style'=>'','id'=>'6','type'=>'子导航','menu'=>'菜单3','link'=>'https://www.baidu.com');


        V::forList('menu',$lArr);

        V::show();

    }





} 
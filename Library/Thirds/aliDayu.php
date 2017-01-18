<?php
/* Created by User: soma Worker: 陈鸿扬 Date: 16/8/11  Time: 16:48 */

namespace Thirds;

require_once('aliyunSdk/TopSdk.php');
use TopClient;
use AlibabaAliqinFcSmsNumSendRequest;


class aliDayu{

    private static $appkey;
    private static $secret;

    private static $SmsType;
    private static $SmsFreeSignName;
    private static $setSmsParam;
    private static $SmsTemplateCode;

    function  __construct(){

    }

    static function SmsInit(
        $Sign="邦聚网络",
        $Param="{code:'999999',product:''}",
        $TemplateCode="SMS_12355379"
    ){
        self::$appkey='[APP_KEY]';
        self::$secret='[SECRET]';

        self::$SmsType='normal';

        self::$SmsFreeSignName=$Sign;
        self::$setSmsParam=$Param;
        self::$SmsTemplateCode=$TemplateCode;

    }


    static function SmsNumSend($RecNum="18588891945",$send=''){

        $c = new TopClient;
        $c ->appkey = self::$appkey ;
        $c ->secretKey = self::$secret ;

        $req = new AlibabaAliqinFcSmsNumSendRequest;
        $req ->setExtend('');
        $req ->setSmsType( self::$SmsType );
        $req ->setSmsFreeSignName( self::$SmsFreeSignName );
        $req ->setSmsParam( self::$setSmsParam );
        $req ->setRecNum( $RecNum );
        $req ->setSmsTemplateCode( self::$SmsTemplateCode );

        if($send=='1'){
            //exit('1');
            $c ->execute( $req );
        }else{
            print_r( $req );
        }


    }

} 
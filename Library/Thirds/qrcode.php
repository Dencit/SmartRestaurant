<?php
/* Created by User: soma Worker:陈鸿扬  Date: 16/10/24  Time: 17:04 */

//require_once('../../Common/app.php');

require_once( "./phpqrcode/phpqrcode.php" ) ;//二维码类

$order_no=isset($_GET['order_no'])?$_GET['order_no']:'';

if($order_no!=''){

    QRcode::png($order_no,false,QR_ECLEVEL_H,13,1,false);

}else{
    exit;
}



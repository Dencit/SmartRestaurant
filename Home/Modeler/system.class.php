<?php
/* Created by User: soma Worker:陈鸿扬  Date: 16/11/9  Time: 14:41 */

namespace Modelers;
use Modelers\baseModel;

class system extends baseModel {

    function __construct(){
        new parent;//可以使用baseModel所有查询方法,包括wpdb的;

    }

} 
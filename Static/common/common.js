$indexUrl='../Action/index.php';
$index2Url='../Action/index2.php';
$shareUrl='../Action/share.php';

//函数调试
$iDebug={'ale':'false', 'con':'true'};
function iAlert(data){
    if($iDebug.ale=='true'){
        alert("iAlert[   "+data+"   ]");
    }
    if($iDebug.con=='true'){
        console.log("iConsole[   "+data+"   ]");
    }
}
//对象调试
$oDebug={'objCon':'true'};
function iObjCon(objRes){
    if($oDebug.objCon=='true'){
        console.log(objRes);
    }
}
//页面调试
$pDebug={'ale':'false', 'con':'true'};
function pAlert(data){
    if($pDebug.ale=='true'){
        alert("pAlert[   "+data+"   ]");
    }
    if($pDebug.con=='true'){
        console.log("pConsole[   "+data+"   ]");
    }
}

//json text to json
function jsonGet(data){
    var $jsonObj=eval("("+data+")");
    return $jsonObj;
}

//静态页获取序列值[?]
function request(paras){
    var url = location.href;
    var paraString = url.substring(url.indexOf("?")+1,url.length).split("&");
    var paraObj = {};
    for (i=0; j=paraString[i]; i++){
        paraObj[j.substring(0,j.indexOf("=")).toLowerCase()] = j.substring(j.indexOf("=")+1,j.length);
    }
    var returnValue = paraObj[paras.toLowerCase()];
    if(typeof(returnValue)=="undefined"){
        return "";
    }else{
        return returnValue;
    }
}

//静态页获取序列值[#]
function htmReq(paras){
    var url = location.href;
    var paraString = url.substring(url.indexOf("#")+1,url.length).split("&");
    var paraObj = {};
    for (i=0; j=paraString[i]; i++){
        paraObj[j.substring(0,j.indexOf("=")).toLowerCase()] = j.substring(j.indexOf("=")+1,j.length);
    }
    var returnValue = paraObj[paras.toLowerCase()];
    if(typeof(returnValue)=="undefined"){
        return "";
    }else{
        return returnValue;
    }
}

//静态页获取序列值[#]
function find(str,cha,num){
    var x=str.indexOf(cha);
    for(var i=0;i<num;i++){
        x=str.indexOf(cha,x+i);
    }
    return x;
}
function pathReq(paras){
    var url = location.href;

    //console.log( find(url,'/',6) );//
    var paraString = url.substring(find(url,'/',6)+1,url.length).split("/");
    //console.log(paraString);//

    var paraObj = {};
    for (i=0; j=paraString[i]; i++){
        paraObj[j.substring(0,j.indexOf("-")).toLowerCase()] =decodeURI( j.substring(j.indexOf("-")+1,j.length) );
    }
    //console.log(paraObj);//

    var returnValue = paraObj[paras.toLowerCase()];
    if(typeof(returnValue)=="undefined"){
        return "";
    }else{
        return returnValue;
    }
}

function uri_get($sign,$start,$url){
    if($sign==undefined){$sign='?'}
    if($start==undefined){$start='1'}

    var url='';
    if($url==undefined){  url= location.href; }
    else{ url=$url }

    //console.log( url );
    //console.log( find(url,$sign,$start) );//地址栏,符合(斜杠),第几个斜杠
    var paraString = url.substring(find(url,$sign,$start)+1,url.length).split($sign);
    //console.log(paraString);//
    return paraString;
}

//获取日期：前天、昨天、今天、明天、后天
function GetDateStr(AddDayCount) {
    var dd = new Date();
    dd.setDate(dd.getDate()+AddDayCount);//获取AddDayCount天后的日期
    var y = dd.getFullYear();
    var m = dd.getMonth()+1;//获取当前月份的日期
    var d = dd.getDate();
    return y+"-"+m+"-"+d;
}

//去掉所有html标记
function delHtmlTag(str){
    return str.replace(/<[^>]+>/g,"");//去掉所有的html标记
}

//随机值
function random(n, m){
    return Math.floor(Math.random()*(m-n+1)+n);
}


//验证集合
$name_Reg=/^[\u4e00-\u9fa5|a-zA-Z]*$/;
$mobile_Reg =/^1[3|4|5|7|8]\d{9}$/;
$ctMobile_Reg = /^(133|153|177|180|181|189)\d{8}$/;
$vCode_Reg = /^[0-9]{6}$/;

$phone_Reg=/^(020)?[0-9]{7,8}$/;

$account_Reg=/^(020)?[0-9]{7,8}$/;

$city_Reg=/^[\u4e00-\u9fa5|a-zA-Z]*$/;
$sex_Reg=/^(男|女)$/;


$inputReg={
    "nameReg":function(data){
        if(data==''||$name_Reg.test(data)===false){
            alert("请输入中文或英文姓名！");
            return false;
        }
    },
    "mobileReg":function(data){
        if(data==''||$mobile_Reg.test(data)===false){
            alert("请输入手机号码！");
            return false;
        }
    },
    "ctMobileReg":function(data){
        if(data==''||$ctMobile_Reg.test(data)===false){
            alert("请输入广州电信手机号码！");
            return false;
        }
    },
    "vCodeReg":function(data){
        if(data==''||$vCode_Reg.test(data)===false){
            alert("请输入六位数验证码！");
            return false;
        }
    },
    "phoneReg":function(data){
        if(data==''||$phone_Reg.test(data)===false){
            alert("请输入广州本地固话号码！");
            return false;
        }
    },
    "accountReg":function(data){
        if(data==''){
            alert("请输入广州本地宽带号码！");
            return false;
        }
    }


};

//cookie

function getCookie(c_name)
{
    if (document.cookie.length>0)
    {
        c_start=document.cookie.indexOf(c_name + "=");
        if (c_start!=-1)
        {
            c_start=c_start + c_name.length+1;
            c_end=document.cookie.indexOf(";",c_start);
            if (c_end==-1) c_end=document.cookie.length;
            return unescape(document.cookie.substring(c_start,c_end))
        }
    }
    return ""
}

function setCookie(c_name,value,expiredays)
{
    var exdate=new Date();
    exdate.setDate(exdate.getDate()+expiredays);
    document.cookie=c_name+ "=" +escape(value)+
    ((expiredays==null) ? "" : ";expires="+exdate.toGMTString())
}

function checkCookie()
{
    username=getCookie('username');
    if (username!=null && username!="")
    {alert('Welcome again '+username+'!')}
    else
    {
        username=prompt('Please enter your name:',"");
        if (username!=null && username!="")
        {
            setCookie('username',username,365)
        }
    }
}

//check array

function equal_arr(arr1,arr2){

    for(var i = 0; i < arr2.length; i++){
        var tag = false;
        for(var j = 0; j < arr1.length; j++){
            if(arr2[i] === arr1[j]){
                tag = true;
            }else{
                tag = false;
                break;
            }
        }
        return tag;
    }

}

//modal alert 自定义模态框

function modalbox(msg,style){

    if(style==undefined){style=0;}

    console.log(style);

    if(style==0){
        alert(msg);
    }
    if(style==1||style=='bootstrap'){

        //必须先有 glob_modal <div>
        $('#glob_modal').html(
            '<div id="g_modal" style="top:20%;" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true"> ' +
            '<div class="modal-dialog modal-sm">' +
            '<div class="modal-content">' +
            '<div class="modal-header">' +
            '<button type="button" class="close" data-dismiss="modal">' +
            '<span aria-hidden="true">&times;</span>' +
            '<span class="sr-only">Close</span></button> ' +
            '<h4 class="modal-title" id="modalLabel">提示</h4> ' +
            '</div> ' +
            '<div class="modal-body">...</div> ' +
            '<div class="modal-footer"> ' +
            '<button type="button" class="btn btn-primary" data-dismiss="modal">知道了</button> ' +
            '</div>' +
            '</div>' +
            '</div>' +
            '</div>'
        );

        $('.modal-body').text(msg);
        $('#g_modal').modal('toggle');

    }


}


//模态窗
/*
* tipModal.set('.modal_bg','hello!');
* tipModal.toggle();
*
* */

var modalTip=function(){

};
modalTip.prototype={
    selector:'.modal_bg',
    init:function(){
        var html='<div class="modal" >'+
            '<p class="modal_content">提示内容</p>'+
            '<p class="modal_ok">知道了</p>'+
            '</div>';

        var div = document.createElement("div");
        div.setAttribute("class", "modal_bg");
        div.innerHTML=html;

        document.body.appendChild(div);
    },
    set:function(content,redirect){

        $(this.selector+' .modal_content').text(content);

        if(redirect!=''){this.redirectto=redirect}

        var This=this;
        $('.modal_ok').bind('click',function(){
            This.hide();
            if(This.redirectto){
                setTimeout(function(){
                    window.location.replace(This.redirectto);
                },200);
            }
        });
    },
    show:function(){
        $(this.selector).fadeIn();
    },
    hide:function(){
        $(this.selector).fadeOut();
    },
    toggle:function(timeout){
        var sec=2000;
        if(timeout!=undefined){ sec=timeout; }
        this.show();

        var This=this;
        setTimeout(function(){
            This.hide();
        },sec);

        if(this.redirectto) {
            setTimeout(function(){
                window.location.replace(This.redirectto);
            },sec+320);
        }
    }
};

tipModal=new modalTip();



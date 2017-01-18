/* Created by User: soma Worker: 陈鸿扬 on 16/8/13 */

//incule: common.js

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

//json text to json
function jsonGet(data){
    var $jsonObj=eval("("+data+")");
    return $jsonObj;
}

var ajax=function(){};
ajax.prototype={
    //start
    /**
     @param {String} url
     @param {object} inputData
     @param {Function} func
     @param {int} async
     @return {number}
     */
    //纯json post
    post:function(url,inputData,func,async){

        if(async==''){
            $.ajaxSetup({async : true});//默认异步型
        }else{
            $.ajaxSetup({async : false});//阻断型
        }

        $.ajax({
            url: url,
            type: 'POST',
            data: inputData,
            dataType: 'html',
            cache:false,
            success: function (result) {

                iAlert("返回HTML数据::↓↓::postBackHtmlData");//正确时 输出html内容//错误时 直接输出后端报错信息
                iAlert(result);

                var res = jsonGet(result);//转为Object对象//json
                iAlert("返回JSON数据::↓↓::getBackJsonData");
                iObjCon(res);

                func(res);

            },
            error:function(msg){

                iAlert("getBackResult::↓↓");
                iObjCon(msg.toSource());//

            }
        });

    },

    getObjectURL:function(file) {
        var url = null ;
        if (window.createObjectURL!=undefined) { // basic
            url = window.createObjectURL(file) ;
        } else if (window.URL!=undefined) { // mozilla(firefox)
            url = window.URL.createObjectURL(file) ;
        } else if (window.webkitURL!=undefined) { // webkit or chrome
            url = window.webkitURL.createObjectURL(file) ;
        }
        return url ;
    },

    imgUpload:function($selector,$num,$function){

        $file=$($selector).val(); //console.log($file);//
        if($file==''){ alert('请选择文件！'); return false; }

        if (!(window.File || window.FileReader || window.FileList || window.Blob)) { alert('该浏览器不支持文件上传！');}

        $file_list=$($selector).prop('files');//console.log( $file_list );//
        $file_desc=$file_list[0]; //console.log( $file_desc );//

        if(!/\.(?:jpg|png|gif|bmp)$/.test($file_desc.name)){ alert('请上传jpg、png、bmp、gif格式的文件！'); return false;}

        var $M=2*131072; if($num!=''){ $M=$num*131072; } //console.log($M);return false;//
        if($file_desc.size>$M){ alert('上传文件不能大于'+$num+'m ！'); return false; }

        if( $file_desc.type.indexOf('image')=='0' && /\.(?:jpg|png|gif|bmp)$/.test($file_desc.name) ){

            var $reader = new FileReader();//新建一个FileReader
            $reader.readAsDataURL($file_list[0]);//读取文件
            $reader.onload = function($e){ //读取完文件之后会回来这里
                var $fileString = $e.target.result; //console.log($fileString);//

                $function($fileString);

            };

        }

    },


    //获取列表多单元数据,整合成单个post对象
    //$list_selector//列表单元 li对象
    //$child_obj//表达式说明// ([变量名]/[目标选择器]/[对象值],[...]/[...]/[...]) //

    listData:function($list_selector,$child_obj,$condition){

        $list=$($list_selector);

        $child=$child_obj.split(',');//切割 子对象表达式 中的 ','
        //console.log($child);//
        $listData={};

        $list.each(function(index){
            $index=index;

            //切割单个 子-子对象 表达式 获得key和val
            $child2=new Array;
            for(var i=0;i<$child.length;i++){
                $child2[i]=$child[i].split('/');//切割 子-子对象表达式 中的 '/'
                //console.log( $child2[i][0] );
            }
            //console.log($child2);//

            //拼接成 json 字符串
            var $json_str="{";
            for(var j=0;j<$child2.length;j++){
                var $val='0';

                //对选择器 取值类型 做判断
                switch ($child2[j][2]){
                    default :
                        var $data_type=$child2[j][2];
                        if($data_type.substring(4,0)=='data'){
                            $data=$data_type.substr(5);
                            //alert($data);//
                            $val=$($list_selector+' '+$child2[j][1]).eq($index).data($data);
                        }
                        break;
                    case 'text':
                        $val=$($list_selector+' '+$child2[j][1]).eq($index).text();
                        break;
                    case 'val':
                        $val=$($list_selector+' '+$child2[j][1]).eq($index).val();
                        break;
                }

                //开始拼接
                if(j!=$child2.length-1 ){
                    $json_str+="'"+$child2[j][0]+"':'"+$val+"',";
                }else{
                    $json_str+="'"+$child2[j][0]+"':'"+$val+"'}";
                }

            }
            //console.log($json_str);//


            //目标val 是否符合 筛选规则
            $condi=$condition;
            if($condi!=undefined){
                var $condi_arr=$condi.split('/');
                //console.log($condi_arr);//

                var $cval='',$sval='',$ctype='',$csymbol='';

                $ctype=$condi_arr[1].substr(4);
                //console.log($ctype);//
                $sval=$($list_selector+' '+$condi_arr[0]).eq($index).val();
                //console.log( $sval );//
                $cval=$condi_arr[1].substring(5);
                //console.log( $cval );//
                $csymbol=$condi_arr[1].substring(3,5);
                //console.log($csymbol);//

                switch($csymbol){//符号判断
                    case '!=' :
                        if($sval!=$cval){
                            $listData[$index]=eval( "("+$json_str+")" );
                        }
                        break;
                    case '==':
                        if($sval==$cval){
                            $listData[$index]=eval( "("+$json_str+")" );
                        }
                        break;
                    case '>>':
                        if($sval>$cval){
                            $listData[$index]=eval( "("+$json_str+")" );
                        }
                        break;
                    case '<<':
                        if($sval<$cval){
                            $listData[$index]=eval( "("+$json_str+")" );
                        }
                        break;
                    case '>=':
                        if($sval>=$cval){
                            $listData[$index]=eval( "("+$json_str+")" );
                        }
                        break;
                    case '<=':
                        if($sval>=$cval){
                            $listData[$index]=eval( "("+$json_str+")" );
                        }
                        break;
                }
            }else{
                $listData[$index]=eval( "("+$json_str+")" );
            }

        });

        //console.log($listData);//

        if(Object.keys($listData).length === 0){
            return false;
        }else{
            return $listData;
        }


    },

    submitData:function($selector,$submit_child,$filter){
        $child=$submit_child.split(',');//切割 子对象表达式 中的 ','
        //console.log($child);//
        if($filter!=null){
            $filter=$filter.split(',');
            //console.log($filter);//
        }

        $submitData={};

        for(var $n in $child){
            $node=$child[$n].split('/');//切割 子对象表达式 中的 '/'
            //console.log($node);//
            $node_key=$node[0];
            $node_selector=$node[1];

            ////过滤不要的对象
            if($filter!=null){
                $continue=false;
                for(var $nu in $filter){ if($node_selector==$filter[$nu]) $continue=true; }
                if($continue){ continue; }
            }
            //\\

            $value_type=$node[2];
            //console.log($node_key);console.log($node_field);//
            //console.log($node_selector);console.log($value_type);//
            switch($value_type){
                case 'val':
                    $val=$($selector+' '+$node_selector).val();
                    eval('$submitData.'+$node_key+'="'+$val+'"');  break;
                case 'opt':
                    $val=$($selector+' '+$node_selector+' option:checked').val();
                    eval('$submitData.'+$node_key+'="'+$val+'"');  break;
                case 'src':
                    $val=$($selector+' '+$node_selector).attr('src');
                    eval('$submitData.'+$node_key+'="'+$val+'"');   break;
            }
        }

        return $submitData;
        //console.log($submitData);

    },

    setResult:function($selector,$set_child,$data_get,$filter){
        $child=$set_child.split(',');//切割 子对象表达式 中的 ','
        //console.log($child);//
        if($filter!=null){
            $filter=$filter.split(',');
            //console.log($filter);//
        }

        for($n in $child){
            $node=$child[$n].split('/');//切割 子对象表达式 中的 '/'
            //console.log($node);//
            $node_result=$node[0];
            $node_field=eval('$data_get.'+$node_result);
            $node_selector=$node[1];

            ////过滤不要的对象
            if($filter!=null){
                $continue=false;
                for(var $nu in $filter){ if($node_selector==$filter[$nu]) $continue=true; }
                if($continue){ continue; }
            }
            //\\

            $value_type=$node[2];
            //console.log($node_result);console.log($node_field);//
            //console.log($node_selector);console.log($value_type);//
            switch($value_type){
                case 'val':$($selector+' '+$node_selector).val($node_field);  break;
                case 'opt':$($selector+' '+$node_selector+' option[value='+$node_field+']').prop('selected','true');  break;
                case 'src':$($selector+' '+$node_selector).attr('src',$node_field); break;
            }
        }
    },

    //把表单序列数组 格式化成 json对象
    formSeriArr2json:function($form_data){

        $length=$form_data.length;
        json_str='{';
        for(var i=0;i<$length;i++){

            $name=$form_data[i]['name'];
            $value=$form_data[i]['value'];

            if(i+1==$length){
                json_str+=$name+':"'+$value+'"}';
                break;
            }else{
                json_str+=$name+':"'+$value+'",';
            }

        }
        //console.log(json_str);//
        new_json=eval("("+json_str+')');
        //console.log(new_json);//
        return new_json;

    },

    //获取json的个数
    JSONLength:function(obj){
    var size = 0, key;
        for (key in obj) {
            if (obj.hasOwnProperty(key)) size++;
        }
        return size;
    },

    json2formSeriArr:function($json){

        $new_arr=new Array();

        var size = 0, key;
        for( key in $json){
            if ($json.hasOwnProperty(key)) size++;
            //console.log( {"name":key,"value":$json[key]} );

            $new_arr[size-1]={"name":key,"value":$json[key]};
        }

        return $new_arr;

    },

    //纯 表单 post
    formPost:function($form_selector,post_url,result_func){

        $form_data=$form_selector.serializeArray();

        if($form_data.length==0){ console.log('none form post !'); return false; }

        $new_json=ajax.formSeriArr2json($form_data);
        console.log($new_json);//
        //return false;//

        ajax.post(post_url,$new_json,result_func,'');

    },

    //表单 + json补充 post
    mixPost:function($form_selector,post_url,add_json_data,result_func){

        $form_data=$form_selector.serializeArray();
        if($form_data.length==0){ console.log('none form post !'); return false; }
        //console.log($form_data);return false;//

        if(add_json_data){

            $new_arr=ajax.json2formSeriArr(add_json_data);

            //console.log($new_arr);return false;//

            $leng_f=$form_data.length;
            $leng_n=$new_arr.length;

            //console.log($leng_n);return false;//

            for(var i=0;i<$leng_n;i++){
                $form_data[$leng_f+i]=$new_arr[i];
            }

            //console.log($form_data);return false;//

        }

        $new_json=ajax.formSeriArr2json($form_data);

        console.log($new_json);//
        //return false;//

        ajax.post(post_url,$new_json,result_func,'');

    },

    msg:function(errmsg,redirect,style){



        if(errmsg){

            if(style==undefined||style==0){ alert( errmsg ); }
            if(style==1||style=='bootstrap'){


                $('#glob_modal').html(
                '<div id="g_modal" style="top:20%;" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true"> ' +
                '<div class="modal-dialog modal-sm">' +
                    '<div class="modal-content">' +
                        '<div class="modal-header">' +
                            '<button type="button" class="close" data-dismiss="modal">' +
                                '<span aria-hidden="true">&times;</span>' +
                                '<span class="sr-only">Close</span></button> ' +
                            '<h4 class="modal-title" id="myModalLabel">提示</h4> ' +
                            '</div> ' +
                        '<div class="modal-body">...</div> ' +
                        '<div class="modal-footer"> ' +
                            '<button type="button" class="btn btn-primary" data-dismiss="modal">知道了</button> ' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>'
                );


                $('.modal-body').text(errmsg);
                $('#g_modal').modal('show');

            }
            if(style==2){


                tipModal.set(errmsg,redirect);
                tipModal.toggle();
            }

        }

        if(errmsg==''||errmsg==undefined&&redirect){
            window.location.replace(redirect);
        }

        if(redirect&&style!=2&&style!=1){
            window.location.replace(redirect);
        }
    },

    msgbox:function(result,style){

        console.log(result);

        if(style==undefined){ style=0 }

        switch (result.errcode){
            case '-1' :
                //错误 带提示
                ajax.msg(result.errmsg,result.redirect,style);
                break;
            case '0' :
                //正确 带提示
                ajax.msg(result.errmsg,result.redirect,style);
                break;
            case '1' :
                //正确 不提示 直接跳转
                ajax.msg(result.errmsg,result.redirect,style);
                break;
        }

    }

    //end
};

var ckUid=function(result){

    switch(result.checkUid){
        case 'noUid':
            alert("微信未授权！返回首页?");
            window.location.href='../?/weixin/index/';
            break;
    }


};

var ckSid=function(result){
    switch(result.checkSid){
        case 'noSid':
            alert("非法操作！将返回首页..");
            window.location.href='../?/weixin/index/';
            break;
    }
};

var ajax = new ajax();
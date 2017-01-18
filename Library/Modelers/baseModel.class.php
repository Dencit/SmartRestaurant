<?php
/* Created by User: soma Worker: 陈鸿扬 Date: 16/6/18  Time: 20:16 */

namespace Modelers;
use Modelers\wpDb;


class baseModel extends wpDb {

    public static $wpDb;

    function __construct(){
        self::$wpDb = new parent(DBUSER,DBPASSWD,DBNAME,DBHOST);
    }

    function init(){
        self::$wpDb = new parent(DBUSER,DBPASSWD,DBNAME,DBHOST);
    }

    function obj2arr($dataArray){

        if(is_object($dataArray)){
            $newData=[];
            foreach($dataArray as $k=>$v){
                $newData[$k]=$v;
            }
            return $newData;
        }

        return false;
    }

    function setMade($setArray){

            if(empty($setArray) ){
                return '';
            }

            if(is_array($setArray) ){
                $set='';
                foreach ($setArray as $k=>$v){
                    if( $v!=end($setArray) ){
                        $set.= $k.'=\''.$v.'\',';
                    }else{
                        $set.= $k.'=\''.$v.'\'';
                    }
                }
                return ' SET '.$set;
            }

            if(!is_array($setArray)){
                $expArr=explode(',',$setArray);
                //var_dump($expArr);//
                if(!is_array($expArr)) return false;
                return ' SET '.$expArr[0].'= \''.$expArr[1].'\'';
            }

        return false;

    }

    function selectMade($selectArray=''){

        if(empty($selectArray)){ return ' SELECT `*`'; }

        if(is_array($selectArray)||is_object($selectArray)){

            $select='';$s='';
            foreach ($selectArray as $k=>$v){
                $s++;
                if( $s==count($selectArray) ){
                    $select.= "`".$v."`";
                }else{
                    $select.= "`".$v."`".',';
                }
            }

            return ' SELECT '.$select;
        }

        if(is_string($selectArray)){

            preg_match_all('/[\,]/',$selectArray,$match);
            //var_dump($match[0]);//exit;//

            switch(empty($match[0])){
                case true: return ' SELECT '.$selectArray; break;
                case false:

                    $newSelect=explode(',',$selectArray);
                    //var_dump($newSelect);//

                    $newArr=''; $i='';
                    foreach($newSelect as $n=>$v){
                        $i++;
                        if($i==count($newSelect)){
                            $newArr.="`".$v."`";
                        }
                        else{
                            $newArr.="`".$v."`,";
                        }
                    }
                    return ' SELECT '.$newArr;
                    break;
            }

        }

    }

    //for selectAddMade()
    private function forSelectAdd($selectArray){
        $select='';
        foreach ($selectArray as $k=>$v){

            preg_match_all("/\+|\-|\*|\//",$v,$matchs);

            $math=$matchs[0][0];
            //print_r($math);exit;//

            $nv=explode($math,$v);
            //var_dump($nv);exit;//
            if(isset($nv[1]) ){
                if( $v!=end($selectArray) ){
                    $select.= $nv[0].' '.$math.$nv[1].' as '.$nv[0].',';
                }else{
                    $select.= $nv[0].' '.$math.$nv[1].' as '.$nv[0];
                }
            }else{
                if( $v!=end($selectArray) ){
                    $select.= $nv[0].',';
                }else{
                    $select.= $nv[0];
                }
            }
        }
        return $select;
    }
    function selectMathMade($selectArray=''){

        if($selectArray!=''&& is_array($selectArray)){
            $select=self::forSelectAdd($selectArray);
            //print_r($select);exit;//
            return ' SELECT '.$select;

        }elseif($selectArray!=''&& $selectArray!='*'){
            $selectArray=explode(',',$selectArray);
            $select=self::forSelectAdd($selectArray);
            //print_r($select);exit;//
            return ' SELECT '.$select;
        }else{
            return ' SELECT *';
        }

    }


    function tableMade($tableArray=''){

        return ' FROM `'.$tableArray.'`';
    }


    private function forWhereCompare($whereArray){
        $i='';
        $count=count($whereArray);
        $where='';
        foreach ($whereArray as $k=>$v){
            $i++;
            //var_dump($k);
            preg_match_all("/\>|\<|\=|\>\=|\<\=|\!\=|\%\%|\%\=|\=\%/",$k,$matchs);

            $separatorA='\'';$separatorB='\'';

            if(isset($matchs[0][0])){
                //var_dump($matchs);//
                $nk=explode('/',$k);
                //print_r($nk);//

                //print_r($v);echo'||c||';
                //print_r($count);echo'||d||';//exit;


                //wpdb中 prepare()使用了vprintf()函数, %s 会被替换掉，故写成 %%s 就能执行
                switch($nk[1]){
                    case '%%' :$nk[1]='LIKE';$separatorA='\'%%';$separatorB='%%\'';break;
                    case '%=' :$nk[1]='LIKE';$separatorA='\'%%';break;
                    case '=%' :$nk[1]='LIKE';$separatorB='%%\'';break;
                }

                if( $i==$count ){ $where.= $nk[0].' '.$nk[1].$separatorA.$v.$separatorB.' '; }
                else{ $where.= $nk[0].' '.$nk[1].$separatorA.$v.$separatorB.' AND '; }
            }
            else{

                //print_r($i);echo'||a||';
                //print_r($count);echo'||b||';//exit;

                if( $i==$count ){ $where.= $k.'=\''.$v.'\' '; }
                else{ $where.= $k.'=\''.$v.'\' AND '; }
            }

        }
        return $where;
    }
    function whereMade($whereArray=''){

        //var_dump($whereArray);//exit;//

        if($whereArray==''||$whereArray=='-'){
            return '';
        }

        if(is_array($whereArray)||is_object($whereArray) ){

            if(!empty($whereArray)){
                $whereArray=$this->forWhereCompare($whereArray);
                //print_r($whereArray);//
                return ' WHERE '.$whereArray;
            }

            return '';

        }

        if(is_string($whereArray) ){
            $whereArray=$this->commaStr2Arr($whereArray);
            $whereArray=$this->forWhereCompare($whereArray);
            //var_dump($whereArray);exit;//
            return ' WHERE '.$whereArray;
        }


    }


    //order limit 通用
    //例：
    //$orderArray['uid']='ASC';
    //$orderArray['limit']='0,5';//

    function orderMade($orderArray=''){
        $s='';
        $order='';
        $orderArrayCount=count($orderArray);

        if(empty($orderArray)||$orderArray=='-'){
            return ' ';
        }

        if(is_array($orderArray)){

            foreach ($orderArray as $k=>$v){
                $s++;
                if( $s==$orderArrayCount ){
                    $order.= ''.$k.' '.$v.' ';
                }else{
                    $order.= ''.$k.' '.$v.' ';
                }
            }

            return ' ORDER BY '.$order;

        }

        if(!is_array($orderArray)){
            return ' ORDER BY '.$orderArray.' ';
        }
    }

    function groupByMade($groupByArray=''){

        $queryStr='group by'.' ';

        if(is_array($groupByArray)||is_object($groupByArray)){

            foreach($groupByArray as $n=>$v){
                $queryStr.=$v.' ';
                if(end($groupByArray)==$v){
                    $queryStr.=$v;
                }
            }

            return $queryStr;

        }

        if(is_string($groupByArray)){

            return $queryStr.$groupByArray.' ';

        }

        if(empty($groupByArray)||$groupByArray=='-'){

            return '';

        }

        return '';

    }

    function limitMade($orderArray=''){



    }


    function rowSelect($tableArray,$selectArray='',$whereArray='',$orderArray=''){

        $selectMade=$this->selectMade($selectArray);
        $tableMade=$this->tableMade($tableArray);
        $whereMade=$this->whereMade($whereArray);
        $orderMade=$this->orderMade($orderArray);
        $query=$selectMade.$tableMade.$whereMade.$orderMade;

        //echo $query;//exit;//

        $wpDb=self::$wpDb;
        $tableRow=$wpDb->get_row( $wpDb->prepare( $query ));
        if($tableRow){
            return $tableRow;
        }else{
            return false;
        }

    }

    function resultSelect($tableArray,$selectArray='',$whereArray='',$orderArray=''){

        $selectMade=$this->selectMade($selectArray);
        $tableMade=$this->tableMade($tableArray);
        $whereMade=$this->whereMade($whereArray);
        $orderMade=$this->orderMade($orderArray);
        $query=$selectMade.$tableMade.$whereMade.$orderMade;

        $wpDb=self::$wpDb;
        //print_r($query);
        //print_r( $wpDb->prepare($query) );//exit;//


        $tableRow=$wpDb->get_results( $wpDb->prepare( $query ));
        if($tableRow){
            return $tableRow;
        }else{
            return false;
        }

    }


    function rowInsert($table,$dataArray){

        if( is_object($dataArray) ) $dataArray=$this->obj2arr($dataArray);
        elseif( is_string($dataArray) ){ $dataArray=$this->commaStr2Arr($dataArray);}
        //var_dump($dataArray);//

        $wpDb=self::$wpDb;
        $tableInsert=$wpDb->insert($table,$dataArray);
        if($tableInsert){
            return true;
        }else{
            return false;
        }
    }

    //used **2
    private function commaStr2Arr($commaStr){

        $commaStr=explode(',',$commaStr);
        //var_dump($commaStr);//

        $newArr=[];
        foreach($commaStr as $n=>$v){
            $v=explode('/',$v);
            $newArr[$v[0]]=$v[1];
        }
        //var_dump($newArr);exit;//

        return $newArr;

    }

    function rowUpdate($table,$dataArray,$whereArray){

        if( is_object($dataArray) ) $dataArray=$this->obj2arr($dataArray);
        elseif( is_string($dataArray) ){ $dataArray=$this->commaStr2Arr($dataArray);}

        if( is_string($whereArray) ){ $whereArray=$this->commaStr2Arr($whereArray);}
        //print_r($whereArray);//

        $wpDb=self::$wpDb;
        $rowUpdate=$wpDb->update($table,$dataArray,$whereArray);

        /*try{
            $rowUpdate=$wpDb->update($table,$dataArray,$whereArray);
        }catch(\Exception $e){
            return  $e->getMessage();
        }*/

        if($rowUpdate){
            return true;
        }else{
            return false;
        }

    }


    function rowDel($tableArray,$whereArray='',$orderArray=''){

        $tableMade=$this->tableMade($tableArray);
        $whereMade=$this->whereMade($whereArray);
        $orderMade=$this->orderMade($orderArray);
        $query='DELETE'.$tableMade.$whereMade.$orderMade;
        //echo($query);//

        $wpDb=self::$wpDb;
        $rowDel=$wpDb->query($wpDb->prepare( $query ));

        return $rowDel;

    }


////////////////////


//检查式 新增条目
    function rowAddCheck($table,$selectArray,$whereArray,$dataArray,$orderArray=''){

        $rowSelect=$this->rowSelect($table,$selectArray,$whereArray,$orderArray);
        //var_dump($rowSelect);exit;//

        if($rowSelect){ $have='1'; }else{ $have='0'; }

        if($have=='0'){
            $rowInsert=$this->rowInsert($table,$dataArray);

            if($rowInsert){ return 'insertOk'; }else{ return 'insertFail'; }

        }else{
            $rowUpdate=$this->rowUpdate($table,$dataArray,$whereArray);

            if($rowUpdate){ return 'updateOk'; }else{ return 'updateFail'; }
        }

    }


//加减表中某字段的值,可自定步进值,根据 $selectArray传参类型 返回同类型处理结果
    function fieldNumSUM($table,$selectArray='',$whereArray,$math='1',$num='1',$orderArray=''){


        //print_r($selectArray);print_r($whereArray);exit;

        $rowSelect=$this->rowSelect($table,$selectArray,$whereArray,$orderArray);
        if(!$rowSelect){
            exit('$rowSelect fail');
        }

        //print_r($rowSelect);exit;

        //加减数据判断 函数
        $dataArray=$this->dataArrayGet($rowSelect,$selectArray,$math,$num);
        //print_r($dataArray);exit;

        $rowUpdate=$this->rowUpdate($table,$dataArray,$whereArray);
        if($rowUpdate){ return $dataArray; }
        else{ return false; }
    }
////加减数据判断,从上~
    private function dataArrayGet($rowSelect,$selectArray,$math='1',$num='1'){
        $dataArray=array();
        if(is_array($selectArray)&&is_array($num)){

            //print_r($rowSelect);print_r($selectArray);exit;

            foreach($selectArray as $k=>$v){

                $rowSelectV=$rowSelect->$v;
                $numV=$num[$k];
                //print_r($rowSelectV);exit;

                if($math=='1'){
                    $addField=$rowSelectV + $numV;
                }else{
                    $addField=$rowSelectV - $numV;
                }
                $dataArray[$v]=$addField;
            }
            //print_r($dataArray); exit;
        }else{

            $rowSelectA=$rowSelect->$selectArray;

            if($rowSelectA=='0'){
                $addField='0';
            }else if($math=='1'){
                $addField=$rowSelectA + $num;
            }else{
                $addField=$rowSelectA - $num;
            }
            $dataArray[$selectArray]=$addField;
            //print_r($dataArray); exit;
        }

        return $dataArray;
    }



    function resultByType($table,$select,$where,$order,$group){

        $WP=self::$wpDb;

        $select=self::selectMade($select);
        $table=self::tableMade($table);
        $order=self::orderMade($order);
        $group_str=self::groupByMade($group);

        $select_type=self::selectMade($group);
        $typeQuery=$select_type.$table.$group_str;
        //echo($typeQuery);//
        $typeArray=$WP->get_results($WP->prepare( $typeQuery ));
        //print_r($typeArray);//

        $resultArray=array();

        foreach($typeArray as $n=>$v ){

            if($where!=''||$where!='-'){
                $where_str=self::whereMade( 'type/'.$v->type.','.$where );
            }
            else{
                $where_str=self::whereMade( 'type/'.$v->type );
                //var_dump($where_str);exit;//
            }

            $resultQuery=$select.$table.$where_str.$order;

            //echo($resultQuery).'<br/>';//

            $resultArray[$n][$group]=$v->type;
            $resultArray[$n][$group.'_info']=$WP->get_results($WP->prepare( $resultQuery ))  ;
        }

        //print_r($resultArray);exit;//

        return $resultArray;

    }


    function haveSomeSecond($table,$uid,$second,$time=''){

        if($time==''){ $time='time';}

        $havePostTime=$this->rowSelect( $table,$time,array('uid'=>$uid),array('time'=>'desc') );

        //var_dump($havePostTime);//

        if($havePostTime && time() < ($havePostTime->time+($second*60)) ){

            return true;

        }

        return false;

    }


    function rowSelectMath($tableArray,$selectArray='',$whereArray='',$orderArray=''){

        $selectMade=$this->selectMathMade($selectArray);
        $tableMade=$this->tableMade($tableArray);
        $whereMade=$this->whereMade($whereArray);
        $orderMade=$this->orderMade($orderArray);
        $query=$selectMade.$tableMade.$whereMade.$orderMade;

        //echo $query;exit;//

        $wpDb=self::$wpDb;
        $tableRow=$wpDb->get_row( $wpDb->prepare( $query ));
        if($tableRow){
            return $tableRow;
        }else{
            return false;
        }

    }






}
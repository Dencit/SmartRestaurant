<?php
/* Created by User: soma Worker:陈鸿扬  Date: 16/11/18  Time: 20:41 */

namespace Commons;


class imgMaker {

    static function imageWrite($base64Data,$base_path,$day_menu,$file_name){

        //文件分成 类型和数据 两个对象
        list($type, $data) = explode(',', $base64Data);

        // 判断类型
        $ext='jpg';
        if(strstr($type,'image/jpeg')!==''){
            $ext = '.jpg';
        }elseif(strstr($type,'image/gif')!==''){
            $ext = '.gif';
        }elseif(strstr($type,'image/png')!==''){
            $ext = '.png';
        }elseif(strstr($type,'image/png')!==''){
            $ext = '.bmp';
        }

        //检查当天文件目录 没有则创建
        $path=$base_path.$day_menu;
        if (!file_exists($path) ){ mkdir ($path); chmod($path,0777); }

        // 生成的文件名
        $file = $file_name.$ext;

        // 生成文件
        file_put_contents($path.'/'.$file, base64_decode($data), true);
        chmod($path.'/'.$file,0755);

        $result['image']=$day_menu.'/'.$file;//子目录和文件对象 用于数据保存
        $result['image_url']=$path.'/'.$file;//直接文件对象 用于前台路径调用

        $result['path']=$path.'/';//文件路径 日志用
        $result['file_name']=$file_name;//文件名 日志用
        $result['suffix']=$ext;//文件后缀 日志用

        return $result;

    }

} 
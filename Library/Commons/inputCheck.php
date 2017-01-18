<?php
/* Created by User: soma Worker:陈鸿扬  Date: 16/10/20  Time: 15:57 */
namespace Commons ;

class inputCheck{

    static function check_identity($id='')
    {
        $set = array(7,9,10,5,8,4,2,1,6,3,7,9,10,5,8,4,2);
        $ver = array('1','0','x','9','8','7','6','5','4','3','2');

        $arr = str_split($id);
        if( count($arr)<17 ){ return false; }

        $sum = 0;
        for ($i = 0; $i < 17; $i++)
        {
            if (!is_numeric($arr[$i]))
            {
                return false;
            }
            $sum += $arr[$i] * $set[$i];
        }
        $mod = $sum % 11;
        if (strcasecmp($ver[$mod],$arr[17]) != 0)
        {
            return false;
        }
        return true;
    }

}
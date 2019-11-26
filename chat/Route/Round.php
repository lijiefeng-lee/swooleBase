<?php
/**
 * Created by PhpStorm.
 * User: Leestar-Peter
 * Date: 2019/3/29
 * Time: 21:32
 */

class Round
{
    static  $lastIndex=0; //
    public static function select(array $list){
           $currentIndex=self::$lastIndex;//当前index
           $url=$list[$currentIndex];
           if($currentIndex+1>count($list)-1){
               self::$lastIndex=0;
           }else{
               self::$lastIndex++;
           }
           return $url; //返回当前url
    }

}
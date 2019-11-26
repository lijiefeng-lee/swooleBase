<?php
/**
 * Created by PhpStorm.
 * User: Leestar-Peter
 * Date: 2019/3/27
 * Time: 21:13
 */

namespace Lee\Core\Event;

class Event
{
    public static $events=[];
    //事件注册
    /*
     * $event 事件名
     * $callback 事件回调
     */
    public static function  register($event,$callback){
        $event=strtolower($event); //不区分大小写

        //var_dump($event,'注册');
        if(!isset(self::$events[$event])){
            self::$events[$event]=[];
        }
        self::$events[$event]=['callback'=>$callback];
    }

    //事件的触发
    public static function  trigger($event,$params=[]){
        $event=strtolower($event);

       // var_dump(self::$events);
        if(isset(self::$events[$event])){
            call_user_func(self::$events[$event]['callback'],$params);
            return true;
        }
        return false;
    }
}
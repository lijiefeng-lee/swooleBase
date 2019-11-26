<?php
/**
 * Created by PhpStorm.
 * User: Sixstar-Peter
 * Date: 2019/3/21
 * Time: 22:35
 */

namespace Lee\Core\WebSocket;

/*
 * 保留当前客户端的上下文信息
 */
class WebSocketContext
{
    /*
     *  fd=>[
     *     $fd,
     *     $path,
     *     $request
     * ]
     * */

    private  static  $connecitons=[];

    //初始化
    public  static  function  init($fd,$path,$request){
         self::$connecitons[$fd]['path']=$path;
         self::$connecitons[$fd]['request']= $request;

    }
    //获取连接所对应的path信息
    public  static  function  get($fd=null){
            if($fd==null){
                return null;
            }
            return self::$connecitons[$fd]??null;
    }
    //删除方法
    public  static  function  del($fd=null){
        if($fd==null){
            return false;
        }
        if(isset(self::$connecitons[$fd])){
            unset(self::$connecitons[$fd]);
            return true;
        }
        return false;
    }
}
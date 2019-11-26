<?php
/**
 * Created by PhpStorm.
 * User: Leestar-Peter
 * Date: 2019/3/27
 * Time: 21:12
 */

namespace App\Listener;
use Firebase\JWT\JWT;
use Lee\Core\WebSocket\WebSocketContext;

/**
 *
 * @Listener(ws.close)
 */
class CloseListener
{
    public  function  handle($event){
        //删除当前客户端所在redis当中保留的路由信息
        $request=WebSocketContext::get($event['fd'])['request'];
        $token=$request->header['sec-websocket-protocol'];
        $key='peter123456';
        $arr=JWT::decode($token,$key,['HS256']);
        $userInfo=$arr->data;
        $key=$userInfo->service_url;
        $uid=$userInfo->uid;
        $redis=$event['redis'];
        $redis->hdel($key,$uid);
        var_dump("关闭事件触发");
    }
}
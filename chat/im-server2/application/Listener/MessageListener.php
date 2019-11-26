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
 * @Listener(ws.message)
 */
class MessageListener
{
    public  function  handle($event){
         $frame=$event['frame'];
         $redis=$event['redis'];
         $server=$event['server'];
         $data=json_decode($frame->data,true);

         var_dump($data);

         switch ($data['method']){
             case 'server_broadcast' :
                 $request=WebSocketContext::get($frame->fd)['request'];
                 $token=$request->header['sec-websocket-protocol'];
                 $key='peter123456';
                 $arr=JWT::decode($token,$key,['HS256']);
                 $userInfo=$arr->data;
                 $key=$userInfo->service_url;
                 $service=$redis->SMEMBERS('im_service');
                 //给不在本机上的客户端,通过路由服务器广播消息(排除当前的机器)
                  foreach($service as $k=>$v ){
                        $url_data=json_decode($v,true);
                        if($url_data['ip'].':'.$url_data['port']==$key){
                          unset($service[$k]);
                        }
                  }
                 $cli = new \Swoole\Coroutine\http\Client("192.168.10.10", 9600);
                 $ret = $cli->upgrade("/"); //升级的websockt
                 if ($ret) {
                     $route_data=['method'=>'route_broadcast','target_server'=>$service,'msg'=>$data['msg']];
                     $cli->push(json_encode($route_data));
                 }
                 //如果说当前服务端连接了客户端那么就直接发
                  $this->sendAll($server,$data['msg']);
                 break;
             case 'route_broadcast' : //接收到服务器广播
                    $this->sendAll($server,$data['msg']);
                 break;
         }
        var_dump("message事件触发");
    }

    public function  sendAll($server,$msg){
         foreach ($server->connections as $fd){
             var_dump($fd);
             if($server->exists($fd)){
                 $server->push($fd,$msg);
             }

         }
    }
}

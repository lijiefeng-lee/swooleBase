<?php
/**
 * Created by PhpStorm.
 * User: Sixstar-Peter
 * Date: 2019/3/21
 * Time: 22:03
 */

namespace Lee\Core\WebSocket;

use Lee\Core\Config;
use Lee\Core\Event\Event;
use Lee\Core\Http;
use Swoole\WebSocket\Server;

class WebSocket extends Http
{
    public  function  run(){

        $config=Config::get_instance();
        $config->load(); //载入配置文件
        $setting=$config->get('ws');

        $this->server=new Server($setting['host'],$setting['port']);
        $this->server->set($setting['swoole_setting']);
        $this->server->on('Start',[$this,'start']);
        $this->server->on('workerStart',[$this,'workerStart']);
        $this->server->on('handshake',[$this,'handshake']);
        $this->server->on('open',[$this,'open']);
        $this->server->on('message',[$this,'message']);
        $this->server->on('close',[$this,'close']);
        $this->collectEvent(); //事件注册
        //启动http服务
        if ((int)$setting['enable_http'] === 1) {
            $this->server->on('request',[$this,'request']);
        }
        //tcp服务端口监听
        if ((int)$setting['tcpable'] === 1) {
            $tcp=$config->get('tcp');
            $this->registerTcp($tcp);
        }

        $this->server->start();
        //1.热重启
        //2.能够支持http请求,解析路由
        //3.能够拥有websocket控制器 ()
    }

    public  function  handshake  (\swoole_http_request $request, \swoole_http_response $response) {
        Event::trigger('ws.hand',['server'=>$this->server,'request'=>$request,'response'=>$response,'redis'=>$this->redis]);
        $this->open($this->server,$request);
    }

    public  function open($server,$request){
        $path=$request->server['path_info'];
        $fd=$request->fd;
        WebSocketContext::init($fd,$path,$request); //保存了上下文信息
        //全局事件优先
        Event::trigger('ws.open',['server'=>$server,'request'=>$request]);
        //触发到控制器,路由
        Dispatcher::open($server,$request,$path);

    }

    public  function message($server,$frame){
        //var_dump($request->server['path_info']);
        $fd=$frame->fd;
        Event::trigger('ws.message',['server'=>$server,'frame'=>$frame,'redis'=>$this->redis]);
        $path=WebSocketContext::get($fd);
        Dispatcher::message($server,$path['path'],$frame);
    }

    public  function close($server,$fd){
        // var_dump($request->server['path_info']);
        $path=WebSocketContext::get($fd);
        Event::trigger('ws.close',['server'=>$server,'fd'=>$fd,'redis'=>$this->redis]);
        Dispatcher::close($server,$path['path'],$fd);
        WebSocketContext::del($fd);
    }


}
<?php
/**
 * Created by PhpStorm.
 * User: lijiefeng
 * Date: 2019/11/6
 * Time: 上午9:16
 */

/**
 * 一、websocket介绍
1.1、websocket是什么呢？
websocket是一个协议，它仅仅就是一个协议而已，跟我们所了解的http协议、https协议、ftp协议等等一样，都是一种单纯的协议。
1.2、websocket的特点呢？
相对于Http这种非持久连接而言，websocket协议是一种持久化连接，它是一种独立的，基于TCP的协议。基于websocket，我们可以实现客户端和服务端双向通信。

在websocket出现之前，为了解决此类问题，常用的解决方法有轮询和long pull,这两种技术都是客户端和服务端建立源源不断的HTTP连接，非常消耗带宽和服务器资源。

websocket是双向持久连接，客户端和服务端只需要第一次建立连接即可实现双向通信。
二、Swoole_websocket简介
Swoole增加了内置的WebSocket服务器支持，通过几行PHP代码就可以写出一个异步非阻塞多进程的WebSocket服务器。
 *
 *
 * onOpen
    WebSocket客户端与服务器建立连接并完成握手后会回调此函数。
    $req 是一个Http请求对象，包含了客户端发来的握手请求信息
    onOpen事件函数中可以调用push向客户端发送数据或者调用close关闭连接
    onOpen事件回调是可选的

 *
 * onMessage
    当服务器收到来自客户端的数据帧时会回调此函数
 * 注意：

    连接保持+心跳
    Websocket也是长连接的形式，同样支持自己实现心跳包的检测，怎么实现请参考前面的

    2、校验客户端连接的有效性
    我们创建的websocket_server,是对外开放的，也就是任何人都能连接过来，对于非websocket协议同样能触发，所以我们要判断当前是websocket客户端并且能够通讯才进行发送。


 */



$server = new Swoole\WebSocket\Server("0.0.0.0", 9501);

//自行握手不会执行open事件回调如过想执行可以 手动执行 onpen 是握手成功的的回调底层自动握手
$server->on('handshake', function (\swoole_http_request $request, \swoole_http_response $response) use($server){
    // print_r( $request->header );
    // if (如果不满足我某些自定义的需求条件，那么返回end输出，返回false，握手失败) {
    //    $response->end();
    //     return false;
    // }

    // websocket握手连接算法验证
    $secWebSocketKey = $request->header['sec-websocket-key'];
    $patten = '#^[+/0-9A-Za-z]{21}[AQgw]==$#';
    if (0 === preg_match($patten, $secWebSocketKey) || 16 !== strlen(base64_decode($secWebSocketKey))) {
        $response->end();
        return false;
    }
    echo $request->header['sec-websocket-key'];
    $key = base64_encode(sha1(
        $request->header['sec-websocket-key'] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11',
        true
    ));

    $headers = [
        'Upgrade' => 'websocket',
        'Connection' => 'Upgrade',
        'Sec-WebSocket-Accept' => $key,
        'Sec-WebSocket-Version' => '13',
    ];

    // WebSocket connection to 'ws://127.0.0.1:9502/'
    // failed: Error during WebSocket handshake:
    // Response must not include 'Sec-WebSocket-Protocol' header if not present in request: websocket
    if (isset($request->header['sec-websocket-protocol'])) {
        $headers['Sec-WebSocket-Protocol'] = $request->header['sec-websocket-protocol'];
    }

    foreach ($headers as $key => $val) {
        $response->header($key, $val);
    }

    $response->status(101);
    $server->defer(function(){
        //手动执行open事件
    //    $this->open($server,$request);
    });
    $response->end();
});

$server->on('open', function (Swoole\WebSocket\Server $server, $request) {
//    WebSocket客户端与服务器建立连接并完成握手后会回调此函数。
//    $req 是一个Http请求对象，包含了客户端发来的握手请求信息
//    onOpen事件函数中可以调用push向客户端发送数据或者调用close关闭连接
//    onOpen事件回调是可选的
    echo "客户端 fd{$request->fd}\n";
});

$server->on('request', function (Swoole\http\request $request, Swoole\http\response $response) use($server) {
    //也可以http  请求
    //检查连接是否为有效的WebSocket客户端连接。此函数与exist方法不同，
    //exist方法仅判断是否为TCP连接，无法判断是否为已完成握手的WebSocket客户端。
    //如果不是不加会报错 因为push是websocket客户端才可以
    $fd = $server->connection_list();//所有连接上来的fd
    foreach ($fd as  $key=>$val){
        if($server->isEstablished($val)){
            $server->push($val,'通知');
        }
    }
    $response->end('111');
});



$server->on('message', function (Swoole\WebSocket\Server $server, $frame) {
    //当服务器收到来自客户端的数据帧时会回调此函数
    echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
    //向websocket客户端连接推送数据，长度最大不得超过2M。
    $server->push($frame->fd, "this is server");
});

$server->on('close', function ($ser, $fd) {
    echo "client {$fd} closed\n";
});

$server->start();



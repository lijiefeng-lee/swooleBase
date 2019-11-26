<?php
/**
 * Created by PhpStorm.
 * User: lijiefeng
 * Date: 2019/9/17
 * Time: 下午2:14
 */

//异步户端

$client = new Swoole\Client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);

//客户端连接服务器成功后会回调此函数。
$client->on("connect", function(swoole_client $cli) {
    $cli->send("GET / HTTP/1.1\r\n\r\n");
});
//客户端收到来自于服务器端的数据时会回调此函数
$client->on("receive", function(swoole_client $cli, $data){
    echo "Receive: $data";
   // $cli->send("1"."\n");
});
//连接服务器失败时会回调此函数
$client->on("error", function(swoole_client $cli){
    echo "error\n";
});
//连接被关闭时回调此函数。
$client->on("close", function(swoole_client $cli){
    echo "Connection close\n";//不会主动关闭通过服务端心跳
});
//连接服务器
$client->connect('192.168.10.10', 9501);


//swoole_timer_tick('9000',function () use ($client){
//    $client->send('1');
//});//保持长连接
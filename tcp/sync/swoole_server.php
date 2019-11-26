<?php
/**
 * Created by PhpStorm.
 * User: lijiefeng
 * Date: 2019/9/12
 * Time: 下午4:13
 */

//绑定服务器ip和端口
$server = new \Swoole\Server('192.168.10.10','9501',SWOOLE_BASE,SWOOLE_SOCK_TCP);

$server->set([
    'worker_num'=>5
]);
//绑定事件当新的连接进来
$server->on('connect',function ($server,$fd,$reactor_id){
    echo '新的连接进来'.$fd.PHP_EOL;
});

//客户端发信息过来
$server->on('receive',function ($server, $fd,$reactor_id,$data){
    var_dump($data);
    $server->send($fd,'我是服务端');
});

//客户端连接关闭了
$server->on('close',function ($server,$fd,$reactor_id){
    echo $fd.'客户端关闭了连接'.PHP_EOL;
});

//启动服务
$server->start();

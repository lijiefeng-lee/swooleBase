<?php
/**
 * Created by PhpStorm.
 * User: lijiefeng
 * Date: 2019/9/19
 * Time: 上午9:09
 */

$server = new Swoole\Server('192.168.10.10',9501,SWOOLE_PROCESS,SWOOLE_SOCK_UDP);
$server->set([
   'worker_num'=>1,
    'heartbeat_idle_time'=>10, //不会影响udp
    'heartbeat_check_interval'=>3//不会影响udp
]);
$server->on('packet',function ($server,$data,$client_info){
    var_dump($client_info) ;
    $server->sendto($client_info['address'],$client_info['port'],'222');
});
$server->start();
<?php
/**
 * Created by PhpStorm.
 * User: lijiefeng
 * Date: 2019/9/17
 * Time: 下午2:14
 */

//同步阻塞客户端

$client = new Swoole\Client(SWOOLE_SOCK_TCP,SWOOLE_SOCK_SYNC);//默认同步阻塞

$client->connect('192.168.10.10','9501') || exit('连接失败');//连接


//$client->recv();//接收数据

$client->close();//关闭连接


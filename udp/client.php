<?php
/**
 * Created by PhpStorm.
 * User: lijiefeng
 * Date: 2019/9/19
 * Time: 上午9:09
 */
$client = new Swoole\Client(SWOOLE_SOCK_UDP);
$client->sendto('192.168.10.10',9501,'11');
echo $client->recv();
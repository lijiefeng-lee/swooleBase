<?php
/**
 * Created by PhpStorm.
 * User: Leestar-Peter
 * Date: 2019/2/19
 * Time: 23:05
 */
 $client=new swoole\Client(SWOOLE_SOCK_TCP);
//（fd+id）识别身份
 //发数据
 $client->connect('192.168.10.10',9501);

 //约定一个分隔符

 //一次性发送多条数据
 for ($i=0;$i<100;$i++){
     $body = '我爱你';
     $data = pack('N',strlen($body)).$body;
     $client->send($data);

 }
//$body = str_repeat('a',1*1024*1024);
//$body = 'peter';
//$data = pack('N',strlen($body)).$body;
//$client->send($data);
//一次发送大量的数据，拆分小数据
//$data=str_repeat('a',12*1024*1024);
//$client->send(json_encode($data));

//echo $client->recv(); //接收消息没有接收

 //22:01

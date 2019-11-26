<?php
/**
 * Created by PhpStorm.
 * User: Sixstar-Peter
 * Date: 2019/2/19
 * Time: 23:05
 */
// $client=new swoole\Client(SWOOLE_SOCK_TCP);
// $client->connect('127.0.0.1',9800);
//
// while (true){
//     sleep(10);
//     $client->send('hello');
// }

$socket=new Co\Socket(AF_INET,SOCK_STREAM,0);

go(function ()use($socket){
    while (true){
        $socket->connect('127.0.0.1',9800);
        $socket->send('hello');
        co::sleep(0.10);
    }
});


$socket=new Co\Socket(AF_INET,SOCK_STREAM,0);
go(function ()use($socket){
    while (true){
        $socket->connect('127.0.0.1',9800);
        $socket->send('hello');
        co::sleep(0.10);
    }

});


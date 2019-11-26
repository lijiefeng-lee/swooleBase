<?php
/**
 * Created by PhpStorm.
 * User: Leestar-Peter
 * Date: 2019/2/19
 * Time: 21:37
 */

//tcp协议
$server=new Swoole\Server("192.168.10.10",9501);   //创建server对象

//eof 检测
//$server->set([
//    'worker_num'=>1, //设置进程
//    'heartbeat_idle_time'=>10,//连接最大的空闲时间
//    'heartbeat_check_interval'=>3, //服务器定时检查
//    'open_eof_check'=>true,//打开eof检测
//    'package_eof'=>"\r\n",//分隔
//    'open_eof_split'=>true//自动检测
//]);
//固定包头+包体
$server->set([
    'worker_num'=>1, //设置进程
    'heartbeat_idle_time'=>10,//连接最大的空闲时间
    'heartbeat_check_interval'=>3, //服务器定时检查
    'open_length_check' => true,
    'package_max_length' => 1024 * 1024 * 3,//包的大小
    'package_length_type' => 'N',//包头的长度
    'package_length_offset' => 0,//整体长度
    'package_body_offset' => 4, //包体从多少开始
]);

//监听事件,连接事件
$server->on('connect',function ($server,$fd){
    echo "新的连接进入:{$fd}".PHP_EOL;
});


//消息发送过来
$server->on('receive',function (swoole_server $server, int $fd, int $reactor_id, string $data){
    $info = unpack('N',$data);//返回数据字符串长度
    var_dump($info);
    //var_dump(substr($data,4));


//    $data=explode("\r\n",$data);
//    var_dump(count($data));
    //服务端
    //$server->send($fd,'我是服务端');


});

//消息关闭
$server->on('close',function (){
    echo "消息关闭".PHP_EOL;
});
//服务器开启
$server->start();


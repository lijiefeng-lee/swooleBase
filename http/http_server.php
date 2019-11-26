<?php
/**
 * Created by PhpStorm.
 * User: lijiefeng
 * Date: 2019/11/5
 * Time: 上午11:07
 */

//$server = new\swoole\http\server();
//
//注意事项：1
//Swoole的HttpServer可以接受application/x-www-form-urlencoded/form-data类型的POST参数，
//并且会将解析后的参数存放在swoole_server_request对象的post成员变量内。
//对于application/json或者其他类型的请求参数，Swoole底层并不会自动解析。
//但是Swoole的swoole_server_request提供了rawContent方法可以获得原始的POST字符串,我们可以根据Content-type类型做相应的解析。


//2.POST/文件上传需要设置临时文件位置（upload_tmp_dir），并且需要设置包的大小，最大尺寸受到 package_max_length 
//配置项限制，默认为2M，调用$response->end后会自动删除，在end之后操作上传文件会抛出文件不存在错误。
//



$server = new swoole_http_server('0.0.0.0',9800);
$server->set([
    'worker_num' =>5,
    'package_max_length'=>1024*1024*2,//2M默认为 2M
    'upload_tmp_dir'=>__DIR__.'/upload',
]);

/**
  addlistener 可以实现多端口监听 但是 http 的不能监听tcp 的似乎 回调函数无法区分
  监听1024以下的端口需要root权限
  主服务器是WebSocket或Http协议，新监听的TCP端口默认会继承主Server的协议设置。必须单独调用set方法设置新的协议才会启用新协议
 * 设置worker_num数量的时候这里的worker进程不会另外创建
 * swoole_server_port设置worker_num是不生效的
 * */
$port = $server->addlistener('192.168.10.10',9501,SWOOLE_SOCK_TCP);
$port->set([
    'worker_num' =>6,
]);
$port->on('receive', function ($serv, $fd, $from_id, $data) {
    $servInfo = $serv->connection_info($fd,$from_id);
    if($servInfo['server_port']=='9501'){
        //echo '内网';
    }
});


$port->on('close', function ($serv, $fd) {
   // echo '内网';
});



$server->on('request',function ($request , $response){
    var_dump($request);
    $response->header("content-type",  "text/html");
    $response->header("Charset",  "utf-8");
    $response->cookie('user','Lee');
    //根据不同的相应类型返回不同的数据格式
    var_dump($request->header['content-type']);
    if($request->header['content-type']=='application/x-www-form-urlencoded'){
        var_dump($request->post);
    }else{
        var_dump($request->rawContent());//原始数据
    }
    $response->end('http  response');
    //$response->end('http  response'); 下面这个是不能相应的

});

$server->on('workerStart', function ($serv, $fd) {
    echo 1;
});


$server->start();
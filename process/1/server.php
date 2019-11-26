<?php
/**
 * Created by PhpStorm.
 * User: lijiefeng
 * Date: 2019/9/25
 * Time: 下午2:46
 */

/**
 * 单进程阻塞的网络服务器
 * 1 创建一个socket，绑定服务器端口（bind），监听端口（listen），在PHP中用stream_socket_server一个函数就能完成上面3个步骤
 * 2 进入while循环，阻塞在accept操作上，等待客户端连接进入。此时程序会进入睡眠状态，直到有新的客户端发起connect到服务器，操作系统会唤醒此进程。
 *   accept函数返回客户端连接的socket
 * 3 利用fread读取客户端socket当中的数据收到数据后服务器程序进行处理然后使用fwrite向客户端发送响应。
 *   长连接的服务会持续与客户端交互，而短连接服务一般收到响应就会close。
 * 缺点：
 * 1  一次只能处理一个连接，不支持多个连接同时处理
 * 2 每个连接进入到我们的服务端的时候,单独创建一个进程/线程提供服务
 */

 class Worker{


     protected $socket = null;

     public $onMessage = null;

     public $onConnect = null;

     public function __construct($socket_address)
     {
         //绑定地址监听端口
         $this->socket = stream_socket_server($socket_address);
     }

     public function start(){
         while (true){
             //阻塞监听客户端socket状态如连接成功发送的消息等，调用相应回调   直到返回
             $clientSocket = stream_socket_accept($this->socket);//返回客户端资源
             if(!empty($clientSocket) && is_callable($this->onConnect)){
                 call_user_func($this->onConnect,$clientSocket);
             }
             //从连接当中读取客户端的内容
             $buffer=fread($clientSocket,65535);

             //正常读取到数据,触发消息接收事件,响应内容
             if(!empty($buffer) && is_callable($this->onMessage)){
                 call_user_func($this->onMessage,$clientSocket,$buffer);
             }
             //如果不关闭连接不能支持大点的并发请求
             //fclose($clientSocket);
         }

     }
 }


$server = new Worker('tcp://0.0.0.0:9800');
 //客户端连接成功触发
 $server->onConnect = function ($fd){
     echo '连接事件触发',(int)$fd,PHP_EOL;
 };
 //客户端端发消息过来触发
 $server->onMessage = function ($conn, $message){
     //事件回调当中写业务逻辑
     //var_dump($conn,$message);
     $content="我是peter";
     $http_resonse = "HTTP/1.1 200 OK\r\n";
     $http_resonse .= "Content-Type: text/html;charset=UTF-8\r\n";
   //  $http_resonse .= "Connection: keep-alive\r\n"; //连接保持
     $http_resonse .= "Server: php socket server\r\n";
     $http_resonse .= "Content-length: ".strlen($content)."\r\n\r\n";
     $http_resonse .= $content;
     fwrite($conn, $http_resonse);
 };
$server->start(); //启动
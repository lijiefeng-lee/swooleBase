<?php
/**
 * Created by PhpStorm.
 * User: lijiefeng
 * Date: 2019/9/25
 * Time: 下午2:46
 */

/**
 * epoll、事件驱动网络服务器
 */



class Worker{
    //监听socket
    protected $socket = NULL;
    //连接事件回调
    public $onConnect = NULL;
    //接收消息事件回调
    public $onMessage = NULL;

    public function __construct($socket_address) {
        //监听地址+端口
        $this->socket=stream_socket_server($socket_address);

    }
    public function start() {
        //获取配置文件
        $this->fork();
    }

    public function fork(){
        $this->accept();//子进程负责接收客户端请求
    }
    public  function  accept(){
        swoole_event_add($this->socket,function ($fd){
            $clientSocket = stream_socket_accept($fd);
            if (!empty($clientSocket) && is_callable($this->onConnect)){
                call_user_func($this->onConnect,$clientSocket);
            }
            //监听客户端可读
            swoole_event_add($clientSocket,function ($fd){
                //从连接当中读取客户端的内容
                $buffer=fread($fd,1024);
                //如果数据为空，或者为false,不是资源类型
                if(empty($buffer)){
                    if(feof($fd) || !is_resource($fd)){
                        //触发关闭事件
                        fclose($fd);
                    }
                }
                //正常读取到数据,触发消息接收事件,响应内容
                if(!empty($buffer) && is_callable($this->onMessage)){
                    call_user_func($this->onMessage,$fd,$buffer);
                }
            });
        });

    }

}


$worker = new Worker('tcp://0.0.0.0:9800');


//连接事件
$worker->onConnect = function ($fd) {
    //echo '连接事件触发',(int)$fd,PHP_EOL;
};

//消息接收
$worker->onMessage = function ($conn, $message) {
    //事件回调当中写业务逻辑
    //var_dump($conn,$message);
    $content="我是peter";
    $http_resonse = "HTTP/1.1 200 OK\r\n";
    $http_resonse .= "Content-Type: text/html;charset=UTF-8\r\n";
    $http_resonse .= "Connection: keep-alive\r\n"; //连接保持
    $http_resonse .= "Server: php socket server\r\n";
    $http_resonse .= "Content-length: ".strlen($content)."\r\n\r\n";
    $http_resonse .= $content;
    fwrite($conn, $http_resonse);
};

$worker->start(); //启动
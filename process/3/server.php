<?php
/**
 * Created by PhpStorm.
 * User: lijiefeng
 * Date: 2019/9/25
 * Time: 下午2:46
 */

/**
 * select、网络服务器
 */



class Worker{
    //监听socket
    protected $socket = NULL;
    //连接事件回调
    public $onConnect = NULL;
    //接收消息事件回调
    public $onMessage = NULL;
    public $workerNum=4; //子进程个数
    public  $allSocket; //存放所有socket

    public function __construct($socket_address) {
        //监听地址+端口
        $this->socket=stream_socket_server($socket_address);

//1、stream_set_blocking 当 socket处于阻塞模式时，
//比如：网络io fread系统调用必须等待socket有数据返回，即进程因系统调用阻塞；相反若处于非阻塞模式，内核不管socket数据有没有准备好，都会立即返回给进程。
//2、stream_set_blocking 另外进程阻塞和socket阻塞不是一个概念，进程阻塞是因为系统调用所致，socket是否阻塞只是说明socket上事件是不是可以内核即刻处理。


//1、select是系统调用，必然会阻塞进程的，和socket是否阻塞并没有关系，我第2点备注了呢。
//2、这里的IO就是针对socket的网络IO，是否是阻塞的，正是你题示所问的问题。
//3、socket之所以设置成非阻塞，是为了同一个进程里可以更多的处理更多的tcp连接，这正是 select、poll 或者 epoll等多路复用模型能够处理高并发的原因所在。



        stream_set_blocking($this->socket,0); //设置网络io 比如 fread 非阻塞


// 0是非阻塞，1是阻塞
//
//阻塞的意义是什么呢？
//
//某个函数读取一个网络流，当没有未读取字节的时候，程序该怎么办？
//
//是一直等待，直到下一个未读取的字节的出现，还是立即告诉调用者当前没有新内容？
//
//前者是阻塞的，后者是非阻塞的。
//
//阻塞的好处是，排除其它非正常因素，阻塞的是按顺序执行的同步的读取。
//
//借用小说里的说法就是“神刀出鞘，无血不归”。在读到新内容之前，它不会往下走，什么别的事情都不做。
//
//而非阻塞，因为不必等待内容，所以能异步的执行，现在读到读不到都没关系，执行读取操作后立刻就继续往下做别的事情。

        $this->allSocket[(int)$this->socket]=$this->socket;
    }
    public function start() {
        //获取配置文件
        $this->fork();
    }

    public function fork(){
        $this->accept();//子进程负责接收客户端请求
    }
    public  function  accept(){
        //创建多个子进程阻塞接收服务端socket
        while (true){
            $write=$except=[];
            //需要监听socket
            $read=$this->allSocket;
            //建议socket状态谁改变
           // var_dump($read);

            stream_select($read,$write,$except,60);//内核遍历循环哪些改变会阻塞 如果只有一个改变也会循环很多次的问题
            //怎么区分服务端跟客户端刚启动服务没有客户端连接进来socket 没有改变 当有新的客户端连接进来当前改变的是服务端所以循环read
            foreach ($read as $index=>$val){
                //循环每一个改变 返回响应
                //当前发生改变的是服务端，有连接进入
                if($val === $this->socket){
                    $clientSocket=stream_socket_accept($this->socket); //阻塞监听
                    //触发事件的连接的回调
                    if(!empty($clientSocket) && is_callable($this->onConnect)){
                        call_user_func($this->onConnect,$clientSocket);
                    }
                    $this->allSocket[(int)$clientSocket]=$clientSocket;//先把资源放入数组 客户端可写时循环响应避免阻塞
                }else{
                    //从连接当中读取客户端的内容
                    $buffer=fread($val,1024);
                    //如果数据为空，或者为false,不是资源类型
                    if(empty($buffer)){
                        if(feof($val) || !is_resource($val)){
                            //触发关闭事件
                            fclose($val);
                            unset($this->allSocket[(int)$val]);
                            continue;
                        }
                    }
                    //正常读取到数据,触发消息接收事件,响应内容
                    if(!empty($buffer) && is_callable($this->onMessage)){
                        call_user_func($this->onMessage,$val,$buffer);
                    }
                }
            }
        }

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
<?php
/**
 * Created by PhpStorm.
 * User: lijiefeng
 * Date: 2019/9/25
 * Time: 下午2:46
 */

/**
 * 多进程阻塞的网络服务器
 * 1 创建一个socket，绑定服务器端口（bind），监听端口（listen），在PHP中用stream_socket_server一个函数就能完成上面3个步骤
 * 2 进入while循环，阻塞在accept操作上，等待客户端连接进入。此时程序会进入睡眠状态，直到有新的客户端发起connect到服务器，操作系统会唤醒此进程。
 *   accept函数返回客户端连接的socket
 * 3 利用fread读取客户端socket当中的数据收到数据后服务器程序进行处理然后使用fwrite向客户端发送响应。
 *   长连接的服务会持续与客户端交互，而短连接服务一般收到响应就会close。
 * 缺点：
 * 虽然创建了多个进程处理但是还是不能保持长连接 必须关闭重新accept
 */




class Worker{


     protected $socket = null;

     public $onMessage = null;

     public $onConnect = null;

     public $workNum = 10;

     public function __construct($socket_address)
     {
         //绑定地址监听端口
         $this->socket = stream_socket_server($socket_address);
     }

    public function start() {
        //获取配置文件
        $this->fork(); //用来创建多个助教老师，创建多个子进程负责接收请求的
    }


     public function fork(){
         for ($i=0;$i<$this->workNum;$i++){
            $pid = pcntl_fork();//下面的代码父子进程都会执行
            if ($pid<0){
                exit('创建失败');
            }elseif($pid>0){
//                $status=0;
//                $pid=pcntl_wait($status);这边会阻塞等待子进程结束后在创建进程  所以放到for 后面  等待子进程创建 执行完成  回收子进程
//                echo "子进程回收了:$pid".PHP_EOL;
            }else{
                $this->accept();
                return; //这边要return 否则子进程还会创建子进程 因为fork 在for 循环里面当然这里在阻塞监听
            }
        }
         //放在父进程空间，结束的子进程信息，阻塞状态
         $status=0;
         $pid=pcntl_wait($status);
         echo "子进程回收了:$pid".PHP_EOL;
     }


    public  function  accept(){
        //创建多个子进程阻塞接收服务端socket
        while (true){
            $clientSocket=stream_socket_accept($this->socket); //阻塞监听
            var_dump(poLee_getpid());
            //触发事件的连接的回调
            if(!empty($clientSocket) && is_callable($this->onConnect)){
                call_user_func($this->onConnect,$clientSocket);
            }
            //从连接当中读取客户端的内容
            $buffer=fread($clientSocket,65535);
            //正常读取到数据,触发消息接收事件,响应内容
            if(!empty($buffer) && is_callable($this->onMessage)){
                call_user_func($this->onMessage,$clientSocket,$buffer);
            }
            // fclose($clientSocket); //必须关闭，子进程不会释放不会成功拿下进入accpet
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
     $http_resonse .= "Connection: keep-alive\r\n"; //连接保持
     $http_resonse .= "Server: php socket server\r\n";
     $http_resonse .= "Content-length: ".strlen($content)."\r\n\r\n";
     $http_resonse .= $content;
     fwrite($conn, $http_resonse);
 };
$server->start(); //启动
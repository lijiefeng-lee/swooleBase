<?php
/**
 * Created by PhpStorm.
 * User: Sixstar-Peter
 * Date: 2019/4/10
 * Time: 22:29
 */
include  __DIR__.'/socket_server.php';
class Job{

     protected  $worker_num=3;
     protected  $manager_num=2;
     protected  $msg_queue;
     public  function  __construct()
     {
         $this->master_pid=getmypid();
         $this->run();
         $this->monitor(); //进程监听,回收
     }
     //负责执行任务处理中心
     public  function  run(){
         $msg_key=ftok(__DIR__,'x'); //注意在php创建消息队列，第二个参数会直接转成字符串，可能会导致通讯失败
         $this->msg_queue=msg_get_queue($msg_key);
         //manager进程组
         for ($i=0;$i<$this->manager_num;$i++){
                $this->create_manager();
         }
         //worker进程组
         for ($i=0;$i<$this->worker_num;$i++){
              $this->create_worker();
         }
     }
     public  function  create_worker(){
         $process=new Swoole\Process(function ($process){
            while (true){
                //从消息队列当中去读取数据
                msg_receive($this->msg_queue,0,$message_type,1024,$message,false);
                //业务逻辑
                sleep(1);
                echo "worker进程".posix_getpid().":".$message.PHP_EOL;
            }

         });

         $process->start();
     }
    public  function  create_manager(){
        $process=new Swoole\Process(function ($process){
             $server=new Server('tcp://0.0.0.0:9800');
             $server->onMessage=function ($socket,$message){
                   //echo getmypid().'接收到消息:'.$message.PHP_EOL;
                 msg_send($this->msg_queue,1,$message,false,false);
             };
             $server->listen();
        });
        $process->start();
    }

    public function monitor(){

        swoole_process::signal(SIGRTMAX-4, function($sig) {
                $this->create_worker();
        });

        //回收以外
        while ($res=swoole_process::wait(false)){
            var_dump($res);
        }
        //检测队列长度,来决定是否要开启多个进程
        swoole_timer_tick(10,function (){
            $stat=msg_stat_queue($this->msg_queue);
            var_dump($stat);
            //超过50个开启一个子进程
            if($stat['msg_qnum']>10){
                var_dump('是否执行');
                //$this->create_worker();
                //直接创建有问题swoole禁止了，发送信号触发信号创建,
                //可以使用swoole的信号监听机制
                swoole_process::kill($this->master_pid,SIGRTMAX-4);
            }
        });


    }
}
$job=new job();
<?php
/**
 * Created by PhpStorm.
 * User: Sixstar-Peter
 * Date: 2019/4/10
 * Time: 20:58
 */

$worker=[];
for ($i=0;$i<3;$i++){
    $process=new Swoole\Process(function ($process){
        //子进程空间
//        while (true){
//            sleep(1);
//        }
        //var_dump("子进程:".$process->read());
        sleep(1);

        echo "子进程数据";
        //$process->write('子进程数据');

        echo posix_getpid().PHP_EOL;
    },true,true);

   $pid=$process->start();
   $worker[$pid]=$process;  //把相应的进程对象

   //$process->write("主进程数据");
   // var_dump("主进程:".$process->read().PHP_EOL);

    //异步监听管道当中的数据,读事件的监听,当管道可读的时候触发
    swoole_event_add($process->pipe,function ($pipe) use($process){
        var_dump("主进程:".$process->read().PHP_EOL);
    });
}


//先让子进程执行完毕,然后再读取阻塞
//foreach ($worker as $w){
//   var_dump($w->read().PHP_EOL);
//}
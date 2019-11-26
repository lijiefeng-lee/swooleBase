
<?php
/**
 * Created by PhpStorm.
 * User: Sixstar-Peter
 * Date: 2019/4/10
 * Time: 20:58
 */


for ($i=0;$i<3;$i++){
    $process=new Swoole\Process(function ($process){
        var_dump("子进程:".$process->pop());
        //$process->push("hello 主进程");
    });
    $process->useQueue(1,2|swoole_process::IPC_NOWAIT); //启用了消息队列
    $pid=$process->start();
    $process->push("hello 子进程"); //不能当做管道使用
    //echo "主进程打印的消息".$process->pop().PHP_EOL;
}


//先让子进程执行完毕,然后再读取阻塞
//foreach ($worker as $w){
//   var_dump($w->read().PHP_EOL);
//}


//进程回收
while ($res=swoole_process::wait()){
        var_dump($res);
}
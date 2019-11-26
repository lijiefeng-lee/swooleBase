<?php
/**
 * Created by PhpStorm.
 * User: lijiefeng
 * Date: 2019/11/1
 * Time: 下午3:56
 */

//tcp协议


//Task常见问题：task_ipc_mode 为1 即默认时 如果数据太大 大于8k  会写入临时文件 没有执行的数据不会继续执行


//Task常见问题：task_ipc_mode 为2 即队列模式 再次重启  会执行之前未执行的task任务 但需要指定队列key



//运行Task,必须要在swoole服务中配置参数task_worker_num,此外，必须给swoole_server绑定两个回调函数：onTask和onFinish。  onTask要return 数据 
//2、Task传递对象 默认task只能传递数据，可以通过序列化传递一个对象的拷贝,Task中对象的改变并不会反映到worker进程中数据库连接,网络连接对象不可传递,会引起php报错
//3、Task的onFinish回调 Task的onFinish回调会回调调用task方法的worker进程
//4、task_max_request 设置task进程的最大任务数。一个task进程在处理完超过此数值的任务后将自动退出。这个参数是为了防止PHP进程内存溢出。如果不希望进程自动退出可以设置为0
//5、每个woker都有可能投递任务给不同的task_worker处理, 不同的woker进程内存隔离,记录着worker_id, 标识woker进程任务处理数量


//task任务切分
//      除了直接将数据投递到task，由task分配处理之外，如果是投递过来的是数据类型的任务，也可以自己去指定分配进程去处理。
//把一个大的任务分配给几个空闲的进程来处理即任务拆分
//
//  场景：假设有一台服务器专门处理前台投递的数据,利用简单的任务拆分,分配到相应的进程去处理
//  1.将一个大的任务拆分成相应份数(是由$task_worker_num数量来确定)
//  2.通过foreach循环将数据投递到指定的task进程,范围是(0-(task_worker_num-1))区间之内
//  3.执行失败的任务，需要保留，重新投递执行（进程间通讯管道方式）


$server=new Swoole\Server("0.0.0.0",9800);   //创建server对象
$key = ftok(__DIR__,1);//队列key
$server->set([
    'worker_num'=>3, //设置进程
    'task_worker_num'=>3,  //task进程数 注意好后必须设置回调
    'task_ipc_mode'=>1,
    'message_query_key'=>$key //指定 队列key   在模式 1  时如果数据大于8K会写入临时文件但是重启后不会继续执行
    //在模式 2 或者3 设置 当队列数据大月8K 写入临时文件 还没有执行完  就关闭 重启后会继续执行
    //默认使用1 使用Unix Socket通信，默认模式可在task和taskwait方法中使用dst_worker_id，制定目标Task进程。
    //、     `dst_worker_id设置为-1时，底层会判断每个Task进程的状态，向当前状态为空闲的进程投递任务

    //模式2和模式3使用sysvmsg消息队列通信。模式2支持定向投递，$serv->task($data, $task_worker_id) 可以指定投递到哪个task进程。
    //模式3是完全争抢模式，task进程会争抢队列，将无法使用定向投递，task/taskwait将无法指定目标进程ID，即使指定了$task_worker_id，在模式3下也是无效的。
]);

$server->on('start',function (){

});


$server->on('workerStart',function ($server,$fd){

    //worker 和 taskWorker都会执行
    if($server->taskworker){
        echo 'task_worker:'.$server->worker_id.PHP_EOL;
    }else{
        echo 'worker:'.$server->worker_id.PHP_EOL;
    }

});


//监听事件,连接事件
$server->on('connect',function ($server,$fd){

});

$server->on('PipeMessage',function (swoole_server $server, int $src_worker_id,$message){
    echo "来自于{$src_worker_id}的错误信息".PHP_EOL;
    var_dump($message);
    //接收到投递的错误信息，记录错误次数，错误次数到达一定次数之后，就保留日志
});


//消息发送过来
$server->on('receive',function (swoole_server $server, int $fd, int $reactor_id, string $data){
    //var_dump("消息发送过来:".$data);

    //不需要立刻马上得到结果的适合task

    for ($i=0;$i<100;$i++){
        $tasks[] =['id'=>$i,'msg'=>time()];
    }
    $count=count($tasks);
    $data=array_chunk($tasks,ceil($count/3));
    foreach ($data as $k=>$v){
        $server->task($v,$k);  //(0-task_woker_num-1)注意是 编号不是进程号也不是worker进程的id号0 就是第一个worker 进程 以此类推
    }



//    $data=['tid'=>time()];
//    sleep(10);
//    $server->task($data,-1); //投递到taskWorker进程组第二个参数默认为1 范围是 0-（taskWorkerNum -1）
   // $server->task($data,-1);swoole 判断哪个是空闲的来投递给哪个task
    //服务端
});

//ontask事件回调
$server->on('task',function ($server,$task_id,$form_id,$data){
    // $task_id 任务id号 同步taskWaitMulti执行可以进行任务拆分异步是没有任务拆分的需要手动去拆分

//    $tasks[] = mt_rand(1000, 9999); //任务1
//    $tasks[] = mt_rand(1000, 9999); //任务2
//    $tasks[] = mt_rand(1000, 9999); //任务3
//    var_dump($tasks);
//
////等待所有Task结果返回，超时为10s
//    $results = $serv->taskWaitMulti($tasks, 10.0);
//
//    if (!isset($results[0])) {
//        echo "任务1执行超时了\n";
//    }
//    if (isset($results[1])) {
//        echo "任务2的执行结果为{$results[1]}\n";
//    }
//    if (isset($results[2])) {
//        echo "任务3的执行结果为{$results[2]}\n";

    try{
        foreach ($data as $k=>$v){
            if(mt_rand(1,5)==3){ //故意的去出现错误
                $server->sendMessage($v,1); //管道主动去通知worker进程，0 ~ (worker_num + task_worker_num - 1）
            }
        }
    }catch (\Exception $e){
        //$server->sendMessage();
    }
    $server->finish("执行完毕");


    var_dump(poLee_getpid());  //进程确实是发生了变化
    echo "任务来自于:$form_id".",任务id为{$task_id}".PHP_EOL;
    sleep(2);
    $server->finish("执行完毕");
});

$server->on('finish',function ($server,$task_id,$data){
    echo "任务{$task_id}执行完毕:{$data}".PHP_EOL;
});



//消息关闭
$server->on('close',function (){
    //echo "消息关闭".PHP_EOL;
});



//服务器开启
$server->start();


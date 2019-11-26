<?php
/**
 * Created by PhpStorm.
 * User: lijiefeng
 * Date: 2019/9/28
 * Time: 下午3:20
 */

//$pid = pcntl_fork();
//var_dump($pid);
//这个调用会输出两个值，但是我们如果直接print的只能看到一个值，也就是子进程的pid，但是使用var_dump我们就可以看到两个值，是0和子进程的pid。0这个值就是子进程返回过来的。

//$i=0;
// while($i!=4){
//     $pid = pcntl_fork();
//     if ($pid == 0) {
//         echo "子进程".PHP_EOL;
//         return;
//
//     }
//     echo $pid."---------hahah".$i++.PHP_EOL;
// }
//echo 1;



for ($i=0;$i<5;$i++){
    $pid = pcntl_fork();
    if ($pid<0){
        //创建失败
        exit('创建失败');
    }elseif($pid>0){
       // $pid = pcntl_wait($status); //子进程执行完毕 回收子进程 返回结束的子进程信息 返回子进程id我们可以通过设置pcntl_wait的第二个参数为WNOHANG来控制进程是否阻塞。
        echo '父进程'.$pid.PHP_EOL;        //父进程空间返回子进程id
    }else{
        echo '子进程'.$pid.PHP_EOL;//子进程空间
        sleep(10);

    }
}



//$i = 0;
//while($i < 2) {
//    $pid = pcntl_fork();
//    // 父进程和子进程都会执行以下代码
//    if ($pid == -1) { // 创建子进程错误，返回-1
//        die('could not fork');
//    } else if ($pid) {
//        // 父进程会得到子进程号，所以这里是父进程执行的逻辑
//        pcntl_wait($status); // 父进程必须等待一个子进程退出后，再创建下一个子进程。
//
//        $cid = $pid; // 子进程的ID
//        $pid = poLee_getpid(); // pid 与mypid一样，是当前进程Id
//        $myid = getmypid();
//        $ppid = poLee_getppid(); // 进程的父级ID
//        $time = microtime(true);
//        echo "I am parent cid:$cid   myid:$myid pid:$pid ppid:$ppid i:$i $time \n";
//    } else {
//        // 子进程得到的$pid 为0，所以这里是子进程的逻辑
//        $cid = $pid;
//        $pid = poLee_getpid();
//        $ppid = poLee_getppid();
//        $myid = getmypid();
//        $time = microtime(true);
//        echo "I am child cid:$cid   myid:$myid pid:$pid ppid:$ppid i:$i  $time \n";
//        //exit;
//        //sleep(2);
//    }
//    $i++;
//}


/**
 *
 *   $i = 0;
    while($i < 2) {
     $pid = pcntl_fork();
     // 父进程和子进程都会执行以下代码
    if ($pid == -1) { // 创建子进程错误，返回-1
     die('could not fork');
    } else if ($pid) {
    // 父进程会得到子进程号，所以这里是父进程执行的逻辑
         pcntl_wait($status); // 父进程必须等待一个子进程退出后，再创建下一个子进程。

        $cid = $pid; // 子进程的ID
        $pid = poLee_getpid(); // pid 与mypid一样，是当前进程Id
        $myid = getmypid();
        $ppid = poLee_getppid(); // 进程的父级ID
        $time = microtime(true);
        echo "I am parent cid:$cid   myid:$myid pid:$pid ppid:$ppid i:$i $time \n";
    } else {
     // 子进程得到的$pid 为0，所以这里是子进程的逻辑
        $cid = $pid;
        $pid = poLee_getpid();
        $ppid = poLee_getppid();
        $myid = getmypid();
        $time = microtime(true);
        echo "I am child cid:$cid   myid:$myid pid:$pid ppid:$ppid i:$i  $time \n";
    //exit;
    //sleep(2);
        }
        $i++;
    }
 * 1.运行shell命令(该进程ID是3471),生成主进程PID为6498

    开始循环i=0

    6498 此时的父进程
    |fork
    6499 父进程(6498阻塞),该子进程(6499)执行 ，输出：child cid:0 myid:6499 pid:6499 ppid:6498 i:0  1491394182.2065
    然后i++ i=1,再次循环
    继续循环i=1

    6499 此时的父进程
    |fork
    6500 父进程(6499阻塞),该子进程(6500)执行，输出：child cid:0  myid:6500 pid:6500 ppid:6499 i:1  1491394182.2077
    然后i++ i=2，本次循环终止,回到其主进程6499
    6499 解除阻塞，
    此时i=1(因为阻塞时i=1),继续执行  输出：parent cid:6500  myid:6499 pid:6499 ppid:6498 i:1  1491394182.2143
    然后i++ i=2,本次循环终止，回到其主进程6498
    6498 解除阻塞,
    此时i=0(因为阻塞时i=0),继续执行，输出：parent cid:6499  myid:6498 pid:6498 ppid:3471 i:0  1491394182.2211
    然后i++ i=1,再次循环
    继续循环i=1

    6498 此时的父进程
    |fork
    6501 父进程(6498阻塞),该子进程(6501)执行，输出：child cid:0  myid:6501 pid:6501 ppid:6498 i:1  1491394182.222
    然后i++ i=2,本次循环终止，回到其主进程6498
    6498 解除阻塞
    此时i=1(因为阻塞时为i=1),继续执行，输出：parent cid:6501 myid:6498 pid:6498 ppid:3471 i:1  1491394182.2302
    然后i++ i=2，本次循环终止，回到其主进程3471，最后命令结束。
 */


<?php
/**
 * Created by PhpStorm.
 * User: Leestar-Peter
 * Date: 2019/3/27
 * Time: 21:12
 */

namespace App\Listener;

/**
 * Task finish handler
 *
 * @Listener(start)
 */
class StartListener
{
    public  function  handle($params){
        var_dump("主进程启动事件触发");
        //创建协程环境
        go(function (){
            $cli = new \Swoole\Coroutine\http\Client("192.168.10.10", 9600);
            $ret = $cli->upgrade("/"); //升级的websockt
            if ($ret) {
                //Config::get();
                $data=[
                    'method'=>'register', //方法
                    'serviceName'=>'IM1',
                    'ip'=>'192.168.10.10',
                    'port'=>9801
                ];
                $cli->push(json_encode($data));
                //var_dump($cli->recv());

                //心跳处理
                swoole_timer_tick(3000,function ()use($cli){
                    if($cli->errCode==0){
                        $cli->push('',WEBSOCKET_OPCODE_PING); //
                    }
                });

            }
        });
    }

}
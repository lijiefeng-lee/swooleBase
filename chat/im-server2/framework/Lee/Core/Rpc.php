<?php
/**
 * Created by PhpStorm.
 * User: Leestar-Peter
 * Date: 2019/3/19
 * Time: 22:28
 */

namespace Lee\Core;

class Rpc
{
    public function listen($server,$tcp){
        $listen=$server->listen($tcp['host'], $tcp['port'],SWOOLE_SOCK_TCP);
        $listen->set($tcp['swoole_setting']);
        $listen->on("receive",[$this,'receive']);
    }
    public  function  receive(){
          //route->dispatch(); //分发到指定的服务当中
          //var_dump('222');
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: Leestar-Peter
 * Date: 2019/3/21
 * Time: 22:14
 */

namespace App\WebSocket;
class Test
{
    public  function  open($server,$request){
           var_dump("我是控制器");
    }
    public  function  message($server,$frame){

    }

    public  function  close($server,$fd){

    }


}
<?php
/**
 * Created by PhpStorm.
 * User: Leestar-Peter
 * Date: 2019/3/27
 * Time: 21:12
 */

namespace App\Listener;

/**
 *
 * @Listener(ws.open)
 */
class OpenListener
{
    public  function  handle(){
        var_dump("open事件触发");
    }
}
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
 * @Listener(workerStart)
 */
class WorkerStartListener
{
    public  function  handle(){
        var_dump("workerStart事件触发");
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: Leestar-Peter
 * Date: 2019/3/15
 * Time: 22:10
 */

namespace Lee;

use Lee\Core\Http;
use Lee\Core\WebSocket\WebSocket;


class App
{

    public function run($argv){
        //自定义常量
        define('ROOT_PATH',dirname(dirname(__DIR__)));
        define('FRAME_PATH',ROOT_PATH.'/framework');
        define('APP_PATH',ROOT_PATH.'/application');
        define('CONFIG_PATH',APP_PATH.'/config');
        define('EVENT_PATH',APP_PATH.'/Listener');
        try{
            switch ($argv[1]){
                case 'start':  //http服务
                    $http_server = new Http();
                    $http_server->run();
                    break;
                case 'ws:start': //websocket服务
                    $ws = new  WebSocket();
                    $ws->run();
                    break;
            }


        }catch (\Exception $e){
           echo '异常'.$e->getMessage().PHP_EOL;
        }catch (\Throwable $t){
            echo '错误'.$t->getMessage().PHP_EOL;
        }



    }



}
<?php
/**
 * Created by PhpStorm.
 * User: Sixstar-Peter
 * Date: 2019/3/21
 * Time: 22:51
 */

namespace Lee\Core\WebSocket;

/**
 * 通过路径触发控制器当中的方法
 * Class Dispatcher
 * @package Six\Core\WebSocket
 */
class Dispatcher
{
    public static function  open($server,$request,$path){
         try{
             //通过路径得到类名然后实例化
              $className=self::getClassName($path);
              if($className!=null){
                  $obj=new $className;
                  $obj->open($server,$request);
              }
         }catch (\Throwable $t){
             var_dump($t->getMessage());
         }
    }

    public static function  message($server, $path, $frame){
        try{
            //通过路径得到类名然后实例化
            $className=self::getClassName($path);
            if($className!=null){
                $obj=new $className;
                $obj->message($server,$frame,$frame->fd);
            }
        }catch (\Throwable $t){
            var_dump($t->getMessage());
        }
    }

    public static  function  close($server,$path,$fd){
        try{
            //通过路径得到类名然后实例化
            $className=self::getClassName($path);
            if($className!=null){
                $obj=new $className;
                $obj->close($server,$fd);
            }
        }catch (\Throwable $t){
            var_dump($t->getMessage());
        }
    }

    public static function getClassName($path=null){
             if($path==null){
                return null;
             }
             $nameSpace='App\WebSocket\\';
             $className=$nameSpace.ucfirst(strtolower(explode('/',$path)[1]));
             if(class_exists($className)){
                return $className;
             }
             return null;
    }
}
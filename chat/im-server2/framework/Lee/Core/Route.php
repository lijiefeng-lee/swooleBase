<?php

namespace Lee\Core;
class Route
{
    private static $route;
    private static $instance;
    private function __construct ()
    {
    }
    public static function  get_instance(){
        if(is_null (self::$instance)){
            self::$instance = new self();
        }
        return self::$instance;
    }
    /*
     * 记录我们的路由配置信息
     */
    public static function __callStatic($method,$param){
          self::$route[strtoupper($method)][]=$param; //保留方法
    }


    /**
     * @throws \Exception
     * @desc 自动路由
     */
    public  function dispatch(\swoole_http_request $request,\swoole_http_response $response)
    {
            $server=$request->server;
            //获取到默认的路由做一个自动加载
            if ('/favicon.ico' == $server['path_info']) {
                 return '';
             }
             //地址栏地址
             $method=$server['request_method']; //请求的方法
             $path=$server['path_info']; //请求的路径

             switch ($method){
                 case 'GET':
                     //遍历路由表
                     foreach (self::$route[$method] as $v){
                           //判断路径是否在已经注册的路由上
                           if(in_array($path,$v)){
                               return  $this->get($request,$response,$v[0],$v[1]);
                           }
                     }
                     break;
                 case 'POST':

                     break;
             }
    }

    /*get请求*/
    private  function get($request,$response,$path,$param){

        if($param instanceof \Closure){
            //判断是不是一个闭包
            $result=$param();
        }else{
            $index=explode('@', $param); //@分隔执行不同的文件
            $namespace="App"; //命名空间
            $class=$namespace.'\\'.ucfirst($index[0]).'\\Controller\\'.ucfirst($index[1]);
            $method=$index[2];
            $result=(new $class)->$method($request);
        }
        $response->end($result);
    }
}
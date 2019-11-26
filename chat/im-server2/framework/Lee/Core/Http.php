<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/19
 * Time: 14:18
 */

namespace Lee\Core;
use App\Listener\StartListener;
use Co\Exception;
use Lee\Core\Event\Event;
use  Swoole\Http\Server;
class Http
{

    public  $redis;
    public  function  run(){

        $config=Config::get_instance();
        $config->load(); //载入配置文件,在启动服务器之前载入的文件是不支持热加载

        $setting=$config->get('http');

        $this->server=new Server($setting['host'],$setting['port']);

        //设置进程数
        $this->server->set($setting['swoole_setting']);

        $this->server->on('request',[$this,'request']);

        $this->server->on('Start',[$this,'start']);

        //进程启动
        $this->server->on('workerStart',[$this,'workerStart']);

        //tcp服务端口监听
        if ((int)$setting['tcpable'] === 1) {
            $tcp=$config->get('tcp');
            $this->registerTcp($tcp);
        }
        $this->server->start();
    }

    public function  registerTcp($tcp){
           $rpc=new Rpc();
           $rpc->listen($this->server,$tcp);
    }
    public  function  request($request,$response){
        try {
            //加载默认路由
            Route::get_instance()->dispatch($request,$response);
        } catch (\Exception $e) { //程序异常
            var_dump($e->getMessage());
        }

    }
    public  function  start($server){
        $reload=Reload::get_instance();
        $reload->watch=[CONFIG_PATH,FRAME_PATH,APP_PATH];
        $reload->md5Flag=$reload->getMd5();
        //主动收集写在listener文件
        //$this->collectEvent();
        //定时器
        swoole_timer_tick(3000, function () use ($reload) {
            if ($reload->reload()) {
                $this->server->reload(); //重启
            }
        });
        //定义一个事件类常量
        Event::trigger('start',[$server]);
    }

    /**
     * 收集事件
     */
    public  function collectEvent(){
        $files = glob(EVENT_PATH."/*.php");
        if (!empty($files)) {
            foreach ($files as $dir => $fileName) {
                include $fileName;
                $fileName=explode('/',$fileName);
                $className=explode('.',end($fileName))[0];
                $nameSpace='App\\Listener\\'.$className;
                if(class_exists($nameSpace)){
                    $obj=new $nameSpace;
                    //希望得到自己定义的事件名称,通过反射读取类当中的文档注释
                    $re=new \ReflectionClass($obj);
                    $str=$re->getDocComment();

                    if(strlen($str)<2){
                            throw  new Exception("没有按照规则定义事件名称");
                    }else{
                        preg_match("/@Listener\((.*)\)/i",$str,$eventName);
                        if(empty($eventName)){
                            throw  new Exception("没有按照规则定义事件名称");
                        }
                        Event::register($eventName[1],[$obj,'handle']);
                    }
                }
            }

        }
        //


    }


    public  function  workerStart($server,$worker_id){
        //支持热加载
        $config=Config::get_instance();
        $config->loadLazy();
        //连接redis、连接数据库
        $this->redis=new \Redis();
        $this->redis->pconnect('127.0.0.1',6379);
        if($worker_id==0){
            Event::trigger('workerStart',['server'=>$server,'worker_id'=>$worker_id]);
        }
        //加载路由配置文件
        include_once APP_PATH.'/route.php';
    }
}
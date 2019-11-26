<?php
/**
 * Created by PhpStorm.
 * User: Leestar-Peter
 * Date: 2019/3/21
 * Time: 22:03
 */
use Swoole\WebSocket\Server;
class Route
{
    protected  $redis;
    protected  $table;
    public  function  run(){
        $this->server=new Server('0.0.0.0','9600');
        $this->server->set(['worker_num'=>4]);
        //$this->server->on('Start',[$this,'start']);
        $this->server->on('workerStart',[$this,'workerStart']);
        $this->server->on('open',[$this,'open']);
        $this->server->on('message',[$this,'message']);
        $this->server->on('close',[$this,'close']);
        $this->server->on('request',[$this,'request']);
        $this->createTable();
        $this->server->start();
    }

    public  function  createTable(){
        $this->table=new Swoole\Table(1024);
        $this->table->column('ack',swoole_table::TYPE_INT,1); //是否应答
        $this->table->column('num',swoole_table::TYPE_INT,1); //统计次数
        $this->table->create(); //
    }

    public  function workerStart($server,$worker_id){
        $this->redis=new Redis();
        include __DIR__."/Round.php";
        //$this->redis->setOption(); //超时时间，设置下socket超时时间
        $this->redis->pconnect('127.0.0.1',6379);
    }


    public  function  issue($id,$url){

        $key = "peter123456";
        $time=time();
        $token = array(
            "iss" => "http://peter.com",
            "aud" => "http://peter.cn",
            "iat" =>$time, //签发时间
            "nbf" => $time, //生效时间
            "exp" => $time+7200, //过期时间
            'data'=>[
                'uid'=>$id,
                'name'=>'peter'.$id,
                'service_url'=>$url
            ]
        );
        return \Firebase\JWT\JWT::encode($token,$key);
    }
    public  function request($request,$response){
        $response->header('Access-Control-Allow-Origin',"*");
        $response->header('Access-Control-Allow-Methods',"GET,POST,OPTIONS");
        //设置请求头
        $data=$request->post;
        switch ($data['method']){
            case 'login':
                //1.权限验证,颁发token
                //2.通过算法返回服务器地址跟token
                $url=json_decode($this->returnURL(),true);
                $url= $url['ip'].':'.$url['port'];
                $token=$this->issue($data['id'],$url);
                $response->end(json_encode(['token'=>$token,'url'=>$url]));
                break;
        }
    }

    public  function returnURL(){
        $arr=$this->redis->smembers("im_service");
        //$arr=['1.1.1.1','2.2.2.2','3.3.3.3'];
        if(!empty($arr)){
            return  Round::select($arr);
        }
        return false;
    }
    public  function open($server,$request){

    }
    public  function message($server,$frame){
        $data=json_decode($frame->data,true);
        $fd=$frame->fd;
        //var_dump($data);
        switch ($data['method']){
            case 'register':
                $service_key='im_service';
                echo $service_key;
                $value=json_encode([
                    'ip'=>$data['ip'],
                    'port'=>$data['port']
                ]);

                //$service_key=>118.24.109.254:9800
                //              118.24.109.254:9801
                $this->redis->sadd($service_key,$value);
                $redis=$this->redis;
                //会存在宕机的情况，主动清除
                $server->tick(3000,function ($id,$redis,$server,$service_key,$fd,$value){
                    //检测fd，是否存活
                    if(!$server->exist($fd)){
                        //删除redis当中的服务信息
                        $redis->srem($service_key,$value);
                        //清除定时器
                        $server->clearTimer($id);
                        var_dump('im宕机了，主动清除');
                    }
                },$redis,$server,$service_key,$fd,$value);
                break;
            case 'route_broadcast':
                $server_url=$data['target_server'];
                foreach ($server_url as $v){
                    //给IM-SERVER发送得我广播消息
                    $push_data=json_decode($v,true);
                    $this->send($push_data['ip'],$push_data['port'],$data['msg']);

                }
                break;
        }
    }
    public  function close($server,$fd){
    }

    public  function  send($ip,$port,$msg)
    {
        $token = $this->issue(0, '192.168.10.10:9600');
        $cli = new \Swoole\Coroutine\http\Client($ip, $port);
        //携带token
        $cli->setHeaders(['sec-websocket-protocol' => $token]);
        $ret = $cli->upgrade("/"); //升级的websockt
        if ($ret) {
            $msg_id = session_create_id();
            $data = [
                'method' => 'route_broadcast', //方法
                'msg' => $msg,
                'msg_id' => $msg_id
            ];
            $this->table->set($msg_id, ['num' => 0]);
            $cli->push(json_encode($data));
           // recv方法用于从服务器端接收数据。底层会自动yield，等待数据接收完成后自动切换到当前协程。


//              //不能发生阻塞
//                go(function () use ($msg_id, $cli, $data) {
//                    while (true) {
//                        Co::sleep(1); //每秒钟执行
//                        $ack_data=$cli->recv(0.2); //超时时间
//                        $ack_data=json_decode($ack_data->data,true);
//                        var_dump("定时器当中",$ack_data);
//                        if(isset($ack_data['method']) && $ack_data['method']=='server_ack'){
//                            $this->table->incr($ack_data['msg_id'],'ack');
//                        }
//                        $ack_data = $this->table->get($msg_id);
//                        if ($ack_data['ack'] > 0) {
//                            $cli->close();
//                            break;
//                        } elseif ($ack_data['num'] == 3) {
//                            //记录下异常
//                            $cli->close();
//                            break;
//                        } else {
//                            //重复发消息
//                            $cli->push(json_encode($data));
//                            var_dump($this->table->incr($msg_id, 'num')); //执行+1
//                            var_dump('执行次数' . $ack_data['num']);
//                        }
//                    }
//                });
//                echo '是否阻塞';
            }
//                $id = $this->server->tick(1000, function () use ($msg_id, &$id, $cli, $data) {
//
//                    $ack_data = $cli->recv(); //超时时间
//                    $ack_data = json_decode($ack_data->data, true);
//                    var_dump("定时器当中", $ack_data);
//                    if (isset($ack_data['method']) && $ack_data['method'] == 'server_ack') {
//                        $this->table->incr($ack_data['msg_id'], 'ack');
//                    }
//                    $ack_data = $this->table->get($msg_id);
//                    if ($ack_data['ack'] > 0) {
//                        swoole_timer_clear($id);
//                        $cli->close();
//                        return;
//                    } elseif ($ack_data['num'] == 3) {
//                        //记录下异常
//                        swoole_timer_clear($id);
//                        $cli->close();
//                        return;
//                    }
//                    //重复发消息
//                    $cli->push(json_encode($data));
//                    $this->table->incr($msg_id, 'num'); //执行+1
//                    var_dump('定时器id' . $id);
//                });
        }

        //1.查询对端是否确认(消息序号)


        //2.有限重试(重试两次)

    //}
}
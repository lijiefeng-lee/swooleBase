<?php
/**
 * Created by PhpStorm.
 * User: Leestar-Peter
 * Date: 2019/3/27
 * Time: 21:12
 */

namespace App\Listener;
use Co\Exception;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\SignatureInvalidException;

/**
 *
 * @Listener(ws.hand)
 */
class HandshakeListener
{
    public  function  handle($event){
        var_dump("握手事件触发");
        $request=$event['request'];
        $response=$event['response'];
        $redis=$event['redis'];
        //1.验证token是否正确，成功就握手，否则直接断开连接
        //通过判断websocket请求头当中是否有一个有效token
        if (!isset($request->header['sec-websocket-protocol'])) {
            $response->end();
            return false;
            //jwt验证
        }
        $flag=0;
        try{
            $token=$request->header['sec-websocket-protocol'];
            $key='peter123456';
            $arr=JWT::decode($token,$key,['HS256']);
            $userInfo=$arr->data;
            $key=$userInfo->service_url;
            //118.24.109.254:9800=> uid =>fd+name
            //                      uid =>fd+name
            //绑定路由关系,通过id来查找fd
            $redis->hset($key,$userInfo->uid,json_encode(['fd'=>$request->fd,'name'=>$userInfo->name]));

        }catch (SignatureInvalidException $e) {  //签名错误
               $flag=1;
                var_dump($e->getMessage());
        }catch (ExpiredException $e){  //token过期
                $flag=1;
                var_dump($e->getMessage());
        }catch (Exception $e){
               $flag=1;
               var_dump($e->getMessage());
        }
        if($flag>0){
            $response->end();
            return false;
        }
        // websocket握手连接算法验证
        $secWebSocketKey = $request->header['sec-websocket-key'];
        $patten = '#^[+/0-9A-Za-z]{21}[AQgw]==$#';
        if (0 === preg_match($patten, $secWebSocketKey) || 16 !== strlen(base64_decode($secWebSocketKey))) {
            $response->end();
            return false;
        }
        echo $request->header['sec-websocket-key'];
        $key = base64_encode(sha1(
            $request->header['sec-websocket-key'] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11',
            true
        ));

        $headers = [
            'Upgrade' => 'websocket',
            'Connection' => 'Upgrade',
            'Sec-WebSocket-Accept' => $key,
            'Sec-WebSocket-Version' => '13',
        ];

        // WebSocket connection to 'ws://127.0.0.1:9502/'
        // failed: Error during WebSocket handshake:
        // Response must not include 'Sec-WebSocket-Protocol' header if not present in request: websocket
        if (isset($request->header['sec-websocket-protocol'])) {
            $headers['Sec-WebSocket-Protocol'] = $request->header['sec-websocket-protocol'];
        }

        foreach ($headers as $key => $val) {
            $response->header($key, $val);
        }
        $response->status(101);
        $response->end();

    }
}
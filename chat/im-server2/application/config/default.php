<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/19
 * Time: 16:03
 */

return [
    'http'=>[
        'host' => '0.0.0.0',                //服务监听ip
        'port' => 9800,                      //监听端口
        'tcpable'=>0,
        'swoole_setting' => [               //swoole配置
            'worker_num' => 2,            //worker进程数量
            'daemonize' => 0,               //是否开启守护进程
            'pack_max_length'=>1024*1024*2,
            // 'upload_tmp_dir'=>__DIR__."/upload",
            // 'document_root' =>__DIR__,
            // 'enable_static_handler' => true
        ]
    ],
    'tcp'=>[
        'host' => '0.0.0.0',                //服务监听ip
        'port' => 8099,                      //监听端口
        'swoole_setting' => [               //swoole配置
            'worker_num' => 1 ,              //worker进程数量
            'pack_max_length'=>1024*1024*2,
            'worker_num'=>3,
        ]
    ],
    'ws'=>[
        'host' => '0.0.0.0',                //服务监听ip
        'port' => 9802,                      //监听端口
        'tcpable'=>0,                        //是否开启tcp监听
        'enable_http' => true,               //是否开启http服务
        'swoole_setting' => [               //swoole配置
            'worker_num' => 2,            //worker进程数量
            'daemonize' => 0,               //是否开启守护进程
            'pack_max_length'=>1024*1024*2,
            // 'upload_tmp_dir'=>__DIR__."/upload",
            // 'document_root' =>__DIR__,
            // 'enable_static_handler' => true
        ]
    ]
];
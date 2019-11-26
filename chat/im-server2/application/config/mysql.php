<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/19
 * Time: 15:11
 */
return [
    'mysql' => [
            'host' => '127.0.0.1',   //数据库ip
            'port' => 3306,          //数据库端口
            'user' => 'root',        //数据库用户名
            'password' => '123456', //数据库密码
            'database' => 'test',   //默认数据库名
            'timeout' => 0.5,       //数据库连接超时时间
            'charset' => 'utf8mb4', //默认字符集
            'strict_type' => true,  //ture，会自动表数字转为int类型
        ]
];
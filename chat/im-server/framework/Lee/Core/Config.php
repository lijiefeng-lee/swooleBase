<?php

namespace Lee\Core;

class Config
{

    /**
     * @var 配置map
     */
    public static $configMap = [];
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
     * 不支持热加载
     */
    public static function load()
    {
        self::$configMap = require CONFIG_PATH. '/default.php';
    }

    /**
     * 载入配置文件,可以热加载
     * 默认是application/config
     */
    public static function loadLazy()
    {
        $files = glob(CONFIG_PATH."/*.php");
        if (!empty($files)) {
            foreach ($files as $dir => $fileName) {
                self::$configMap+= include "{$fileName}";
            }
        }
    }

    /**
     * @desc 读取配置
     * @return string|null
     */
    public static function get($key)
    {
        if (isset(self::$configMap[$key])) {
            return self::$configMap[$key];
        }
        return false;
    }

}
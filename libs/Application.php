<?php
namespace pvf;

class Application {
    /**
     * 启动应用程序
     */
    public static function run() {
        // 初始化配置
        self::_init();
        // 启动应用程序
        Router::dispatch();
    }

    /**
     * 初始化配置
     */
    protected static function _init() {
        // 添加路由
        require_once root_path() . '/app/routes.php';
    }
}
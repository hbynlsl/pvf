<?php
namespace pvf;

/**
 * 轻量级自定义路由类
 * 支持GET/POST请求参数路由
 */
class Router {
    /**
     * 存储路由规则
     */
    protected static $routes = [
        'GET' => [],
        'POST' => []
    ];

    /**
     * 路由参数
     */
    protected static $params = [];

    /**
     * 启动应用程序
     */
    public static function dispatch() {
        $handler = self::handleRequest();
        if (is_callable($handler)) { // 可直接调用
            call_user_func($handler, self::$params);
        } else if (is_string($handler)) { // 字符串形式
            $flag = '@';
            if (strpos($handler, $flag) !== false) { // @符号（对象形式调用）
                
            } else if (strpos($handler, '::') !== false) { // ::符号（静态方法形式调用）
                $flag = '::';
            }
            // 分隔@
            $handler = explode($flag, $handler);
            // 判断类是否存在
            if (!class_exists($handler[0])) {
                $handler[0] = basename($handler[0], '\\');
                $handler[0] = "\\app\\controller\\" . $handler[0];
            }
            // 创建控制器类并调用它的方法
            if ($flag == '@') {
                call_user_func([new $handler[0], $handler[1]], self::$params);
            } else {
                call_user_func([$handler[0], $handler[1]], self::$params);
            }
        } else if (is_array($handler)) { // 数组形式（静态方法调用）
            // 判断类是否存在
            if (!class_exists($handler[0])) {
                $handler[0] = basename($handler[0], '\\');
                $handler[0] = "\\app\\controller\\" . $handler[0];
            }
            // 静态调用
            call_user_func([$handler[0], $handler[1]], self::$params);
        } else {
            // 未找到路由，返回404
            return json([
                'msg' => 'Route not found',
                'code' => 404,
                'data' => null
            ], 404);
        }
    }

    /**
     * 添加GET路由
     * @param string $pattern 路由模式
     * @param mixed $handler 处理函数
     */
    public static function get(string $pattern, mixed $handler) {
        self::$routes['GET'][$pattern] = $handler;
    }

    /**
     * 添加POST路由
     * @param string $pattern 路由模式
     * @param mixed $handler 处理函数
     */
    public static function post(string $pattern, mixed $handler) {
        self::$routes['POST'][$pattern] = $handler;
    }

    /**
     * 处理HTTP请求
     */
    protected static function handleRequest() {
        // 获取请求方法
        $method = strtoupper($_SERVER['REQUEST_METHOD']);
        if (!isset(self::$routes[$method])) {
            return json([
                'msg' => 'Unsupported HTTP method',
                'code' => 405,
                'data' => null
            ], 405);
        }
        // 获取请求URI
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        // 移除index.php部分
        if (strpos($uri, 'index.php') !== false) {
            $uri = str_replace('index.php', '', $uri);
        }
        // 匹配路由规则
        foreach (self::$routes[$method] as $routePath => $handler) {
            // 将路由模式转换为正则表达式（处理参数，如 :id 转为 ([^/]+)）
            $pattern = preg_replace('/:(\w+)/', '([^/]+)', $routePath);
            // 匹配请求URI
            if (preg_match("#^$pattern$#", $uri, $matches)) {
                // 移除匹配项中的完整匹配
                array_shift($matches);
                // 提取路由参数
                if (preg_match_all('/:(\w+)/', $routePath, $paramNames)) {
                    foreach ($paramNames[1] as $index => $name) {
                        self::$params[$name] = $matches[$index] ?? null;
                    }
                } 
                // 返回处理函数 
                return $handler;
            }
        }
    }
}
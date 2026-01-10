<?php

use pvf\DB;
use pvf\View;

/**
 * 获取数据库对象
 */
function db() {
    return DB::getInstance();
}

/**
 * 发送JSON响应
 * @param array $data 响应数据
 * @param int $statusCode HTTP状态码
 */
function json(array $data, int $statusCode = 200) {
    // 设置响应头
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *'); // 跨域支持
    // 输出JSON（保留中文不转义）
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * 显示模板视图
 * @param string $viewFile 视图文件名称
 * @param array $vars 视图变量数组
 */
function view($viewFile, $vars = []) {
    $view = new View();
    if (!empty($vars)) {
        $view->assign($vars);
    }
    return $view->fetch($viewFile);
}

/**
 * 从当前文件目录向上查找项目根目录
 * 匹配规则：目录下同时存在 public + vendor 两个目录
 * @param string $startDir 开始查找的目录(默认当前文件所在目录)
 * @param int $maxDepth 最大递归层数，防止死循环
 * @return string|false 成功返回根目录绝对路径，失败返回false
 */
function root_path(string $startDir = __DIR__, int $maxDepth = 30): string|false {
    // 规范化路径，去除多余分隔符，转为绝对路径
    $currentDir = realpath($startDir);
    $depth = 0;
    // 向上查找：目录存在 + 未超过最大层数
    while ($currentDir && is_dir($currentDir) && $depth < $maxDepth) {
        // 核心匹配：同时存在 public、vendor 两个目录
        $hasPublic  = is_dir($currentDir . DIRECTORY_SEPARATOR . 'public');
        $hasVendor  = is_dir($currentDir . DIRECTORY_SEPARATOR . 'vendor');
        if ($hasPublic && $hasVendor) {
            // 找到根目录，返回【结尾无/】的绝对路径，和laravel base_path()一致
            return $currentDir;
        }
        // 未找到，获取当前目录的上级目录，继续循环
        $currentDir = realpath($currentDir . DIRECTORY_SEPARATOR . '..');
        $depth++;
    }
    // 查找失败：未找到符合条件的目录 或 超过最大递归层数
    return false;
}

/**
 * 原生PHP解析根目录下的.env文件，加载配置到系统环境
 * @param string $param 待获取的参数项
 * @return string 获取到的参数项的内容
 * @example 使用 `$_ENV['参数项']` 或 `getenv('参数项')` 或 `env('参数项')` 方式调用
 */
function env($param = '') {
    // 1. 拼接ini配置文件的绝对路径
    $envFile = root_path() . DIRECTORY_SEPARATOR . '.env';
    // 2. 判断文件是否存在，不存在返回默认值
    if (!file_exists($envFile) || !is_readable($envFile)) {
        return '';
    }
    // 3. 逐行读取文件内容开启分段解析(true)
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $segment = '';
    $segmentArray = [];
    foreach ($lines as $line) {
        $line = trim($line);
        // 跳过 注释行(#开头) 和 非键值对的行
        if (str_starts_with($line, '#')) {
            continue;
        } else if (str_starts_with($line, '[') && str_ends_with($line, ']')) { 
            // 处理上一段
            if ($segment) {
                $_ENV[$segment] = $segmentArray;
                $sajson = json_encode($segmentArray);
                putenv("$segment=$sajson}");
                $segmentArray = [];
            }
            // 记录当前段
            $segment = trim($line, '[]');
            continue;
        }
        // 分割 键=值 （只分割第一个=，兼容值中包含=的情况）
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        $segmentArray[$key] = $value;
        // 存入系统环境变量，供全局读取
        if ($segment) {
            $key = $segment . '.' . $key;
        }
        $_ENV[$key] = $value;
        putenv("$key=$value");
    }
    // 栅栏柱问题
    $_ENV[$segment] = $segmentArray;
    $sajson = json_encode($segmentArray);
    putenv("$segment=$sajson}");
    // 返回结果
    if ($param) {
        return $_ENV[$param];
    }
    return '';
}

/**
 * 读取配置文件（.ini）
 * @param string $file 配置文件名称
 * @param string $key 配置项
 * @param string $default 默认配置项结果
 * @return string|array 指定配置文件的配置项或所有配置项
 * @example `config('database', '参数项')`
 */
function config(string $file = 'app', string $key = '', $default = '') {
    // 1. 拼接ini配置文件的绝对路径
    $iniFile = root_path() . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . $file . '.ini';
    // 2. 判断文件是否存在，不存在返回默认值
    if (!file_exists($iniFile) || !is_readable($iniFile)) {
        return $default;
    }
    // 3. 读取ini文件，开启分段解析(true)，返回二维数组，和ini分段对应
    static $configCache = []; // 静态缓存，避免重复读取文件，提升性能
    if (!isset($configCache[$file])) {
        $configCache[$file] = parse_ini_file($iniFile, true) ?: [];
    }
    $config = $configCache[$file];
    // 4. 如果不传key，返回整个配置文件的二维数组
    if (empty($key)) {
        return $config;
    }
    // 5. 核心：解析 "分段.配置项" 格式的key 如 database.dbname
    $keys = explode('.', $key);
    $currentValue = $config;
    foreach ($keys as $segment) {
        if (!is_array($currentValue) || !isset($currentValue[$segment])) {
            // 找不到对应分段/配置项，返回默认值
            return $default;
        }
        $currentValue = $currentValue[$segment];
    }
    // 6. 返回最终匹配到的配置值
    return $currentValue;
}
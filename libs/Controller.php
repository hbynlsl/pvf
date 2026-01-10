<?php
namespace pvf;

class Controller {
    /**
     * 视图模版
     */
    protected $view = '';

    /**
     * 发送JSON响应
     * @param array $data 响应数据
     * @param int $statusCode HTTP状态码
     */
    public function json(array $data, int $statusCode = 200) {
        json($data, $statusCode);
    }

    /**
     * 渲染模版视图
     * @param string $view 待渲染的模版视图（若未传入，使用本控制器方法）
     */
    public function fetch($view = null) {
        if (!$view) {
            // 获取当前类名
            $className = strtolower(str_replace('Controller', '', substr(strrchr(get_class($this), '\\'), 1)));
            // 获取子类的方法名
            $trace = debug_backtrace();
            $callerMethod = $trace[1]['function'];
            // 拼接视图文件
            $view = $className . '/' . $callerMethod;
        }
        // 渲染视图
        echo $this->view->fetch($view);
    }

    /**
     * 为模版变量赋值
     * @param array|string $params 待赋值的模版变量（数组或键）
     * @param string $value 待赋值的值
     */
    public function assign($params, $value = '') {
        $this->view->assign($params);
    }

    /**
     * 初始化
     */
    public function __construct() {
        // 设置模版
        $this->view = new View();
        // 设置模版目录
        $this->view->setViewPath(root_path() . '/app/view');
        // 设置缓存目录
        $this->view->setCachePath(root_path() . '/runtime/cache');
    }
}
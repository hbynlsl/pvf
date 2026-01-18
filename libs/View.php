<?php
namespace pvf;
/**
 * 模拟ThinkPHP模板引擎（轻量版）
 */
class View {
    // 模板文件目录
    protected $viewPath = './view/';
    // 编译后缓存文件目录
    protected $cachePath = './runtime/cache/';
    // 模板变量
    protected $vars = [];
    // 模板继承的区块内容
    protected $blocks = [];


    /**
     * 设置模板目录
     * @param string $path
     */
    public function setViewPath(string $path): void {
        $this->viewPath = rtrim($path, '/') . '/';
    }


    /**
     * 设置缓存目录
     * @param string $path
     */
    public function setCachePath(string $path): void {
        $this->cachePath = rtrim($path, '/') . '/';
        // 自动创建缓存目录
        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }
    }


    /**
     * 分配模板变量
     * @param string|array $name 变量名（或键值对数组）
     * @param mixed $value 变量值
     */
    public function assign($name, $value = null): void {
        if (is_array($name)) {
            $this->vars = array_merge($this->vars, $name);
        } else {
            $this->vars[$name] = $value;
        }
    }


    /**
     * 渲染模板
     * @param string $template 模板文件名（不带后缀）
     * @return string 渲染后的HTML
     */
    public function fetch(string $template): string {
        $templateFile = $this->viewPath . $template . '.html';
        if (!file_exists($templateFile)) {
            throw new \Exception("模板文件不存在：{$templateFile}");
        }
        // 1. 读取模板文件
        $content = file_get_contents($templateFile);
        // 2. 实时编译模板（compile方法完全不变）
        $content = $this->compile($content);
        // var_dump($content);exit;
        // 3. 提取变量到当前作用域
        extract($this->vars, EXTR_OVERWRITE);
        // 4. 渲染编译后的PHP代码
        ob_start();
        eval('?>' . $content); 
        return ob_get_clean();
    }


    /**
     * 编译模板（核心：将模板语法转为PHP代码）
     * @param string $content 模板内容
     * @return string 编译后的PHP代码
     */
    protected function compile(string $content): string {
        // 兼容 {$变量} 和 {$变量|默认值} 和 {$变量|json} 三种语法
        $content = preg_replace_callback('/\{\$\s*([a-zA-Z0-9_\-]+)\s*(\|.+?)?\}/', function ($matches) {
            $varName = trim($matches[1]);
            $filter = isset($matches[2]) ? trim(trim($matches[2]), '|') : '';
            
            // ✅ 新增：如果过滤器是json，直接用json_encode转原生JS值，带中文不转义
            if ($filter === 'json') {
                return '<?php echo json_encode(isset($'.$varName.') ? $'.$varName.' : null, JSON_UNESCAPED_UNICODE); ?>';
            }
            // 原有的默认值逻辑
            $default = $filter;
            return '<?php echo $this->escape( isset($'.$varName.') && $'.$varName.' !== null ? $'.$varName.' : "'.$default.'" ); ?>';
        }, $content);
        // 1. 模板继承 {extend name="base"}
        $content = preg_replace_callback(
            '/\{extend\s+name\s*=\s*["\'](.*?)["\']\s*\}/',
            function ($matches) {
                $parentFile = $this->viewPath . $matches[1] . '.html';
                return file_exists($parentFile) ? file_get_contents($parentFile) : '';
            },
            $content
        );

        // 2. 区块替换 {block name="content"}{/block}
        $content = preg_replace_callback(
            '/\{block\s+name\s*=\s*["\'](.*?)["\']\s*\}(.*?)\{\/block\}/is',
            function ($matches) {
                return '<?php ob_start(); ?>'.$matches[2].'<?php $this->blocks["'.$matches[1].'"] = ob_get_clean(); ?>';
            },
            $content
        );
        $content = preg_replace(
            '/\{__block__\s+name\s*=\s*["\'](.*?)["\']\s*\}/',
            '<?php echo isset($this->blocks["$1"]) ? $this->blocks["$1"] : ""; ?>',
            $content
        );

        // 3. 核心变量解析 + 默认值 + 容错 + XSS转义 
        $content = preg_replace_callback('/\{\$\s*([a-zA-Z0-9_]+)\s*(\|.+?)?\}/', function ($matches) {
            $varName = trim($matches[1]);
            $default = isset($matches[2]) ? trim(trim($matches[2]), '|') : '';
            return '<?php echo $this->escape( isset($'.$varName.') && $'.$varName.' !== null ? $'.$varName.' : "'.$default.'" ); ?>';
        }, $content);

        // 4. 条件判断标签 容错处理
        $content = preg_replace('/\{if\s+(.*?)\}/', '<?php if(isset($1) && $1) { ?>', $content);
        $content = preg_replace('/\{\elseif\s+(.*?)\}/', '<?php  } elseif(isset($1) && $1) { ?>', $content);
        $content = preg_replace('/\{else\}/', '<?php } else { ?>', $content);
        $content = preg_replace('/\{\/if\}/', '<?php } ?>', $content);

        // 5. 循环标签 容错处理
        $content = preg_replace_callback('/\{foreach\s+(.*?)\s+as\s+(.*?)\}/', function ($matches) {
            $arrName = trim($matches[1], '$');
            $itemStr = trim($matches[2], '$');
            return '<?php if(isset($'.$arrName.') && is_array($'.$arrName.')) foreach($'.$arrName.' as $'.$itemStr.') { ?>';
        }, $content);
        $content = preg_replace('/\{\/foreach\}/', '<?php } ?>', $content);        

        return $content;
    }


    /**
     * 变量转义（防XSS）
     * @param mixed $value
     * @return string
     */
    public function escape($value): string {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}
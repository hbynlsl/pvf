<?php
namespace pvf;

/**
 * 数据库操作类（基于Medoo库）
 * @method array select(string $table, array $columns)
 * @method mixed select(string $table, string $column)
 * @method array select(string $table, array $columns, array $where)
 * @method mixed select(string $table, string $column, array $where)
 * @method array select(string $table, array $join, array $columns)
 * @method mixed select(string $table, array $join, string $column)
 * @method null select(string $table, array $columns, callable $callback)
 * @method null select(string $table, string $column, callable $callback)
 * @method null select(string $table, array $columns, array $where, callable $callback)
 * @method null select(string $table, string $column, array $where, callable $callback)
 * @method null select(string $table, array $join, array $columns, array $where, callable $callback)
 * @method null select(string $table, array $join, string $column, array $where, callable $callback)
 * @method mixed get(string $table, array|null $join = null, array|string|null $columns  = null, array|null $where = null)
 * @method bool has(string $table, array $where)
 * @method mixed rand(string $table, array|string $column, array $where)
 * @method int count(string $table, array $where)
 * @method string max(string $table, string $column)
 * @method string min(string $table, string $column)
 * @method string avg(string $table, string $column)
 * @method string sum(string $table, string $column)
 * @method string max(string $table, string $column, array $where)
 * @method string min(string $table, string $column, array $where)
 * @method string avg(string $table, string $column, array $where)
 * @method string sum(string $table, string $column, array $where)
 */
class DB {
    // 静态保存Medoo单例实例
    private static ?Medoo $instance = null;

    // 私有化构造方法，禁止外部实例化
    private function __construct(){}

    // 私有化克隆方法，禁止克隆实例
    private function __clone(){}

    // ✅ 核心：获取Medoo单例实例
    public static function getInstance(): Medoo {
        if (self::$instance === null) {
            // 创建实例
            self::$instance = new Medoo($_ENV[$_ENV['app.dbtype']]);
        }
        return self::$instance;
    }

    // ✅ 核心魔术方法：实现【所有静态方法】转发到Medoo实例
    public static function __callStatic(string $method, array $arguments) {
        // 调用Medoo实例的对应方法，并传递参数
        return call_user_func_array([self::getInstance(), $method], $arguments);
    }
}
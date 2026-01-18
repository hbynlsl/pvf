<?php
use pvf\Router;
// 应用程序路由规则

Router::get('/hello', function($params) {
    echo 'hello page';
});
Router::get('/', 'IndexController@index');
Router::get('/about', 'IndexController::about');
Router::get('/show/:id', 'IndexController@show');
Router::get('/score', function($params) {
    // 数据库操作
    $rows = db()->count('score_querys');
    print_r($rows);
});
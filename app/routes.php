<?php
use pvf\Router;
// 应用程序路由规则

Router::get('/hello', function($params) {
    echo 'hello page';
});
Router::get('/', 'IndexController@index');
Router::get('/show/:id', 'IndexController@show');
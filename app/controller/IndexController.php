<?php
namespace app\controller;

use pvf\Controller;

class IndexController extends Controller {
    public function index($params) {
        $msgs = [
            '欢迎使用 PVF 框架！',
            '这是一个轻量级的 PHP MVC 框架。',
            '希望你喜欢它！',
        ];
        $this->assign([
            'msgs'  =>  $msgs,
        ]);
        return $this->fetch();
    }

    public function show($params) {
        return json([
            'show-id' => $params['id']
        ]);
    }

    public static function about($params) {
        $info = "这是一个关于页面。";
        echo $info;
    }
}
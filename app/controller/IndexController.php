<?php
namespace app\controller;

use pvf\Controller;

class IndexController extends Controller {
    public function index($params) {
        $text = "测试";
        $this->assign([
            'text'  =>  $text,
        ]);
        return $this->fetch();
    }

    public function show($params) {
        return json([
            'show-id' => $params['id']
        ]);
    }
}
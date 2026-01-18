## pvf
The php-vue framework project.

### 1. useage
（1）clone
```bash
git clone https://github.com/hbynlsl/pvf.git
```

（2）install

Go to the cloned dir, run the following commands in current terminal.
```bash
composer dump-autoload
npm install  # or pnpm install
```

（3）run

Open one terminal, run the php program.
```bash
php -S 127.0.0.1:88 -t public
```
Open the other terminal, run the vue program.
```bash
npm run dev
```

（4）build & public

Before public the app, you first should run the command `npm run build` in the terminal. Then the assets resources will created in `/public/assets` directory. The second step is uncomment the `/app/views/layout.html` file.

```html
<!-- ✅ 核心2：引入Vite的开发服务（开发环境） -->
<!-- <script type="module" src="http://localhost:5173/resources/js/app.js"></script> -->
<!-- 生产环境部署 -->
<script type="module" src="/assets/app.js"></script>
<link rel="stylesheet" href="/assets/style.css">
```

After that, upload the `/app`, `/public`, `/libs`, `/config`, `/vendor`, `/runtime`, `/.env` directories & files onto your server. The `/node_modules`, `/resources` directories are not need to upload.

### 2. php development
（1）routes

In the `/app/routes.php` file, define the app routes.
```php
<?php
use pvf\Router;

// get-route, callback
Router::get('/hello', function($params) {
    echo 'hello page';
});
// get-route, Controller@action
Router::get('/', 'IndexController@index');
// get-route, Controller::Action (the action must be static)
Router::get('/about', 'IndexController::About');
// get-route, dynamic params(such as :id)
Router::get('/show/:id', 'IndexController@show');
// post-route
Router::post('/submit', 'IndexController@handleSubmit');
```

（2）controller

In the `/app/controller` dir, create your own controllers. Note that if you want use the buildin view & db-function, the controller must be extends the `pvf\Controller`. 
```php
<?php
namespace app\controller;

use pvf\Controller;

class IndexController extends Controller {
    public function index() {
        ....
    }
}
```

After your controller extends the `pvf\Controller`, you can call `$this->assign($param, $val)` method pass the variable to the view-file. Also, you can use the `$this->fetch()` to show the view-file, by default the file name is the current action name, or you can transfer the exactly file-name to the `$this->fetch('your file name')` method.

（3）view

The view files are in the `/app/views` directory, the view-file's extension name is `html`.

① layout

Create the layout.html file, then the subfile can extends it.
```html
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>PHP + Vue3 开发轻量级框架</title>
    <!-- ✅ 核心1：CSRF令牌，axios会自动读取 -->
    <meta name="csrf-token" content="{$csrf_token|''}">
    <!-- ✅ 核心2：引入Vite的开发服务（开发环境） -->
    <script type="module" src="http://localhost:5173/resources/js/app.js"></script>
    <!-- 生产环境部署 -->
    <!-- <script type="module" src="/assets/app.js"></script>
    <link rel="stylesheet" href="/assets/style.css"> -->
    <style>
        .container { width: 800px; margin: 50px auto; }
    </style>
</head>
<body>
<div id="app">
    <!-- 区块占位 -->
    {__block__ name="content"}
</div>
</body>
</html>
```

② subfile

The subfile can be in the subdirs as the controller names, such as `/app/views/index` means the `IndexController`, and the `/app/views/index/index.html` means the `IndexController@index` method.
```html
{block name="content"}
    <h1>text = {$text}</h1>
    
    <hello-world></hello-world>
{/block}

{extend name="layout"}
```

> i) Note that, the `{extend}` directive must be the last line code.

> ii) In this html file, you can use any html tags, or you can use any your own vue single file component.

③ variable

You can show the variable transfered by php in this way : `{$variableName}`. Otherwise, you can show the default value by `{$variableName | default-value}` 。

④ if-else

You can use the `{if}、{else}、{elseif}、{/if}` directives to show the conditional blocks.

⑤ foreach

You can use the `{foreach $msgs as $msg} {/foreach}` to show the loop infomations.

（4）db

The `pvf` framework use the `medoo` library as the default database handler, you can use `db()` helper function to get the handle objects. Then you can use any method as `medoo` library, such as `db()->select('users', '*', [where condition])`. The whole `medoo` document is in https://medoo.in/doc[https://medoo.in/doc] . 

The database configuation is in the `/.env` file, you can config your own database informations. 

```ini
[app]
debug = true
dbtype = sqlite
default_timezone = Asia/Shanghai

[mysql]
type = mysql
host = 127.0.0.1
port = 3306
dbname = test_db
username = root
password = 123456
charset = utf8mb4
```

（5）config

You can also create you own config files in the `/config` directory, the config file's extension name is `.ini`. Then you can use the helper function `config($filenameWithoutExtension, $configParam)` to get the config informations.

（6）helpers

The `pvf` framework has created some helper functions, these function can be used anywhere.

```php
root_path()             // get the root_path of the project
config($file, $param)   // get the config params, the config file is in /config directory
db()                    // get the database handler, as medoo object
view($viewFile)         // get the view template handler, as the controller method $this->fetch()
json(array $responseDatas, int $statusCode)           // response the json datas
```

### 3. vue development

The vue files are in the `/resources` directory, it uses `vite` build tools (the config is `/vite.config.js`). In the `pvf` project, there has added `ElementPlus` & `Vant` supported.

（1）`/resources/js`

The `/resources/js/app.js` file is the main bootstrap file, your `vue3` app will be created in this file. Otherwise, all of your components will be registered in this file. And your own components will be created in the `/resources/js/components` directory. All of your components are SPA component, so you can use any of the `Vue3` rules. 

（2）`/resources/css`

This directory will contain all of your own style files.
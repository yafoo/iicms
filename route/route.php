<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
Route::get('/', "home/Index/index");
Route::get('view/:id$', 'home/Article/view')->pattern(['id' => '\d+'])->ext('html');
Route::get(':name', 'home/Type/:name')->pattern(['name' => '(?!admin)(?!captcha)(?!install)\w+']);
Route::get('captcha/[:id]', "login/captcha");
return [
];
<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2015 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------

use com\captcha\Captcha;
use think\facade\Config;
use think\Validate;

Route::get('captcha/[:id]', "\\com\\captcha\\CaptchaController@index");

Validate::extend('captcha', function ($value, $id = '') {
    return captcha_check($value, $id);
});
Validate::setTypeMsg('captcha', ':attribute错误!');


/**
 * @param string $id
 * @param array  $config
 * @return \think\Response
 */
function captcha($id = '', $config = [])
{
    $captcha = new Captcha($config);
    return $captcha->entry($id);
}


/**
 * @param $id
 * @return string
 */
function captcha_src($id = '')
{
    return Url::build('/captcha' . ($id ? "/{$id}" : ''));
}


/**
 * @param $id
 * @return mixed
 */
function captcha_img($id = '')
{
	$src = captcha_src($id);
	return '<img src="' . $src . '?id='.time().'" title="看不清？点击更换" style="cursor:pointer;" onclick="this.src=\''.$src.'?id=\'+Math.random();" />';
}


/**
 * @param        $value
 * @param string $id
 * @param array  $config
 * @return bool
 */
function captcha_check($value, $id = '')
{
    $captcha = new Captcha((array)Config::get('captcha'));
    return $captcha->check($value, $id);
}

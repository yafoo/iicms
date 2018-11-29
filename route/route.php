<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

return [
	//Ê×Ò³
	'/'   => ['home/Index/index', ['method' => 'get']],
	//ÄÚÈÝ
	'view/:id$'   => ['home/Article/view', ['method' => 'get', 'ext'=>'html'], ['id' => '\d+']],
	//À¸Ä¿
	':name'   => ['home/Type/:name', ['method' => 'get', 'ext'=>''], ['name' => '(?!admin)(?!captcha)\w+']],
];
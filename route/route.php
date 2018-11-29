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
	//��ҳ
	'/'   => ['home/Index/index', ['method' => 'get']],
	//����
	'view/:id$'   => ['home/Article/view', ['method' => 'get', 'ext'=>'html'], ['id' => '\d+']],
	//��Ŀ
	':name'   => ['home/Type/:name', ['method' => 'get', 'ext'=>''], ['name' => '(?!admin)(?!captcha)\w+']],
];
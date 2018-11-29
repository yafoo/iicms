<?php
namespace app\admin\controller;

use think\facade\App;
use think\Controller;
use app\admin\model\Sysinfo;

class Syscache extends Controller
{
	public function index()
	{
		if(request()->isPost()){
			deletedir(App::getRuntimePath() . 'cache/');
			deletedir(App::getRuntimePath() . 'log/');
			deletedir(App::getRuntimePath() . 'temp/');
			Sysinfo::setCache();
			return $this->success('操作成功！');
		}else{
			$this->assign('title','更新缓存');
			return $this->fetch();
		}
	}

}
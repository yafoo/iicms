<?php
namespace app\admin\controller;

use think\Controller;
use think\facade\App;

class Index extends Controller
{
	public function index()
	{
		//后台首页关闭日志
		config('app_debug',false);
		config('app_trace',false);
		
		$admin_nickname = model('user')->get(session('uid'))['nickname'];
		$menu_related = model('channel')->all(['related'=>1, 'state'=>1]);
		$menu_single = model('channel')->all(['related'=>0, 'state'=>1]);
		
		$this->assign('admin_nickname',$admin_nickname);
		$this->assign('menu_related',$menu_related);
		$this->assign('menu_single',$menu_single);
		return $this->fetch();
	}

	public function welcome()
	{
		$wel = [];
		$info_mysql = db()->query("select version() as v;");
		$wel['sql_v'] = $info_mysql['0']['v'];
		$wel['tp_v'] = App::version();
		
        $this->assign('wel', $wel);
		$this->assign('title', '欢迎使用YFcms！');
		return $this->fetch();
	}
	
	public function dialog()
	{
		$this->assign('title','dialog！');
		return $this->fetch();
	}
	
}
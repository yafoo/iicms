<?php
namespace app\admin\controller;

use think\Controller;

class Sysinfo extends Controller
{
	use \app\admin\traits\Curd;
	protected $model = 'sysinfo';
	protected $title = '系统参数';
	
	public function index()
	{
		if($this->request->isPost()){
			$sys = $this->request->post();
			$sysinfo = model('sysinfo');
			foreach($sys as $k=>$v){
				$sysinfo->isUpdate(true)->save(['content'=>$v], ['name'=>$k]);
			}
			
			return $this->success('保存成功！');
		}else{
			$sysinfo = model('sysinfo')->all();
			
			$this->assign('sysinfo',$sysinfo);
			$this->assign('title','系统设置');
			return $this->fetch();
		}
	}
	
}
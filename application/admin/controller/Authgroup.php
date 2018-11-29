<?php
namespace app\admin\controller;
use think\Controller;

class Authgroup extends Controller
{
	use \app\admin\traits\Curd;
	protected $model = 'auth_group';
	protected $title = '用户组';
	
	public function auth($id)
	{
		if($this->request->isPost()){
			$result = model('auth_group')->allowField(true)->save($this->request->post(), ['id'=>$id]);
			
			if($result === false) return $this->error('编辑失败！');
			else return $this->success('编辑成功！',url('index'));
		}else{
			$row = model('auth_group')->get($id);
			$authrule = model('auth_rule')->order('pid,id')->select();
			
			$this->assign('row',$row);
			$this->assign('authrule',$authrule);
			$this->assign('title','权限编辑');
			return $this->fetch();
		}
	}
	
}
<?php
namespace app\admin\controller;

use think\Controller;
use app\common\controller\UserApi;

class User extends Controller
{
	use \app\admin\traits\Curd;
	protected $model = 'user';
	protected $title = '用户管理';
	protected $up = 'face';
	
	protected $beforeActionList = [
		'checkData' => ['only'=>'add']
	];
	
	protected function checkData()
	{
		if($this->request->isPost()){
			$result = UserApi::check_reg_data($this->request->post());
			if(true !== $result) return $this->error($result);
		}
	}
	
	public function upFace()
	{
		return UserApi::up_face();
	}
	
}
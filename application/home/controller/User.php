<?php
namespace app\home\controller;

use think\Controller;

class User extends Controller
{
	public function index()
	{
		return $this->fetch();
	}
	
	public function edit($id)
	{
		if($this->request->isPost()){
			$data = $this->request->post();
			if(empty($data) || empty($data['user']) || empty($data['nickname'])) return "请填写完整信息！";
			if(empty($data['password'])) unset($data['password']);
			else if($data['password'] != $data['password2']) return "两次输入密码不一致！";
			unset($data['user']);
			$result = model('user')->allowField(true)->save($data, ['id'=>$id]);
			
			if($result == "success") return $this->success('编辑成功！','index');
			else return $this->error($result);
		}else{
			$user = model('user')->get($id);
			$this->assign('user',$user);
			$this->assign('title','用户编辑');
			return $this->fetch();
		}
	}
	
	public function upface()
	{
		return \app\common\controller\UpApi::up('lit', 'face');
	}
	
	public function regin()
	{
		$data = $this->request->post();
		if(empty($data) || empty($data['user']) || empty($data['password']) || empty($data['password2']) || empty($data['nickname'])) return "请完善注册信息！";
		if($data['password'] != $data['password2']) return "两次输入密码不一致！";
		
		$result = model('user')->allowField(true)->save($data);
		if(!$result) $this->error('注册失败！');
		return $this->success('注册成功！');
	}
	
}
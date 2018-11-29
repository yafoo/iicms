<?php
namespace app\admin\controller;

use think\Controller;
use app\common\controller\UserApi;

class Login extends Controller
{
	public function index()
	{
		if($this->request->isPost()){
			if(!captcha_check($this->request->post('captcha'))){
				return $this->error('验证码错误！');
			};
			
			if(!UserApi::login($this->request->post('user'), $this->request->post('password'))){
				return $this->error('账户或密码错误！');
			};
			
			return $this->success('登录成功！','admin/index/index');
		}else{
			$this->assign('title','后台登录');
			return $this->fetch();
		}
	}
	
	public function logout()
	{
		if(!UserApi::logout()){
			return $this->error('退出失败！');
		};
		
		return $this->success('退出成功！','index');
	}
}
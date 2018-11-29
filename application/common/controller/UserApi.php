<?php
namespace app\common\controller;

use app\common\model\User;
use app\common\controller\UpApi;

class UserApi
{
	public static function login($user, $password){
		$info = User::get(['user'=>$user]);
		if(empty($info['password']) || $info['password'] != User::passmd5($password, $info['salt'])) return 0;
		$info->logintime = time();
		$info->loginip = request()->ip();
		$info->allowField(['id', 'logintime', 'loginip'])->save();
		session('uid',$info['id']);
		return $info['id'];
	}
	
	public static function logout(){
		session('uid', null);
		if(null === session('uid')) return true;
		else return false;
	}
	
	public static function login_id(){
		if(session('uid') === null) return 0;
		else return session('uid');
	}
	
	public static function login_info(){
		if(session('uid') === null) return 0;
		else return session('uid');
	}
	
	public static function is_administrator(){
		if(session('uid') == 1) return true;
		else return false;
	}
	
	public static function register($data){
		return false;
	}
	
	public static function check_reg_data($data){
		return User::checkData($data);
	}
	
	public static function up_face(){
		return UpApi::up('lit', 'face');
	}
	
	public function check_password($uid, $password){
		$info = $this->get($uid); 
		if($info['password'] === User::passmd5($password, $info['salt'])){
			return true;
		}
		return false;
	}
	
}
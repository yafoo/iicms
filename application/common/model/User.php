<?php
namespace app\common\model;

use think\Model;
use think\Validate;

class User extends Model
{
	protected $autoWriteTimestamp = true;
	protected $createTime = 'addtime';
	protected $updateTime = false;
	protected $insert = ['password', 'ip'];
	protected static $rule = [
		'user'		=> 'require|max:50',
		'password'	=>  'require|min:6|max:50',
		'nickname'		=>  'require',
	];
	protected static $msg = [
		'user.require'   =>  '账户不能为空',
		'user.max'       =>  '账户名超出最大字符限制',
		'password.require'     =>  '密码不能为空',
		'password.min'   =>  '密码不能少于6位',
		'password.max'   =>  '密码超出最大字符限制',
		'nickname.require'     =>  '昵称不能为空',
	];
	protected $readonly = ['user', 'addtime', 'ip'];
	
	protected function setPasswordAttr($value)
	{
		$salt = self::randchar();
		$this->setAttr('salt', $salt);
		return self::passmd5($value, $salt);
	}
	
	protected function setIpAttr()
	{
		return request()->ip();
	}
	
	public static function checkData($data)
	{
		$vali = new Validate(self::$rule, self::$msg);
		$status = $vali->check($data);
		if(!$status) return $vali->getError();
		else return true;
	}
	
	private static function randchar($length = 8){
		$str = null;
		$strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
		$max = strlen($strPol)-1;
		for($i = 0; $i < $length; $i++){
			$str .= $strPol[rand(0,$max)];
		}
		return $str;
	}
	
	public static function passmd5($password, $salt){
		return md5(md5($password.$salt));
	}

}
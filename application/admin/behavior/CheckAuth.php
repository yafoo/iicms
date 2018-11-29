<?php
namespace app\admin\behavior;

use think\facade\Request;
use think\facade\Log;
use app\common\controller\UserApi;

/**
 * Description of CheckAuth
 * 检测用户权限行为
 * @author static7
 */
class CheckAuth {

	use \traits\controller\Jump;

	private $visit = [
		'allow'	=>	['admin/index/index', 'admin/index/welcome'], //允许任何人访问
		'deny'	=>	['admin/authgroup/index', 'admin/authgroup/add', 'admin/authgroup/edit', 'admin/authgroup/del'], //不允许任何人访问（超管除外）
	];
	private $uid; //登录用户
	private $admin; //超级管理员

	public function __construct() {
		$this->uid = UserApi::login_id();
		$this->admin = UserApi::is_administrator();
	}

	/**
	* 检测用户行为
	*/
	public function run() {
		if(strtolower(Request::controller()) == 'login') return true;
		if($this->uid < 1){
			session('login_from',Request::url());
			return $this->error('尚未登录！',url('admin/login/index'));
		}
		if($this->admin){
			Log::record("[ 权限 ]：超级管理员跳过");
			return true;
		}
		
		$status = false;
		$check = strtolower(Request::module() . '/' .Request::controller() . '/' . Request::action());
		$access = $this->accessControl($check);
		if (false === $access) {
			return $this->error('403:禁止访问!');
		} elseif (null === $access) {
			//检测访问权限
			$status = $this->checkRule($check, $this->uid);
		} else {
			return true;
		}
		Log::record("[ 权限 ]：已经检查" . $check ? $check : '');
		return $status ? true : $this->error('未授权访问!');
	}

    /**
     * 全局权限检测
     *
     */
	final protected function accessControl($check) {
		$allow = $this->visit['allow'];
		$deny = $this->visit['deny'];
		
		if ($deny && in_array($check, $deny)) {
			return false;
		}
		if ($allow && in_array($check, $allow)) {
			return true;
		}
		return null;
	}

    /**
     * 权限检测
     * @param string  $rule    检测的规则
     * @param string  $mode    check模式
     * @return boolean
     */
	final protected function checkRule($name, $uid) {
		static $Auth = null;
		if($Auth == null)
		$Auth = new \com\Auth();
		if (!$Auth->check($name, $uid)) {
			return false;
		}
		return true;
	}

}
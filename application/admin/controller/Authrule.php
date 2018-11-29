<?php
namespace app\admin\controller;
use think\Controller;

class Authrule extends Controller
{
	use \app\admin\traits\Curd;
	protected $model = 'auth_rule';
	protected $title = '权限规则';
}
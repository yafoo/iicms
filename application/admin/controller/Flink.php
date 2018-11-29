<?php
namespace app\admin\controller;
use think\Controller;

class Flink extends Controller
{
	use \app\admin\traits\Curd;
	protected $model = 'flink';
	protected $title = '友情链接';
}
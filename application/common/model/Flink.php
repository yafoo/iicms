<?php
namespace app\common\model;

use think\Model;

class Flink extends Model
{
	protected $autoWriteTimestamp = true;
	protected $createTime = 'addtime';
	protected $updateTime = false;
}
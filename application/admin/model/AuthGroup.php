<?php
namespace app\admin\model;

use think\Model;

class AuthGroup extends Model
{
	public function setRulesAttr($value)
	{
		return implode(',', $value);
	}
}
<?php
namespace app\common\model;

use think\Model;

class Addtb extends Model
{
	protected $pk = 'aid';
	
	protected function initialize()
	{
		$this->name = config('addtb')['addtable'];
		parent::initialize();
	}
}
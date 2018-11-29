<?php
namespace app\common\model;
use think\Model;

class Upload extends Model
{
	protected $autoWriteTimestamp = true;
	protected $createTime = 'addtime';
	protected $updateTime = false;
	
	public function user()
	{
		return $this->belongsTo('User','uid');
	}
	
	public static function init()
	{
		self::afterDelete(function($up){
			if($up->url){
				if(file_exists('.' . $up->url)) @unlink('.' . $up->url);
			}
		});
	}
}
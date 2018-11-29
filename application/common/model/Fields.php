<?php
namespace app\common\model;

use think\Model;
use \app\common\model\Channel;

class Fields extends Model
{
	public static function init()
	{
		self::afterWrite(function(){
			Channel::setCache();
		});
		
		self::afterDelete(function(){
			Channel::setCache();
		});
	}
	
	public function getItemsAttr($value, $data)
	{
		$arrs = [];
		if(empty($data['item'])) return $arrs;
		$arr = explode('|', $data['item']);
		if(!empty($arr)){
			foreach($arr as $vv){
				list($k, $v) = explode('=', $vv);
				$arrs[$k] = $v;
			}
		}
		return $arrs;
	}
}
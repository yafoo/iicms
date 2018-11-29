<?php
namespace app\common\model;

use think\Model;
use think\Db;
use think\facade\Cache;

class Channel extends Model
{
	protected static $cache = null;
	
	public static function init()
	{
		if(self::$cache === null){
			self::$cache = Cache::get('channel', '');
			if(!self::$cache) self::$cache =self::setCache();
			
			self::afterInsert(function($row){
				$fields = new Fields();
				$fields->saveAll([
					['cid'=>$row['id'], 'name'=>'aid', 'title'=>'ID', 'type'=>'fixed', 'sortrank'=>1, 'f_type'=>'int', 'f_length'=>11],
					['cid'=>$row['id'], 'name'=>'status', 'title'=>'状态', 'type'=>'fixed', 'sortrank'=>99, 'f_type'=>'tinyint', 'f_length'=>2]
				]);
			});
			
			self::afterWrite(function(){
				self::setCache();
			});
			
			self::afterDelete(function(){
				self::setCache();
			});
		}
	}
	
	public static function getCache()
	{
		self::init();
		return self::$cache;
	}
	
	public static function setCache()
	{
		self::init();
		$cache = '';
		$row = Db::name('channel')->order('id')->select();
		if($row){
			foreach($row as $v){
				$fields = Db::name('fields')->where('cid', $v['id'])->where('type', 'neq', 'fixed')->order('sortrank')->select();
				$v['fields'] = '';
				foreach($fields as $f){
					$v['fields'] .= $v['fields']=='' ? $f['name'] : ','.$f['name'];
				}
				
				$cache[$v['id']] = $v;
			}
		}else{
			$cache = '';
		}
		Cache::set('channel', $cache);
		return $cache;
	}
	
	public static function getAddtb($cid, $field = '')
	{
		self::init();
		if(!self::$cache) return '';
		if($field == '') return isset(self::$cache[$cid]) ? self::$cache[$cid] : '';
		return isset(self::$cache[$cid][$field]) ? self::$cache[$cid][$field] : '';
	}
	
	public static function getAddtable($cid)
	{
		self::init();
		if(!self::$cache) return '';
		return isset(self::$cache[$cid]['addtable']) ? self::$cache[$cid]['addtable'] : '';
	}
	
	public static function getAddname($cid)
	{
		self::init();
		if(!self::$cache) return '';
		return isset(self::$cache[$cid]['cname']) ? self::$cache[$cid]['cname'] : '';
	}
	
	public static function getAddfields($cid)
	{
		self::init();
		if(!self::$cache) return '';
		return isset(self::$cache[$cid]['fields']) ? self::$cache[$cid]['fields'] : '';
	}
	
}
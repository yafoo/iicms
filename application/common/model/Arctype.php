<?php
namespace app\common\model;

use think\Model;
use think\Db;
use think\facade\Cache;

class Arctype extends Model
{
	protected static $cache = null;
	
	public static function init()
	{
		if(self::$cache === null){
			self::$cache = Cache::get('type', '');
			if(!self::$cache) self::$cache =self::setCache();
			
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
		$cache = [];
		$row = Db::name('arctype')->field('id,pid,typename,typedir,channelid,ishidden')->order('sortrank,id')->select();
		if($row){
			$row = unlimitedForLevel($row);
			foreach($row as $v){
				$v['typeid'] = $v['id'];
				$v['url'] = $v['typedir'];
				$v['sonid'] = self::getSonid($v['id'], $row);
				$cache[$v['id']] = $v;
			}
		}else{
			$cache = [];
		}
		Cache::set('type', $cache);
		return $cache;
	}
	
	public static function getUrl($tid)
	{
		self::init();
		if(!self::$cache) return '';
		return isset(self::$cache[$tid]['typedir']) ? self::$cache[$tid]['typedir'] : '';
	}
	
	public static function getTypeName($tid)
	{
		self::init();
		if(!self::$cache) return '';
		return isset(self::$cache[$tid]['typename']) ? self::$cache[$tid]['typename'] : '';
	}
	
	public static function getPlace($tid, $cache = null)
	{
		if($cache === null){
			self::init();
			$cache = self::$cache;
		}
		if(!$cache) return '';
		
		$place = "";
		$place = ' &gt; <a href="'.$cache[$tid]['typedir'].'">'.$cache[$tid]['typename'].'</a>';
		if($cache[$tid]['pid'] != 0) $place = self::getPlace($cache[$tid]['pid'],$cache).$place;
		return $place;
	}
	
	public static function getSonid($tid, $cache = null)
	{
		if($cache === null){
			self::init();
			$cache = self::$cache;
		}
		
		$tid = intval($tid);
		$ids = '';
		
		if(!$cache) return $ids;
		
		$pid = [];
		$start = false;
		foreach($cache as $v){
			if($v['id'] != $tid && !$start){ $pid['pid_'.$v['pid']] = $v['pid']; continue;}
			if($v['id'] == $tid && !$start){ $start = true; $pid['pid_'.$v['pid']] = $v['pid']; continue;}
			if(isset($pid['pid_'.$v['pid']])) break;
			$ids .= $ids=='' ? $v['id'] : ','.$v['id'];
		}
		
		return $ids;
	}
	
	public function wchannel()
	{
		return $this->belongsTo('Channel', 'channelid');
	}
	
	public function getTypeidAttr($value,$data)
	{
		return $data['id'];
	}
}
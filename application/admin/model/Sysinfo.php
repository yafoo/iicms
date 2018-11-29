<?php
namespace app\admin\model;

use think\Model;
use think\facade\App;

class Sysinfo extends Model
{
	public static function init()
	{
		self::afterWrite(function(){
			self::setCache();
		});
		
		self::afterDelete(function(){
			self::setCache();
		});
	}
	
	public static function setCache()
	{
		$sys_str = "<?php\r\nreturn [\r\n";
		$sys = Sysinfo::all();
		foreach($sys as $v){
			$sys_str .= "	'".$v['name']."'		=>	'".$v['content']."',\r\n";
		}
		$sys_str .= "	'flag'		=>	['h'=>[1,'头条'],'c'=>[2,'推荐'],'f'=>[4,'幻灯'],'a'=>[8,'特荐'],'s'=>[16,'滚动'],'b'=>[32,'加粗'],'p'=>[64,'图片'],'j'=>[128,'跳转']]\r\n";
		$sys_str .= "];";
		
		$sys_file = App::getConfigPath().'sys.php';
		$fp = @fopen($sys_file,"w");
		@fwrite($fp,$sys_str);
		@fclose($fp);
		return true;
	}
}
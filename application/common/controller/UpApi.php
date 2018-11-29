<?php
namespace app\common\controller;

use app\common\controller\UserApi;

class UpApi
{
	public static $thumb;
	
	public static function up($ext = null, $dir = null)
	{
		switch($ext){
			case 'lit':
				$ext = 'img';
				$dir === null && $dir = "litpic";
				self::$thumb = true;
			case 'img':
				$ext = 'gif,jpg,jpeg,png';
				$dir === null && $dir = "image";
				break;
			case 'file':
				$ext = null;
			case '':
				$ext = null;
			case null:
				$ext = 'gif,jpg,jpeg,png,zip,rar,doc,xls,txt';
				$dir === null && $dir = "file";
				break;
			default:
				if($dir === null){
					$type = explode(',', $ext);
					foreach($type as $e){
						if($e != 'gif' && $e != 'jpg' && $e != 'jpeg' && $e != 'png'){
							$dir = "file";
							break;
						}
					}
					$dir === null && $dir = "image";
				}
		}
		
		$dir = config('sys.upload') . '/' . $dir;
		
		$file = request()->file('upfile');
		$info = $file->validate(['size'=>5242880, 'ext'=>$ext])->move('.' . $dir);
		if($info){
			$url = $dir . '/' . str_replace('\\', '/', $info->getSaveName());
			$title = htmlspecialchars($info->getInfo()['name']);
			$filesize = $info->getInfo()['size'];
			$result = model('upload')->save(["url"=>$url, "title"=>$title, "filesize"=>$filesize, "uid"=>UserApi::login_id(), "ip"=>request()->ip()]);
			if($result){
				if(self::$thumb) self::thumb($url);
				return ["state"=>1, "msg"=>"上传成功！", "url"=>$url, "size"=>$filesize];
			}else{
				@unlink("." . $url);
				return ["state"=>0, "msg"=>"保存失败！", "url"=>"", "size"=>0];
			}
		}else{
			return ["state"=>0, "msg"=>$file->getError(), "url"=>"", "size"=>0];
		}
	}
	
	public static function thumb($img)
	{
		$image = \com\Image::open('.'.$img);
		$image->thumb(config('sys.img_width'), config('sys.img_height'), \com\Image::THUMB_CENTER)->save('.'.$img);
	}
	
	public static function del($file = '')
	{
		//需做安全处理，防止乱删别人文件
		if(empty($file)) return null;
		
		$row = model('upload')->get(['url' => $file]);
		if($row) $row->delete();
		return true;
	}
	
	public static function set_use($file = '', $state = 1)
	{
		if(empty($file)) return false;
		$row = model('upload')->get(['url' => $file]);
		if(!$row) return false;
		$row->state = $state;
		$row->save();
		return true;
	}
	
}
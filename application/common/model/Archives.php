<?php
namespace app\common\model;

use think\facade\Request;
use think\facade\Config;
use think\Model;
use think\Validate;
use app\common\model\Channel;
use app\common\model\Arctype as Type;

class Archives extends Model
{
	protected $autoWriteTimestamp = true;
	protected $createTime = 'addtime';
	protected $updateTime = false;//更新点击时会造成此时间更新，所以关闭
	protected $auto = ['flag'];
	protected $rule = [
		'title'   => 'require|max:200',
		'typeid'     =>  'require',
		'channel'   =>  'require',
	];
	protected $msg = [
		'title.require'   =>  '标题不能为空',
		'title.max'       =>  '标题超出最大字符限制',
		'typeid.require'     =>  '栏目不能为空',
		'channel.require'   =>  '频道不能为空',
	];
	protected $rule_body = [
		'body'   =>  'require|min:10',
	];
	protected $msg_body = [
		'body.min'       =>  '正文内容不能少于10个字符',
	];
	
	//预载入需先设置channel
	public function addtb()
	{
		$cid = $this->channel;
		if($cid){
			$addtb = Channel::getAddtb($cid);
			Config::set('addtb', $addtb);
		}else{
			$addtb = Config::get('addtb');
		}
		if(empty($addtb['fields'])) return $this;
		return $this->hasOne('addtb', 'aid')->bind($addtb['fields']);
	}

	public function getUrlAttr($value,$data)
	{
		return "/view/".$data['id'].".html";
	}
	
	public function getTypeurlAttr($value,$data)
	{
		return Type::getUrl($data['typeid']);
	}
	
	public function getTypenameAttr($value,$data)
	{
		return Type::getName($data['typeid']);
	}

	public function setFlagAttr($value)
	{
		if(is_numeric($value)) return $value;
		else return flag($value);
	}
	
	public function getFlagtextAttr($value,$data)
	{
		$flags = Config::get('sys.flag');
		$str = flag($data['flag']);
		foreach($flags as $k => $v){
			$str = str_replace($k, $v[1], $str);
		} 
		return str_replace(',', ' ', $str);
	}
  
	public function auto_fill($data){
		$body = empty($data['body']) ? '' : $data['body'];
		if(empty($data['keywords']) && Config::get('sys.auto_keywords')) $data['keywords'] = $this->get_keywords($data['title'], $body);
		if(empty($data['description']) && Config::get('sys.auto_description')) $data['description'] = $this->get_description($body, $data['title'], Config::get('sys.auto_description'));
		
		//缩略图
		if(Request::file('litpic')){
			$image = $this->get_thumb(Request::file('litpic'));
			if($image){
				$data['litpic'] = $image;
				$data['flag'][] = 'p';
				if(!empty($data['litpic_old'])) @unlink('.'.$data['litpic_old']);
			}
		}elseif(empty($data['litpic_old']) && !empty($data['body']) && Config::get('sys.auto_litpic')){
			$image = $this->get_thumb($data['body']);
			if($image){
				$data['litpic'] = $image;
				$data['flag'][] = 'p';
			}
		}
		
		$data['uptime'] = time();
		$data['ip'] = Request::ip();
		return $data;
	}
	
	private function get_keywords($subject,$message,$encode = 'utf-8'){
		$subject = rawurlencode(strip_tags($subject));
		$message = strip_tags(htmlspecialchars_decode($message));
		$message = strip_tags($message);
		if(strlen($message)>2400){ //在线分词服务有长度限制
			$message =  mb_substr($message, 0, 800, $encode);
		}
		$message = rawurlencode($message);
		$url = 'http://keyword.discuz.com/related_kw.html?title='.$subject.'&content='.$message.'&ics='.$encode.'&ocs='.$encode;
		$xml_array=simplexml_load_file($url);                        //将XML中的数据,读取到数组对象中
		$result = $xml_array->keyword->result;
		$data = array();
		foreach ($result->item as $key => $value) {
			array_push($data, (string)$value->kw);
		}
		if(count($data) > 0){
			return implode(',',$data); //返回为,隔开字符
		}else{
			return false;
		}
	}
	
	private function get_description($message = '',$subject = '',$num = 120)
	{
		$message = strip_tags(htmlspecialchars_decode($message));
		$str_message = array(" ","　","\t","\n","\r","&nbsp;");//出去空格和换行
		$nbsp_message = array("","","","","");//替换为出去空格
		$reg_message = str_replace($str_message,$nbsp_message,$message);//替换
		$description = mb_substr($reg_message,0,$num,'utf-8');
		if($description){
			return $description;
		}else{
			return $subject;
		}
	}

	private function get_thumb($message){
		//$thumb =preg_replace('/\.(jpg|png|gif|bmp)/','_',$img).'182x100==.jpg';
		$thumb = Config::get('sys.upload').'/litpic/'.time().rand(100,999).'_182x100==.jpg';
		if(is_string($message)){
			$message = htmlspecialchars_decode($message);
			preg_match('/<img.*?src=[\'|\"](.*?\.(jpg|gif|bmp|bnp|png))[\'|\"].*?[\/]?>/i',$message,$match);
			if(!empty($match)){
				$img = '';
				if(strstr($match['1'],'http')){
					$img = @file_get_contents($match['1']);
					if(empty($img)) return '';
					$fp = @fopen('.'.$thumb,"w");
					@fwrite($fp,$img);
					@fclose($fp);
					$img = '.'.$thumb;
				}elseif(is_file('.'.$match['1'])) $img = '.'.$match['1'];
				if($img){
					$image = \com\Image::open($img);
					$image->thumb(Config::get('sys.img_width'),Config::get('sys.img_height'),\com\Image::THUMB_CENTER)->save('.'.$thumb);
				}else{
					return '';
				}
				
			}else{
				return '';
			}
		}else{
			$image = \com\Image::open($message);
			$image->thumb(Config::get('sys.img_width'),Config::get('sys.img_height'),\com\Image::THUMB_CENTER)->save('.'.$thumb);
		}
		
		return $thumb;
	}
  
	public function checkData($data)
	{
		if($data['channel']==1){
			$this->rule = array_merge($this->rule,$this->rule_body);
			$this->msg = array_merge($this->msg,$this->msg_body);
		}
		$vali = new Validate($this->rule,$this->msg);
		$status = $vali->check($data);
		if(!$status) return $vali->getError();
		else return;
	}
	

}
<?php
namespace app\home\controller;

use think\Controller;
use app\common\model\Channel;
use app\common\model\Arctype;

class Article extends Controller
{
	public function view($id)
	{
		$id = intval($id);
		if(empty($id) || $id < 0) return $this->error("非法的请求！");
		$_fields = model('archives')->get($id);
		if(false === $_fields) return $this->error("获取数据失败！");
		$_fields->click = $_fields->click + 1;
		$_fields->save();
		//追加附加表数据
		$_fields->appendRelationAttr('addtb', Channel::getAddfields($_fields->channel));

		$prev = model('archives')->where('typeid',$_fields['typeid'])->where('id','>',$id)->order('id','ASC')->find();
		$next = model('archives')->where('typeid',$_fields['typeid'])->where('id','<',$id)->order('id','DESC')->find();
		

		$this->assign("place", Arctype::getPlace($_fields['typeid']));
		$this->assign("_fields", $_fields);
		$this->assign("prev", $prev);
		$this->assign("next", $next);
		//根据频道类型选择模板
		return $this->fetch(str_replace("add_", "", Channel::getAddtable($_fields['channel'])));
	}
}
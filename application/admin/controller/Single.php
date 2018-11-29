<?php
namespace app\admin\controller;

use think\Controller;
use app\common\model\Channel;

class Single extends Controller
{
	use \app\admin\traits\Curd;
	protected $model = 'addtb';
	protected $title = '独立模型';
	protected $pk = 'aid';
	protected $redirect;
	protected $checkbox = '';
	//文档状态
	protected $arcranks = [
		'1'		=>	'已审核',
		'0'		=>	'待审核',
		'2'		=>	'已推荐',
		'-1'	=>	'回收站',
	];
	protected $beforeActionList = [
		'setAddtb',
		'setRedirect' => ['only'=>['add', 'edit']]
	];
	
	protected function setAddtb()
	{
		$cid = intval($this->request->param('cid'));
		$addtb = Channel::getAddtb($cid);
		config('addtb', $addtb);
		
		$fieldlist = model('fields')->where('cid', $cid)->where('type', 'neq', 'fixed')->order('sortrank')->select();
		$checkbox = [];
		foreach($fieldlist as $item)
			if($item->type == 'checkbox') $this->checkbox .= $this->checkbox == '' ? $item->name : ',' . $item->name;
		
		$this->assign('cid', $cid);
		$this->assign('arcranks', $this->arcranks);
		$this->assign('fieldlist', $fieldlist);
	}
	
	//审核
	public function check($check, $ids)
	{
		if(empty($ids)) return $this->error('请先选择要操作的文档！');
		$arr = ['tuijian'=>2, 'tongguo'=>1, 'quxiao'=>0, 'shibai'=>-1];
		if(!array_key_exists($check, $arr)) return $this->error('方法不存在！');
		model('addtb')->where('aid', 'in', $ids)->update(['status' => $arr[$check]]);;
		return $this->success('操作成功！');
	}
	
	//删除
	public function del($ids)
	{
		$ids = explode(',', $ids);
		foreach ($ids as $v){
			$v = intval($v);
			if(empty($v)) continue;
			model('addtb')->destroy($v);
		}
		return $this->success('删除成功！');
	}
	
	protected function setRedirect()
	{
		$this->redirect = url('index?cid=' . intval($this->request->param('cid')));
	}
	
}
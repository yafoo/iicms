<?php
namespace app\admin\controller;

use think\Controller;
use think\facade\Config;
use app\common\model\Channel;
use app\common\model\Arctype;

class Archives extends Controller
{
	//文档状态
	protected $arcranks = [
		'1'		=>	'已审核',
		'0'		=>	'待审核',
		'-1'	=>	'回收站',
	];
	
	public function index($arcrank = '')
	{
		$map = array();
		if($this->request->isPost()) $data = $this->request->post();
		else $data = $this->request->param();
		$order = "id";
		$rows = 15;
		
		if($arcrank == '') $map['arcrank'] = ['>',-1];
		else $map['arcrank'] = ['=',$arcrank];
		if(!empty($data['channelid']) && $data['channelid'] > 0) $map['channel'] = ['=',$data['channelid']];
		
		if(!empty($data['typeid']) && $data['typeid'] > 0){
			$map['typeid'] = ['=',$data['typeid']];
			$type = Arctype::getCache();
			if(!empty($type[$data['typeid']]['sonid'])) $map['typeid'] = ['exp', ' in ('.$data['typeid'].','.$type[$data['typeid']]['sonid'].')'];
		}
		if(!empty($data['keyword'])) $map['title'] = ['like','%'.$data['keyword'].'%'];
		if(!empty($data['flag'])) $map['flag'] = ['exp','& '.flag($data['flag']).'!=0'];
		if(!empty($data['order'])) $order = $data['order'];
		if(!empty($data['rows']) && $data['rows'] > 0) $rows = $data['rows'];
		
		//$list = model('archives')->all(function($query)use($map,$order){$query->where($map)->order($order,'desc');});
		$option = ['type'=>'\\app\\common\\paginator\\Meizi', 'var_page'=>'page', 'list_rows'=>$rows, 'query'=>$data];
		$list = model('archives')->where($map)->order($order,'desc')->paginate(null,false,$option);
		
		if(!empty($data['channelid']) && $data['channelid'] > 0) $typelist = unlimitedForLevel(model('arctype')->where(['channelid'=>$data['channelid']])->order('sortrank,id')->select());
		else $typelist = unlimitedForLevel(model('arctype')->order('sortrank,id')->select());
		$channels = model('channel')->all(['related'=>1, 'state'=>1]);
		
		$this->assign('opts', $data);
		$this->assign('channels', $channels);
		$this->assign('typelist', $typelist);
		$this->assign('arcranks', $this->arcranks);
		$this->assign('flags', Config::get('sys.flag'));
		$this->assign('list', $list);
		$this->assign('title', '所有文档');
		return $this->fetch();
	}
	
	public function add($cid = 1)
	{
		if($this->request->isPost()){
			$arc = model('archives');
			$data = $this->request->post();
			$result = $arc->checkData($data);
			if(!empty($result)) return $this->error($result);
			$data = $arc->auto_fill($data);
			
			if(!$arc->allowField(true)->save($data)) return $this->error('保存主表失败！');
			if(!$arc->addtb()->allowField(true)->save($data)){
				$arc->delete($arc->id);
				return $this->error('保存附加表失败！！');
			}
			return $this->success('添加成功！',url('index'));
		}else{
			$typelist = unlimitedForLevel(model('arctype')->where(['channelid'=>$cid])->order('sortrank,id')->select());
			$fieldlist = model('fields')->where('cid', $cid)->where('type', 'neq', 'fixed')->order('sortrank')->select();
			
			$this->assign('typelist', $typelist);
			$this->assign('fieldlist', $fieldlist);
			$this->assign('channel', $cid);
			$this->assign('arcranks', $this->arcranks);
			$this->assign('flags', Config::get('sys.flag'));
			$this->assign('title', '新增文档');
			return $this->fetch();
		}
	}
	
	public function edit($id)
	{
		if($this->request->isPost()){
			$arc = model('archives')->get($id);
			if(empty($arc)) return $this->error('文档不存在！');
			$addtb = $arc->addtb->getData();
			if(empty($addtb)) return $this->error('文档附加数据不存在！');
			
			$data = $this->request->post();
			if($data['channel'] != $arc['channel']) return $this->error('频道不允许修改！');
			$result = $arc->checkData($data);
			if(!empty($result)) return $this->error($result);
			$data = $arc->auto_fill($data);
			
			if(false === $arc->allowField(true)->save($data,['id'=>$id])) return $this->error('保存主表失败！');
			if(false === $arc->addtb->allowField(true)->save($data)) return $this->error('保存附加表失败！！');
			return $this->success('编辑成功！',url('index'));
		}else{
			$arc = model('archives')->get($id);
			if(empty($arc)) return $this->error('文档不存在！');
			$arc->appendRelationAttr('addtb', Channel::getAddfields($arc->channel));
			
			$typelist = unlimitedForLevel(model('arctype')->where(['channelid'=>$arc['channel']])->order('sortrank,id')->select());
			$fieldlist = model('fields')->where('cid', $arc->channel)->where('type', 'neq', 'fixed')->order('sortrank')->select();
			
			$this->assign('typelist', $typelist);
			$this->assign('fieldlist', $fieldlist);
			$this->assign('row', $arc);
			$this->assign('arcranks', $this->arcranks);
			$this->assign('flags', Config::get('sys.flag'));
			$this->assign('title', '文档编辑');
			return $this->fetch();
		}
	}
	
	//删除
	public function del($ids)
	{
		$data = ['arcrank'=>-1];
		$where = ['id'=>['exp',' in ('.$ids.')']];
		
		$this->arcset($data,$where);
		return $this->success('删除成功！');
	}
	
	//清空
	public function clear($ids)
	{
		$ids = explode(',', $ids);
		foreach ($ids as $v){
			$v = intval($v);
			if(empty($v)) continue;
			$this->arcdel($v);
		}
		return $this->success('清除成功！');
	}
	
	//审核
	public function check($ids)
	{
		$data = ['arcrank'=>1];
		$where = ['id'=>['exp',' in ('.$ids.')']];
		
		$this->arcset($data,$where);
		return $this->success('审核成功！');
	}
	
	//取消审核
	public function checkno($ids)
	{
		$data = ['arcrank'=>0];
		$where = ['id'=>['exp',' in ('.$ids.')']];
		
		$this->arcset($data,$where);
		return $this->success('取消成功！');
	}
	
	//增加属性
	public function flag($ids,$flag)
	{
		$data = ['flag'=>['exp','flag | '.flag($flag)]];
		$where = ['id'=>['exp',' in ('.$ids.')']];
		
		$this->arcset($data,$where);
		return $this->success('增加成功！');
	}
	
	//删除属性
	public function flagno($ids,$flag)
	{
		$data = ['flag'=>['exp','flag ^ '.flag($flag)]];
		$where = ['id'=>['exp',' in ('.$ids.')']];
		
		$this->arcset($data,$where);
		return $this->success('删除成功！');
	}
	
	//移动(限制只能移动同一模型栏目下文章)
	public function move($ids,$type)
	{
		$data = ['typeid'=>intval($type)];
		$where = ['id'=>['exp',' in ('.$ids.')'],'channel'=>Arctype::getCache()[$type]['channelid']];
		
		$this->arcset($data,$where);
		return $this->success('移动成功！');
	}
	
	private function arcdel($id)
	{
		$arc = model('archives');
		$data = $arc->get($id);
		if($data){
			$addtb = Channel::getAddtable($data['channel']);
			model($addtb)->destroy($id);
			$arc->destroy($id);
		}
	}
	
	private function arcset($data,$where)
	{
		$arc = model('archives');
		$arc->where($where)->update($data);
	}
}
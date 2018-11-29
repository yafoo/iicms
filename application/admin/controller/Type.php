<?php
namespace app\admin\controller;

use think\Controller;
use app\common\model\Arctype;

class Type extends Controller
{
	public function index()
	{
		$typelist = unlimitedForLevel(model('arctype')->with('wchannel')->order('sortrank,id')->select());
		$this->assign('type',$typelist);
		$this->assign('title','栏目管理');
		return $this->fetch();
	}
	
	public function add($id = 0)
	{
		if(request()->isPost()){
			$type = model('arctype');
			$type->data(request()->post());
			$result = $type->save();
			if($result) return $this->success('添加成功！',url('index'));
			else return $this->error('添加失败！');
		}else{
			$channel =  model('channel')->all(['related'=>1, 'state'=>1]);
			$typelist = unlimitedForLevel(model('arctype')->order('sortrank,id')->select());
			$this->assign('pid',$id);
			$this->assign('channel',$channel);
			$this->assign('typelist',$typelist);
			$this->assign('title','新增栏目');
			return $this->fetch();
		}
	}
	
	public function edit($id)
	{
		if(request()->isPost()){
			$type = model('arctype');
			$result = $type->allowField(true)->save(request()->post(),['id'=>$id]);
			if($result === false) return $this->error('编辑失败！');
			else return $this->success('编辑成功！',url('index'));
		}else{
			$type = model('arctype')->get($id);
			$this->assign('type',$type);
			$channel =  model('channel')->all(['related'=>1, 'state'=>1]);
			$this->assign('channel',$channel);
			$typelist = unlimitedForLevel(model('arctype')->order('sortrank,id')->select());
			$this->assign('typelist',$typelist);
			$this->assign('title','栏目编辑');
			return $this->fetch();
		}
	}
	
	public function del($id)
	{
		$result = model($this->model)->destroy($id);
		
		if($result) return $this->success('删除成功！',url('index'));
		else return $this->error('删除失败！');
	}
	
	public function sort()
	{
		if(request()->isPost()){
			$ids = model('arctype')->column('id');
			$type = model('arctype');
			foreach ($ids as $i) {
				$result = $type->save(['sortrank'=>request()->post('sortrank'.$i)], ['id'=>$i]);
				if($result === false) return $this->error('排序失败！');
			}
			return $this->success('排序成功',url('index'));
		}
	}
	
}
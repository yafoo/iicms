<?php
namespace app\admin\controller;

use think\Controller;
use app\common\model\Channel;
use com\Datatable;

class Fields extends Controller
{
	public static $fieldtype = [
		'fixed'		=>	'系统字段',
		'input'		=>	'输入框',
		'textarea'	=>	'长字符',
		'select'	=>	'下拉选择',
		'radio'		=>	'单项选择',
		'checkbox'	=>	'多项选择',
		'html'		=>	'富文本',
		'image'		=>	'图片',
		'file'		=>	'文件'
	];
	protected $beforeActionList = [
		'setFieldType'
	];
	
	public function setFieldType()
	{
		if(!$this->request->isPost()) $this->assign('fieldtype', static::$fieldtype);
	}
	
	public function index($cid)
	{
		$rows = model('fields')->where(['cid'=>$cid])->order('sortrank')->select();
		
		$this->assign('cid', $cid);
		$this->assign('rows', $rows);
		$this->assign('title', Channel::getAddname($cid) . ' - 字段管理');
		return $this->fetch();
	}
	
	public function add($cid)
	{
		if($this->request->isPost()){
			$data = $this->request->post();
			if(!$data['title']) return $this->error('名字不能为空！');
			if(!$data['name']) return $this->error('字段不能为空！');
			$row = model('channel')->get($cid);
			$dt = new Datatable();
			if($dt->CheckField($row['addtable'], $data['name'])) return $this->error('新增失败，字段已存在！');
			if(in_array($data['f_type'], ['int', 'unint', 'tinyint', 'untinyint'])){
				$data['f_default'] = intval($data['f_default']);
				if($data['f_length'] > 11) $data['f_length'] = 11;
			}
			
			if($data['f_type'] == 'mediumtext' || $data['f_type'] == 'text'){
				$data['f_length'] = '';
			}
			
			$add = [];
			$add['field'] = $data['name'];
			$add['type'] = $data['f_type'];
			$add['length'] = intval($data['f_length']);
			$add['default'] = $data['f_default'];
			$add['is_null'] = true;
			$add['after'] = 'aid';
			
			$re = $dt->colum_field($row['addtable'], $add)->query();
			if(!$re) return $this->error('创建字段失败！');
			
			$result = model('fields')->allowField(true)->save($data);
			
			if($result) return $this->success('新增成功！','index?cid='.$cid);
			else return $this->error('新增失败！');
		}else{
			$this->assign('cid', $cid);
			$this->assign('title', '新增字段');
			return $this->fetch();
		}
	}
	
	public function edit($id)
	{
		if($this->request->isPost()){
			$data = $this->request->post();
			if($data['name'] == 'aid' || $data['name'] == 'status') return $this->error('系统字段不能编辑！');
			if(!$data['title']) return $this->error('名字不能为空！');
			if(!$data['name']) return $this->error('字段不能为空！');
			if(in_array($data['f_type'], ['int', 'unint', 'tinyint', 'untinyint'])){
				$data['f_default'] = intval($data['f_default']);
				if($data['f_length'] > 11) $data['f_length'] = 11;
			}
			
			if($data['f_type'] == 'mediumtext' || $data['f_type'] == 'text'){
				$data['f_length'] = '';
			}
			
			$field = model('fields')->get($id);
			$row = model('channel')->get($field['cid']);
			$dt = new Datatable();
			
			$add = [];
			$add['field'] = $data['name'];
			$add['type'] = $data['f_type'];
			$add['length'] = $data['f_length'];
			$add['default'] = $data['f_default'];
			$add['is_null'] = true;
			
			$add['oldname'] = $field['name'];
			$add['newname'] = $data['name'];
			if($data['name'] != $field['name']){
				if($dt->CheckField($row['addtable'], $data['name'])) return $this->error('修改失败，字段已存在！');
			}
			
			$add['action'] = 'CHANGE';
			
			$re = $dt->colum_field($row['addtable'], $add)->query();
			if(!$re) return $this->error('字段修改失败！');
			
			$result = model('fields')->allowField(true)->save($data, ['id'=>$id]);
			
			if($result === false) return $this->error('编辑失败！');
			else return $this->success('编辑成功！','index?cid='.$data['cid']);
		}else{
			$row = model('fields')->get($id);
			if(empty($row)) return $this->error('字段不存在！');
			if($row->name == 'aid' || $row->name == 'status') return $this->error('系统字段不能编辑！');
			
			$this->assign('row',$row);
			$this->assign('title', '字段编辑');
			return $this->fetch();
		}
	}
	
	public function del($id)
	{
		$row = model('fields')->get($id);
		if(empty($row)) return $this->error('字段不存在！');
		if($row->name == 'aid' || $row->name == 'status') return $this->error('系统字段不能删除！');
		
		$channel = model('channel')->get($row['cid']);
		$dt = new Datatable();
		$re = $dt->del_field($channel['addtable'], $row->name)->query();
		if(!$re) return $this->error('字段删除失败！');
		$result = $row->delete();
		
		if($result) return $this->success('删除成功！');
		else return $this->error('删除失败！');
	}
	
	public function sort($cid)
	{
		if($this->request->isPost()){
			$ids = model('fields')->where('cid', $cid)->column('id');
			$fields = model('fields');
			foreach ($ids as $i) {
				$result = $fields->save(['sortrank'=>$this->request->post('sortrank'.$i)], ['id'=>$i]);
				if($result === false) return $this->error('排序失败！');
			}
			return $this->success('排序成功',url('index?cid='.$cid));
		}
	}
}
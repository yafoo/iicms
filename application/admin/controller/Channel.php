<?php
namespace app\admin\controller;

use think\Controller;
use com\Datatable;

class Channel extends Controller
{
	use \app\admin\traits\Curd;
	protected $model = 'channel';
	protected $title = '频道模型';
	protected $beforeActionList = [
		'sqlAdd'=>['only'=>['add']],
		'sqlEdit'=>['only'=>['edit']],
		'sqlDel'=>['only'=>['del']]
	];
	
	public function sqlAdd()
	{
		if($this->request->isPost()){
			$data = $this->request->post();
			if(!$data['cname']) return $this->error('模型名字不能为空！');
			if(!$data['addtable']) return $this->error('表名不能为空！');
			if(!strstr($data['addtable'], 'add_')) return $this->error('表名需以"add_"开头！');
			$addtable = $data['addtable'];
			$auto_increment = $data['related'];
			$auto_increment = !$auto_increment;
			$comment = $data['cname'];
			
			$dt = new Datatable();
			if($dt->CheckTable($addtable)) return $this->error('新增失败，数据表已存在！');
			
			$result = $dt->start_table($addtable)->create_id('aid', 11, '主键', $auto_increment)->create_status()->create_key('aid')->end_table($comment)->create();
			if(!$result) return $this->error('数据表创建失败，请重试！');
		}
	}
	
	public function sqlEdit()
	{
		if($this->request->isPost()){
			$data = $this->request->post();
			$row = model('channel')->get($data['id']);
			
			$dt = new Datatable();
			
			//改表注释
			if($data['cname'] != $row['cname']){
				$dt->sql = "ALTER TABLE `".$dt->getTablename($row['addtable'], true)."` COMMENT '".$data['cname']."';";
				$dt->query();
			}
			
			//改表名字
			if($data['addtable'] != $row['addtable']){
				$dt->sql = "ALTER TABLE `".$dt->getTablename($row['addtable'], true)."` RENAME `".$dt->getTablename($data['addtable'], true)."`;";
				$dt->query();
			}
			
			//关联改变
			if($data['related'] != $row['related']){
				//待修改
				return $this->error('独立与关联更改暂不支持！');
			}
		}
	}
	
	public function sqlDel()
	{
		$id = $this->request->param('id');
		$row = model('channel')->get($id);
		
		model('fields')->where('cid', $id)->delete();
		if($row['related']){
			model('archives')->where('channel', $id)->delete();
			model('arctype')->where('channelid', $id)->delete();
		}
		
		$dt = new Datatable();
		$dt->del_table($row['addtable'])->query();
	}
}
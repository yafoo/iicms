<?php
namespace app\admin\traits;

use app\common\controller\UpApi;

trait Curd
{
	public function index()
	{
		$rows = 15;
		$option = ['type'=>'\\app\\common\\paginator\\Meizi', 'var_page'=>'page', 'list_rows'=>$rows];
		$rows = $this->getModel()->order($this->getPk() . ' desc')->paginate(null,false,$option);
		
		$this->assign('rows', $rows);
		$this->assign('title', $this->title);
		return $this->fetch();
	}
	
	public function add()
	{
		$ups = $this->getUp();
		
		if($this->request->isPost()){
			$data = $this->request->post();
			$model = $this->getModel();
			if(!empty($ups)){
				foreach($ups as $up){
					if($data[$up] != ''){
						$key = $this->model . '_' . $up;
						$model->event('after_insert', function($m) use ($up, $key){
							UpApi::set_use($m->$up);
							session($key, null, 'up');
						});
					}
				}
			}
			$this->checkbox($data);
			$result = $model->allowField(true)->save($data);
			
			if($result){
				if($this->getRedirect()) return $this->success('新增成功！', $this->getRedirect());
				else return $this->success('新增成功！','index');
			}else return $this->error('新增失败！');
		}else{
			if(!empty($ups)){
				foreach($ups as $up){
					session($this->model . '_' . $up, null, 'up');
				}
			}
			
			$this->assign('title', $this->title . '新增');
			return $this->fetch();
		}
	}
	
	public function edit($id)
	{
		$ups = $this->getUp();
		
		if($this->request->isPost()){
			$data = $this->request->post();
			$model = $this->getModel();
			if(!empty($ups)){
				foreach($ups as $up){
					$key = $this->model . '_' . $up;
					$model->event('after_update', function($m) use ($up, $key){
						$oldup = session($key, '', 'up');
						if(empty($oldup)) $oldup = '';
						if($m->$up != $oldup){
							if($m->$up) UpApi::set_use($m->$up);
							if($oldup) UpApi::set_use($oldup, 0);
							session($key, null, 'up');
						}
					});
				}
			}
			
			$this->checkbox($data);
			$result = $model->allowField(true)->save($data, [$this->getPk()=>$id]);
			
			if($result === false) return $this->error('编辑失败！');
			else{
				if($this->getRedirect()) return $this->success('编辑成功！', $this->getRedirect());
				else return $this->success('编辑成功！','index');
			}
		}else{
			$row = $this->getModel()->get($id);
			if(empty($row)) return $this->error('数据不存在！');
			
			if(!empty($ups)){
				foreach($ups as $up){
					session($this->model . '_' . $up, $row->$up, 'up');
				}
			}
			
			$this->assign('row',$row);
			$this->assign('title', $this->title . '编辑');
			return $this->fetch();
		}
	}
	
	public function del($id)
	{
		$up = isset($this->up) ? $this->up : null;
		$model = $this->getModel();
		
		if(!empty($ups)){
			foreach($ups as $up){
				$model->event('after_delete', function($m) use ($up){
					if($m->$up) UpApi::del($m->$up);
				});
			}
		}
		
		$result = $model->destroy($id);
		
		if($result) return $this->success('删除成功！');
		else return $this->error('删除失败！');
	}
	
	protected function getPk()
	{
		return isset($this->pk) && !empty($this->pk) ? $this->pk : 'id';
	}
	
	protected function getModel()
	{
		return model($this->model);
	}
	
	protected function getRedirect()
	{
		return isset($this->redirect) && !empty($this->redirect) ? $this->redirect : '';
	}
	
	protected function getUp()
	{
		$up = isset($this->up) ? $this->up : [];
		if(!is_array($up)) $up = explode(',', trim($up, ','));
		return $up;
	}
	
	protected function checkbox(&$data)
	{
		if(empty($this->checkbox) || !isset($data)) return false;
		$checkbox = $this->checkbox;
		if(!is_array($checkbox)) $checkbox = explode(',', trim($checkbox, ','));
		
		foreach($checkbox as $field){
			if($field != '' && isset($data[$field]) && is_array($data[$field])) $data[$field] = implode(',', $data[$field]);
		}
	}
}
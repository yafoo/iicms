<?php
namespace app\admin\controller;

use think\Controller;

class Upload extends Controller
{
	public function index()
	{
		$rows = 15;
		$option = ['type'=>'\\app\\common\\paginator\\Meizi', 'var_page'=>'page', 'list_rows'=>$rows];
		$upload = model('upload')->with('user')->order('id','desc')->paginate(null,false,$option);
		$this->assign('upload',$upload);
		$this->assign('title','附件管理');
		return $this->fetch();
	}
	
	public function upFile()
	{
		return \app\common\controller\UpApi::up();
	}
	
	public function del($id)
	{
		$upload = model('upload')->get($id);
		if($upload) $upload->delete();
		return $this->success('删除成功！');
	}
	
}
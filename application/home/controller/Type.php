<?php
namespace app\home\controller;

use think\Controller;
use app\common\model\Arctype;

class Type extends Controller
{
	protected $dir;
	
	protected function initialize()
	{
		$url = $this->request->url();
		if($this->contains($url, '?') && !$this->contains($url, '/?')){
			return $this->redirect(str_replace('?',"/?",$url),301);
		}
		if($this->contains($url, '#') && !$this->contains($url, '/#')){
			return $this->redirect(str_replace('#',"/#",$url),301);
		}
		if(!$this->contains($url, ['?','#']) && substr($url, -1) != '/'){
			return $this->redirect($url.'/',301);
		}
		
		$url = str_replace(['?','#'],",",$url);
		$url = explode(',',$url);
		$this->dir = $url[0];
	}
	
	public function _empty()
	{
		return $this->view();
	}
	
	protected function view()
	{
		$pagetype = ['list','list','type','index'];
		
		$_fields = model('arctype')->get(['typedir' => $this->dir]);
		if(empty($_fields)) return $this->error('栏目不存在！');
		if(isset($pagetype[$_fields['pagetype']])) $pagetype[0] = $pagetype[$_fields['pagetype']];
		
		$map = ['arcrank'=>1, 'typeid'=>$_fields['id']];
		$type = Arctype::getCache();
		if(!empty($type[$_fields['id']]['sonid'])) $map['typeid'] = ['exp', ' in ('.$_fields['id'].','.$type[$_fields['id']]['sonid'].')'];
		$options = ['type'=>'\\app\\common\\paginator\\Page', 'var_page'=>'page', 'list_rows'=>config('sys.list_rows'), 'path'=>$this->dir.'/'];
		
		if($pagetype[0] == 'list' || config('sys.list_index')){
			$_lists = model('archives')->where($map)->order('id','desc')->paginate(null,false,$options);
			if(config('sys.list_with')) $_lists->load('addtb');
		}
		
		$this->assign("place", Arctype::getPlace($_fields['id']));
		$this->assign('_fields', $_fields);
		if($pagetype[0] == 'list' || config('sys.list_index')) $this->assign('_lists',$_lists);
		
		return $this->fetch($pagetype[0]);
	}
	
	private function contains($haystack, $needles)
	{
		foreach ((array) $needles as $needle) {
			if ($needle != '' && strpos($haystack, $needle) !== false) {
				return true;
			}
		}

		return false;
	}
	
}
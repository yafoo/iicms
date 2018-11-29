<?php
// +----------------------------------------------------------------------
// | Author: yusi <zhyaphoo@qq.com>
// +----------------------------------------------------------------------

namespace app\common\taglib;

use think\template\TagLib;

/**
 * PC标签库解析类
 */
class My extends Taglib
{

	// 标签定义
	protected $tags = [
		// 标签定义： attr 属性列表 close 是否闭合（0 或者1 默认1） alias 标签别名 level 嵌套层次
		//'arclist'     => ['attr' => 'channel,typeid,flag,row,offset,orderby,order'],
		'arclist'     => ['attr' => ''],
		'typelist'     => ['attr' => ''],
		'flink'     => ['attr' => ''],
	];

	/**
	 * arclist标签解析 循环输出数据集
	 * 格式：
	 * {arclist name="" id="field" index="index" with="addtb,arctype" channel="1" typeid="1" flag="c" offset="0" row="10" orderby="id" order="DESC" titlelen="" infolen="" subday=""}
	 *    {$field.title}
	 *    {$field.url}
	 * {/arclist}
	 * @access public
	 * @param array $tag 标签属性
	 * @param string $content 标签内容
	 * @return string|void
	 */
	public function tagArclist($tag, $content)
	{
		$name   = !empty($tag['name']) ? '$'.$tag['name'] : '';
		$item   = !empty($tag['id']) ? '$'.$tag['id'] : '$field';
		$index    = !empty($tag['index']) ? '$'.$tag['index'] : '$index';
		$with = "";
		$where = "";
		
		if(!empty($tag['with'])) $with = $tag['with'];
		if(!empty($tag['channel'])) $where .= "->where('channel',{$tag['channel']})";
		if(!empty($tag['typeid'])){
			$tag['typeid'] = array_filter(explode(',',trim($tag['typeid'],', ')));
			$type = \app\common\model\Arctype::getCache();
			$tags = [];
			foreach($tag['typeid'] as $t){
				$tags[] = $t;
				if(!empty($type[$t]['sonid'])) $tags[] = $type[$t]['sonid'];
			}
			$tags = implode(',',array_unique(explode(',',implode(',',$tags))));
			if(false === strpos($tags,',')) $where .= "->where('typeid',{$tags})";
			else $where .= "->where('typeid','exp',' in ({$tags})')";
		}
		if(!empty($tag['flag'])) $where .= "->where('flag','exp','&".flag($tag['flag'])."!=0')";
		//暂时处理单个关键词
		if(!empty($tag['keywords']) && $tag['keywords'] != "like") $where .= "->where('title',['like','%{$tag['keywords']}%']])";

		$offset    = !empty($tag['offset']) ? $tag['offset'] : '0';
		$row    = !empty($tag['row']) ? $tag['row'] : '10';
		$orderby    = !empty($tag['orderby']) ? $tag['orderby'] : 'id';
		$order    = !empty($tag['order']) ? $tag['order'] : 'DESC';
		$subday    = !empty($tag['subday']) ? $tag['subday'] : '0';

		$parseStr = '<?php ';
		$parseStr .= '$map=array();';
		if($subday > 0) $parseStr .= '$limitday = time() - ('.$subday.' * 24 * 3600);$map["addtime"]=[">",$limitday];';
		$parseStr .= '$type=\app\common\model\Arctype::getCache();';
		if(!isset($tag['channel'])){
			$parseStr .= 'if(!empty($_fields["channel"])) $map["channel"]=$_fields["channel"];';
			$parseStr .= 'elseif(!empty($_fields["channelid"])) $map["channel"]=$_fields["channelid"];';
			if(!empty($name)) $parseStr .= '$options = '.$name.';if(!empty($options)){if(isset($options["channel"]))$map["channel"]=$options["channel"];else if(isset($options["channelid"])) $map["channel"]=$options["channelid"];}';
		}
		if(!isset($tag['typeid'])){
			$parseStr .= '$typeid="";if(!empty($_fields["typeid"]))$typeid=$_fields["typeid"];elseif(!empty($_fields["typename"]))$typeid=$_fields["typeid"];';
			if(!empty($name)) $parseStr .= '$options = '.$name.';if(!empty($options) && isset($options["typeid"]))$typeid=$options["typeid"];';
			$parseStr .= 'if($typeid != ""):$map["typeid"]=$typeid;if(!empty($type[$typeid]["sonid"]))$map["typeid"]=["exp"," in (".$typeid.",".$type[$typeid]["sonid"].")"];endif;';
		}
		if(!empty($tag['keywords']) && $tag['keywords'] == "like") $parseStr .= 'if(!empty($_fields["keywords"])) $map["title"]=["like","%".$_fields[\'keywords\']."%"];';
		$parseStr .= '$_archives_list=model("archives")'.$where.'->where($map)->limit('.$offset.','.$row.')->order("'.$orderby.'","'.$order.'")->select();';
		if(!empty($with)) $parseStr .= '$_archives_list->load("'.$with.'");';
		$parseStr .= $index.'=0;foreach($_archives_list as '.$item.'):'.$index.'++;';
		if(!empty($tag['titlelen'])) $parseStr .= $item.'["title"]=mb_substr('.$item.'["title"],0,'.$tag['titlelen'].',"utf-8");';
		if(!empty($tag['infolen'])) $parseStr .= $item.'["description"]=mb_substr('.$item.'["description"],0,'.$tag['infolen'].',"utf-8");';
		$parseStr .= '?>';
		$parseStr .= $content;
		$parseStr .= '<?php endforeach; ?>';
		return $parseStr;
	}
	
	/**
	 * typelist标签 获取栏目列表 循环输出数据集
	 * 格式：
	 * {typelist name="" id="field" index="index" typeid="" type="son" row="10"}
	 *    {$field.typename}
	 *    {$field.typedir}
	 * {/arclist}
	 * @access public
	 * @param array $tag 标签属性
	 * @param string $content 标签内容
	 * @return string|void
	 */
	public function tagTypelist($tag, $content)
	{
		$name   = !empty($tag['name']) ? '$'.$tag['name'] : '';
        $item   = !empty($tag['id']) ? '$'.$tag['id'] : '$field';
        $index    = !empty($tag['index']) ? '$'.$tag['index'] : '$index';
		if(empty($tag['type'])) $tag['type'] = '';
		if(empty($tag['row'])) $tag['row'] = 100;
		
		$parseStr = '<?php ';
		$parseStr .= '$type=\app\common\model\Arctype::getCache();';
		$parseStr .= 'if($type):';
		$parseStr .= '$pid=0;';
		if(!empty($tag['typeid'])) $parseStr .= '$pid='.$tag['typeid'].';';
		else{
			$parseStr .= 'if(!empty($_fields["typename"])) $pid=$_fields["typeid"];';
			if(!empty($name)) $parseStr .= '$options = '.$name.';if(!empty($options) && isset($options["typeid"])) $pid=$options["typeid"];';
		}
		switch($tag['type']){
			case 'top':
				$parseStr .= '$pid=0;';
				break;
			case 'parent':
				$parseStr .= 'if(isset($type[$pid])) $pid=$type[$pid]["pid"]; if(isset($type[$pid])) $pid=$type[$pid]["pid"];';
				break;
			case 'self':
				$parseStr .= 'if(isset($type[$pid])) $pid=$type[$pid]["pid"];';
				break;
			case 'son':
			default:
		}
		$parseStr .= '$type=category($type,$pid);';
		$parseStr .= $index.'=0;foreach($type as '.$item.'):'.$index.'++;';
		if(!empty($tag['row'])) $parseStr .= 'if('.$index.'>'.$tag['row'].') break;';
		$parseStr .= '?>';
		$parseStr .= $content;
		$parseStr .= '<?php endforeach; endif; ?>';
		return $parseStr;
	}
	
	/**
	 * flink标签 获取友情链接
	 * 格式：
	 * {flink name="" id="field" index="index" row="10"}
	 *    {$field.name}
	 *    {$field.url}
	 * {/flink}
	 * @access public
	 * @param array $tag 标签属性
	 * @param string $content 标签内容
	 * @return string|void
	 */
	public function tagFlink($tag, $content)
	{
		$name   = !empty($tag['name']) ? '$'.$tag['name'] : '';
        $item   = !empty($tag['id']) ? '$'.$tag['id'] : '$field';
        $index    = !empty($tag['index']) ? '$'.$tag['index'] : '$index';
		if(empty($tag['row'])) $tag['row'] = 100;
		
		$parseStr = '<?php ';
		$parseStr .= '$flink=model("flink")->where("state",1)->order("sortrank,id")->limit('.$tag['row'].')->select();';
		$parseStr .= $index.'=0;foreach($flink as '.$item.'):'.$index.'++;';
		$parseStr .= '?>';
		$parseStr .= $content;
		$parseStr .= '<?php endforeach; ?>';
		return $parseStr;
	}

}

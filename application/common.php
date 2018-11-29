<?php
use think\facade\Config;
use think\facade\Env;

/**
 * 获取自子孙级分类
 * @param $category
 * @param int $pid
 * @return array
 */
function category($category, $pid=0){
    $arr = array();
    foreach($category as $v){
        if($v['pid'] == $pid){
            $v['child'] = category($category, $v['id']);
            $arr[] = $v;
        }
    }
    return $arr;
}

/**
 * 按父子孙关系排列
 * @param $list
 * @param int $pid
 * @param int $level
 * @return array
 */
function unlimitedForLevel($list, $pid=0, $level=0 ) {
    $arr = array();
    foreach($list as $k => $v){
        if($v['pid'] == $pid){
            $v['level'] = $level + 1;
            $arr[] = $v;
            $arr = array_merge($arr, unlimitedForLevel($list, $v['id'], $level + 1));
        }
    }
    return $arr;
}

// FLAG转换
function flag($n){
	if(empty($n)) return "";
	
	$result = "";
	$flag = Config::get('sys.flag');
	if(is_numeric($n)){
		foreach($flag as $k=>$v){
			if($v[0] & $n) $result .= $result=="" ? $k : ",".$k;
		} 
	}else{
		$result = 0;
		if(!is_array($n)) $n = explode(",",$n);
		foreach($n as $v){
			$result = $result | $flag[$v][0];
		} 
	}
	return $result;
}

// 文件大小转换
function format_bytes($size) {
    $mod = 1024;
    $units = explode(' ','B KB MB GB TB PB');
    for ($i = 0; $size > $mod; $i++) {
        $size /= $mod;
    }
    return round($size, 2) . '' . $units[$i];
}

// 手机号隐藏中间四位
function yc_phone($str){
	return substr_replace($str, '****', 3, 4);
}

// 删除文件夹
function deletedir($path){
	$op = dir($path);
	while(false != ($item = $op->read())){
		if($item == '.' || $item == '..'){
			continue;
		}
		if(is_dir($op->path.'/'.$item)){
			deletedir($op->path.'/'.$item);
			rmdir($op->path.'/'.$item);
		}else{
			unlink($op->path.'/'.$item);
		}
	}
}

// 扩展函数
if(is_file(Env::get('extend_path') . 'com/captcha/helper.php')){
	include Env::get('extend_path') . 'com/captcha/helper.php';
}
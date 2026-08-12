<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
function get_id(){
	$id=time().(mt_rand(11111,99999)+0);
	return $id;
}




// 操作记录
function text_log($user,$text){
	$arr['User']=$user;
	$arr['Content']=$text;
	$arr['Time']=date('Y-m-d H:i:s',time());

	return $arr;
}
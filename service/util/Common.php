<?php
//公共方法文件


//数据库空值处理
if (! function_exists ( 'sqlFilter' )) {
	/*
	 * $r 1--id处理,转化为空   2--数字处理，空时补0  3--字符串处理，空时补‘’
	 */
function sqlFilter($value, $r = 0) {
		if ($r == 1) {
			if (isNull ( $value )) {
				$value =  'null';
			}else{
				$value = str_replace ( "'" , "''" , $value );
				$value = "'" . $value . "'";
			}
		}elseif ($r == 2) {
			if (isNull ( $value )) {
				$value = '0';
			}else{
				$value = replaceComma ( $value );
				if(!is_numeric($value)){
					$value = '0';
				}
			}
		}elseif ($r == 3) {
			if (isNull ( $value )) {
				$value =  "''";
			}else{
				$value = str_replace ( "'" , "''" , $value );
				$value =  "'" . $value . "'";
			}
		}else{
			if (isNull ( $value )) {
				return null;
			}
			$value = str_replace ( "'" , "''" , $value );
		}
		return $value;
	}
}
//判断空值
if (! function_exists ( 'isNull' )) {
	function isNull($wf_value) {
		if (is_numeric ( $wf_value )) {
			return false;
		}
		if ($wf_value == null || (is_string ( $wf_value ) && trim ( $wf_value ) == '')) {
			return true;
		} else if (is_array ( $wf_value ) && count ( $wf_value ) == 0) {
			return true;
		}
		
		return false;
	}
}
if(!function_exists('wf_iconvutf'))
{
   function wf_iconvutf($value)   
  {   
		//if(ENVIRONMENT != 'testing'){
	  		return $value;
	  	//}else{
	 	//	return iconv("gbk","utf-8//IGNORE",$value);
	  	//}  
  }  
}

if(!function_exists('wf_iconvgbk'))
{
   function wf_iconvgbk($value)   
  {   
  	//if(ENVIRONMENT != 'testing'){
  		return $value;
  	//}else{
  	//	return iconv("utf-8","gbk//IGNORE",$value);
  	//}
  }  
}

//打印xml信息
if (! function_exists ( "system_print" )) {
	function system_print($node_xml) {
		var_dump( simplexml_import_dom ( $node_xml )->asXML () );
	}
}
if (! function_exists ( 'init_connect' )) {
	function init_connect($active_db = 'default') {
		return DBcommon::init_connect($active_db);
	}
}
/*
 * 调试信息记录
 */
if (! function_exists ( "wf_debug" )) {
	function wf_debug($wf_msg, $level = '', $mode = 'a') {
		$commandContext = CommandContext::getInstance();
		if($commandContext->getSysVar('debug')==DEBUG_DEBUG){
			$set_level = 1;
		}elseif($commandContext->getSysVar('debug')==DEBUG_EXEC){
			$set_level = 4;
		}else{
			$set_level = 4;
		}
		$set_level = 1; //(1:DEBUG,2:INFO,3:WARN,4:ERROR)
		if ($level == '') {
			$level = 'DEBUG';
		}
		$level = strtoupper ( $level );
		switch ($level) {
			case "DEBUG" :
				$level_num = 1;
				break;
			case "INFO" :
				$level_num = 2;
				break;
			case "WARN" :
				$level_num = 3;
				break;
			case "ERROR" :
				$level_num = 4;
				break;
			default :
				$level_num = 1;
		}
		if ($level_num < $set_level) {
			return;
		}
		$path = WORKFLOW_BASE."/log/" . date ( "Y-m-d" ) . ".log";
		$pre =  "\r\n" . date ( "Y-m-d H:i:s" ) . " " . $level . ":";
		if(is_array($wf_msg)){
			$data = $pre."\r\n".var_export($wf_msg,true);
		}else{
			$data = $pre.$wf_msg;
		}
		$fp = @fopen ( $path, $mode );
		@flock ( $fp, 3 );
		if (! $fp) {
			Return false;
		} else {
			@fwrite ( $fp, $data );
			@fclose ( $fp );
			Return true;
		}
	}
}
/**
 * 替换全局变量
 */
if (! function_exists ( "__replaceGlobalVar" )) {
	function __replaceGlobalVar($source) {
		
		if (isNull ( $source )) {
			return $source;
		}
		//任务全局类
		$commandContext = CommandContext::getInstance();
		$commandContext->getTempVar();
		foreach ( $commandContext->getTempVar() as $key => $value ) {
			if (! is_array ( $value ))
				$source = str_replace ( $key, $value, $source );
		}
		return $source;
	}
}
/**
 * 添加全局变量
 */
if (! function_exists ( "__addGlobalVar" )) {
	function __addGlobalVar($key, $val) {
		if(is_string($val)){
			$val = trim($val);
		}
		//任务全局类
		$commandContext = CommandContext::getInstance();
		$commandContext->setTempVar($key,$val);
	
	}
}
/**
 * 获取全局变量
 */
if (! function_exists ( "__getGlobalVar" )) {
	function __getGlobalVar($key) {
		//任务全局类
		$commandContext = CommandContext::getInstance();
		$result = $commandContext->getTempVar($key);
		if(isNull($result)){
			$result = $commandContext->getSysVar($key);
		}
		if(is_string($result)){
			$result = trim($result);
		}
		return $result;
	}
}
/**
 * 获取当前节点
 */
if (! function_exists ( "__getNodeVar" )) {
	function __getNodeVar($key) {
		$infoService = InfoService::getInstance();
		return $infoService->getNodeVar($key);
	}
}
/**
 * 添加到response消息体中
 */
if (! function_exists ( "__addResponseVar" )) {
	function __addResponseVar($key, $val) {
		$commandContext = CommandContext::getInstance();
		$commandContext->setResponseBox($key, $val);
	}
}
/**
 * 
 * 获取trace
 */
if (! function_exists ( "getBenchMark" )) {
	function getBenchMark() {
		if (BENCHMARK == 'no') {
			return array ();
		}
		$trace = debug_backtrace ();
		array_shift($trace);//排除本层访问
		
		$benchMark = array ();
		$benchMark ['start'] = microtime_float ();
		
		
	//	//记录第一个调用BenchMark的方法
	//	global $wf_global;
	//	if (! array_key_exists ( 'bench_mark_open', $wf_global )){
	//		$wf_global ['bench_mark_open'] = "";
	//	}
	//	if ($wf_global ['bench_mark_open'] == ""){
	//		$wf_global ['bench_mark_open'] = $trace [0] ['function'];
	//	}
		$bench_mark_open = $trace [0] ['function'];
		//访问路径
		$trace = array_reverse($trace);
		$path = "";
		foreach ( $trace as $tr ) {
			//从第一个调用BenchMark的方法开始记录访问路径
			if($bench_mark_open == $tr ['function'] || $path != ""){
				$path .= $tr ['function'] . "==>";
			}
		}
		$benchMark ['path'] = $path;
		
		return $benchMark;
	}
}
/**
 * 记录时长
 */
if (! function_exists ( "setBenchMark" )) {
	function setBenchMark($benchMark) {
		if (BENCHMARK == 'no') {
			return;
		}
		$commandContext = CommandContext::getInstance();
		$start = $benchMark ['start'];
		$end = microtime_float ();
		$time = round ( $end - $start,3 );
		$benchMark ['end'] = $end;
		$benchMark ['time'] = $time;
		$commandContext->setBenchmark($benchMark);
	
	}
}
/**
 * 获取时间点
 */
if (! function_exists ( "microtime_float" )) {
	function microtime_float() {
		list($usec, $sec) = explode(' ', microtime());
	    return ((float)$usec + (float)$sec);
	}
}
/**
 * 取36位唯一uid
 */
if (! function_exists ( "uuid" )) {
	function uuid($prefix=''){
		$chars = md5(uniqid(mt_rand(), true));   
	    $uuid  = substr($chars,0,8) . '-';   
	    $uuid .= substr($chars,8,4) . '-';   
	    $uuid .= substr($chars,12,4) . '-';   
	    $uuid .= substr($chars,16,4) . '-';   
	    $uuid .= substr($chars,20,12);   
	    return $prefix . $uuid;   
	}
}	

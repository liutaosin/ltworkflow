<?php
/**
 * 
 * 用户自定义文件，需要继承此类
 * @author Administrator
 *
 */
class WorkflowBase {
	var $local_param = array ();
	var $global_param = array ();
	var $db = null;
	
	//初始化全局变量
	function _init($init_param) {
		$this->local_param = $init_param ['local_param'];
		$this->global_param = $init_param ['global_param'];
	}
	
	//添加本地变量
	function _add($key, $value) {
		$this->local_param [$key] = $value;
	}
}
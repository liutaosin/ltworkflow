<?php
/*
 * 主流程节点检测
 */

class main_check extends WorkflowBase {

	function main_check() {
	
	}
	
	function validate() {
		//获取自定义变量和业务ID
		$etuid = $this->local_param ['etuid'];
		$type = $this->local_param ['type'];

		if ($type == "act2") { 
			return true;
		} else if ($type == "act3") { 
			return true;
		} 
		return FALSE;
	
	}
}
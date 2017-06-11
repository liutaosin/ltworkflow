<?php
/*
 * 校验不同的地区负责人是否审批。
 */
class main_before extends WorkflowBase {
	
	function main_before() {
	
	}
	
	function validate() {
		//获取自定义变量和业务ID
		$type = $this->local_param ['type'];
		$etuid = $this->local_param ['etuid'];
		
		if ($type == "submit") { //送审时校验
			
		} else if ($type == "act2") { //环节2通过时 校验
			
		} else if ($type == "act3") { //环节3通过时 校验
			
		} 
		return true;
	}
	
	
}
<?php
/**
 * 主流程获取审批人信息
 *
 */
class main_user extends Roles {
	
	function main_user() {
	
	}
	
	function getUserRole() {
		$users = array ();
		$etuid = $this->local_param ['etuid'];
		$type = $this->local_param ['type'];
		if ($type == "act1") { //环节1人员
			 array_push($users,"7320");
		} else if ($type == "act2") { //环节2人员
			 array_push($users,"5459");
		} else if ($type == "act3") { // 环节3人员
			 array_push($users,"1779");
		} else if ($type == "act4") { // 环节4人员
			 array_push($users,"301");
		} else if ($type == "act5") { // 环节4人员
			 array_push($users,"637");
		} 
		if (count ( $users ) == 0) {
			throw new Exception ( "未找到下一处理人！" );
		}
		return $users;
	}

}
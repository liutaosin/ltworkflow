<?php
/**
 * 
 * 人员信息接口，取人员、部门等信息必需实现此接口
 * @author liutao
 *
 */
interface  InterfaceUserDB {
	
	function getUserList($userList);
	function getUser($ssn);
	function getUserByRole($roleid); 
	function getGlobalParam($ssn);
	function queryWorkflowLog($etuid);
}
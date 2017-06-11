<?php 
/**
 * 
 * 流程处理操作接口
 * @author liutao
 * liutao 2014-11-21
 */
interface InterfaceTask{
	
	/**
	 *
	 *功能:	创建工作流处理
	 *参数:	wf_uid -> 工作流模板id
	 *		wf_createuser -> 实体创建人
	 *		wf_id -> 工作流初始状态
	 *	
	 **/
	
	function createEntry($wf_uid, $wf_createuser, $wf_id, $wf_etuid );
	/**
	 * 
	 * 通过到下一节点
	 * @param unknown_type $wf_users
	 * @param unknown_type $wf_id
	 * @param unknown_type $wf_etuid
	 * @param unknown_type $wf_nodestatus
	 * @param unknown_type $wf_uid
	 * @param unknown_type $wf_puid
	 * @param unknown_type $wf_pid
	 * @param unknown_type $nodename
	 * @throws Exception
	 */
	function completeEntry($wf_users, $wf_id, $wf_etuid, $wf_nodestatus, $wf_uid, $wf_puid, $wf_pid, $nodename);
	/**
	 * 
	 * 删除当前环节:$wf_uid流程ID
	 * @param $wf_id
	 * @param $wf_etuid
	 * @param $wf_uid
	 */
	function deleteCurrentStep($wf_id, $wf_etuid, $wf_uid);
	/**
	 * 
	 * 获取当前处理人
	 * @param $wf_etuid
	 */
	function getCurrentStep($wf_etuid,$ssn);
	/**
	 * 
	 * 获取当前处理人
	 * @param $wf_etuid
	 */
	function getCurrentSteps($wf_etuid);
	/**
	 * 
	 * 获取父流程的ID和节点
	 * @param $wf_etuid
	 * @param $wf_uid
	 * @param $wf_id
	 */
	 
	function getParentFlow($wf_etuid, $wf_uid, $wf_id);
	
	/**
	 * 删除流程栈
	 * @param $fs_uid
	 */
	function deleteFlowStack($fs_uid);
	/**
	 * 
	 * 保存父流程信息
	 * @param $wf_puid
	 * @param $wf_pid
	 * @param $wf_uid
	 * @param $wf_etuid
	 */
	function saveFlowStack($wf_puid, $wf_pid, $wf_uid, $wf_etuid) ;
	/**
	 * 
	 * 审批完成
	 * @param unknown_type $wf_etuid
	 */
	function finishedWorkflow($wf_etuid) ;
	/**
	 * 进入流程中
	 *
	 * @param unknown_type $wf_etuid
	 */
	function joinWorkflow($wf_etuid) ;
	/**
	 *  保存工作流程日志信息
	 *  $wf_etuid   实例ID
	 *  $wf_currid  当前节点ID
	 *  $wf_currname  当前节点名称
	 *  $wf_status 当前节点状态   在workflow中定义
	 *  $wf_comment   日志备注信息
	 *  $wf_type    日志类型  在workflow中定义
	 *  $wf_actionid   当前动作ID
	 *  $wf_uid     当前工作流定义ID
	 */
	function worklowLog($wf_etuid, $wf_currid,$wf_currname,$wf_status, $wf_comment, $wf_type, $wf_actionid = null, $wf_uid = null);
}
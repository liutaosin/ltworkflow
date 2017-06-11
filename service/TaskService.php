<?php 
require_once WORKFLOW_BASE . '/service/command/Command.php';
class TaskService{
	function __construct(){
		$this->init();
	}
	/**
	 * 
	 * 流程初始化
	 */
	public function init(){
		require_once WORKFLOW_BASE . '/service/command/InitCommand.php';
		$command = new InitCommand();
		$command->execute();
	}
	/**
	 * 
	 * 普通流程通过
	 */
	public function doTask(){
		require_once WORKFLOW_BASE . '/service/command/TaskCommand.php';
		$command = new TaskCommand();
		$command->execute();
	}
	/**
	 * 
	 * 自由流程通过
	 */
	public function doFreeTask(){
		require_once WORKFLOW_BASE . '/service/command/FreeTaskCommand.php';
		$command = new FreeTaskCommand();
		$command->execute();
	}
	/**
	 * 
	 * 驳回
	 */
	public function reject(){
		require_once WORKFLOW_BASE . '/service/command/RejectCommand.php';
		$command = new RejectCommand();
		$command->execute();
	}
	/**
	 * 
	 * 收回
	 */
	public function recycle(){
		require_once WORKFLOW_BASE . '/service/command/RecycleCommand.php';
		$command = new RecycleCommand();
		$command->execute();
	}
	/**
	 * 
	 * 会签发起
	 */
	public function freeRoute(){
		require_once WORKFLOW_BASE . '/service/command/FreeRouteCommand.php';
		$command = new FreeRouteCommand();
		$command->execute();
	}
	/**
	 * 
	 * 循环会签发起
	 */
	public function otherRoute(){
		require_once WORKFLOW_BASE . '/service/command/OtherRouteCommand.php';
		$command = new OtherRouteCommand();
		$command->execute();
	}
	/**
	 * 
	 * 会签确认
	 */
	public function doFreeRoute(){
		require_once WORKFLOW_BASE . '/service/command/DoFreeRouteCommand.php';
		$command = new DoFreeRouteCommand();
		$command->execute();
	}
	/**
	 * 
	 * 循环会签确认
	 */
	public function doOtherRoute(){
		require_once WORKFLOW_BASE . '/service/command/DoOtherRouteCommand.php';
		$command = new DoOtherRouteCommand();
		$command->execute();
	}
	/**
	 * 
	 * 直接结束流程
	 */
	public function doForceFinish(){
		require_once WORKFLOW_BASE . '/service/command/DoForceFinishCommand.php';
		$command = new DoForceFinishCommand();
		$command->execute();
	}
	/**
	 * 
	 * 记录审批意见
	 */
	public function worklowLog(){
		$commandContext = CommandContext::getInstance();
		$wf_etuid = $commandContext->getEtuid();
		$comment = $commandContext->getComment();
		$router = $commandContext->getRouter();
		$wf_status = $commandContext->getFlowStatus();
		$wf_comment = $comment['comment'];
		$wf_id = $comment['stepid'];
		$wf_stepName = $comment['stepName'];
		$wf_uid = $comment['wfuid'];
		$actionid = $comment['actionid'];
		$taskDB = TaskDB::getInstance();
		$taskDB->worklowLog($wf_etuid, $wf_id,$wf_stepName,$wf_status,$wf_comment, WF_LOG_TYPE_APP, $actionid, $wf_uid);
	}
	
}
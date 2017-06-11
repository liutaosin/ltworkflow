<?php 
class RejectCommandImpl{
	/**
	 * 驳回操作
	 *
	 */
	public function reject() {
		//配置全局类
		$configContext = ConfigContext::getInstance();
		//任务全局类
		$commandContext = CommandContext::getInstance();
		$wf_uid = $commandContext->getWfuid();
		$wf_etuid = $commandContext->getEtuid();
		$wf_id = $commandContext->getStepid();
		$actionid = $commandContext->getActionid();
		$rejectDB = new RejectDB();
		//插入当前操作记录
		$nextuserList = $rejectDB->rejectEntry ( $wf_etuid, $wf_id, $wf_uid );
		$commandContext->setNextuser($nextuserList);
		$commandContext->setResponseBox( "message", '您的单据已驳回至' . $nextuserList[0] . '处理！' );
		
		//驳回时自定义脚本处理
		if ($configContext->getGlobalVar('reject') != "") {
			$reject_cls = &class_load ( $configContext->getGlobalVar('reject'), "function" );
			$reject_cls->reject_entry ( $wf_etuid );
		}
	}
	/**
	 * 
	 * 设置审批状态
	 */
	public function setApproveStatus(){
		$commandContext = CommandContext::getInstance();
		$commandContext->setEmail('approvetype',WF_EMAIL_REJECT);
		$commandContext->setFlowStatus(WF_STATUS_REJECT);
	}
	/**
	 * 收回操作
	 *
	 */
	public function recycle() {
		//配置全局类
		$configContext = ConfigContext::getInstance();
		//任务全局类
		$commandContext = CommandContext::getInstance();
		$wf_uid = $commandContext->getWfuid();
		$wf_etuid = $commandContext->getEtuid();
		$wf_id = $commandContext->getStepid();
		$actionid = $commandContext->getActionid();
		$rejectDB = new RejectDB();
		//插入当前操作记录
		$nextuserList = $rejectDB->recycleEntry ( $wf_etuid, $wf_id, $wf_uid );
		$commandContext->setNextuser($nextuserList);
		$commandContext->setResponseBox( "message", '您的单据已收回！' );
		
		//驳回时自定义脚本处理
		if ($configContext->getGlobalVar('reject') != "") {
			$reject_cls = &class_load ( $configContext->getGlobalVar('reject'), "function" );
			$reject_cls->reject_entry ( $wf_etuid );
		}
	}
	/**
	 * 
	 * 设置收回状态
	 */
	public function setRecycleStatus(){
		$commandContext = CommandContext::getInstance();
		$commandContext->setEmail('approvetype',WF_EMAIL_RECYCLE);
		$commandContext->setFlowStatus(WF_STATUS_RECYCLE);
	}
}
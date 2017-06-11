<?php 
class FreeRouteCommandImpl{
	/**
	 * 会签发起
	 *
	 */
	public function freeRoute() {
		//任务全局类
		$commandContext = CommandContext::getInstance();
		$wf_uid = $commandContext->getWfuid();
		$wf_etuid = $commandContext->getEtuid();
		$wf_id = $commandContext->getStepid();
		$router = $commandContext->getRouter();
		if (isNull ( $router )) {
			throw new Exception ( "未能找到下一处理人，请重新操作！" );
		}
		$freeRouteDB = new FreeRouteDB();
		$nextuserList = $freeRouteDB->freeRouteEntry ( $wf_etuid, $wf_id, $wf_uid, $router );
		$commandContext->setNextuser($nextuserList);
		$commandContext->setResponseBox( "message", '您的单据已会签至' . implode(",", $nextuserList) . '处理！' );
	}
	/**
	 * 会签确认
	 *
	 */
	public function doFreeRoute() {
		//配置全局类
		$configContext = ConfigContext::getInstance();
		//任务全局类
		$commandContext = CommandContext::getInstance();
		$wf_uid = $commandContext->getWfuid();
		$wf_etuid = $commandContext->getEtuid();
		$wf_id = $commandContext->getStepid();
		$router = $commandContext->getRouter();
		//会签时自定义脚本处理
		if ($configContext->getGlobalVar('countersign') != "") {
			if ($commandContext->getSysVar('@@createuser__') == $commandContext->getSessionSsn()) {
				$countersign_cls = &class_load ( $configContext->getGlobalVar('countersign'), "function" );
				$countersign_cls->check_countersign ( $wf_etuid );
				$countersign_cls->on_countersign ( $wf_etuid );
			}
		}
		$freeRouteDB = new FreeRouteDB();
		//查询返回节点处的人员信息.
		$nextuserList = $freeRouteDB->doRouteEntry ( $wf_etuid, $wf_id, $wf_uid, $router );
		$commandContext->setNextuser($nextuserList);
		$commandContext->setResponseBox( "message", '您的单据已确认至' . implode(",", $nextuserList). '处理！' );
		
		
	}
	/**
	 * 直接结束
	 *
	 */
	public function doForceFinish() {
		//任务全局类
		$commandContext = CommandContext::getInstance();
		$wf_etuid = $commandContext->getEtuid();
		$freeRouteDB = new FreeRouteDB();
		$freeRouteDB->doForceFinish ( $wf_etuid );
		
	}
	/**
	 * 
	 * 设置审批状态
	 */
	public function setApproveStatus(){
		$commandContext = CommandContext::getInstance();
		$commandContext->setEmail('approvetype',WF_EMAIL_ROUTING);
		$commandContext->setFlowStatus(WF_STATUS_ROUTING);
	}
}
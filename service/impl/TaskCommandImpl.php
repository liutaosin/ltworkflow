<?php 
class TaskCommandImpl{
	/**
	 * 执行任务
	 *
	 */
	public function doTask() {
		//任务全局类
		$commandContext = CommandContext::getInstance();
		$wf_uid = $commandContext->getWfuid();
		$wf_etuid = $commandContext->getEtuid();
		$wf_id = $commandContext->getStepid();
		$actionid = $commandContext->getActionid();
		
		$action = XmlUtil::getAction($wf_uid, $wf_id,$actionid);//获取当前任务
		if($commandContext->getSysVar('debug')!=DEBUG_PREDICTION){//只走流程时不做处理
			$this->beforeAction($wf_etuid);//每次流程前校验
			$this->validateBefore($action, $wf_etuid);//任务前处理
		}
		$behavior = XmlUtil::getBehavior($action,$wf_etuid);
		$behavior->execute();//处理当前任务
		if($commandContext->getSysVar('debug')!=DEBUG_PREDICTION){//只走流程时不做处理
			$this->postfunction($action, $wf_etuid);//任务后处理
			$this->afterAction($wf_etuid);//每次流程后处理
		}
		

	}

	/**
	 * 
	 * 设置审批状态
	 */
	public function setApproveStatus(){
		$commandContext = CommandContext::getInstance();
		$actionid = $commandContext->getActionid();
		//通过
		$commandContext->setEmail('approvetype',WF_EMAIL_DEALING);
		//审批完成
		if ($commandContext->getFlowStatus()==WF_STATUS_FINISHED) {
			$commandContext->setEmail('approvetype',WF_EMAIL_FINISHED);
		}elseif ($actionid == "0") {//起草送审
			$commandContext->setEmail('approvetype',WF_EMAIL_SUBMIT);
			$commandContext->setFlowStatus(WF_STATUS_SUBMIT);
		}
		
	}
	/**
	 * 
	 * 任务前校验
	 * @param $wf_action 当前action 节点
	 * @param $wf_etuid 实例id
	 */
	private function validateBefore($wf_action, $wf_etuid) {
		//获取validate
		$validates = $wf_action->getElementsByTagName ( 'validate' );
		foreach ( $validates as $validate ) {
			$type = $validate->getAttribute ( "type" );
			if ($type == 'beanshell') {
				//自定义条件
				$result_class = &class_load ( $validate, 'restrict' );
				$result_class->_add ( 'etuid', $wf_etuid );
				$result_class->validate ();
			}
		}
		return true;
	
	}
	/**
	 * 
	 * 任务后处理
	 * @param $wf_action 当前action 节点
	 * @param $wf_etuid 实例id
	 */
	private function postfunction($wf_action, $wf_etuid) {
		$validates = $wf_action->getElementsByTagName ( 'post-function' );
		foreach ( $validates as $validate ) {
			$type = $validate->getAttribute ( "type" );
			if ($type == 'beanshell') {
				//自定义条件
				$result_class = &class_load ( $validate, 'function' );
				$result_class->_add ( 'etuid', $wf_etuid );
				$result_class->validate ();
			}
		}
	}

	/**
	 * 每步流程前都校验
	 *
	 * @param unknown_type $wf_etuid  实例id
	 */
	private function beforeAction($wf_etuid) {
		//每步流程前校验
		//配置全局类
		$configContext = ConfigContext::getInstance();
		if ($configContext->getGlobalVar('each_before')  != "") {
			
			$result_class = &class_load ( $configContext->getGlobalVar('each_before'), "restrict" );
			$result_class->_add ( 'etuid', $wf_etuid );
			$result_class->validate ();
		}
		return true;
	}
	/**
	 * 每步流程后都处理
	 *
	 * @param unknown_type $wf_etuid  实例id
	 */
	private function afterAction($wf_etuid) {
		//配置全局类
		$configContext = ConfigContext::getInstance();
		if ($configContext->getGlobalVar('each_after')  != "") {
			$result_class = &class_load ( $configContext->getGlobalVar('each_after'), "function" );
			$result_class->_add ( 'etuid', $wf_etuid );
			$result_class->validate ();
		}
	}
	/**
	 * 
	 * 下一节点前处理，在下一节点上配置
	 */
	public function nextNoteBefore() {
		$commandContext = CommandContext::getInstance();
		$wf_etuid = $commandContext->getEtuid();
		$next_wf_uid = $commandContext->getWfuid();//下一节点xml文件
		$nextStepid = $commandContext->getNextStepid();//下一节点id
		if(!isNull($nextStepid)){
			$nextAction = XmlUtil::getAction($next_wf_uid, $nextStepid,DEFAULT_ACTION);//获取当前任务
			$beforeactions = $nextAction->getElementsByTagName ( 'before-action' );
			foreach ( $beforeactions as $beforeaction ) {
				$type = $beforeaction->getAttribute ( "type" );
				if ($type == 'beanshell') {
					$result_class = &class_load ( $beforeaction, 'restrict' );
					$result_class->_add ( 'etuid', $wf_etuid );
					$result_class->action ();
				}
			}
		}
		return true;
	}
}
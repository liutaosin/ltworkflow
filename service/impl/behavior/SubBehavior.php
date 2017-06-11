<?php 
class SubBehavior implements Behavior{
	private $wf_result;
	function __construct($wf_result){
		$this->wf_result = $wf_result;
	}
	public function execute(){
		$taskDB = TaskDB::getInstance();
		//任务全局类
		$commandContext = CommandContext::getInstance();
		$wf_uid = $commandContext->getWfuid();
		$wf_etuid = $commandContext->getEtuid();
		$wf_id = $commandContext->getStepid();
		$actionid = $commandContext->getActionid();
		
		$wf_nodevalue = $this->wf_result->getAttribute ( "step" );
		//处理子流程标记
		$subflow_over = "false";
		//获取节点
		$wf_xml = XmlUtil::getConfiguration($wf_uid);
		$wf_node = XmlUtil::getElementById ( $wf_nodevalue, $wf_xml );
		
		foreach ( $wf_node->getElementsByTagName ( 'sub-flow' ) as $wf_subflow ) {
			if (XmlUtil::isResultCondition ( $wf_subflow, $wf_etuid )) {
				$subflow_over = "true";
				$wf_subuid = $wf_subflow->getAttribute ( 'uid' );
				wf_debug ( $wf_subflow->getAttribute ( 'title' ) );
				
				$taskDB->deleteCurrentStep ( $wf_id, $wf_etuid, $wf_uid );
				$this->initialize_subflow ( $wf_subuid, $wf_etuid, $wf_uid, $wf_nodevalue );
				break;
			}
		}
		//未找到任何子流程，自动送审下一环节	
		if ($subflow_over == "false") {
			//处理结果集
			$taskDB->deleteCurrentStep ( $wf_id, $wf_etuid, $wf_uid );
			//修改当前执行环境
			$commandContext->setActionid(DEFAULT_ACTION);
			$commandContext->setStepid($wf_nodevalue);
			//自动通过
			$taskCommandImpl = new TaskCommandImpl();
			$taskCommandImpl->doTask();
		
		}

	}
	/*
	 *创建子流程
	 */
	private function initialize_subflow($wf_uid, $wf_etuid, $wf_puid, $wf_pid) {
		$taskDB = TaskDB::getInstance();
		wf_debug ( 'doaction:初始化子流程..' . $wf_uid );
		//任务全局类
		$commandContext = CommandContext::getInstance();
		//配置全局类
		$configContext = ConfigContext::getInstance();
		//保存到子流程栈中, wf_puid 父流程ID， wf_pid 父流程节点
		$taskDB->saveFlowStack ( $wf_puid, $wf_pid, $wf_uid, $wf_etuid );
		
		$wf_xml = XmlUtil::getConfiguration ( $wf_uid );
		//根据子流程xml初始化全局变量
		XmlUtil::setGlobal ( $wf_uid );
		$initSubNode = $configContext->getInitElement($wf_uid);
		if (!empty($initSubNode)) {
			//初始化配置信息。
			$init_sub = &class_load ($initSubNode, "init", $wf_etuid );
			$init_sub->init_entry ();
		}
		
		//获取 initial-actions
		$wf_init_actions = $wf_xml->getElementsByTagName ( 'initial-actions' );
	
		//获取action id
		$wf_init = $wf_init_actions->item ( 0 );
		$wf_id = $wf_init->getAttribute ( "id" );
		$wf_actionid = $wf_init->getElementsByTagName ( 'action' )->item ( 0 )->getAttribute ( "id" );
		//修改当前执行环境
		$commandContext->setActionid($wf_actionid);
		$commandContext->setStepid($wf_id);
		$commandContext->setWfuid($wf_uid);
		//自动通过
		$taskCommandImpl = new TaskCommandImpl();
		$taskCommandImpl->doTask();
	}
}
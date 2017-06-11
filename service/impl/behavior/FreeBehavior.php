<?php 

class FreeBehavior  implements Behavior{
	private $wf_result;
	function __construct($wf_result){
		$this->wf_result = $wf_result;
	}
	public function execute() {
		$configContext = ConfigContext::getInstance();
		//任务全局类
		$commandContext = CommandContext::getInstance();
		$wf_uid = $commandContext->getWfuid();
		$wf_etuid = $commandContext->getEtuid();
		$wf_id = $commandContext->getStepid();
		$actionid = $commandContext->getActionid();
		//下一环节为自由节点，计算审批人
		$nextstep = $this->wf_result->getAttribute ( "step" );
		$nextnode = XmlUtil::getElementById ( $nextstep );
		$next_freestep = $nextnode->getAttribute ( "freestep" );
		
		$user = '';
		$step_name = '';
		$freeconfig_info = $commandContext->getSysVar('freeconfig_info');
		if (isset ( $freeconfig_info ['step_' . $next_freestep] )) {
			$nextstep_config = $freeconfig_info['step_' . $next_freestep];
			$nextuser_type = $nextstep_config ['roletype'];
			$step_name = $nextstep_config ['stepname'];
			if ($nextuser_type == 1) {
				//直接定义员工
				$user = $nextstep_config ['rolevalue'];
			} else if ($nextuser_type == 2) {
				//脚本计算
				$freeconfig_class = &class_load ( $configContext->getGlobalVar('freeconfig'), "freeconfig" );
				$freeconfig_class->_add ( 'etuid', $wf_etuid );
				$freeconfig_class->_add ( 'type', $nextstep_config ['rolevalue'] );
				$user = $freeconfig_class->getuser ();
			}
		}
		//当前处理人为空或很当前登录人相同，自动处理下一环节
		if ($user == '' || $user == $commandContext->getSessionSsn()) {
			wf_debug ( 'doaction:处理自由节点无处理人,自动执行下一环节..' );
			$taskDB = TaskDB::getInstance();
			$taskDB->deleteCurrentStep ( $wf_id, $wf_etuid, $wf_uid );
			//修改当前执行环境
			$commandContext->setActionid(DEFAULT_ACTION);
			$commandContext->setStepid($nextstep);
			//自动通过
			$freeTaskCommandImpl = new FreeTaskCommandImpl();
			$freeTaskCommandImpl->doTask();
		} else {
			//加工xml处理人
			
			$wf_result = $this->handleRoleXML ( $this->wf_result, $user, $step_name );
			wf_debug ( 'doaction:处理自由节点结果..' . simplexml_import_dom ( $wf_result )->saveXML () );
			$behavior = new NormalBehavior($wf_result);
			$behavior->execute();
		}
		
	}
	
	/**
	 * 处理角色xml模板
	 *
	 * @param unknown_type $node	result dom
	 * @param unknown_type $user	下一处理人
	 * @param unknown_type $stepname	环节名称
	 * @return unknown
	 */
	private function handleRoleXML($node, $user, $stepname = '') {
		$domxml = new DOMDocument ( );
		//读取当前环节节点(解决node对象不能正确读取问题)
		$newnode = $domxml->importNode ( $node, TRUE );
		//创建role节点
		$rolenode = $domxml->createElement ( "role" );
		//创建属性-角色类型
		$roleAttribute = $domxml->createAttribute ( 'type' );
		$roleAttribute->value = '11';
		$rolenode->appendChild ( $roleAttribute );
		//创建属性-角色名称
		$roleAttribute = $domxml->createAttribute ( 'typeName' );
		$roleAttribute->value = $stepname;
		$rolenode->appendChild ( $roleAttribute );
		//创建子节点
		$usernode = $domxml->createElement ( "usercode", $user );
		$rolenode->appendChild ( $usernode );
		//将创建好的节点添加至原xml
		$rolesnode = $newnode->getElementsByTagName ( "roles" )->item ( 0 );
		$rolesnode->appendChild ( $rolenode );
		return $newnode;
	}
}
<?php 
//流程前校验xml模板
if (! defined ( "FREECONFIG_VALIDATE" )) {
	define ( "FREECONFIG_VALIDATE", '<validate id="validate" type="beanshell" path="#validate_path#"><type>#validate_type#</type></validate>' );
}
//流程后处理xml模板
if (! defined ( "FREECONFIG_POSTFUN" )) {
	define ( "FREECONFIG_POSTFUN", '<post-function id="postfun" type="beanshell" path="#post_path#"><type>#post_type#</type></post-function>' );
}
//角色模板
if (! defined ( "FREECONFIG_ROLE" )) {
	define ( "FREECONFIG_ROLE", '<role type="11"><usercode>#user_code#</usercode></role>' );
}
class FreeTaskCommandImpl{
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
		
		$this->loadFreeCoinfig();//加载配置信息
		
		$wf_node = XmlUtil::getElementById ( $wf_id );
		$cur_freestep = $wf_node->getAttribute ( "freestep" );
		$action = XmlUtil::getAction($wf_uid, $wf_id,$actionid);//获取当前任务
		if($cur_freestep != ""){
			//判断、加载流程前校验
			$action = $this->handlePreXML ( $cur_freestep, $action );
			//判断、加载流程后处理
			$action = $this->handlePostXML ( $cur_freestep, $action );
		}
		
		$this->validateBefore($action, $wf_etuid);//任务前处理
		$behavior = XmlUtil::getBehavior($action,$wf_etuid);
		$behavior->execute();//处理当前任务
		$this->postfunction($action, $wf_etuid);//任务后处理
	}
	/**
	 * 
	 * 设置审批状态
	 */
	public function setApproveStatus(){
		$commandContext = CommandContext::getInstance();
		$actionid = $commandContext->getActionid();

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
	 * 读取自由配置信息
	 * @param $name
	 */
	private function loadFreeCoinfig() {
		//自定义条件，取自定义流程定义
		$configContext = ConfigContext::getInstance();
		$commandContext = CommandContext::getInstance();
		$wf_etuid = $commandContext->getEtuid();
		$freeconfig_info = $commandContext->getSysVar('freeconfig_info');
		if(empty($freeconfig_info)){
			$freeconfig_class = &class_load ( $configContext->getGlobalVar('freeconfig'), "freeconfig" );
			$freeconfig_class->_add ( 'etuid', $wf_etuid );
			$name = $freeconfig_class->gettype ();
			$freeTaskDB = new FreeTaskDB();
			$freeconfig_info = $freeTaskDB->getFreeCoinfig($name);
			$commandContext->setSysVar('freeconfig_info',$freeconfig_info);
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
	 * 处理事前xml模板
	 *
	 * @param unknown_type $name	节点名称
	 * @param unknown_type $node	action dom
	 * @return unknown	加工完毕 action dom
	 */
	private function handlePreXML($name, $node) {
		$newnode = $node;
		$commandContext = CommandContext::getInstance();
		$freeconfig_info = $commandContext->getSysVar('freeconfig_info');
		if ( isset ( $freeconfig_info ['step_' . $name] )) {
			$stepconfig = $freeconfig_info['step_' . $name];
			//判断、加载流程前校验
			$domxml = new DOMDocument ( );
			$current_validate = $stepconfig ['stepbefore'];
			if ($current_validate != '') {
				//替换validate xml模板变量
				$validate_str = str_replace ( "#validate_path#", $freeconfig_info ['beforefile'], FREECONFIG_VALIDATE );
				$validate_str = str_replace ( "#validate_type#", $current_validate, $validate_str );
				$domxml->loadXML ( $validate_str );
				$validate_node = $domxml->getElementsByTagName ( "validate" )->item ( 0 );
				//读取当前环节节点(解决node对象不能正确读取问题)
				$newnode = $domxml->importNode ( $node, TRUE );
				//插入validate xml
				$newnode->appendChild ( $validate_node );
			}
		
		}
		return $newnode;
	}
	
	/**
	 * 处理事后xml模板
	 *
	 * @param unknown_type $name	节点名称
	 * @param unknown_type $node	action dom
	 * @return unknown	加工完毕 action dom
	 */
	private function handlePostXML($name, $node) {
		$newnode = $node;
		$commandContext = CommandContext::getInstance();
		$freeconfig_info = $commandContext->getSysVar('freeconfig_info');
		if (isset ( $freeconfig_info['step_' . $name] )) {
			$stepconfig = $freeconfig_info ['step_' . $name];
			//判断、加载流程后处理
			$domxml = new DOMDocument ( );
			$current_postfun = $stepconfig ['stepafter'];
			if ($current_postfun != '') {
				//替换post-function xml模板变量
				$postfun_str = str_replace ( "#post_path#", $freeconfig_info ['afterfile'], FREECONFIG_POSTFUN );
				$postfun_str = str_replace ( "#post_type#", $current_postfun, $postfun_str );
				$domxml->loadXML ( $postfun_str );
				$postfun_node = $domxml->getElementsByTagName ( "post-function" )->item ( 0 );
				//读取当前环节节点(解决node对象不能正确读取问题)
				$newnode = $domxml->importNode ( $node, TRUE );
				//插入post-function xml
				$newnode->appendChild ( $postfun_node );
			}
		}
		return $newnode;
	}
}
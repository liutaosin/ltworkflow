<?php 
class InitCommandImpl{
	/**
	 * 初始化全局配置。
	 *
	 */
	function init() {
		//配置全局类
		$configContext = ConfigContext::getInstance();
		//任务全局类
		$commandContext = CommandContext::getInstance();
		$et_uid = $commandContext->getEtuid();
		$actionid = $commandContext->getActionid();
		$ssn = $commandContext->getSessionSsn();
		$user_code = $commandContext->getOrgcreateuser();
		$router = $commandContext->getRouter();
		$comment = $commandContext->getComment();
		
		//获取单据流程信息
		$workflowInfoDB = new InfoDB();
		$wfEntry = $workflowInfoDB->getWfEntry($et_uid);
		if(empty($wfEntry)){//如果单据信息不存在，创建单据实例信息
			$wfuid = $commandContext->getWfuid();
			$this->createEntry($wfuid, $ssn, $et_uid);
			$wfEntry = $workflowInfoDB->getWfEntry($et_uid);
		}
		//初始化全局变量
		if($commandContext->getSysVar('@@createuser__')==""){
			$userDB = $configContext->getCustomObj('userinfo');
			$userInfo = $userDB->getUser($wfEntry ['et_createuser']);
			$commandContext->setSysVar('@@wf_uid__',$wfEntry ['wf_uid']);
			$commandContext->setSysVar('@@createuser__',$wfEntry ['et_createuser']);
			$commandContext->setSysVar('@@orgcreateuser__',$user_code!=""?$user_code:$wfEntry ['et_createuser']);
			if(!empty($userInfo)){
				$commandContext->setSysVar('@@createusername__', $userInfo['u_name']);
				$commandContext->setSysVar('@@createusermail__', $userInfo['u_email']);
			}
		}

		//判断是否有条件处理
		$error_msg = $this->validate ($et_uid, $actionid, $router );
		if (! isNull ( $error_msg )) {
			throw new Exception ( $error_msg,2);
		}
		//补充流程信息
		$taskDB  = TaskDB::getInstance();
		$curstep = $taskDB->getCurrentStep ( $et_uid,$ssn );
		$wf_uid = $curstep['wf_uid'];
		$commandContext->setStepid($curstep['cs_id']);
		$commandContext->setWfuid($curstep['wf_uid']);
		$commandContext->setComment($curstep['wf_uid'],$comment['actionid'],$curstep['cs_id'],$curstep['cs_nodename'],$comment['comment']);//记录审批意见用，只保存进入时状态
		
		//非保存处理
		if ($actionid != "-11") { //如果不是保存草稿则算是进入流程，进入流程标记
			$taskDB = TaskDB::getInstance();
			$taskDB->joinWorkflow ( $et_uid );
		}	
		
		//根据主流程xml初始化全局变量;
		$main_wf_uid = $commandContext->getSysVar('@@wf_uid__');
		XmlUtil::setGlobal ($main_wf_uid);
		$initMainNode = $configContext->getInitElement($main_wf_uid);
		if (!empty($initMainNode)) {
			//初始化配置信息。
			$init_cls = &class_load ($initMainNode, "init", $et_uid );
			$init_cls->init_entry ();
		}
		//子流程处理 ，$commandContext->getSysVar('@@wf_uid__')为主流程id
		if ($wf_uid != $main_wf_uid) {
			XmlUtil::setGlobal ( $wf_uid );
			$initSubNode = $configContext->getInitElement($wf_uid);
			if (!empty($initSubNode)) {
				//初始化配置信息。
				$init_sub = &class_load ( $initSubNode, "init", $et_uid );
				$init_sub->init_entry ();
			}
		}
		
	}
	/**
	 * 
	 * 创建流程实例
	 */
	private function createEntry($wf_wfuid,$wf_createuser,$et_uid){
		//根据条件获取对应的流程
		$wf_xml = XmlUtil::getConfiguration ( $wf_wfuid );
		//获取 initial-actions
		$wf_init_actions = $wf_xml->getElementsByTagName ( 'initial-actions' );
		//获取action id
		$wf_init = $wf_init_actions->item ( 0 );
		$wf_id = $wf_init->getAttribute ( "id" );
		$taskDB = TaskDB::getInstance();
		$taskDB->createEntry ( $wf_wfuid, $wf_createuser, $wf_id, $et_uid );
	}
	/*
	 * 校验是否符合处理的权限
	 * 
	 */
	private function validate($wf_etuid, $wf_actionid, $router = "") {
		$commandContext = CommandContext::getInstance();
		$configContext = ConfigContext::getInstance();
		$userDB = $configContext->getCustomObj('userinfo');
		
		if (isNull ( $wf_etuid )) {
			return "实例信息丢失，请联系系统管理员!";
		}
		//会签时校验会签人不能为空
		if ($wf_actionid == "-500" || $wf_actionid == "-550") {
			if (empty($router) ) {
				return "会签人员选择失败，请重新选择！";
			}
			$freeRouteDB = new FreeRouteDB();
			$existRouter = $freeRouteDB->isExistRouter ( $wf_etuid,$router );
			if(!empty($existRouter)){
				$userArray = $userDB->getUserList($existRouter);
				$cur_user = "";
				foreach ($userArray as $user){
					$cur_user .= $user['u_name'].",";
				}
				return "会签人".$cur_user."已在流程中，不能重复选择 ！";
			}
		}
		
		//判断是否可收回
		if ($wf_actionid == '-99') {
			if ($commandContext->getSysVar('@@createuser__') == $commandContext->getSessionSsn()) {
				return "";
			} else {
				return "您不是该单据起草人，不能回收此单据！";
			}
		}
		
		//判断当前处理人是否正确
		//根据et_uid获取current_step中的数据
		$taskDB  = TaskDB::getInstance();
		$steps = $taskDB->getCurrentSteps ( $wf_etuid );
		//检查当前审批人是否能处理
		$userList = array();
		foreach ( $steps as $row ) {
			array_push($userList, $row ['cs_salarysn']);
			if (strtoupper ( $row ['cs_salarysn'] ) == strtoupper ($commandContext->getSessionSsn())) {
				return "";
			}
		}
		if (empty( $userList )) {
			return "该单据已审批完成！";
		} else {
			$userArray = $userDB->getUserList($userList);
			$cur_user = "";
			foreach ($userArray as $user){
				$cur_user .= $user['u_name'].",";
			}
			return "该单据已提交[" . $cur_user . "]处理！";
		}
	
	}
	/**
	 * 
	 * 设置审批状态
	 */
	public function setApproveStatus(){
		//任务全局类
		$commandContext = CommandContext::getInstance();
		$commandContext->setFlowStatus(WF_STATUS_DEALING);
		$commandContext->setResponseBox('status', 'success');
		$commandContext->setResponseBox('flowstatus','');
	}
}
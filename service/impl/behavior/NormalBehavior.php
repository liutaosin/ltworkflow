<?php 
class NormalBehavior implements Behavior{
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
		
		//获取节点名称
		$wf_nodeName =  XmlUtil::getElementById( $wf_nodevalue )->getAttribute ( "name" );
		
		//获取用户信息
		$wfroles = $this->wf_result->getElementsByTagName ( 'roles' )->item ( 0 );
		$wf_users = XmlUtil::getRoles ( $wfroles, $wf_etuid );
		//获取流程描述
		$usercomm = "";
		foreach ( $wfroles->getElementsByTagName ( 'role' ) as $wf_xmlrole ) {
			$usercomm =  $wf_xmlrole->getAttribute ( 'typeName' ) ;
		}
		//操作currentstep表避免循环取值时数据不变导致死循环
		$taskDB->deleteCurrentStep ( $wf_id, $wf_etuid, $wf_uid );
		$taskDB->completeEntry ( $wf_users, $wf_nodevalue, $wf_etuid, '', $wf_uid, '', '', $usercomm );
		$commandContext->setNextuser($wf_users);
		if (count ( $wf_users ) == 1) {
			//判断是否同一个节点,同一节点时自动执行下一步
			$cur_user = current ( $wf_users );
			if ($cur_user == $commandContext->getSessionSsn()) {
				//判断下一环节是否自动通过,autopass属性（如果自动通过环节有必填项，不能自动通过）
				$next_node = XmlUtil::getElementById ( $wf_nodevalue );
				$passflag = $next_node->getAttribute ( 'autopass' );
				if ($passflag != 'no') {
					
					//获取流程基本信息
					$wf_etuid = $commandContext->getEtuid();
					$comment = $commandContext->getComment();
					$router = $commandContext->getRouter();
					$wf_status = $commandContext->getFlowStatus();
					$wf_comment = $comment['comment'];
					$wf_id = $comment['stepid'];
					$wf_stepName = $comment['stepName'];
					$wf_uid = $comment['wfuid'];
					$actionid = $comment['actionid'];

					//记录审批日志
					$taskDB->worklowLog($wf_etuid, $wf_id,$wf_stepName,$wf_status,$wf_comment, WF_LOG_TYPE_APP, $actionid , $wf_uid);					

					//修改当前执行环境
					$commandContext->setActionid(DEFAULT_ACTION);
					$commandContext->setStepid($wf_nodevalue);
					//自动通过
					$taskCommandImpl = new TaskCommandImpl();
					$taskCommandImpl->doTask();
					
					//更新流程状态，只更新comment中的步骤ID记录
					$commandContext->setComment($wf_uid,DEFAULT_ACTION,$wf_nodevalue,$wf_nodeName,$wf_comment);
				}
			}
		}
	}
	
}
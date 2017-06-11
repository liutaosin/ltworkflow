<?php

/**
 * Class ParallelBehavior
 * 旧版不再使用，为兼容老系统保留
 */
class ParallelBehavior implements Behavior{
	private $wf_result;
	function __construct($wf_result){
		$this->wf_result = $wf_result;
	}
	public function execute(){
		//任务全局类
		$commandContext = CommandContext::getInstance();
		$wf_uid = $commandContext->getWfuid();
		$wf_etuid = $commandContext->getEtuid();
		$wf_id = $commandContext->getStepid();
		$actionid = $commandContext->getActionid();
		
		$wf_splitid = $this->wf_result->getAttribute ( "split" );
		$wf_joinid = $this->wf_result->getAttribute ( "join" );
		//流程拆分
		if (! isNull ( $wf_splitid )) {
			$this->splitFlow($wf_splitid,$wf_uid,$wf_etuid,$wf_id);
			return;
		}
		//流程合并
		if (! isNull ( $wf_joinid )) {
			
			$this->joinFlow($wf_joinid,$wf_uid,$wf_etuid,$wf_id);
			return;
		}
	}
	/**
	 * 
	 * 流程拆分
	 * @param unknown_type $wf_splitid
	 * @param unknown_type $wf_uid
	 * @param unknown_type $wf_etuid
	 * @param unknown_type $wf_id
	 */
	private function splitFlow($wf_splitid,$wf_uid,$wf_etuid,$wf_id){
		$taskDB = TaskDB::getInstance();
		wf_debug ( 'split:进入流程拆分..' . simplexml_import_dom ( $this->wf_result )->saveXML () );
		$commandContext = CommandContext::getInstance();
		//取拆分配置
		$wf_xml = XmlUtil::getConfiguration($wf_uid);
		$split_node = XmlUtil::getElementById ( $wf_splitid, $wf_xml );
		
		//删除当前环节
		$taskDB->deleteCurrentStep ( $wf_id, $wf_etuid, $wf_uid );
		//处理条件结果集
		foreach ( $split_node->getElementsByTagName ( 'result' ) as $split_result ) {
			if (XmlUtil::isResultCondition ( $split_result, $wf_etuid )) {
				wf_debug ( 'split:进入条件结果集..' . simplexml_import_dom ( $split_result )->saveXML () );
				$split_roles = $split_result->getElementsByTagName ( 'roles' )->item ( 0 );
				$split_users = XmlUtil::getRoles ( $split_roles, $wf_etuid );
				$split_nodevalue = $split_result->getAttribute ( "step" );
				$split_nodestatus = $wf_splitid;
				//获取流程描述
				$usercomm = "";
				foreach ( $split_roles->getElementsByTagName ( 'role' ) as $wf_xmlrole ) {
					$usercomm =  $wf_xmlrole->getAttribute ( 'typeName' ) ;
				}
				$taskDB->completeEntry ( $split_users, $split_nodevalue, $wf_etuid, $split_nodestatus, $wf_uid, '', $wf_splitid, $usercomm );

				//并行流程拆分时多次调用，合并下一处理人
				$nextuserList = $commandContext->getNextuser();
				$nextuserList = array_merge($nextuserList,$split_users);
				$commandContext->setNextuser($nextuserList);
				
				
			}
		}
		//处理无条件结果集	
		foreach ( $split_node->getElementsByTagName ( 'unconditional-result' ) as $split_result ) {
			wf_debug ( 'split:进入无条件结果集..' . simplexml_import_dom ( $split_result )->saveXML () );
			$split_roles = $split_result->getElementsByTagName ( 'roles' )->item ( 0 );
			$split_users = XmlUtil::getRoles ( $split_roles, $wf_etuid );
			$split_nodevalue = $split_result->getAttribute ( "step" );
			$split_nodestatus = $wf_splitid;
			//获取流程描述
			$usercomm = "";
			foreach ( $split_roles->getElementsByTagName ( 'role' ) as $wf_xmlrole ) {
				$usercomm =  $wf_xmlrole->getAttribute ( 'typeName' ) ;
			}
			$taskDB->completeEntry ( $split_users, $split_nodevalue, $wf_etuid, $split_nodestatus, $wf_uid, '', $wf_splitid, $usercomm );
			//并行流程拆分时多次调用，合并下一处理人
			$nextuserList = $commandContext->getNextuser();
			$nextuserList = array_merge($nextuserList,$split_users);
			$commandContext->setNextuser($nextuserList);
		}
	}
	/**
	 * 
	 * 流程合并
	 * @param unknown_type $wf_joinid
	 * @param unknown_type $wf_uid
	 * @param unknown_type $wf_etuid
	 * @param unknown_type $wf_id
	 */
	private function joinFlow($wf_joinid,$wf_uid,$wf_etuid,$wf_id){
		$taskDB = TaskDB::getInstance();
		wf_debug ( 'join:进入流程合并..' . simplexml_import_dom ( $this->wf_result )->saveXML () );
		$commandContext = CommandContext::getInstance();
		//取合并配置
		$wf_xml = XmlUtil::getConfiguration($wf_uid);
		$split_node = XmlUtil::getElementById ( $wf_joinid, $wf_xml );
		//删除当前环节
		$taskDB->deleteCurrentStep ( $wf_id, $wf_etuid, $wf_uid );
		//判断是否其他分支是否全部通过
		$othersteps = $taskDB->getCurrentSteps ( $wf_etuid );
		if (count ( $othersteps ) == 0) {
			wf_debug ( 'join:流程已合并，进入下一节点判断..' . simplexml_import_dom ( $split_node )->saveXML () );
			//修改当前执行环境
			$commandContext->setActionid(DEFAULT_ACTION);
			$commandContext->setWfuid($wf_uid);
			$commandContext->setStepid($wf_joinid);
			//处理下一节点
			$taskCommandImpl = new TaskCommandImpl();
			$taskCommandImpl->doTask();
			
		} else {
			wf_debug ( 'join:流程未合并，等待其他环节处理' );
			//读取其他分支当前处理人
			$commandContext = CommandContext::getInstance();
			$nextuserList = array();
			foreach ($othersteps as $row){
				array_push($nextuserList, $row['cs_salarysn']);
			}
			$commandContext->setNextuser($nextuserList);
		}
	}
	
}
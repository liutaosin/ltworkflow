<?php 
class InfoService{
	private static $_instance;
	//private标记的构造方法
	private function __construct(){
	}
	//创建__clone方法防止对象被复制克隆
	public function __clone(){
		echo 'Clone is not allow!';
	}
	 
	//单例方法,用于访问实例的公共的静态方法
	public static function getInstance(){
		if(!(self::$_instance instanceof self)){
			self::$_instance = new self;
		}
		return self::$_instance;
	}
	/**
	 * 根据单据ID获取流程信息
	 * @param unknown_type $etuid 实例id
	 * @param unknown_type $ssn 工号
	 */
	function workflowInfo($etuid,$ssn) {
		$workflowInfoDB = new InfoDB();
		$workflow =  $workflowInfoDB->workflowInfo($etuid, $ssn);
		if(empty($workflow)){//还没有工作流实例为新建状态
			$workflow ['wf_uid'] = "";//当前流程id
			$workflow ['cs_id'] = "1";//当前环节id
			$workflow ['cs_status'] = "Draft";//审批状态
			$workflow ['et_state'] = "2";//流程状态（ 2 流程中 ，5审批完成）
			$workflow ['cs_salarysn'] = $ssn;//当前处理人
			$workflow ['cs_updateby'] = $ssn;//当前处理人
			$workflow ['et_createuser'] = $ssn;//起草人
			$workflow ['isCreateUser'] = true;
		}
		return $workflow;
	}
	/**
	 * 返回实例当前操作
	 *
	 * @param string $wf_uid	工作流id
	 * @param string $wf_id		环节id
	 * @param string $wf_status	流程状态
	 * @param boolean $isRouting 是否含会签按钮
	 * @return array	当前操作数组
	 */
	function getAvailableActions($wf_uid, $wf_id, $wf_status,$isRouting = true) {
		
		$wf_actions = array ();

		//如果是会签
		if ($wf_id == '-600') {
			$wf_action ["id"] = $wf_id;
			$wf_action ["name"] = '确认';
			array_push ( $wf_actions, $wf_action );
			
			//2013-8-27 增加驳回按钮
			$wf_action ["id"] = "-200";
			$wf_action ["name"] = '驳回';
			array_push ( $wf_actions, $wf_action );
			
			$wf_action ["id"] = "-550";
			$wf_action ["name"] = '会签';
			array_push ( $wf_actions, $wf_action );
			
			return $wf_actions;
		}
		
		//如果是循环会签
		if ($wf_id == '-650') {
			$wf_action ["id"] = $wf_id;
			$wf_action ["name"] = '确认';
			array_push ( $wf_actions, $wf_action );
			
			//2013-8-27 增加驳回按钮
			$wf_action ["id"] = "-250";
			$wf_action ["name"] = '驳回';
			array_push ( $wf_actions, $wf_action );
			
			$wf_action ["id"] = "-550";
			$wf_action ["name"] = '会签';
			array_push ( $wf_actions, $wf_action );
			
			return $wf_actions;
		}
		
		//如果$wf_status = 'Draft', 'Create' , 'Reject'， 添加保存动作
		if ($wf_status == 'Draft' || $wf_status == 'Create' || $wf_status == 'Reject') {
			$wf_action ["id"] = "-11";
			$wf_action ["name"] = '保存';
			array_push ( $wf_actions, $wf_action );
		}
		
		if ($wf_status == 'Create' || $wf_status == 'Draft') {
			
			$wf_xml = XmlUtil::getConfiguration ( $wf_uid );
			
			//根据ID获取符合条件的actions
			$wf_node = $wf_xml->getElementsByTagName ( 'initial-actions' )->item ( 0 );
			
			//获取action 
			foreach ( $wf_node->getElementsByTagName ( "action" ) as $wf_actionnode ) {
				$wf_action = array ();
				$wf_action ["id"] = $wf_actionnode->getAttribute ( "id" );
				$wf_action ["name"] = $wf_actionnode->getAttribute ( "name" );
				
				array_push ( $wf_actions, $wf_action );				
			}
		} else if ($wf_status != 'Routing') {
			
			$wf_xml = XmlUtil::getConfiguration ( $wf_uid );
			//根据ID获取符合条件的actions
			$wf_node = XmlUtil::getElementById ( $wf_id, $wf_xml );
			
			//获取action 
			foreach ( $wf_node->getElementsByTagName ( "action" ) as $wf_actionnode ) {
				$wf_action = array ();
				$wf_action ["id"] = $wf_actionnode->getAttribute ( "id" );
				$wf_action ["name"] = $wf_actionnode->getAttribute ( "name" );
				
				array_push ( $wf_actions, $wf_action );
			}
			//如果处理才驳回
			if ($wf_status == 'Dealing') {
				$wf_action ["id"] = "-100";
				$wf_action ["name"] = '驳回';
				array_push ( $wf_actions, $wf_action );
			}
		}
		if($isRouting){
			$wf_action ["id"] = "-500";
			$wf_action ["name"] = '会签';
			array_push ( $wf_actions, $wf_action );
		}
		return $wf_actions;
	}
	/**
	 * 
	 * 获取审批意见
	 * @param $etuid
	 */
	function queryWorkflowLog($etuid){
		$configContext = ConfigContext::getInstance();
		$userDB = $configContext->getCustomObj('userinfo');
		return $userDB->queryWorkflowLog($etuid);
	}
}
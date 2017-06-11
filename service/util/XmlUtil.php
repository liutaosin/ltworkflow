<?php 
class XmlUtil{
	/**
	 * 读取xml配置
	 * @param $wf_wfuid 流程id
	 */
	public static function getConfiguration($wf_wfuid) {
		//配置全局类
		$configContext = ConfigContext::getInstance();
		$wf_xml = $configContext->getRootElement($wf_wfuid);
		if($wf_xml == ""){
			$wf_xml = new DOMDocument ( );
			$workflowInfoDB = new InfoDB();
			$dxml = $workflowInfoDB->getWorkflowById ( $wf_wfuid );
			if (ENVIRONMENT=='production'){//为兼容linux 下freeTDS添加
				$dxml = iconv ( "utf-8", "gbk//IGNORE", $dxml );
			}
			$wf_xml->loadXML ($dxml);
			$configContext->setRootElement($wf_wfuid,$wf_xml);
			
		}
		return $wf_xml;
	}

	/**
	 * 
	 * 获取xml配置global内容
	 * @param $wf_uid 流程id
	 */
	public static function setGlobal($wf_uid) {
		//配置全局类
		$configContext = ConfigContext::getInstance();
		//任务全局类
		$commandContext = CommandContext::getInstance();
		$wf_xml = self::getConfiguration ( $wf_uid );
		//获取global变量
		$globalnodes = $wf_xml->getElementsByTagName ( 'global' );
		
		if ($globalnodes->length > 0) {
			$globalnode = $globalnodes->item ( 0 );
			
			//执行初始化方法
			$wf_init = $globalnode->getElementsByTagName ( 'init' );
			if ($wf_init->length > 0) {
				$configContext->setInitElement($wf_uid,$wf_init->item ( 0 ));
			}
			
			//驳回方法
			$wf_reject = $globalnode->getElementsByTagName ( 'reject' );
			if ($wf_reject->length > 0) {
				$configContext->setGlobalVar('reject',$wf_reject->item ( 0 ));
			}
			//会签时处理（会签到起草人）
			$wf_countersign = $globalnode->getElementsByTagName ( 'countersign' );
			if ($wf_countersign->length > 0) {
				$configContext->setGlobalVar('countersign',$wf_countersign->item ( 0 ));
			}
			
			//获取email节点		
			if (WF_EMAIL_FLAG) {
				$email = $globalnode->getElementsByTagName ( 'email' );
				if ($email->length > 0) {
					$commandContext->setEmail('type',$email->item ( 0 )->getAttribute ( "type" ));
					$commandContext->setEmail('doctype',$email->item ( 0 )->getAttribute ( "doctype" ));
				}
			}
			
			//每步流程前校验
			$wf_each_before = $globalnode->getElementsByTagName ( 'each_before' );
			if ($wf_each_before->length > 0) {
				$configContext->setGlobalVar('each_before',$wf_each_before->item ( 0 ));
			}
			
			//每步流程后处理
			$wf_each_after = $globalnode->getElementsByTagName ( 'each_after' );
			if ($wf_each_after->length > 0) {
				$configContext->setGlobalVar('each_after',$wf_each_after->item ( 0 ));
			}
			
			//组织结构判断
			$wf_organization = $globalnode->getElementsByTagName ( 'organization' );
			if ($wf_organization->length > 0) {
				$configContext->setGlobalVar('organization',$wf_organization->item ( 0 ));
			}
			
			//自定义流程处理
			$wf_freeconfig = $globalnode->getElementsByTagName ( 'freeconfig' );
			if ($wf_freeconfig->length > 0) {
				$configContext->setGlobalVar('freeconfig',$wf_freeconfig->item ( 0 ));
			}
		}
	}
	/**
	 * 获取xml配置节点
	 *
	 * @param unknown_type $wf_id	环节id
	 * @param unknown_type $wf_xml	xml配置文件
	 * @return 匹配节点
	 */
	public static function getElementById($wf_id, $wf_xml='') {
		if($wf_xml == ''){
			$commandContext = CommandContext::getInstance();
			$wf_uid = $commandContext->getWfuid();
			$wf_xml = XmlUtil::getConfiguration ( $wf_uid );
		}
		foreach ( $wf_xml->getElementsByTagName ( 'initial-actions' ) as $node ) {
			//判断是否id
			if ($node->getAttribute ( "id" ) == $wf_id) {
				return $node;
			}
		}
		
		foreach ( $wf_xml->getElementsByTagName ( 'step' ) as $node ) {
			//判断是否id
			if ($node->getAttribute ( "id" ) == $wf_id) {
				return $node;
			}
		}
		
		foreach ( $wf_xml->getElementsByTagName ( 'split' ) as $node ) {
			//判断是否id
			if ($node->getAttribute ( "id" ) == $wf_id) {
				return $node;
			}
		}
		
		foreach ( $wf_xml->getElementsByTagName ( 'join' ) as $node ) {
			//判断是否id
			if ($node->getAttribute ( "id" ) == $wf_id) {
				return $node;
			}
		}
		
		foreach ( $wf_xml->getElementsByTagName ( 'node' ) as $node ) {
			//判断是否id
			if ($node->getAttribute ( "id" ) == $wf_id) {
				return $node;
			}
		}
	}
	/**
	 * 判断流程条件
	 *
	 * @param unknown_type $wf_result 当前result节点
	 * @param unknown_type $wf_etuid 实例id
	 * @param unknown_type $rpath 反射目录
	 * @return unknown
	 */
	public static function isResultCondition($wf_result, $wf_etuid, $rpath = 'results') {
		$wf_conditions = $wf_result->getElementsByTagName ( 'condition' );
		foreach ( $wf_conditions as $wf_condition ) {
			$wf_conditiontype = $wf_condition->getAttribute ( 'type' );
			
			if ($wf_conditiontype == "shell") {
				$wf_value = $wf_condition->getAttribute ( 'value' );
				if (eval ( "return " . wf_iconvgbk ( $wf_value ) . ";" )) {
					return true;
				}
				return false;
			} else if ($wf_conditiontype == "beanshell") {
				//自定义条件
				$result_class = &class_load ( $wf_condition, $rpath );
				$result_class->_add ( 'etuid', $wf_etuid );
				
				$valid = $result_class->validate ();
				return $valid;
			
			} else if ($wf_conditiontype == "config") {
				//从配置取
				$infoDB = new InfoDB();
				$nextstepid = $wf_result->getAttribute ( 'step' );
				$expressions = $infoDB->getConditionExpression($nextstepid);
				foreach ($expressions as $expression){
					if(strpos($expression['expression'], "#") !== false || strpos($expression['expression'], "@") !== false){
						ErrorList::add(new ErrorBox($expression['expression'],1));
						throw new Exception ( '审批条件判断错误，请联系系统管理员',1 );
					}else{
						if (eval ( "return " .  $expression['expression']  . ";" )) {
							return true;
						}
					}
					
				}
				return false;
			
			} else if ($wf_conditiontype == "true") {
				return true;
			}
		}
		return false;
	}
	/**
	 * 
	 * 获取下一处理人
	 * @param unknown_type $wf_roles 当前role节点
	 * @param unknown_type $wf_etuid 实例id
	 */
	public static function getRoles($wf_roles, $wf_etuid) {
		$configContext = ConfigContext::getInstance();
		$userDB = $configContext->getCustomObj('userinfo');
		$wf_users = array ();
		foreach ( $wf_roles->getElementsByTagName ( 'role' ) as $wf_xmlrole ) {
			$wf_roletype = $wf_xmlrole->getAttribute ( 'type' );
			if ($wf_roletype == "0") {
				//取角色表
				$wf_ruid = $wf_xmlrole->getElementsByTagName ( 'uid' )->item ( 0 )->nodeValue;
				$wf_users = $userDB->getUserByRole ( $wf_ruid );
			
			} else if ($wf_roletype == "1") {
				//行政角色  
				//$wf_xmlrole->setAttribute('path', 'common/admin');
				$roles_class = &class_load ( $wf_xmlrole, 'roles' );
				$roles_class->_add ( 'etuid', $wf_etuid );
				$wf_users = $roles_class->getUserRole ();
			} else if ($wf_roletype == "2") {
				//从配置表取下一处理人
				$infoDB = new InfoDB();
				$expressions = $infoDB->getRoleExpression();
				foreach ($expressions as $expression){
					if(strpos($expression['expression'], "#") !== false || strpos($expression['expression'], "@") !== false){
						ErrorList::add(new ErrorBox($expression['expression'],1));
						throw new Exception ( '审批角色判断错误，请联系系统管理员',1 );
					}else{
						if (eval ( "return " .  $expression['expression']  . ";" )) {
							$wf_users = $infoDB->getUserByConfig($expression['group_uid']);
							break;//第一个满足条件的跳出
						}
					
					}
				}
			} else if ($wf_roletype == "10") {
				//自定义角色。
				$roles_class = &class_load ( $wf_xmlrole, 'roles', $wf_etuid );
				$roles_class->_add ( 'etuid', $wf_etuid );
				$wf_users = $roles_class->getUserRole ();
			
			} else if ($wf_roletype == "11") {
				//直接添加人员工号 多个用逗号分隔
				$code_list = $wf_xmlrole->getElementsByTagName ( 'usercode' )->item ( 0 )->nodeValue;
				$wf_users = explode ( ',', $code_list );
			
			}
		}
		return $wf_users;
	}
	/**
	 * 
	 * 获取当前动作
	 * @param $wf_uid 流程id
	 * @param $wf_id 当前环节id
	 * @param $wf_actionid 当前动作id
	 */
	public static function getAction($wf_uid,$wf_id,$wf_actionid){
		//读取xml配置文件
		$wf_xml = self::getConfiguration ( $wf_uid );
		$wf_node = self::getElementById ( $wf_id, $wf_xml );
		$wf_nodeid = $wf_node->getAttribute ( "id" );
		$wf_nodeaction = "";
		//获取对应的actions
		foreach ( $wf_node->getElementsByTagName ( "action" ) as $wf_nodeaction ) {
			if ($wf_nodeaction->getAttribute ( "id" ) == $wf_actionid || $wf_actionid == DEFAULT_ACTION) {
				wf_debug("stepid=".$wf_id."  actionid=".$wf_actionid);
				return $wf_nodeaction;
			}
		}
		return $wf_nodeaction;
	}
	/**
	 * 
	 * 获取流程出口
	 * @param  $wf_nodeaction 当前action 节点
	 * @param  $wf_etuid 实例id
	 */
	public static function getBehavior($wf_nodeaction,$wf_etuid){
		require_once WORKFLOW_BASE . '/service/impl/behavior/BehaviorFactory.php';

        $actionType = $wf_nodeaction->getAttribute ( "type" );
        //处理结果集
        $wf_results = $wf_nodeaction->getElementsByTagName ( 'result' );
		if($actionType == "split"){//并行审批
			 wf_debug ( 'doaction:进入并行流程判断...actionType='.$actionType);
            $paralleResult = array();
            foreach ( $wf_results as $wf_result ) {
                //处理顺序流程
                if (self::isResultCondition ( $wf_result, $wf_etuid )) {
                    wf_debug ( 'doaction:满足某条件..' . simplexml_import_dom ( $wf_result )->saveXML () );
                    array_push($paralleResult,$wf_result);
                }
            }
            $unconditional_result = $wf_nodeaction->getElementsByTagName ( 'unconditional-result' );
            if ($unconditional_result->length > 0) {
                $wf_result = $unconditional_result->item ( 0 );
                wf_debug ( 'doaction:处理无条件结果集..' . simplexml_import_dom ( $wf_result )->saveXML () );
                array_push($paralleResult,$wf_result);
            }
            $behavior = BehaviorFactory::getParalleBehavior($paralleResult);
        }else{
            $wf_iscondition = false;
            foreach ( $wf_results as $wf_result ) {
                //处理顺序流程
                if (self::isResultCondition ( $wf_result, $wf_etuid )) {
                    wf_debug ( 'doaction:满足某条件..' . simplexml_import_dom ( $wf_result )->saveXML () );
                    $wf_iscondition = true;
                    break;
                }
            }
            //处理无条件结果集
            if (!$wf_iscondition) {
                $wf_result = $wf_nodeaction->getElementsByTagName ( 'unconditional-result' )->item ( 0 );
                wf_debug ( 'doaction:处理无条件结果集..' . simplexml_import_dom ( $wf_result )->saveXML () );
            }
            $behavior = BehaviorFactory::getOuterBehavior( $wf_result);

        }

		wf_debug("当前处理Behavior：".get_class($behavior));
		return $behavior;
		
		
	}
	/**
	 * 
	 * 流程结束时处理
	 */
	static public function endTask($wf_uid,$wf_etuid){
		$wf_xml = self::getConfiguration($wf_uid);
		foreach ( $wf_xml->getElementsByTagName ( 'end-nodes' ) as $endnodes) {
			foreach ( $endnodes->getElementsByTagName ( 'node' ) as $node) {
				$wf_path = $node->getAttribute ( 'path' );
				 //自定义条件
				 if (! isNull ( $wf_path )) {
					$handl_class = &class_load ( $node, 'function' );
					$handl_class->_add ( 'etuid', $wf_etuid );
					$handl_class->validate ();
				}
			}
		}
	}
}
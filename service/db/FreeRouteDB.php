<?php 
class FreeRouteDB{
	/*
	 * 会签确认
	 */
	public function doRouteEntry($wf_etuid, $wf_id, $wf_uid, $router) {
		$commandContext = CommandContext::getInstance();
		$ssn = $commandContext->getSessionSsn();
		//下一处理人
		$nextuserList = array ();
		//查询当前处理人会签记录uid,cs_parentuid,cs_parentid
		$wf_sql = "SELECT uid,cs_parentuid,cs_parentid FROM t_wf_currentstep 
			WHERE  et_uid=".sqlFilter($wf_etuid,1)." AND  cs_salarysn = ".sqlFilter($ssn,3)." AND cs_id = ".sqlFilter($wf_id,1);
		$result = DBCommon::query ( $wf_sql );
		if ($row = DBCommon::fetch_array ( $result )) {
			$uid = $row['uid'];
			$cs_parentuid =  $row['cs_parentuid'];
			$cs_parentid =  $row['cs_parentid'];
			
			//删除当前处理人会签记录
			$wf_sql = "delete from t_wf_currentstep WHERE et_uid=".sqlFilter($wf_etuid,1)." AND  cs_salarysn = ".sqlFilter($ssn,3)." AND cs_id = ".sqlFilter($wf_id,1);
			DBCommon::exec($wf_sql);
			
			//相同cs_parentuid是否仍然存在会签记录
			$wf_sql = "SELECT cs_salarysn FROM t_wf_currentstep 
				WHERE  et_uid=".sqlFilter($wf_etuid,1)." AND  cs_parentuid = ".sqlFilter($cs_parentuid,1);
			$result = DBCommon::query ( $wf_sql );
			if (!DBCommon::fetch_array ( $result )) {//剩余会签人为空，返回上一级审批
				//还原cs_parentuid对应审批记录状态
				$wf_sql = "update t_wf_currentstep set cs_status=cs_prestatus where uid=".sqlFilter ( $cs_parentuid, 1 );
				DBCommon::exec( $wf_sql );
			}	
			//查询当前审批人信息
			$wf_sql = "select cs_salarysn from t_wf_currentstep where cs_status<>'Underway' AND et_uid=" . sqlFilter ( $wf_etuid, 1 );
			$result = DBCommon::query ( $wf_sql );
			while ( $row = DBCommon::fetch_array ( $result ) ) {
				array_push ( $nextuserList, $row ['cs_salarysn'] );
	
			}
		}
		return $nextuserList;
	}
	
	/**
	 * 功能: 会签
	 * 可会签多人，循环会签
	 */
	function freeRouteEntry($wf_etuid, $wf_id, $wf_uid, $wf_router) {
		$commandContext = CommandContext::getInstance();
		$ssn = $commandContext->getSessionSsn();
		//查询当前处理人审批记录uid,cs_id
		$wf_sql = "SELECT uid,cs_id,cs_status FROM t_wf_currentstep 
			WHERE  et_uid=".sqlFilter($wf_etuid,1)." AND  cs_salarysn = ".sqlFilter($ssn,3)." AND cs_id = ".sqlFilter($wf_id,1);

		$result = DBCommon::query ( $wf_sql );
		if ($row = DBCommon::fetch_array ( $result )) {
			$uid = $row['uid'];
			$cs_id =  $row['cs_id'];
			$cs_status =  $row['cs_status'];
			
			//备份审批状态，修改当前审批状态为Underway
			$wf_sql = "update t_wf_currentstep set cs_prestatus=cs_status, cs_status='Underway' 
					where uid=".sqlFilter($uid,1);
			DBCommon::exec($wf_sql);
				
			if($cs_status != "Routing"){//首次会签，删除其他审批点
				$wf_sql = "delete from t_wf_currentstep where uid<>".sqlFilter($uid,3). " and et_uid=" . sqlFilter ( $wf_etuid, 1 );
				DBCommon::exec($wf_sql);
			}
			
			//添加会签节点，记录cs_parentuid=uid,cs_parentid=cs_id
			foreach ($wf_router as $router){
				$wf_sql = "insert into t_wf_currentstep(
							uid,
							et_uid,
							cs_salarysn,
							cs_id,
							cs_status,
							cs_updateby,
							steplock,
							cs_endtime,
							wf_uid,
							cs_nodename,
							cs_parentuid,
							cs_parentid) 
				values('".uuid()."',
							".sqlFilter($wf_etuid,1).",
							".sqlFilter ($router,3).",
							'-600',
							 'Routing',
							 ".sqlFilter ($router, 3).",
							 'locked',
							 '".date('Y-m-d H:i:s')."',
							 ".sqlFilter($wf_uid,1).",
							 '会签',
							 ".sqlFilter($uid,1).",
							 ".sqlFilter($cs_id,3).")";
				DBCommon::exec( wf_iconvgbk ( $wf_sql ) );
			
			}
		}
		return $wf_router;
	
	}
	/**
	 * 
	 * 判断会签人是否已在会签流程中
	 * @param unknown_type $routers
	 */
	public function isExistRouter($wf_etuid,$routers){
		//流程中用户
		$curUserList = array();
		$wf_sql = "SELECT cs_salarysn FROM t_wf_currentstep WHERE et_uid=".sqlFilter($wf_etuid,1)." AND  cs_status = 'Routing' ";
		$result = DBCommon::query ( $wf_sql );
		while ($row = DBCommon::fetch_array ( $result )) {
			array_push ( $curUserList, $row ['cs_salarysn'] );
		}
		//会签用户是否也在流程中
		$existRouter = array();
		foreach ($routers as $router){
			if(in_array($router, $curUserList)){
				array_push($existRouter, $router);
			}
		}
		return $existRouter;
		
	}
	/*
	 * 直接结束
	 */
	public function doForceFinish($etuid){
		
		// 删除所有下一处理人
		$currentstep_sql = "delete from t_wf_currentstep where et_uid = '$etuid'";
		DBCommon::exec( $currentstep_sql );
		// 修改单据状态为审批结束
		$entry_sql = "update t_wf_entry set et_state = 5 where et_uid = '$etuid'";
		DBCommon::exec( $entry_sql );
	}
	
}
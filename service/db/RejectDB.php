<?php 
class RejectDB{
	/**
	 *
	 *功能:	驳回时处理
	 *参数:	wf_etuid -> 当前工作流的entryId
	 *		wf_id -> 驳回的状态.-100
	 *	
	 **/
	function rejectEntry($wf_etuid, $wf_id, $wf_uid) {
		//查询实例创建人
		$wf_sql = "select et_createuser,wf_uid from t_wf_entry  where et_uid = " . sqlFilter ( $wf_etuid, 1 ) ;
		$result = DBCommon::query ( $wf_sql );
		
		$wf_uid = "";
		$nextuserList = array ();
		if ($row = DBCommon::fetch_array ( $result )) {
			$wf_createuser = $row ['et_createuser'];
			//获取最初的工作流ID
			$wf_uid = $row ['wf_uid'];

			array_push ( $nextuserList, $wf_createuser );
		}
		//获取初始节点的ID
		$wf_id = "1";
		$wf_sql = "delete from t_wf_currentstep where et_uid=" . sqlFilter ( $wf_etuid, 1 );
		DBCommon::exec( wf_iconvgbk ( $wf_sql ) );
		
		//插入当前操作表,根据该表进行查询
		$wf_sql = "insert into t_wf_currentstep(uid, et_uid, cs_salarysn, cs_id, cs_status, cs_updateby, steplock, cs_endTime, wf_uid,cs_nodename)
		values('".uuid()."', " . sqlFilter ( $wf_etuid, 1 ) . ", " . sqlFilter ( $wf_createuser, 1 ) . ", " . sqlFilter ( $wf_id, 1 ) . ", '" . WF_STATUS_REJECT . "', " . sqlFilter ( $wf_createuser, 1 ) . ", 'unlocked', '".date('Y-m-d H:i:s')."', " . sqlFilter ( $wf_uid, 1 ) . ",'驳回')";
		//删除当前操作记录
		DBCommon::exec( wf_iconvgbk ( $wf_sql ) );
		
		//删除statck中的记录
		$wf_sql = "delete from t_wf_flowstack where et_uid=" . sqlFilter ( $wf_etuid, 1 );
		DBCommon::exec( wf_iconvgbk ( $wf_sql ) );
		
		
		return $nextuserList;
	}
	
	/**
	 *
	 *功能:	收回时处理
	 *参数:	wf_etuid -> 当前工作流的entryId
	 *		wf_id -> 驳回的状态.-100
	 *	
	 **/
	function recycleEntry($wf_etuid, $wf_id, $wf_uid) {
		//查询实例创建人
		$wf_sql = "select et_createuser,wf_uid from t_wf_entry  where et_state='2' and et_uid = '" . sqlFilter ( $wf_etuid,3 ) . "'";
		$result = DBCommon::query ( $wf_sql );
		
		$wf_uid = "";
		$nextuserList = array ();
		if ($row = DBCommon::fetch_array ( $result )) {
			$wf_createuser = $row ['et_createuser'];
			
			//获取最初的工作流ID
			$wf_uid = $row ['wf_uid'];
			array_push ( $nextuserList, $wf_createuser );
		} 
		//获取初始节点的ID
		$wf_id = "1";
		
		$wf_sql = "delete from t_wf_currentstep where et_uid=" . sqlFilter ( $wf_etuid, 1 );
		DBCommon::exec( wf_iconvgbk ( $wf_sql ) );
		
		//插入当前操作表,根据该表进行查询
		$wf_sql = "insert into t_wf_currentstep(uid, et_uid, cs_salarysn, cs_id, cs_status, cs_updateby, steplock, cs_endTime, wf_uid,cs_nodename)
		values('".uuid()."', " . sqlFilter ( $wf_etuid, 1 ) . ", " . sqlFilter ( $wf_createuser, 1 ) . ", " . sqlFilter ( $wf_id, 1 ) . ", '" . WF_STATUS_REJECT . "', " . sqlFilter ( $wf_createuser, 1 ) . ", 'unlocked', '".date('Y-m-d H:i:s')."', " . sqlFilter ( $wf_uid, 1 ) . ",'收回')";
		//删除当前操作记录
		DBCommon::exec( wf_iconvgbk ( $wf_sql ) );
		
		//删除statck中的记录（防止已经入子流程）
		$wf_sql = "delete from t_wf_flowstack where et_uid=" . sqlFilter ( $wf_etuid, 1 );
		DBCommon::exec( wf_iconvgbk ( $wf_sql ) );
		
		
		return $nextuserList;
	}
}
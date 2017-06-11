<?php 
/**
 * 
 * 流程处理数据库操作
 * @author liutao
 * ------------------------------------------
 * 为实现流程模拟，修改实例化对象方法。
 * 为保证数据库操作和模拟统一，添加上层接口InterfaceTask
 * liutao 2014-11-21
 */
class TaskDB implements InterfaceTask{
	//private标记的构造方法
	private function __construct(){
	}
	/**
	 * 
	 * 返回新建的对象
	 * @throws Exception
	 */
	public static function getInstance(){
		//任务全局类
		$commandContext = CommandContext::getInstance();
		//流程模拟时调用模拟类
		if($commandContext->getSysVar('debug')==DEBUG_PREDICTION){
			return SimulateUtil::getInstance();
		}else{
			return new self;
		}
	}
	/**
	 *
	 *功能:	创建工作流处理
	 *参数:	wf_uid -> 工作流模板id
	 *		wf_title -> 当前实体名称
	 *		wf_createuser -> 实体创建人
	 *		wf_id -> 工作流初始状态
	 *	
	 **/
	
	function createEntry($wf_uid, $wf_createuser, $wf_id, $wf_etuid) {
		//查询流程名称
		$wf_sql = "select wf_name from t_wf_workflow where wf_uid = '$wf_uid'";
		$result = DBCommon::query ( $wf_sql );
		$wf_name = "";
		if ($row = DBCommon::fetch_array ( $result )) {
			$wf_name = $row ['wf_name'];
		}
		
		//插入工作流实体
		$wf_sql = "insert into t_wf_entry(wf_uid, et_title, et_uid, et_state, et_createdate, et_createuser)
		values(" . sqlFilter ( $wf_uid ,1) . ", " . sqlFilter ( $wf_name , 1 ) . ", " . sqlFilter ( $wf_etuid ,1) . ", '1', '".date('Y-m-d H:i:s')."', " . sqlFilter ( $wf_createuser ,1) . ")";
		DBCommon::exec( $wf_sql );

		//插入当前操作表,根据该表进行查询
		$wf_sql = "insert into t_wf_currentstep(uid, et_uid, cs_salarysn, cs_id, cs_status, cs_updateby, steplock, cs_endTime, wf_uid)
		values('".uuid()."', " . sqlFilter ( $wf_etuid, 1 ) . ", " . sqlFilter ( $wf_createuser, 1 ) . ", " . sqlFilter ( $wf_id, 1 ) . ", 'Draft', " . sqlFilter ( $wf_createuser, 1 ) . ", 'unlocked', '".date('Y-m-d H:i:s')."', " . sqlFilter ( $wf_uid, 1 ) . ")";
		DBCommon::exec( $wf_sql );
	}
	/**
	 * 
	 * 通过到下一节点
	 * @param unknown_type $wf_users
	 * @param unknown_type $wf_id
	 * @param unknown_type $wf_etuid
	 * @param unknown_type $wf_nodestatus
	 * @param unknown_type $wf_uid
	 * @param unknown_type $wf_puid
	 * @param unknown_type $wf_pid
	 * @param unknown_type $nodename
	 * @throws Exception
	 */
	function completeEntry($wf_users, $wf_id, $wf_etuid, $wf_nodestatus, $wf_uid, $wf_puid, $wf_pid, $nodename) {
		if (isNull ( $wf_users ) || ! is_array ( $wf_users )) {
			throw new Exception ( "未能找到下一处理人，请重新操作！" );
		}
		//任务全局类
		$commandContext = CommandContext::getInstance();
		//cs_parentid里判断组织结构（记录当前环节部门id）
		//组织结构取部门领导
		if ($commandContext->getSysVar('organization_current')!="") {
			$deptinfo = $commandContext->getSysVar('organization_current');
			$wf_nodestatus = $deptinfo['id'];
		}
		$nodename = $nodename == ''?$commandContext->getNextStepname():$nodename;
		foreach ( $wf_users as $value ) {
			if (isNull ( $value )) {
				throw new Exception ( "未能找到下一处理人，请重新操作！" );
			}
			$value = trim($value);
			$wf_sql = "insert into t_wf_currentstep(uid, et_uid, cs_salarysn, cs_id, cs_status, cs_updateby, steplock, cs_endTime,  wf_uid, cs_parentuid, cs_parentid,cs_orgid, cs_nodename) 
			values('".uuid()."', " . sqlFilter ( $wf_etuid, 1 ) . ", " . sqlFilter ( $value, 1 ) . ", " . sqlFilter ( $wf_id, 1 ) . ", 'Dealing', " . sqlFilter ( $value, 1 ) . ", 'locked','".date('Y-m-d H:i:s')."', " . sqlFilter ( $wf_uid, 1 ) . ", " . sqlFilter ( $wf_puid, 1 ). ", " . sqlFilter ( $wf_pid, 1 ) . ", " . sqlFilter ( $wf_nodestatus, 1 ) . ", " . sqlFilter ( $nodename, 1 ) . ")";
			DBCommon::exec( wf_iconvgbk ( $wf_sql ) );
			
		}
	}
	/**
	 * 
	 * 删除当前环节:$wf_uid流程ID
	 * @param $wf_id
	 * @param $wf_etuid
	 * @param $wf_uid
	 */
	function deleteCurrentStep($wf_id, $wf_etuid, $wf_uid) {
		$wf_sql = "delete from t_wf_currentstep where et_uid=" . sqlFilter ( $wf_etuid, 1 ) . " and cs_id=" . sqlFilter ( $wf_id, 1 ) ;
		DBCommon::exec( wf_iconvgbk ( $wf_sql ) );
	}
	/**
	 * 
	 * 获取当前处理人
	 * @param $wf_etuid
	 */
	function getCurrentStep($wf_etuid,$ssn) {
		$step = array ();
		$wf_sql = "select cs_salarysn,cs_id,cs_nodename,wf_uid 
		from t_wf_currentstep where cs_status<>'Underway' and cs_salarysn=".sqlFilter ( $ssn, 1 )." and et_uid=" . sqlFilter ( $wf_etuid, 1 );
		$result = DBCommon::query ($wf_sql);
		if ( $row = DBCommon::fetch_array ( $result ) ) {
			$step['cs_salarysn'] = wf_iconvutf($row['cs_salarysn']);
			$step['cs_id'] = wf_iconvutf($row['cs_id']);
			$step['cs_nodename'] = wf_iconvutf($row['cs_nodename']);
			$step['wf_uid'] = wf_iconvutf($row['wf_uid']);
		}
		return $step;
	}
	/**
	 * 
	 * 获取当前处理人
	 * @param $wf_etuid
	 */
	function getCurrentSteps($wf_etuid) {
		$steps = array ();
		$wf_sql = "select cs_salarysn,cs_id,wf_uid,cs_parentid as flag from t_wf_currentstep where cs_status<>'Underway' and et_uid=" . sqlFilter ( $wf_etuid, 1 );
		$result = DBCommon::query ( $wf_sql );
		while ( $row = DBCommon::fetch_array ( $result ) ) {
			array_push ( $steps, $row);
		}
		return $steps;
	}
	/**
	 * 
	 * 获取父流程的ID和节点
	 * @param $wf_etuid
	 * @param $wf_uid
	 * @param $wf_id
	 */
	 
	function getParentFlow($wf_etuid, $wf_uid, $wf_id) {
		$wf_sql = "select fs_puid wf_puid, fs_pcsid wf_pid,fs_uid 
		from t_wf_flowstack where et_uid=" . sqlFilter ( $wf_etuid, 1 ) . " and wf_uid=" . sqlFilter ( $wf_uid, 1 ) . " order by fs_createdate desc";
		$result = DBCommon::query ( $wf_sql );
		if ( $row = DBCommon::fetch_array ( $result ) ) {
			return $row;
		}
		return array ();
	}
	
	/**
	 * 删除流程栈
	 * @param $fs_uid
	 */
	function deleteFlowStack($fs_uid) {
		$wf_sql = "delete from t_wf_flowstack where fs_uid=" . sqlFilter ( $fs_uid, 1 );
		DBCommon::exec( wf_iconvgbk ( $wf_sql ) );
	}
	/**
	 * 
	 * 保存父流程信息
	 * @param $wf_puid
	 * @param $wf_pid
	 * @param $wf_uid
	 * @param $wf_etuid
	 */
	function saveFlowStack($wf_puid, $wf_pid, $wf_uid, $wf_etuid) {
		$wf_sql = "insert into t_wf_flowstack(fs_uid, fs_puid, fs_pcsid, wf_uid, fs_createdate, et_uid) 
		values('".uuid()."', " . sqlFilter ( $wf_puid, 1 ) . ", " . sqlFilter ( $wf_pid, 1 ) . "," . sqlFilter ( $wf_uid, 1 ) . ", '".date('Y-m-d H:i:s')."'," . sqlFilter ( $wf_etuid, 1 ) . ")";
		DBCommon::exec( wf_iconvgbk ( $wf_sql ) );
		
	}
	/**
	 * 
	 * 审批完成
	 * @param unknown_type $wf_etuid
	 */
	function finishedWorkflow($wf_etuid) {
		$wf_sql = 'update t_wf_entry set et_state=\'5\' where et_uid=' . sqlFilter ( $wf_etuid, 1 );
		DBCommon::exec( wf_iconvgbk ( $wf_sql ) );
		
	}
	/**
	 * 进入流程中
	 *
	 * @param unknown_type $wf_etuid
	 */
	function joinWorkflow($wf_etuid) {
		$wf_sql = "update t_wf_entry set et_state='2' where et_uid=" . sqlFilter ( $wf_etuid, 1 );
		DBCommon::exec( wf_iconvgbk ( $wf_sql ) );

	}
	/**
	 *  保存工作流程日志信息
	 *  $wf_etuid   实例ID
	 *  $wf_currid  当前节点ID
	 *  $wf_currName  当前节点名称
	 *  $wf_status 当前节点状态   在workflow中定义
	 *  $wf_comment   日志备注信息
	 *  $wf_type    日志类型  在workflow中定义
	 *  $wf_actionid   当前动作ID
	 *  $wf_uid     当前工作流定义ID
	 */
	function worklowLog($wf_etuid, $wf_currid,$wf_currName,$wf_status, $wf_comment, $wf_type, $wf_actionid = null, $wf_uid = null) {

		if (isNull ( $wf_uid )) {
			throw new Exception ( "流程信息丢失，请联系系统管理员!" );
			return;
		}
		
		//获取最后一次的结束时间,只有wf_type == WF_LOG_TYPE_APP时才处理
		$startdate = "'".date('Y-m-d H:i:s')."'";
		if ($wf_type == WF_LOG_TYPE_APP) {
			$wf_sql = "select wflg_finishDate finishdate from 
			t_wf_log where wflg_type='" . WF_LOG_TYPE_APP . "' and et_uid=" . sqlFilter ( $wf_etuid, 1 ) . " order by wflg_date desc";
			
			$result = DBCommon::query ( $wf_sql );
			if ($row = DBCommon::fetch_array ( $result )) {
				$startdate = sqlFilter ( $row ['finishdate'], 1 );
			}
		}
		$commandContext = CommandContext::getInstance();
		$ssn = $commandContext->getSessionSsn();
		$accredit = $commandContext->getSessionAccredit();
		$accredit = $accredit==""?$ssn:$accredit;
		$uuid = uuid();
		//保存日志信息
		$wf_sql = "insert into t_wf_log(et_uid, wflg_uid, wflg_salarysn, wflg_accredit, wflg_date, wflg_startDate, wflg_finishDate, wf_status, cs_id,cs_name,wflg_comment, wf_actionid, wf_uid, wflg_type) 
		values(" . sqlFilter ( $wf_etuid, 1 ) . ",'$uuid', " . sqlFilter ( $ssn, 1 ) . "," . sqlFilter ( $accredit, 1 ) . ", '".date('Y-m-d H:i:s')."',  " . $startdate . ",'".date('Y-m-d H:i:s')."', " . sqlFilter ( $wf_status, 1 ) . ", " . sqlFilter ( $wf_currid, 1 ) . ", " . sqlFilter ( $wf_currName, 1 ) . ", " . sqlFilter ( $wf_comment, 1 ) . ", " . sqlFilter ( $wf_actionid, 1 ) . ", " . sqlFilter ( $wf_uid, 1 ) . ", " . sqlFilter ( $wf_type, 1 ) . ")";
		DBCommon::exec( wf_iconvgbk ( $wf_sql ) );
		
	}
}
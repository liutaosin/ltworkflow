<?php 
class UserDB implements InterfaceUserDB{
	/**
	 * 
	 * 填充用户信息
	 * @param $userList 工号数组
	 */
	public function getUserList($userList) {
		$nextuserList = array();
		if(!is_array($userList) || count($userList)==0){
			return $nextuserList;
		}
		$where_user = " u_usercode in(";
		foreach ( $userList as $value ) {
			if (isNull ( $value )) {
				return $nextuserList;
			}
			$where_user .= sqlFilter ( $value,3 ) . ",";
		}
		$where_user = substr ( $where_user, 0, strlen ( $where_user ) - 1 ) . ")";
		
		$wf_sql = "select u_name,u_usercode,u_email from v_wf_user where " . $where_user;
		$result = DBCommon::query ( $wf_sql );
		while ( $row = DBCommon::fetch_array ( $result ) ) {
			$nextuser ['u_name'] = $row ['u_name'] ;
			$nextuser ['u_usercode'] = $row ['u_usercode'];
			$nextuser ['u_email'] = $row ['u_email'];
			array_push ( $nextuserList, $nextuser );
		}
		return $nextuserList;
	}
	/**
	 * 
	 * 取用户信息
	 * @param $ssn 工号
	 */
	public function getUser($ssn) {
		$nextuser = array();
		$wf_sql = "select u_name,u_usercode,u_email from v_wf_user where u_usercode=" . sqlFilter ( $ssn,3 );
		$result = DBCommon::query ( $wf_sql );
		if ( $row = DBCommon::fetch_array ( $result ) ) {
			$nextuser ['u_name'] = $row ['u_name'] ;
			$nextuser ['u_usercode'] = $row ['u_usercode'];
			$nextuser ['u_email'] = $row ['u_email'];
		}
		return $nextuser;
	}
	/**
	 * Enter 取角色配置（系统自扩展）
	 *
	 * @param unknown_type $wf_roleid 角色编码
	 * @return unknown
	 */
	public function getUserByRole($wf_roleid) {
		$wf_users = array ();
		$wf_sql = "select salarysn from v_wf_role_user where code = " . sqlFilter ( $wf_roleid,3 );
		$wf_result = DBCommon::query ( $wf_sql );
		while ( $row = DBCommon::fetch_array ( $wf_result ) ) {
			array_push ( $wf_users, $row [0] );
		}
		return $wf_users;
	}
	/**
	 * 
	 * 起草人部门信息
	 * @param $user_code
	 */
	function getGlobalParam($user_code) {
		return array();
		
	}
	/**
	 * 取组织结构信息
	 * @param unknown_type $usercode	工资号
	 * @return unknown 组织结构数组（以部门id为key）
	 */
	function getOrganization($usercode = '') {
		$_deptstruct = array ();
		return $_deptstruct;
	}
	/**
	 * 汇报线判断
	 * @param unknown_type $ssn 起草人工资号
	 * @return unknown 汇报线
	 */
	function getApprovalRelations($ssn) {
		$deptarray = array ();
		return $deptarray;
	}
	/**
	 * 
	 * 查询审批日志信息
	 */
	function queryWorkflowLog($etuid) {
		$logs = array ();
		//需要依据组织结构修改语句
		$sql = "select wflg_finishdate as finishdate, u_name, wf_status, wflg_comment as comment,wf_actionid 
				from t_wf_log log inner join v_wf_user us on log.wflg_salarysn=us.u_usercode where et_uid=" . sqlFilter ( $etuid,3 ) . " and wflg_type='Handle' order by wflg_finishdate asc";
		//echo $sql;
		$query = DBCommon::query ( $sql );
		while ( $row = DBCommon::fetch_array ( $query ) ) {
			$row ['u_name'] = wf_iconvutf ( $row ['u_name'] );
			$row ['comment'] = wf_iconvutf ( $row ['comment'] );
			$row ['wflg_comment'] = $row ['comment'] ;
			switch ($row ['wf_status']) {
				case WF_STATUS_SUBMIT :
					$row ['wf_status'] = '提交';
					break;
				case WF_STATUS_DEALING :
					$row ['wf_status'] = '同意';
					break;
				case  WF_STATUS_FINISHED:
					$row ['wf_status'] = '同意';
					break;
				case WF_STATUS_REJECT :
					$row ['wf_status'] = '驳回';
					break;
				case WF_STATUS_RECYCLE :
					$row ['wf_status'] = '收回';
					break;
				case WF_STATUS_ROUTING :
					if ($row ['wf_actionid'] == "-500") {
						$row ['wf_status'] = '会签';
					} else if ($row ['wf_actionid'] == "-550") {
						$row ['wf_status'] = '循环会签';
					} else if ($row ['wf_actionid'] == "-600") {
						$row ['wf_status'] = '会签确认';
					} else if ($row ['wf_actionid'] == "-650") {
						$row ['wf_status'] = '循环会签确认';
					} else if ($row ['wf_actionid'] == "-200") {
						$row ['wf_status'] = '会签驳回';
					} else if ($row ['wf_actionid'] == "-250") {
						$row ['wf_status'] = '循环会签驳回';
					} else {
						$row ['wf_status'] = '会签';
					}
					
					break;
				case 'Recycle' :
					$row ['wf_status'] = '收回';
					break;
			}
			
			array_push ( $logs, $row );
		}
		
		return $logs;
	}
}
<?php 
/**
 * 
 * 流程预测工具
 * 将对流程表的操作改为静态数组
 * @author liutao
 *
 */
class SimulateUtil implements InterfaceTask{
	private static $_instance;
	
	private $entry = array();
	private $t_wf_currentstep = array();
	private $t_wf_flowstack = array();
	private $t_wf_log = array();
	
	//private标记的构造方法
	private function __construct(){
	}
	 
	//单例方法,用于访问实例的公共的静态方法
	public static function getInstance(){
		if(!(self::$_instance instanceof self)){
			self::$_instance = new self;
		}
		return self::$_instance;
	}
	/**
	 * 
	 * 初始化数据库数据到数组
	 */
	public function init($etuid){
		$this->entry = array();
		$this->t_wf_currentstep = array();
		$this->t_wf_flowstack = array();
		$this->t_wf_log = array();
		
		//查询实例
		$sql = "select et_uid ,
        wf_uid ,
        et_title ,
        et_state ,
        et_createuser ,
        et_createdate ,
        et_handle 
        from t_wf_entry where et_uid = ".sqlFilter($etuid,1);
		$result = DBCommon::query ( $sql );
		if ( $row = DBCommon::fetch_array ( $result ) ) {
			foreach ($row as $key=>$value){
				$row[$key] = wf_iconvutf($value);
			}
			$this->entry = $row;
		}
		
		//查询当前环节
		$sql = "select 
        et_uid ,
        cs_salarysn ,
        cs_id ,
        cs_status ,
        cs_updateby ,
        steplock ,
        cs_endTime ,
        cs_parentuid ,
        cs_parentid ,
        wf_uid ,
        cs_prestatus ,
        cs_nodename,
        u_name as name 
        from t_wf_currentstep c inner join v_wf_user on cs_salarysn = u_usercode
				where cs_status<>'Routing' and cs_nodename <>'会签' and  et_uid = ".sqlFilter($etuid,1);
		$result = DBCommon::query ( wf_iconvgbk($sql)  );
		while ( $row = DBCommon::fetch_array ( $result ) ) {
			foreach ($row as $key=>$value){
				$row[$key] = wf_iconvutf($value);
			}
			array_push ( $this->t_wf_currentstep , $row);
		}
		//查询子流程
		$sql = "select 
		 fs_uid ,
         fs_puid ,
         et_uid ,
         wf_uid ,
         fs_pcsid ,
         fs_createdate 
         from t_wf_flowstack where et_uid = ".sqlFilter($etuid,1);
		$result = DBCommon::query ( $sql );
		while ( $row = DBCommon::fetch_array ( $result ) ) {
			foreach ($row as $key=>$value){
				$row[$key] = wf_iconvutf($value);
			}
			array_push ( $this->t_wf_flowstack , $row);
		}
		return $this->entry;
	}
	/**
	 * 
	 * 获取当前环节
	 */
	public function get_t_wf_currentstep(){
		return $this->t_wf_currentstep;
	} 
	
	/**
	 *
	 *功能:	创建工作流处理（模拟时忽略此方法）
	 *参数:	wf_uid -> 工作流模板id
	 *		wf_title -> 当前实体名称
	 *		wf_createuser -> 实体创建人
	 *		wf_id -> 工作流初始状态
	 *	
	 **/
	public function createEntry($wf_uid, $wf_createuser, $wf_id, $wf_etuid ) {
		return;
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
	public function completeEntry($wf_users, $wf_id, $wf_etuid, $wf_nodestatus, $wf_uid, $wf_puid, $wf_pid, $nodename) {
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
//			$wf_sql = "insert into t_wf_currentstep(uid, et_uid, cs_salarysn, cs_id, cs_status, cs_updateby, steplock, cs_endTime,  wf_uid, cs_parentuid, cs_parentid, cs_nodename)values(newid(), " . sqlFilter ( $wf_etuid, 1 ) . ", " . sqlFilter ( $value, 1 ) . ", " . sqlFilter ( $wf_id, 1 ) . ", 'Dealing', " . sqlFilter ( $value, 1 ) . ", 'locked', getdate(), " . sqlFilter ( $wf_uid, 1 ) . ", " . sqlFilter ( $wf_puid, 1 ) . ", " . sqlFilter ( $wf_nodestatus, 1 ) . ", " . sqlFilter ( $nodename, 1 ) . ")";
//			DBCommon::exec( iconvgbk ( $wf_sql ) );
			//根据工号取姓名
			$sql = "select u_name from v_wf_user where u_usercode  = " .sqlFilter($value,1);
			$result = DBCommon::query ( $sql );
			if ( $row = DBCommon::fetch_array ( $result ) ) {
				$name = wf_iconvutf($row['u_name']);
			}
			
			$currentstep = array();
			$currentstep['uid'] = uuid();
			$currentstep['et_uid'] = $wf_etuid;
			$currentstep['cs_salarysn'] = $value;
			$currentstep['cs_id'] = $wf_id;
			$currentstep['cs_status'] = "Dealing";
			$currentstep['cs_updateby'] = $value;
			$currentstep['steplock'] = "locked";
			$currentstep['cs_endTime'] = date('Y-m-d H:i:s');
			$currentstep['wf_uid'] = $wf_uid;
			$currentstep['cs_parentuid'] = $wf_puid;
			$currentstep['cs_parentid'] = $wf_nodestatus;
			$currentstep['cs_nodename'] = $nodename;
			$currentstep['name'] = $name;
			array_push($this->t_wf_currentstep, $currentstep);
			
		}
	}
	/**
	 * 
	 * 删除当前环节:$wf_uid流程ID
	 * @param $wf_id
	 * @param $wf_etuid
	 * @param $wf_uid
	 */
	public function deleteCurrentStep($wf_id, $wf_etuid, $wf_uid) {
//		$wf_sql = "delete from t_wf_currentstep where et_uid=" . sqlFilter ( $wf_etuid, 1 ) . " and cs_id=" . sqlFilter ( $wf_id, 1 ) ;
//		DBCommon::exec( iconvgbk ( $wf_sql ) );
		$currentstepList = $this->t_wf_currentstep;
		$this->t_wf_currentstep = array();
		foreach ($currentstepList as $currentstep){
			if(!(strtoupper($currentstep['et_uid']) == strtoupper($wf_etuid) && strtoupper($currentstep['cs_id']) == strtoupper($wf_id))){
				array_push($this->t_wf_currentstep, $currentstep);
			}
		}
	}
	/**
	 * 
	 * 获取当前处理人
	 * @param $wf_etuid
	 */
	function getCurrentStep($wf_etuid,$ssn) {
		$step = array ();
		foreach ($this->t_wf_currentstep as $currentstep){
			if(strtoupper($currentstep['et_uid']) == strtoupper($wf_etuid) && $currentstep['cs_salarysn'] == $ssn && $currentstep['cs_salarysn']!="Underway" ){
				$step['cs_salarysn'] = $currentstep['cs_salarysn'];
				$step['cs_id'] = $currentstep['cs_id'];
				$step['cs_nodename'] = $currentstep['cs_nodename'];
				$step['wf_uid'] = $currentstep['wf_uid'];
				
			}
		}
		return $step;
	}
	/**
	 * 
	 * 获取当前处理人
	 * @param $wf_etuid
	 */
	public function getCurrentSteps($wf_etuid) {
		$steps = array ();
//		$wf_sql = "select cs_salarysn,cs_id,isnull(cs_parentid,'') as flag from t_wf_currentstep where cs_status<>'Underway' and et_uid=" . sqlFilter ( $wf_etuid, 1 );
//		$result = DBCommon::query ( $wf_sql );
		foreach ($this->t_wf_currentstep as $currentstep){
			if(strtoupper($currentstep['et_uid']) == strtoupper($wf_etuid) ){
				$row = array();
				$row['cs_salarysn'] = $currentstep['cs_salarysn'];
				$row['cs_id'] = $currentstep['cs_id'];
				$row['wf_uid'] = $currentstep['wf_uid'];
				$row['flag'] = $currentstep['cs_parentid'];
				array_push($steps, $row);
			}
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
	 
	public function getParentFlow($wf_etuid, $wf_uid, $wf_id) {
		foreach ($this->t_wf_flowstack as $flowstack){
			if(strtoupper($flowstack['et_uid']) == strtoupper($wf_etuid) && strtoupper($flowstack['wf_uid']) == strtoupper($wf_uid)){
				$row = array();
				$row['wf_puid'] = $flowstack['fs_puid'];
				$row['wf_pid'] = $flowstack['fs_pcsid'];
				$row['fs_uid'] = $flowstack['fs_uid'];
				return $row;
			}
		}
		return array ();
	}
	
	/**
	 * 删除流程栈
	 * @param $fs_uid
	 */
	public function deleteFlowStack($fs_uid) {
//		$wf_sql = "delete from t_wf_flowstack where fs_uid=" . sqlFilter ( $fs_uid, 1 );
//		DBCommon::exec( iconvgbk ( $wf_sql ) );
		$flowstackList = $this->t_wf_flowstack;
		$this->t_wf_flowstack = array();
		foreach ($flowstackList as $flowstack){
			if(!(strtoupper($flowstack['fs_uid']) == strtoupper($fs_uid) )){
				array_push($this->t_wf_flowstack, $flowstack);
			}
		}
	}
	/**
	 * 
	 * 保存父流程信息
	 * @param $wf_puid
	 * @param $wf_pid
	 * @param $wf_uid
	 * @param $wf_etuid
	 */
	public function saveFlowStack($wf_puid, $wf_pid, $wf_uid, $wf_etuid) {
//		$wf_sql = "insert into t_wf_flowstack(fs_uid, fs_puid, fs_pcsid, wf_uid, fs_createdate, et_uid) values(newid(), " . sqlFilter ( $wf_puid, 1 ) . ", " . sqlFilter ( $wf_pid, 1 ) . "," . sqlFilter ( $wf_uid, 1 ) . ", getdate()," . sqlFilter ( $wf_etuid, 1 ) . ")";
//		DBCommon::exec( iconvgbk ( $wf_sql ) );
		$flowstack = array();
		$flowstack['fs_uid'] = uuid();
		$flowstack['fs_puid'] = $wf_puid;
		$flowstack['fs_pcsid'] = $wf_pid;
		$flowstack['wf_uid'] = $wf_uid;
		$flowstack['fs_createdate'] = date('Y-m-d H:i:s');;
		$flowstack['et_uid'] = $wf_etuid;
		array_push($this->t_wf_flowstack, $flowstack);
	}
	/**
	 * 
	 * 审批完成
	 * @param unknown_type $wf_etuid
	 */
	public function finishedWorkflow($wf_etuid) {
//		$wf_sql = 'update t_wf_entry set et_state=\'5\' where et_uid=' . sqlFilter ( $wf_etuid, 1 );
//		DBCommon::exec( iconvgbk ( $wf_sql ) );
		$this->entry['et_state'] = "5";

		
	}
	/**
	 * 进入流程中
	 *
	 * @param unknown_type $wf_etuid
	 */
	public function joinWorkflow($wf_etuid) {
//		$wf_sql = "update t_wf_entry set et_state='2' where et_uid=" . sqlFilter ( $wf_etuid, 1 );
//		DBCommon::exec( iconvgbk ( $wf_sql ) );
		$this->entry['et_state'] = "2";

	}
	/**
	 *  保存工作流程日志信息
	 */
	public function worklowLog($wf_etuid, $wf_currid,$wf_currname,$wf_status, $wf_comment, $wf_type, $wf_actionid = null, $wf_uid = null) {
		$commandContext = CommandContext::getInstance();
		$ssn = $commandContext->getSessionSsn();
		$accredit = $commandContext->getSessionAccredit();
		$accredit = $accredit==""?$ssn:$accredit;
		$sqlArray = array();
		$sqlArray['wflg_uid'] = uuid();
		$sqlArray['et_uid'] = $wf_etuid;
		$sqlArray['wflg_salarysn'] = $ssn;
		$sqlArray['wflg_accredit'] = $accredit;
		$sqlArray['wflg_date'] = date('Y-m-d H:i:s');
		$sqlArray['wflg_startDate'] = date('Y-m-d H:i:s');
		$sqlArray['wflg_finishDate'] = date('Y-m-d H:i:s');
		$sqlArray['wf_status'] = $wf_status;
		$sqlArray['cs_id'] = $wf_currid;
		$sqlArray['cs_name'] = $wf_currname;
		$sqlArray['wflg_comment'] = $wf_comment;
		$sqlArray['wf_actionid'] = $wf_actionid;
		$sqlArray['wf_uid'] = $wf_uid;
		$sqlArray['wflg_type'] = $wf_type;
		array_push($this->t_wf_log, $sqlArray);
	}
	/**
	 * 
	 *获取审批日志
	 */
	public function getWorklowLog(){
		return $this->t_wf_log;
	}
}
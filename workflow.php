<?php
error_reporting ( E_ALL );

define ( 'WORKFLOW_BASE', realpath ( dirname ( __FILE__ ) ) );

//配置文件引入
require_once WORKFLOW_BASE . '/service/config/config.php';

if (strpos ( $wf_custom_folder, '/' ) === FALSE) {
	if (function_exists ( 'realpath' ) and @realpath ( dirname ( __FILE__ ) ) !== FALSE) {
		$wf_custom_folder = realpath ( dirname ( __FILE__ ) ) . '/' . $wf_custom_folder;
	}
} else {
	$wf_custom_folder = str_replace ( '\\', '/', $wf_custom_folder );
}
define ( 'WORKFLOW_EXT', '.php' );
define ( 'WORKFLOW_BASEPATH', $wf_custom_folder . '/' );
//定义自定义脚本目录
define ( 'RESTPATH', WORKFLOW_BASEPATH . 'restrict/' );
define ( 'ROLEPATH', WORKFLOW_BASEPATH . 'roles/' );
define ( 'RESUPATH', WORKFLOW_BASEPATH . 'results/' );
define ( 'FUNC', WORKFLOW_BASEPATH . 'function/' );
define ( 'INITPATH', WORKFLOW_BASEPATH . 'init/' );
define ( 'ORGANIZATION', WORKFLOW_BASEPATH . 'organization/' );
define ( 'FREECONFIG', WORKFLOW_BASEPATH . 'freeconfig/' );
//定义邮件动作变量
define ( 'WF_EMAIL_FLAG', TRUE );//邮件开关
define ( 'WF_EMAIL_DEALING', '通过' ); 
define ( 'WF_EMAIL_SUBMIT', '送审' ); 
define ( 'WF_EMAIL_REJECT', '驳回' ); 
define ( 'WF_EMAIL_ROUTING', '会签' ); 
define ( 'WF_EMAIL_RECYCLE', '收回' ); 
define ( 'WF_EMAIL_FINISHED', '审批完成' );

//定义日志变量
define ( 'WF_LOG_TYPE_APP', 'Handle' ); //应用日志
define ( 'WF_LOG_TYPE_SYS', 'System' ); //系统流程日志（待扩展）
define ( 'WF_LOG_TYPE_ERROR', 'Error' ); //系统错误日志（待扩展）
//定义处理动作
define ( 'WF_STATUS_SAVE', 'Save' ); //保存
define ( 'WF_STATUS_SUBMIT', 'Submit' ); //提交
define ( 'WF_STATUS_DEALING', 'Dealing' ); //处理
define ( 'WF_STATUS_REJECT', 'Reject' ); //驳回动作
define ( 'WF_STATUS_RECYCLE', 'Recycle' ); //收回动作
define ( 'WF_STATUS_ROUTING', 'Routing' ); //会签动作
define ( 'WF_STATUS_FINISHED', 'finished' ); //审批完成

//继承类引用
require_once WORKFLOW_BASE . '/service/inheritance/WorkflowBase.php';
require_once WORKFLOW_BASE . '/service/inheritance/Restrict.php';
require_once WORKFLOW_BASE . '/service/inheritance/Roles.php';
require_once WORKFLOW_BASE . '/service/inheritance/Results.php';
require_once WORKFLOW_BASE . '/service/inheritance/Func.php';
require_once WORKFLOW_BASE . '/service/inheritance/BeforeAction.php';
require_once WORKFLOW_BASE . '/service/inheritance/InterfaceNotice.php';
require_once WORKFLOW_BASE . '/service/inheritance/InterfaceUserDB.php';
require_once WORKFLOW_BASE . '/service/inheritance/InterfaceCounterSign.php';
require_once WORKFLOW_BASE . '/service/inheritance/InterfaceInit.php';
require_once WORKFLOW_BASE . '/service/inheritance/InterfaceReject.php';
require_once WORKFLOW_BASE . '/service/inheritance/InterfaceTask.php';
//工具类
require_once WORKFLOW_BASE . '/service/util/XmlUtil.php';
require_once WORKFLOW_BASE . '/service/util/ClassUtil.php';
require_once WORKFLOW_BASE . '/service/util/Common.php';
require_once WORKFLOW_BASE . '/service/util/ErrorBox.php';
require_once WORKFLOW_BASE . '/service/util/SimulateUtil.php';
//数据库操作
require_once WORKFLOW_BASE . '/service/db/common/DBcommon.php';
require_once WORKFLOW_BASE . '/service/db/InfoDB.php';
require_once WORKFLOW_BASE . '/service/db/FreeRouteDB.php';
require_once WORKFLOW_BASE . '/service/db/RejectDB.php';
require_once WORKFLOW_BASE . '/service/db/TaskDB.php';
require_once WORKFLOW_BASE . '/service/db/FreeTaskDB.php';
//全局上下文
require_once WORKFLOW_BASE . '/service/CommandContext.php';
require_once WORKFLOW_BASE . '/service/ConfigContext.php';
//服务类
require_once WORKFLOW_BASE . '/service/InfoService.php';
require_once WORKFLOW_BASE . '/service/TaskService.php';

//默认审批动作
define ( 'DEFAULT_ACTION', 'default' );

//系统变量
define ( 'DEBUG_EXEC', 'exec' );
define ( 'DEBUG_SIMULATE', 'simulate' );
define ( 'DEBUG_PREDICTION', 'prediction' );
define ( 'DEBUG_DEBUG', 'debug' );

//任务全局类
$commandContext = CommandContext::getInstance();
$commandContext->setSysVar('debug',DEBUG_EXEC);//设置是否调试  exec 表示执行  debug表示调试  simulate表示模拟   prediction表示流程预测

//配置全局类
$configContext = ConfigContext::getInstance();
$configContext->setEmailObj(@$email);
$configContext->setCustomObj($custom);
/**
 * --------------------------------------------------------------------------------
 * Prj. Module  : 处理工作流
 * File Name    : workflow
 * Description  : 
 * Parameter    : 
 * Rreference   : 
 * Creator      : 
 * Create Date  : 2014-7-10
 * Version      : 
 * --------------------------------------------------------------------------------
 */
class workflow{
	/**
	 *  处理流程
	 *  $config['etuid'];	实例ID（必填）
	 *  $config['wfuid'];	流程ID（必填）
	 *	$config['actionid'];当前动作ID（必填）
	 *	$config['ssn'];	   当前登录人工号（必填）
	 *	$config['name']; 当前登录人姓名
	 *	$config['router'];会签人工号（会签时必填）
	 *	$config['comment'];审批意见
	 *	$config['accredit'];授权人工号
	 *	$config['orguser'];按此工号组织结构审批，为空时按起草人组织结构审批
	 *	$config['var'];array型，初始化全局变量，用户流程判断等
	 *	$config['disable_tran']; boolean型, true|false，是否禁用事务，如果调用程序有事务管理此处才能禁用。
	 *  返回结果集包括
	 *  array(
	 *       status   ----------  操作成功失败状态  success | error
	 *       message   -----------  提示消息
	 *       flowstatus ----------流程状态 Save|Submit|Dealing|Reject|Recycle|Routing|finished
	 *       nextuser [{u_name,u_usercode,u_email}] ---下一处理人
	 *   )
	 */
	function doAction($config) {
		//配置全局类
		$configContext = ConfigContext::getInstance();
		//任务全局类
		$commandContext = CommandContext::getInstance();

		try {
			//流程处理
			if($commandContext->getSysVar('debug')!=DEBUG_SIMULATE && $commandContext->getSysVar('debug')!=DEBUG_PREDICTION){
				DBCommon::begin_tran();//sql事务开始
			}
			if( @$config['wfuid']=='' || @$config['etuid']=='' || @$config['actionid']==='' || @$config['ssn']==''){
		       	throw new Exception ( "config参数信息不全！" );
			}
			//判断是否禁用事务
			if(key_exists("disable_tran", $config) && is_bool($config['disable_tran'])){
				if($config['disable_tran']){
					DBcommon::disable_tran();
				}
			}
			$commandContext->setEtuid(@$config['etuid']);
			$commandContext->setWfuid(@$config['wfuid']);
			$commandContext->setActionid(@$config['actionid']);
			$commandContext->setRouter(@$config['router']);
			$commandContext->setOrgcreateuser(@$config['orguser']);
			$commandContext->setSessionSsn(@$config['ssn']);
			$commandContext->setSessionName(@$config['name']);
			$commandContext->setSessionAccredit(@$config['accredit']);
			$commandContext->setComment(@$config['wfuid'],@$config['actionid'],'1','',@$config['comment']);
			if(key_exists("var", $config) && is_array($config['var'])){//初始化全局变量，用于流程判断
				$commandContext->initTempVar($config['var']);
				
			}
			
			$taskService = new TaskService();//工作流服务
			//保存
		    $wf_actionid = @$config['actionid'];
	        if ($wf_actionid == "-11") { //保存草稿
	            $commandContext->setResponseBox('message', '保存成功！');
	            DBCommon::end_tran (); //sql事务结束
	            return $commandContext->getResponseBox();
	        }
			if ($wf_actionid == "-100") {//驳回处理
				$taskService->reject();
			} else if ($wf_actionid == "-500") {//会签
				$taskService->freeRoute();
			} else if ($wf_actionid == "-600") {//会签确认
				$taskService->doFreeRoute();
			} else if ($wf_actionid == "-99") { //发起人收回
				$taskService->recycle();
			} else if ($wf_actionid == "-700") { //直接结束审批
				$taskService->doForceFinish();
			} else { //通过
				if ($configContext->getGlobalVar('freeconfig') != "") {
					$taskService->doFreeTask();//自由流程
				}else{
					$taskService->doTask();
				}
			}
			//插入日志
			$taskService->worklowLog ();
			
			//获取审批人员
			$commandContext->setResponseBox('nextuser',$commandContext->getNextuserList());
			//流程状态
			$commandContext->setResponseBox('flowstatus', $commandContext->getFlowStatus());
			//添加默认返回消息
			if(isNull($commandContext->getResponseBox('message'))){
				$commandContext->setResponseBox('message',"审批通过");
			}
			if($commandContext->getSysVar('debug')==DEBUG_DEBUG){//调试
				$commandContext->setResponseBox('status','error');
				throw new Exception ( "test" );
			}
		} catch ( exception $e ) {
			$message = $e->getMessage ();
			$code = $e->getCode();
			$commandContext->setResponseBox('status','error');
			if (! isNull ( $message )) {
				$commandContext->setResponseBox('message',$message);
				ErrorList::add(new ErrorBox($message,$code));
			}
			//记录错误错误日志
			wf_debug ( "name:" .$commandContext->getSessionName(). " etuid:" . $config['etuid'] ." actionid:" . $config['actionid'],'error' );
			$errorList = ErrorList::getList();
			foreach ($errorList as $errorBox){
				wf_debug("code:".$errorBox->code." message:".$errorBox->message,'error');
			}
		}
		if($commandContext->getSysVar('debug')!=DEBUG_SIMULATE && $commandContext->getSysVar('debug')!=DEBUG_PREDICTION){
			$status = $commandContext->getResponseBox('status');
			if($status == 'error'){
				DBCommon::end_tran (false); 
			}else{
				DBCommon::end_tran (); //sql事务结束
				//通知处理人
				if (WF_EMAIL_FLAG && $commandContext->getSysVar('debug') == DEBUG_EXEC ) {
					$email = $configContext->getEmailObj('default');
					if($email){
						$email->noticeNextUser();
					}
					//向手机应用推送代办
					$appService = $configContext->getEmailObj('app');
					if($appService){
						$appService->noticeNextUser();
					}
				}
			}
		}
		return $commandContext->getResponseBox();
	}
	/**
	 * 根据单据ID获取流程信息
	 * @param  $etuid 实例id
	 * @param  $ssn 工号
	 */
	function getWorkFlow($etuid,$ssn){
		$infoService = InfoService::getInstance();
		return $infoService->workflowInfo($etuid, $ssn);
	}
	/**
	 * 返回实例当前操作
	 *
	 * @param string $etuid	实例id
	 * @param string $ssn	当前登录人id
	 * @param string $wfuid	流程id
	 * @return array	当前操作数组
	 */
	function getAvailableActions($etuid,$ssn,$wfuid,$isRouting = true) {
		try {
			$availableActions = array();
			$infoService = InfoService::getInstance();
			$curstep = $infoService->workflowInfo($etuid, $ssn);
			$wfuid = $curstep['wf_uid']==''?$wfuid:$curstep['wf_uid'];
			if($curstep['cs_id']!=''){
				$availableActions = $infoService->getAvailableActions($wfuid, $curstep['cs_id'], $curstep['cs_status'],$isRouting);
			}
			return $availableActions;
		} catch ( exception $e ) {
			$message = $e->getMessage ();
			$code = $e->getCode();
			if (! isNull ( $message )) {
				ErrorList::add(new ErrorBox($message,$code));
			}
			$errorList = ErrorList::getList();
			foreach ($errorList as $errorBox){
				wf_debug("code:".$errorBox->code." message:".$errorBox->message,'error');
			}
			die($message);
		}
	
	}
	/**
	 * 
	 * 获取审批意见
	 * @param $etuid
	 */
	function queryWorkflowLog($etuid){
		try {
			$infoService = InfoService::getInstance();
			return $infoService->queryWorkflowLog($etuid);
		} catch ( exception $e ) {
			$message = $e->getMessage ();
			$code = $e->getCode();
			if (! isNull ( $message )) {
				ErrorList::add(new ErrorBox($message,$code));
			}
			$errorList = ErrorList::getList();
			foreach ($errorList as $errorBox){
				wf_debug("code:".$errorBox->code." message:".$errorBox->message,'error');
			}
			die($message);
		}
	}
}



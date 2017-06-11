<?php 
class EndBehavior implements Behavior{
	private $wf_result;
	function __construct($wf_result){
		$this->wf_result = $wf_result;
	}
	public function execute(){
		$taskDB = TaskDB::getInstance();
		//任务全局类
		$commandContext = CommandContext::getInstance();
		$configContext = ConfigContext::getInstance();
		
		$wf_uid = $commandContext->getWfuid();
		$wf_etuid = $commandContext->getEtuid();
		$wf_id = $commandContext->getStepid();
		$actionid = $commandContext->getActionid();
		
		//删除当前环节
		$taskDB->deleteCurrentStep ( $wf_id, $wf_etuid, $wf_uid );
		
		//判断是否子流程
		$wf_parent = $taskDB->getParentFlow ( $wf_etuid, $wf_uid, $wf_id );
		$wf_puid = @$wf_parent ['wf_puid'];
		$wf_pid = @$wf_parent ['wf_pid'];
		$fs_uid = @$wf_parent ['fs_uid'];
		//判断是否子流程（跳出）
		if (! isNull ( $wf_puid )) {//如果当前是子流程，执行父流程
			//删除子流程记录
			$taskDB->deleteFlowStack ( $fs_uid );
			//将子流程初始化脚本清空
			$configContext->setInitElement($wf_uid, "");
			//修改当前执行环境
			$commandContext->setActionid(DEFAULT_ACTION);
			$commandContext->setEtuid($wf_etuid);
			$commandContext->setWfuid($wf_puid);
			$commandContext->setStepid($wf_pid);
			//处理父节点
			$taskCommandImpl = new TaskCommandImpl();
			$taskCommandImpl->doTask();
		} else {//结束流程
			$taskDB->finishedWorkflow ( $wf_etuid );
			$nextuserList = array ();
			array_push ($nextuserList, $commandContext->getSysVar('@@createuser__'));
			$commandContext->setNextuser($nextuserList);
			$commandContext->setFlowStatus(WF_STATUS_FINISHED);
		}
	}
}
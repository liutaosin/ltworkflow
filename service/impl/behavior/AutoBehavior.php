<?php 
class AutoBehavior implements Behavior{
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
		
		$next_node = $this->wf_result->getAttribute ( "step" );

		$taskDB = TaskDB::getInstance();
		$taskDB->deleteCurrentStep ( $wf_id, $wf_etuid, $wf_uid );
		//修改当前执行环境
		$commandContext->setActionid(DEFAULT_ACTION);
		$commandContext->setStepid($next_node);
		//自动通过
		$taskCommandImpl = new TaskCommandImpl();
		$taskCommandImpl->doTask();
	}
}
<?php 
require_once WORKFLOW_BASE . '/service/impl/FreeTaskCommandImpl.php';
class FreeTaskCommand implements Command{
	
	public function execute(){
		//任务全局类
		$commandContext = CommandContext::getInstance();
		$wf_etuid = $commandContext->getEtuid();
		$wf_uid = $commandContext->getWfuid();
		
		$freeTaskCommandImpl = new FreeTaskCommandImpl();
		$freeTaskCommandImpl->doTask();
		$freeTaskCommandImpl->setApproveStatus();
		if($commandContext->getFlowStatus()==WF_STATUS_FINISHED){//审批结束时处理
			XmlUtil::endTask($wf_uid, $wf_etuid);
		}
	}
}
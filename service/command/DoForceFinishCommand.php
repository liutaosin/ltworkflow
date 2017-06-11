<?php 
require_once WORKFLOW_BASE . '/service/impl/FreeRouteCommandImpl.php';
class DoForceFinishCommand implements Command{
	
	public function execute(){
		$commandContext = CommandContext::getInstance();
		$wf_etuid = $commandContext->getEtuid();
		$wf_uid = $commandContext->getWfuid();
		
		$freeRouteCommandImpl = new FreeRouteCommandImpl();
		$freeRouteCommandImpl->doForceFinish();
		//设置完成状态
		$commandContext->setFlowStatus(WF_STATUS_FINISHED);
		$commandContext->setEmail('approvetype',WF_EMAIL_FINISHED);
		
		//审批完成处理
		XmlUtil::endTask($wf_uid, $wf_etuid);
	}
}
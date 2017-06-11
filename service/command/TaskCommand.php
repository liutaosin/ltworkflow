<?php 
require_once WORKFLOW_BASE . '/service/impl/TaskCommandImpl.php';
class TaskCommand implements Command{
	
	public function execute(){
		//任务全局类
		$commandContext = CommandContext::getInstance();
		$wf_etuid = $commandContext->getEtuid();
		$wf_uid = $commandContext->getWfuid();
		
		$taskCommandImpl = new TaskCommandImpl();
		$taskCommandImpl->doTask();//执行通过操作
		$taskCommandImpl->setApproveStatus();//设置审批状态
		
		if($commandContext->getSysVar('debug')!=DEBUG_PREDICTION){//只走流程时不做处理
			if($commandContext->getFlowStatus()==WF_STATUS_FINISHED){//审批结束时处理
				//当前流程后处理
				XmlUtil::endTask($wf_uid, $wf_etuid);
				$main_wf_uid = $commandContext->getSysVar('@@wf_uid__');
				//如果当前流程不是主流程，主流程后处理
				if($main_wf_uid != $wf_uid){
					XmlUtil::endTask($main_wf_uid, $wf_etuid);
				}
				
			}else{
				//下一节点前处理，在下一节点上配置，审批完成时没有下一节点，不调用
				$taskCommandImpl->nextNoteBefore();
			}
		}
	}
}
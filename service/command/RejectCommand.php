<?php 
require_once WORKFLOW_BASE . '/service/impl/RejectCommandImpl.php';
class RejectCommand implements Command{
	
	public function execute(){
		$rejectCommandImpl = new RejectCommandImpl();
		$rejectCommandImpl->reject();
		$rejectCommandImpl->setApproveStatus();
	}
}
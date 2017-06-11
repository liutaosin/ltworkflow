<?php 
require_once WORKFLOW_BASE . '/service/impl/RejectCommandImpl.php';
class RecycleCommand implements Command{
	
	public function execute(){
		$rejectCommandImpl = new RejectCommandImpl();
		$rejectCommandImpl->recycle();
		$rejectCommandImpl->setRecycleStatus();
	}
}
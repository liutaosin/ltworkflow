<?php 
require_once WORKFLOW_BASE . '/service/impl/FreeRouteCommandImpl.php';
class DoOtherRouteCommand implements Command{
	
	public function execute(){
		$freeRouteCommandImpl = new FreeRouteCommandImpl();
		$freeRouteCommandImpl->doOtherRoute();
		$freeRouteCommandImpl->setApproveStatus();
	}
}
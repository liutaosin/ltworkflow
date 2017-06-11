<?php 
require_once WORKFLOW_BASE . '/service/impl/FreeRouteCommandImpl.php';
class OtherRouteCommand implements Command{
	
	public function execute(){
		$freeRouteCommandImpl = new FreeRouteCommandImpl();
		$freeRouteCommandImpl->otherRoute();
		$freeRouteCommandImpl->setApproveStatus();
	}
}
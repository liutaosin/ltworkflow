<?php 
require_once WORKFLOW_BASE . '/service/impl/FreeRouteCommandImpl.php';
class FreeRouteCommand implements Command{
	
	public function execute(){
		$freeRouteCommandImpl = new FreeRouteCommandImpl();
		$freeRouteCommandImpl->freeRoute();
		$freeRouteCommandImpl->setApproveStatus();
	}
}
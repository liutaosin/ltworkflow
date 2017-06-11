<?php 
require_once WORKFLOW_BASE . '/service/impl/FreeRouteCommandImpl.php';
class DoFreeRouteCommand implements Command{
	
	public function execute(){
		$freeRouteCommandImpl = new FreeRouteCommandImpl();
		$freeRouteCommandImpl->doFreeRoute();
		$freeRouteCommandImpl->setApproveStatus();
	}
}
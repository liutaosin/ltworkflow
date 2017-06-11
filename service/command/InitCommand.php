<?php 
require_once WORKFLOW_BASE . '/service/impl/InitCommandImpl.php';
class InitCommand implements Command{
	
	public function execute(){
		$initCommandImpl = new InitCommandImpl();
		$initCommandImpl->init();
		$initCommandImpl->setApproveStatus();
	}
}
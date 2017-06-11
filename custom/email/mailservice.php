<?php
require_once 'smtp.php';
class MailService implements InterfaceNotice{
	/*
	 * 每个节点给处理人发送邮件
	 *
	 */
	function noticeNextUser() {
		//任务全局类
		$commandContext = CommandContext::getInstance();
		$configContext = ConfigContext::getInstance();
		$email = $commandContext->getEmail() ;
		if ( count ($email ) == 0) {
			return;
		}

		foreach (  $commandContext->getNextuserList()  as $user ) {

			//邮件通知下一审批人
		}

	}
	
	
}

<?php
/**
 * 
 * 会签接口
 * @author liutao
 *
 */
interface InterfaceCounterSign {
	
	function check_countersign($etuid);//会签到起草人时校验
	function on_countersign($etuid);//起草人送审后处理

}
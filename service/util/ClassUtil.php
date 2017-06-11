<?php

function &class_load($cp_node, $wf_handle, $wf_etuid = '') {
	$configContext = ConfigContext::getInstance();
	$userDB = $configContext->getCustomObj('userinfo');
	//任务全局类
	$commandContext = CommandContext::getInstance();
	$init_param = array ();
	$local_param = array ();
	//读取配置文件自定义变量（待扩展）
	foreach ( $cp_node->childNodes as $node ) {
		if (! isNull ( $node->localName )) {
			$local_param [$node->localName] = $node->nodeValue;
		}
	}
	
	$init_param ['local_param'] = $local_param;
	
	$global_param = $commandContext->getSysVar('global_param');
	if($global_param == ""){
		$global_param = $userDB->getGlobalParam ( $commandContext->getSysVar( '@@orgcreateuser__' ) );
		$global_param ['sys_etuid'] = $wf_etuid;
		$commandContext->setSysVar('global_param',$global_param);
	}
	$init_param ['global_param'] = $global_param;
	
	$handl_class = "";
	$path = $cp_node->getAttribute ( 'path' );
	switch ($wf_handle) {
		case "restrict" :
			$handl_class = &load_workflow_class ( $path, RESTPATH );
			break;
		case "roles" :
			$handl_class = &load_workflow_class ( $path, ROLEPATH );
			break;
		case 'results' :
			$handl_class = &load_workflow_class ( $path, RESUPATH );
			break;
		case 'init' :
			$handl_class = &load_workflow_class ( $path, INITPATH );
			break;
		case 'function' :
			$handl_class = &load_workflow_class ( $path, FUNC );
			break;
		case 'organization' :
			$handl_class = &load_workflow_class ( $path, ORGANIZATION );
			break;
		case 'freeconfig' :
			$handl_class = &load_workflow_class ( $path, FREECONFIG );
			break;
	}
	wf_debug("加载自定义脚本……".$wf_handle.":".$path);
	$handl_class->_init ( $init_param );
	
	return $handl_class;
}
//反射类
function &load_workflow_class($class, $base_path, $instantiate = TRUE) {
	static $objects = array ();
	
	if (isset ( $objects [$class] )) {
		return $objects [$class];
	}
	
	$fetch_directory = "";
	$fetch_class = "";
	if (strrpos ( $class, '/' ) === FALSE) {
		$fetch_class = $class;
	} else {
		$index = strrpos ( $class, '/' );
		$fetch_directory = substr ( $class, 0, $index );
		$fetch_class = substr ( $class, $index + 1 );
	}
	//引入类。
	require_once ($base_path . $fetch_directory . '/' . $fetch_class . WORKFLOW_EXT);
	
	//创建类
	$objects [$class] = &instantiate_workflow_class ( new $fetch_class ( ) );
	
	return $objects [$class];
}

//创建类
function &instantiate_workflow_class(&$class_object) {
	return $class_object;
}
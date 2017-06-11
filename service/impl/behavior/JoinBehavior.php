<?php

/**
 * JoinBehavior
 * 合并分支
 */
class JoinBehavior implements Behavior{
    private $wf_result;
    function __construct($wf_result){
        $this->wf_result = $wf_result;
    }
	public function execute(){
        $taskDB = TaskDB::getInstance();
		//任务全局类
		$commandContext = CommandContext::getInstance();
		$wf_uid = $commandContext->getWfuid();
		$wf_etuid = $commandContext->getEtuid();
		$wf_id = $commandContext->getStepid();
        wf_debug ( 'join:进入流程合并..' . simplexml_import_dom ( $this->wf_result )->saveXML () );
        //删除当前环节
        $taskDB->deleteCurrentStep ( $wf_id, $wf_etuid, $wf_uid );
        //判断是否其他分支是否全部通过
        $othersteps = $taskDB->getCurrentSteps ( $wf_etuid );
        if (count ( $othersteps ) == 0) {
            wf_debug ( 'join:流程已合并，进入下一节点判断..' );
            //获取用户信息
            $wfroles = $this->wf_result->getElementsByTagName ( 'roles' )->item ( 0 );
            $wf_users = XmlUtil::getRoles ( $wfroles, $wf_etuid );
            //下一节点id
            $wf_nodevalue = $this->wf_result->getAttribute ( "step" );

            $taskDB->completeEntry ( $wf_users, $wf_nodevalue, $wf_etuid, '', $wf_uid, '', '', '' );
            $commandContext->setNextuser($wf_users);

        } else {
            wf_debug ( 'join:流程未合并，等待其他环节处理' );
            //读取其他分支当前处理人
            $commandContext = CommandContext::getInstance();
            $nextuserList = array();
            foreach ($othersteps as $row){
                array_push($nextuserList, $row['cs_salarysn']);
            }
            $commandContext->setNextuser($nextuserList);
        }


	}

	
}
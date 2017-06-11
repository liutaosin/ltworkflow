<?php
/**
 * SplitBehavior
 * 并行流程
 */
class SplitBehavior implements Behavior{
    private $wf_results;
    function __construct($wf_results){
        $this->wf_results = $wf_results;
    }
	public function execute(){
        $taskDB = TaskDB::getInstance();
        //任务全局类
        $commandContext = CommandContext::getInstance();
        $wf_uid = $commandContext->getWfuid();
        $wf_etuid = $commandContext->getEtuid();
        $wf_id = $commandContext->getStepid();
        wf_debug ( 'split:进入流程拆分..' );

        //删除当前环节
        $taskDB->deleteCurrentStep ( $wf_id, $wf_etuid, $wf_uid );
        //处理条件结果集
        foreach ( $this->wf_results as $split_result ) {
            wf_debug ( 'split:进入条件结果集..' . simplexml_import_dom ( $split_result )->saveXML () );
            $next_node = $split_result->getAttribute ( 'step' );//下一节点id
            $next_node_obj = null;//下一节点对象
            //最后节点处理：如果取不到节点则是审批结束，则不执行下面语句
            if(!isNull($next_node)){
                $next_node_obj =  XmlUtil::getElementById( $next_node );
                $next_node_name =  $next_node_obj->getAttribute ( "name" );//下一节点名称
                $commandContext->setNextStepid($next_node);//保存下一节点id
                $commandContext->setNextStepname($next_node_name);//保存下一节点名称
            }

            $split_roles = $split_result->getElementsByTagName ( 'roles' )->item ( 0 );
            $split_users = XmlUtil::getRoles ( $split_roles, $wf_etuid );
            //获取流程描述
            $taskDB->completeEntry ( $split_users, $next_node, $wf_etuid, '', $wf_uid, '', '', '' );

            //并行流程拆分时多次调用，合并下一处理人
            $nextuserList = $commandContext->getNextuser();
            $nextuserList = array_merge($nextuserList,$split_users);
            $commandContext->setNextuser($nextuserList);

        }

	}
}
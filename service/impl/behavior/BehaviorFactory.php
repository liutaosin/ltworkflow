<?php
require_once WORKFLOW_BASE . '/service/impl/behavior/Behavior.php';
require_once WORKFLOW_BASE . '/service/impl/behavior/EndBehavior.php';
require_once WORKFLOW_BASE . '/service/impl/behavior/OrgBehavior.php';
require_once WORKFLOW_BASE . '/service/impl/behavior/NormalBehavior.php';
require_once WORKFLOW_BASE . '/service/impl/behavior/SubBehavior.php';
require_once WORKFLOW_BASE . '/service/impl/behavior/ParallelBehavior.php';
require_once WORKFLOW_BASE . '/service/impl/behavior/AutoBehavior.php';
require_once WORKFLOW_BASE . '/service/impl/behavior/FreeBehavior.php';
require_once WORKFLOW_BASE . '/service/impl/behavior/SplitBehavior.php';
require_once WORKFLOW_BASE . '/service/impl/behavior/JoinBehavior.php';
class BehaviorFactory {
	/**
	 * 
	 * 获取出口处理类
	 * @param $wf_result
	 */
	public static function getOuterBehavior($wf_result){
		
		$commandContext = CommandContext::getInstance();
		$wf_nodeid = $commandContext->getStepid();//当前节点
		$next_node = $wf_result->getAttribute ( 'step' );//下一节点id
        $next_node_obj = null;//下一节点对象
        $next_node_name = "";//下一节点名称
        $next_node_type = "";//下一节类型
		//最后节点处理：如果取不到节点则是审批结束，则不执行下面语句
		if(!isNull($next_node)){
		    $next_node_obj =  XmlUtil::getElementById( $next_node );
			$next_node_name =  $next_node_obj->getAttribute ( "name" );//下一节点名称
            $next_node_type =  $next_node_obj->getAttribute ( "type" );//下一节类型
			$commandContext->setNextStepid($next_node);//保存下一节点id
			$commandContext->setNextStepname($next_node_name);//保存下一节点名称
		}
		//送审组织结构，修改result内容
		if($wf_nodeid=='1001' || $next_node=='1001' ){
			$orgBehavior = new OrgBehavior();
			$orgarray = $orgBehavior->isOrgApprove();//是否需要组织结构审批
			if(count ( $orgarray ) > 0){//需要组织结构审批
				//修改result内容为组织结构审批，并更换审批人
				$wf_result = $orgBehavior->getResult($wf_result,$orgarray);
				$next_node = $wf_result->getAttribute ( 'step' );
			}else{//不需要组织结构审批
				//当前节点不是组织结构审批，但下一节点是组织结构审批，自动执行下一环节
				if($wf_nodeid!='1001' && $next_node=='1001'){
					$behavior = new AutoBehavior($wf_result);
					return $behavior;
				}
			}
		}
		//自由流程处理
		$nextstep = $wf_result->getAttribute ( "step" );
		if(! isNull ( $nextstep )){
			$nextnode = XmlUtil::getElementById ( $nextstep );
			$next_freestep = $nextnode->getAttribute ( "freestep" );
			if ($next_freestep != '') {
				$behavior = new FreeBehavior($wf_result);
				return $behavior;
			}
		}
		//流程完成。
		$wf_nodevalue = $wf_result->getAttribute ( "end" );
		if (! isNull ( $wf_nodevalue )) {
			$behavior = new EndBehavior($wf_result);
			return $behavior;
		}
		//子流程
		$wf_substep = $wf_result->getAttribute ( "auto" );
		if (@$wf_substep == "true") {
			$behavior = new SubBehavior($wf_result);
			return $behavior;
		}
		//旧版并行流程，不推荐使用
		$wf_splitid = $wf_result->getAttribute ( "split" );
		$wf_joinid = $wf_result->getAttribute ( "join" );
		if (! isNull ( $wf_splitid ) || ! isNull ( $wf_joinid )) {
			$behavior = new ParallelBehavior($wf_result);
			return $behavior;
		}
        //新版并行流程，推荐使用
        if ($next_node_type=="join") {
            $behavior = new JoinBehavior($wf_result);
            return $behavior;
        }

		//默认下一环节
		if (! isNull ( $next_node )) {
			$behavior = new NormalBehavior($wf_result);
			return $behavior;
		}
	}

    /**
     * 获取并行流程处理类
     * @param $wf_result
     * @return ParalleStepBehavior
     */
    public static function getParalleBehavior($wf_results){
        $behavior = new SplitBehavior($wf_results);
		return $behavior;
    }

}
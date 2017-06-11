<?php
//组织结构通用类引用
require_once WORKFLOW_BASE . '/service/inheritance/AbstractOrgRule.php';

class OrgBehavior {
    /**
     *
     *组织结构审批修改result节点
     *
     * @param unknown_type $wf_result
     */
    public function getResult($wf_result, $orgarray) {
        //任务全局类
        $commandContext = CommandContext::getInstance();

        $deptinfo = array_shift($orgarray);
        $commandContext->setSysVar('organization_current', $deptinfo);
        //重定义结果集模板
        $orgresult = str_replace("#leader#", $deptinfo ['leader'], ORGANIZATION_RESULT);
        $orgxml = new DOMDocument ();
        $orgxml->loadXML($orgresult);
        $wf_result = $orgxml->getElementsByTagName("result")->item(0);
        wf_debug('organization:循环组织结构..' . simplexml_import_dom($wf_result)->saveXML());
        return $wf_result;
    }

    /**
     *
     * 需要审批的组织结构
     */
    public function isOrgApprove() {
        //任务全局类
        $commandContext = CommandContext::getInstance();
        $wf_etuid = $commandContext->getEtuid();
        $wf_id = $commandContext->getStepid();
        $orglist = $this->__doOrganization($wf_etuid, $wf_id);
        $orgarray = $orglist ['rmd'];
        return $orgarray;

    }

    //组织结构处理
    private function __doOrganization($etuid, $nodeid) {
        $taskDB = TaskDB::getInstance();
        //配置全局类
        $configContext = ConfigContext::getInstance();
        $commandContext = CommandContext::getInstance();
        //避免单据起草人和流程起草人不一致情况（采购报销）
        $createuser = __getGlobalVar('@@orgcreateuser__');
        //取审批起始点
		$startid = '';
		//取审批终止点
		$endid = '';
        //跳过节点
        $skipstruct = array();
        //组织结构树
        $orgarray_all = array();
        //剩余节点
        $orgarray_rmd = array();
        //组织结构返回数组
        $orgarray = array();


        //加载组织结构类
        if ($configContext->getGlobalVar('organization') != "") {
            $org_class = & class_load($configContext->getGlobalVar('organization'), "organization");
        } else {
            $pathxml = new DOMDocument ();
            $pathxml->loadXML('<organization path="organizationbase"/>');
            $wf_path = $pathxml->getElementsByTagName("organization")->item(0);
            $org_class = & class_load($wf_path, "organization");
        }

        //取当前环节
        $current_rows = $taskDB->getCurrentSteps($etuid);
        //取审批起始点(流程中时起始点为当前环节)
        //组织结构内跳过当前审批环节
        if ($nodeid == '1001') {
            //处理当前环节为空时的错误
            if (count($current_rows) == 0) {
                return array('rmd' => array());
            } else {
                //取当前环节部门id
                $startid = trim($current_rows [0] ['flag']);
                $skipstruct ['dept' . $startid] = $startid; //当前环节正审批，定义此环节跳过
            }
        }
        //取自定义脚本
        $org_class->_add('etuid', $etuid);
        $org_class->_add('createuser', $createuser);
        //组织结构树
        $orgarray_all = $org_class->getOrgarray($createuser);
        //自定义规则
        $skipstruct = array_merge($skipstruct, $org_class->specialRule());
        //单据类型规则
        $approvel_rule = $org_class->getApproveRule();
       
        //部门领导处理
        $curorg = current($orgarray_all);
        if ($createuser == $curorg ['leader']) {
            $skipstruct ['dept' . $curorg ['id']] = $curorg ['id']; //起草人为当前部门领导，跳过本部门
        }
        /*进入组织结构循环时判断单据类型
         *合同类--contract_type（默认） 严格按照部门层级判断，允许部门内部审批全部跳过
         *个人费用类--personal_type：不允许部门内部全部跳过，如果全部跳过取上级领导审批
        */
        if ($nodeid != '1001' && count(array_diff_key($orgarray_all, $skipstruct)) == 0) {
            //合同类、系统判断组织结构全部跳过、且起草人为部门领导时，组织结构全部跳过
            if ($approvel_rule == 'contract_type' && $curorg ['leader'] == $createuser) {
                $orgarray_rmd = array();
            } else {
                $i = 0;
                foreach ($orgarray_all as $row) {
                    //当起草人为部门领导时，寻找上级
                    if ($row ['leader'] == $createuser) {
                        $i++;
                    } else {
                        break;
                    }
                }
                //如果全部跳过保留一个审批环节
                $orgarray_rmd = array_slice($orgarray_all, $i, 1);
            }
        } else {
            $orgarray_rmd = $org_class->getRemaindOrg ( $orgarray_all, $skipstruct, $startid, $endid );
        }
        //判断当前登录人是否为下一环节审批人，避免重复判断及debug模拟时不操作数据库造成死循环
        if (count($orgarray_rmd) > 0) {
            foreach ($orgarray_rmd as $rowarray) {
                if ($rowarray ['leader'] == $commandContext->getSessionSsn()) {
                    array_shift($orgarray_rmd);
                } else {
                    break;
                }
            }
        }
        //统一返回数据
        $orgarray ['all'] = $orgarray_all;
        $orgarray ['skip'] = $skipstruct;
        $orgarray ['rmd'] = $orgarray_rmd;
        $orgarray ['formtype'] = $approvel_rule;
        return $orgarray;
    }
}
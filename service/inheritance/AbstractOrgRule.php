<?php

/**
 * 依据PS组织结构修改
 * 组织结构审批，用户自定义文件需继承此类
 *
 * @author yunan
 *
 */
abstract class AbstractOrgRule extends WorkflowBase {
    //组织结构
    protected $orglist = array();

    public function __construct() {
        //角色xml模板
        if (!defined("ORGANIZATION_RESULT")) {
            define ("ORGANIZATION_RESULT", '<result step="1001"><roles><role type="11" typeName="部门领导审批"><usercode>#leader#</usercode></role></roles></result>');
        }
        //组织结构xml模板
        if (!defined("ORGANIZATION_PATH")) {
            define ("ORGANIZATION_PATH", '<organization path="organizationbase"/>');
        }
    }

    //定义按部门id判断审批规则(含下属部门)
    abstract function getRuleByDeptId();

    //定义按审批大部判断审批规则
    abstract function getRuleByHrDept();


    /**
     * 根据部门判断跳过规则，判断统一入口，继承类中定义特殊规则
     * 执行顺序：
     *  1.根据部门ID
     *  2.根据审批大部
     *
     * @return unknown
     */
    public function specialRule() {
        $skiparray = array();
        $orgarray = $this->orglist;
        $firstrow = current($orgarray);
   		//如果是汇报线，不跳过任何规则
        if($firstrow['id']=='-999999'){
        	return $skiparray;
        }
        //根据部门id判断跳过规则
        if (count($skiparray) == 0) {
            $idRule = $this->getRuleByDeptId();
            //由本部门找到顶级部门，任意部门满足规则认为符合
            foreach ($orgarray as $row) {
                foreach ($idRule as $key => $value) {
                    //相同审批大部时按照特殊规则，如果不归属与同一审批大部，不满足条件--20141203 jyn
                    if ($row ['id'] == $key && $firstrow ['hr_name'] == $row['hr_name']) {
                        wf_debug('按部门id，满足规则===' . $value);
                        wf_debug('满足部门：');
                        wf_debug($value);
                        $skiparray = $this->$value ($orgarray);
                        break 2;
                    }
                }
            }
        }
        //根据审批大部判断跳过规则
        if (count($skiparray) == 0) {
            $hrRule = $this->getRuleByHrDept();
            //审批大部规则只取第一条，直属部门信息
            foreach ($hrRule as $key => $value) {
                if ($firstrow ['hr_name'] == $key) {
                    wf_debug('按审批大部，满足规则===' . $value);
                    wf_debug('满足部门：');
                    wf_debug($value);
                    $skiparray = $this->$value ($orgarray);
                    break;
                }
            }
        }
        //默认规则  直接成本中心--CC4领导 20141203 jyn
        if (count($skiparray) == 0) {
            wf_debug('均不满足条件，满足规则===系统默认规则');
            $firstCc = $this->firstCcdept($orgarray);
            $skipLevel = $this->skipByLevel($orgarray, $firstCc ['level'], 'CC4');
            $orgarray = array_diff_key($orgarray, $skipLevel);
            //保留起始点为1的部门
            $skipStartEnd = $this->skipByBeginEnd($orgarray, 1, 1);
            //合并跳过节点
            $skiparray = array_merge($skipStartEnd, $skipLevel);
        }
        return $skiparray;
    }

    //获取组织结构树
    //默认规则：对组织结构不作处理， 特殊规则：自定义脚本里覆盖此方法实现
    public function getOrgarray($usercode = '') {
        if ($usercode != '') {
            $this->getOrganization($usercode);
        }
        return $this->orglist;
    }

    //设置组织结构树
    public function setOrgarray($orglist) {
        if (count($orglist) > 0) {
            $this->orglist = $orglist;
        }
    }

    /**
     * 审批规则设定：
     *    合同类（默认） 严格按照部门层级判断，允许部门内部审批全部跳过
     *  个人费用类：不允许部门内部全部跳过，如果全部跳过取上级领导审批
     */
    public function getApproveRule() {
        return "contract_type";
    }

    /**
     * 通过起止数量判断跳过环节
     *
     * @param unknown_type $depttree 组织结构树
     * @param unknown_type $start    从起始保留位置（数字）
     * @param unknown_type $end      从末尾保留位置（数字）
     *
     * @return array() 需要跳过的组织结构
     */
    public function skipByBeginEnd($depttree, $start = '', $end = '') {
        $ary_return = array();
        $len = count($depttree);
        //输入值检查
        $start = intval($start);
        if ($start == '') {
            $start = 0;
        } else if ($start > $len) {
            $start = $len - 1;
        }
        $end = intval($end);
        if ($end == '') {
            $end = $len;
        } else if ($end > $len) {
            $end = $len;
        } else {
            $end = 0 - $end;
        }
        $ary_return = array_slice($depttree, $start, $end);

        wf_debug('按照起止点数目判断：');
        wf_debug('起始部门数：' . ($start == '' ? '无' : $start));
        wf_debug('终止部门数：' . ($end == '' ? '无' : $end));
        wf_debug('跳过部门：');
        wf_debug($ary_return);
        return $ary_return;
    }

    /**
     * 通过部门层级判断跳过环节
     *
     * @param unknown_type $depttree 组织结构树
     * @param unknown_type $start    起始部门级别（两种输入格式：部门层级[1、2、3]成本中心[CC4、CC3]）
     * @param unknown_type $end      终止部门级别（两种输入格式：部门层级[1、2、3]成本中心[CC4、CC3]）
     * @param unknown_type  $addcoun  强制增加的审批节点不算在审批长度之内，该参数为强制增加的节点的个数
     * @return array() 需要跳过的组织结构
     */
    function skipByLevel($depttree, $start = '', $end = '',$addcount = 0) {
        $ary_return = array();
        //未设置起至点
        if ($start == '' && $end == '') {
            return $ary_return;
        }
        $dept_len = count($depttree) - $addcount;

        $startskip_ary = $endskip_ary = array();
        //取起始点需要跳过的部门级别
        if ($start != '') {
            $startskip = $this->getOrgLevel($start, $depttree);
            if ($startskip < $dept_len) {
                $startskip_ary = array_slice($depttree, 0, $dept_len - $startskip - 1);
            }
        }
        //取终止点需要跳过的部门级别
        $endskip = 2;
        if ($end != '') {
            $endskip = $this->getOrgLevel($end, $depttree);
            if ( $endskip != 0) {
                $endskip_ary = array_slice($depttree, 0 - $endskip);
            }
        }
        //合并所有跳过环节
        $ary_return = array_merge($startskip_ary, $endskip_ary);

        wf_debug('按照部门层级判断：');
        wf_debug('起始部门层级：' . ($start == '' ? '无' : $start));
        wf_debug('终止部门层级：' . ($end == '' ? '无' : $end));
        wf_debug('跳过部门：');
        wf_debug($ary_return);
        return $ary_return;
    }

    /**
     * 通过员工号判断跳过环节
     *
     * @param unknown_type $depttree
     * @param unknown_type $usercode
     *
     * @return array() 查找到的部门数组
     */
    public function skipByUserCode($depttree, $usercode = '') {
        $ary_return = array();
        foreach ($depttree as $key => $value) {
            if ($value ['leader'] == $usercode) {
                $ary_return [$key] = $value;
            }
        }
        return $ary_return;
    }

    /*
     * 取最近的成本中心的环节
     * @param unknown_type $depttree	组织结构树
     *
     * @return array() 查找到的部门数组
     */
    public function firstCcdept($depttree) {
        $ary_return = array();
        foreach ($depttree as $row) {
            $is_ccdept = $row ['is_ccdept'];
            if ($is_ccdept == 'Yes') {
                $ary_return = $row;
                break;
            }
        }
        return $ary_return;
    }

    /**
     * 取组织结构信息
     *
     * @param unknown_type $usercode 工资号
     *
     * @return unknown 组织结构数组（以部门id为key）
     */
    public function getOrganization($usercode = '') {
        $userDB = new UserDB();
        $_deptstruct = $userDB->getOrganization($usercode);
        $this->setOrgarray($_deptstruct);
    }
	/**
	 * 取剩余组织机构信息
	 * @param array $deptstruct 全组织结构 
	 * @param array $overstruct	跳过组织结构
	 * @param unknow $startid	起始点
	 * @param unknow $endid	终止点
	 * @return unknown	需要通过的剩余组织结构
	 */
	function getRemaindOrg(array $deptstruct, array $overstruct, $startid = '', $endid = '') {
		$reurnary = array ();
		foreach ( $deptstruct as $key => $value ) {
			//有起始点时将数组清空
			if ($startid != '' && 'dept' . $startid == $key) {
				$reurnary = array ();
			}
			$reurnary [$key] = $value;
			//有终止点时跳出
			if ($endid != '' && 'dept' . $endid == $key) {
				break;
			}
		}
		//去除跳过节点
		$reurnary = array_diff_key ( $reurnary, $overstruct );
		return $reurnary;
	}
    /**
     * getOrgLevel 获取部门层级
     *
     * @param string $level    部门层级（两种输入格式：部门层级[1、2、3]成本中心[CC4、CC3]）
     * @param array  $depttree 部门层级（两种输入格式：部门层级[1、2、3]成本中心[CC4、CC3]）
     *
     * @return int 部门层级
     */
    private function getOrgLevel($level = '', $depttree) {
        $deptlevel = 0;
        //数字格式时
        if (is_numeric($level)) {
            $deptlevel = intval($level);
        } else {
            //CC1\CC2格式时
            $key_list = array('CC4', 'CC3', 'CC2', 'CC1');
            $level = strtoupper($level);
            if (in_array($level, $key_list) && count($depttree) > 0) {
                $curdept = current($this->orglist);
                if ($level == 'CC4') {
                    $deptoid = $curdept['cc4_dept_id'];
                } else if ($level == 'CC3') {
                    $deptoid = $curdept['cc3_dept_id'];
                } else if ($level == 'CC2') {
                    $deptoid = $curdept['cc2_dept_id'];
                } else {
                    $deptoid = $curdept['cc1_dept_id'];
                }
                //按成本中心编号比较 liutao 20150123
                foreach ($depttree as $dept){
                	if($dept['code'] == $deptoid){
                		$deptlevel = intval($dept['level']);
                		break;
                	}
                }
            } else {
                $deptlevel = 0;
            }
        }
        if ($deptlevel < 0) {
            $deptlevel = 0;
        }
        return $deptlevel;
    }

    /**
     * 汇报线判断
     *
     * @param unknown_type $ssn 起草人工资号
     *
     * @return unknown 汇报线
     */
    public function getApprovalRelations($ssn) {
        $userDB = new UserDB();
        $deptarray = $userDB->getApprovalRelations($ssn);
        if (!empty($deptarray)) {
            $this->setOrgarray(array('dept-999999' => $deptarray));
        }
    }

}
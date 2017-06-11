<?php
/*
 *  服务采购初始化方法
 */
class test extends WorkflowBase implements InterfaceInit{
  


  function init_entry()
  {
        $etuid = $this->global_param['sys_etuid']; 
  }

	/**
     * 
     * 延迟加载系统所需变量
     * @param $name
     */
    function lazyLoad($name){
    	//变量名对应方法，必须在本类中实现
    	$lazyLoadVar = array(
    						"物品类型"=>"loadBaseData",
							"合同类型"=>"loadBaseData"
							);

		$result = "";
		foreach ($lazyLoadVar as $key=>$value){
			if($name == $key ){
				$result = $this->$value($name);
				break;
			}
		}
		return $result;
    }
	
	/**
	 * 起草人信息
	 */
	private function loadBaseData($name){
		
		$etuid = $this->global_param['sys_etuid'];
		//查询人员信息
		$sql= "select goods_type,contract_type from t_purchase_contract where et_uid = '$etuid'";
		$query = DBCommon::query ( $sql );
		if ($row = DBCommon::fetch_array ( $query )) {
			__addGlobalVar("物品类型", $row['goods_type']);
			__addGlobalVar("合同类型",$row['contract_type']);
		}else{
			throw new Exception ( "未找到系统变量(".$name.")！" );
		}
		return __getGlobalVar($name);
	}
}
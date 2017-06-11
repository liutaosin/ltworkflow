<?php
/**
 * 数据库结果集
 */
class DBCommonResult{
	var $result         = array();
	var $current_row	= 0;
	var $num_rows       = 0;
	
	function __construct($result){
		$this->result = $result;
		$this->num_rows = count($result);
	}
	/**
	 * 
	 * 遍历结果集
	 * @param unknown_type $result
	 */
	public function fetch_array($result_type = ""){
		
		if (count($this->result) == 0)
		{
			return false;
		}
		$index = $this->current_row;
		if (isset($this->result[$index]))
		{
			$this->current_row++;
			$num_result = array();
			$assoc_result = array();
			$both_result = $this->result[$index];
			if($result_type == ""){//不需要处理
				return $both_result;
			}else{//mssql 特殊处理
				//拆分结果集
				foreach ($both_result as $key=>$value){
					if(is_int($key)){
						$num_result[$key] = $value;
					}else{
						$assoc_result[$key] = $value;
					}
				}
				if($result_type == @MSSQL_ASSOC){
					return $assoc_result;
				}elseif($result_type == @MSSQL_NUM){
					return $num_result;
				}else{
					return $both_result;
				}
			}
			
		}else{
			return false;
		}
	}
	/**
	 * 
	 * 返回结果集
	 */
	public function result(){
		return $this->result;
	}
	/**
	 * 
	 * 结果集行数
	 */
	public function num_rows() { 
		return $this->num_rows; 
	}
}

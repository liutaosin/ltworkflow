<?php 
class FreeTaskDB{
	
	/**
	 * 
	 * 读取自由配置信息
	 * @param $name
	 */
	public function getFreeCoinfig($name) {
		$freeconfig_info = array();
		$sql = "select fc_flowname,fc_beforefile,fc_afterfile,fs_step,fs_stepname,fs_roletype,fs_rolevalue,fs_stepbefore,fs_stepafter 
			from t_wf_freeconfig a
			inner join t_wf_freeconfig_step b
			on a.fc_id=b.fs_pid
			where fc_key='$name'
			order by fs_step";
		$query = DBCommon::query ( iconvgbk ( $sql ) );
		//取自由配置，初始化进全局变量
		while ( $row = DBCommon::fetch_array ( $query ) ) {
			$step_array = array ();
			$step_array ['stepname'] = wf_iconvutf ( $row ['fs_stepname'] );
			$step_array ['roletype'] = $row ['fs_roletype'];
			$step_array ['rolevalue'] = $row ['fs_rolevalue'];
			$step_array ['stepbefore'] = $row ['fs_stepbefore'];
			$step_array ['stepafter'] = $row ['fs_stepafter'];
			$freeconfig_info ['step_' . $row ['fs_step']] = $step_array;
			$freeconfig_info ['beforefile'] = $row ['fc_beforefile'];
			$freeconfig_info ['afterfile'] = $row ['fc_afterfile'];
			$freeconfig_info ['flowname'] = $row ['fc_flowname'];
		}
		return $freeconfig_info;
	}
}
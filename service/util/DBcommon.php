<?php
/**
 * 数据库操作类
 */
class DBCommon{
	private static $wf_conn = false;
	private static $sql_flag = true;
	
	public static function init_connect($active_db = 'default') {
		if (! defined ( 'ROOTPATH' )) {
				define('ROOTPATH',$_SERVER['DOCUMENT_ROOT'].'/');	//系统路径
		}
		require (ROOTPATH . 'crm/config/database.php');
	    
		self::$wf_conn = mssql_connect ( $db [$active_db] ['hostname'], $db [$active_db] ['username'], $db [$active_db] ['password'] ) or die ( "Couldn't connect to SQL Server on server on DBcommon.php" . $db [$active_db] ['hostname'] );
		mssql_select_db ( $db [$active_db] ['database'], self::$wf_conn );
		return self::$wf_conn;
	}
	/**
	 * 
	 * 事务开启
	 */
	public static function begin_tran() {
		$sql = " SET XACT_ABORT ON; 
				begin tran";
		self::wf_exec( $sql );
	}
	/**
	 * 
	 * 事务结束
	 * @param unknown_type $flag
	 */
	public static function end_tran($flag = true) {
		if (self::$sql_flag && $flag) {
			$sql = "commit tran";
		} else {
			$sql = "rollback tran";
		}
		self::wf_exec( $sql );
	}

	/**
	 * 
	 * 查询
	 * @param $sql
	 */
	public static function wf_query($sql) {
		if(! self::$wf_conn){
			self::init_connect();
		}
		$result = @DBCommon::query ( $sql , self::$wf_conn);
		if($result === false){
			self::$sql_flag = false;
			$sql_str = $sql . " " . wf_iconvutf ( mssql_get_last_message () );
			ErrorList::add(new ErrorBox($sql_str,1));
			throw new Exception ( '数据库执行错误，请联系系统管理员！',1 );
			
		}
		return $result;
	}

	/**
	 * 
	 * 执行sql
	 * @param unknown_type $sql
	 */
	public static function wf_exec($sql) {
		
		if(! self::$wf_conn){
			self::init_connect();
		}
		if(!@DBCommon::query ( $sql , self::$wf_conn)){
			self::$sql_flag = false;
			$sql_str = $sql . " " . wf_iconvutf ( mssql_get_last_message () );
			ErrorList::add(new ErrorBox($sql_str,1));
			echo $sql_str;
			throw new Exception ( '数据库执行错误，请联系系统管理员！',1 );
			return false;
		}else{
			return true;
		}
	}
}

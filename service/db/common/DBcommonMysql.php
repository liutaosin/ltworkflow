<?php
/**
 * 
 * mysql 数据库操作
 * @author liutao
 *
 */
class DBcommonMysql{
	var $username;
	var $password;
	var $hostname;
	var $database;
	var $dbdriver		= 'mysql';
	var $dbprefix		= '';
	var $char_set		= 'utf8';
	var $dbcollat		= 'utf8_general_ci';
	var $autoinit		= TRUE; // Whether to automatically initialize the DB
	var $port			= '';
	var $pconnect		= FALSE;
	var $conn_id		= FALSE;
	var $debug          = FALSE;
	
	function __construct($params){
		//将配置放到本类中
		foreach ($params as $key => $val){
			$this->$key = $val;
		}
		//连接数据库
		$this->conn_id = ($this->pconnect == FALSE) ? $this->db_connect() : $this->db_pconnect();
		if ( ! $this->conn_id)
		{
			$this->log_message('error', 'Unable to connect to the database');
			return FALSE;
		}
		$this->db_select();//数据库选择
		
	}
	/**
	 * 
	 * 查询结果集或执行sql
	 * @param unknown_type $sql
	 * @param unknown_type $conn_id
	 */
	public function query($sql){
		if($this->debug){
			$this->log_message('error'," debug ".$sql);
		}
		//执行sql
		$query = @mysql_query($sql, $this->conn_id);
		if($query === false){//执行语句错误
			$this->log_message('error',$sql);
			return false;
		}
		if ($this->is_write_type($sql) === TRUE)
		{
			$this->exec( $sql );
		}
		//查询结果集
		$result = array();
		while($row = mysql_fetch_assoc($query)){
			array_push($result, $row);
		}
		return new DBCommonResult($result);
	}
	/**
	 * 
	 * 执行sql
	 * @param unknown_type $sql
	 * @param unknown_type $conn_id
	 */
	public function exec($sql){
		if($this->debug){
			$this->log_message('error'," debug ".$sql);
		}
		//执行sql
		$query = @mysql_query($sql, $this->conn_id);
		if($query === false){//执行语句错误
			$this->log_message('error',$sql);
			return false;
		}
		return true;
	}
	/**
	 * 
	 * 执行行数
	 */
	public function rows_affected() { 
		return @mysql_affected_rows($this->conn_id);
	}
	/**
	 * 
	 * 错误信息
	 */
	public function get_last_message(){
		return mysql_error($this->conn_id);;
	}
	/**
	 * 
	 * 事务开启
	 */
	public function begin_tran() {
		$this->exec("SET AUTOCOMMIT=0" );
		$this->exec("START TRANSACTION");
	}
	/**
	 * 
	 * 提交结束
	 * @param unknown_type $flag
	 */
	public function commit_tran() {
		$this->exec('COMMIT');
		$this->exec('SET AUTOCOMMIT=1');
	}
	/**
	 * 
	 * 回滚结束
	 * @param unknown_type $flag
	 */
	public function rollback_tran() {
		$this->exec('ROLLBACK');
		$this->exec('SET AUTOCOMMIT=1');
	}
	/**
	 * 
	 * 关闭
	 */
	public function  close()
	{
		@mysql_close($conn_id);
	}
	/**
	 * Non-persistent database connection
	 *
	 * @access	private called by the base class
	 * @return	resource
	 */
	function db_connect()
	{
		if ($this->port != '')
		{
			$this->hostname .= ':'.$this->port;
		}

		return @mysql_connect($this->hostname, $this->username, $this->password, TRUE);
	}
	/**
	 * Persistent database connection
	 *
	 * @access	private called by the base class
	 * @return	resource
	 */
	function db_pconnect()
	{
		if ($this->port != '')
		{
			$this->hostname .= ':'.$this->port;
		}

		return @mysql_pconnect($this->hostname, $this->username, $this->password);
	}
	/**
	 * Select the database
	 *
	 * @access	private called by the base class
	 * @return	resource
	 */
	function db_select()
	{
		return @mysql_select_db($this->database, $this->conn_id);
	}
	
	/**
	 * Determines if a query is a "write" type.
	 *
	 * @access	public
	 * @param	string	An SQL query string
	 * @return	boolean
	 */
	private function is_write_type($sql)
	{
		if ( ! preg_match('/^\s*"?(SET|INSERT|UPDATE|DELETE|REPLACE|CREATE|DROP|TRUNCATE|LOAD DATA|COPY|ALTER|GRANT|REVOKE|LOCK|UNLOCK)\s+/i', $sql))
		{
			return FALSE;
		}
		return TRUE;
	}
	/**
	 * 
	 * 错误信息处理
	 * @param  $message
	 */
	private function log_message($level,$message){
		$sqlmsg = mssql_get_last_message();
		wf_debug($message,$level);
		wf_debug($sqlmsg,$level);
	}
}
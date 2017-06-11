<?php
/**
 * 数据库操作类
 */
require_once 'DBcommonMssql.php';
require_once 'DBcommonMysql.php';
require_once 'DBcommonCache.php';
require_once 'DBcommonResult.php';
class DBCommon{
	private static $wf_conn = false;
	private static $sql_flag = true;
	private static $db_driver = false;
	private static $disable_tran = false;//禁用事务,默认不禁用
	/**
	 * 
	 * 连接数据库，获取连接对象
	 * @param  $params 数据库配置
	 */
	public static function get_db_driver($params) {
		$db_driver = false ;
		if (!is_array($params)){
			require (WORKFLOW_BASE . '/service/config/database.php');
			//$debug = $params == "default"?false:true;//其他连接打开debug
			$params = $db [$params];
			//$params['debug'] = $debug;
			
		}
		if($params['dbdriver']=='mysql'){
			$db_driver = new DBcommonMysql($params);
		}
		return $db_driver;
	}
	/**
	 * 
	 * 设置默认连接
	 */
	public static function init_connect($params = 'default') {
		self::$db_driver = self::get_db_driver($params);
	}
	/**
	 * 
	 * 获取当前连接对象
	 */
	public static function get_self_driver($params = 'default') {
		if(!self::$db_driver){
			return self::get_db_driver($params);
		}else{
			return self::$db_driver;
		}
	}
	/**
	 * 
	 * 事务开启
	 */
	public static function begin_tran() {
		if(!self::$disable_tran){//是否禁用事务
			$db_driver = self::get_self_driver();
			$db_driver->begin_tran();
		}
		
	}
	/**
	 * 
	 * 事务结束
	 * @param unknown_type $flag
	 */
	public static function end_tran($flag = true) {
		if(!self::$disable_tran){//是否禁用事务
			$db_driver = self::get_self_driver();
			if (self::$sql_flag && $flag) {
				$db_driver->commit_tran();
			} else {
				$db_driver->rollback_tran();
			}
		}
		
	}
	/**
	 * 
	 * 查询
	 * @param $sql
	 */
	public static function query($sql) {
		$db_driver = self::get_self_driver();
		//sql是否为写操作
		if (self::is_write_type($sql) === TRUE)
		{
			return self::exec( $sql );;
		}
		//缓存
		$cache = DBcommonCache::getInstance();
		if($cache->get_cache_on() && stristr($sql, 'SELECT')){
			if($result = $cache->get_cache_result($sql)){
				return $result;
			}
		}
		$result = $db_driver->query($sql);
		if($result === false){
			self::$sql_flag = false;
			$sql_str = $sql . " " . wf_iconvutf ($db_driver->get_last_message() );
			ErrorList::add(new ErrorBox($sql_str,1));
			throw new Exception ( '数据库执行错误，请联系系统管理员！',1 );
		}else{
			if($cache->get_cache_on()){//将结果集添加到缓存
				$cache->set_cache_result($sql, $result);
			}
		}
		return $result;
	}
	/**
	 * 
	 * 执行sql
	 * @param unknown_type $sql
	 */
	public static function exec($sql) {
		$db_driver = self::get_self_driver();
		$result = $db_driver->exec($sql);
		if($result === false){
			self::$sql_flag = false;
			$sql_str = $sql . " " . wf_iconvutf ($db_driver->get_last_message() );
			ErrorList::add(new ErrorBox($sql_str,1));
			throw new Exception ( '数据库执行错误，请联系系统管理员！',1 );
			return false;
		}else{
			return true;
		}
	}
	/**
	 * 
	 * 遍历结果集
	 * @param $result
	 */
	public static function fetch_array($result,$result_type = ""){
		return $result->fetch_array($result_type);
	}
	/**
	 * 
	 * 结果集行数
	 * @param $result
	 */
	public static function num_rows($result){
		return $result->num_rows();
	}
	/**
	 * 
	 * 缓存开关
	 * @param unknown_type $flag
	 */
	public static function set_cache($flag){
		$cache = DBcommonCache::getInstance();
		$cache->set_cache_on($flag);
	}
	/**
	 * 
	 * 事务开关
	 * 默认启用事务，但根据需要也可以统一管理事务
	 */
	public static function disable_tran(){
		self::$disable_tran = true;
	}
	/**
	 * 
	 * 是否为写操作
	 * @param unknown_type $sql
	 */
	private static function is_write_type($sql)
	{
		if ( ! preg_match('/^\s*"?(SET|INSERT|UPDATE|DELETE|REPLACE|CREATE|DROP|TRUNCATE|LOAD DATA|COPY|ALTER|GRANT|REVOKE|LOCK|UNLOCK)\s+/i', $sql))
		{
			return FALSE;
		}
		return TRUE;
	}
}
<?php
/**
 * 数据库操作类
 */
class DBcommonCache{
	private $cache_on = false;
	private $time_out = 43200;//一天86400
	private static $_instance;
	private $excludeList = array();
	private function __construct(){
	}
	//创建__clone方法防止对象被复制克隆
	public function __clone(){
		echo 'Clone is not allow!';
	}
	//单例方法,用于访问实例的公共的静态方法
	public static function getInstance(){
		if(!(self::$_instance instanceof self)){
			self::$_instance = new self;
		}
		return self::$_instance;
	}

	/**
	 * 
	 * 设置例外列表
	 */
	public function setExcludeList($excludeList) {
		$this->excludeList = $excludeList;
	}
	/**
	 * 
	 * 设置缓存开关
	 * @param $flag  true|false 默认false
	 */
	public function set_cache_on($flag) {
		$this->cache_on = $flag;
		$this->clear();
	}
	/**
	 * 
	 * 获取缓存开关
	 * @param $flag  true|false 默认false
	 */
	public function get_cache_on() {
		return $this->cache_on ;
	}
	/**
	 * 
	 * 超时时间
	 * @param $second
	 */
	public function set_time_out($second) {
		$this->time_out = $second;
	}
	/**
	 * 
	 * 获取缓存结果集
	 * @param  $sql
	 */
	public function get_cache_result($sql) {
		foreach ($this->excludeList as $exclude){
			if(strpos(strtolower($sql) , $exclude)){
				return false;
			}
		}
		$file_name =  md5($sql);
		$path = $this->get_cache_path();
		$file = $path.'/'.$file_name;
		if ( ! file_exists($file))
		{
			return FALSE;
		}

		if (function_exists('file_get_contents'))
		{
			$data = file_get_contents($file);
			return unserialize($data);
		}

		if ( ! $fp = @fopen($file, FOPEN_READ))
		{
			return FALSE;
		}

		flock($fp, LOCK_SH);

		$data = '';
		if (filesize($file) > 0)
		{
			$data =& fread($fp, filesize($file));
		}

		flock($fp, LOCK_UN);
		fclose($fp);
		return unserialize($data);
	}
	/**
	 * 
	 * 设置缓存结果集
	 * @param  $sql
	 * @param  $result
	 */
	public function set_cache_result($sql,$result) {
		foreach ($this->excludeList as $exclude){
			if(strpos(strtolower($sql) , $exclude)){
				return false;
			}
		}
		$file_name =  md5($sql);
		$path = $this->get_cache_path();
		if ( ! @is_dir($path))
		{
			if ( ! @mkdir($path, DIR_WRITE_MODE))
			{
				return FALSE;
			}

			@chmod($path, DIR_WRITE_MODE);
		}
		//打开文件
		if ( ! $fp = @fopen($path.'/'.$file_name, FOPEN_WRITE_CREATE_DESTRUCTIVE))
		{
			return FALSE;
		}
		flock($fp, LOCK_UN);
		fwrite($fp, serialize($result));
		fclose($fp);
	}
	/**
	 * 
	 * 清楚缓存
	 * @param $del_dir
	 * @param $level
	 */
	public function clear()
	{
		$path = $this->get_cache_path();
		// Trim the trailing slash
		$path = rtrim($path, DIRECTORY_SEPARATOR);

		if ( ! $current_dir = @opendir($path))
		{
			return FALSE;
		}

		while (FALSE !== ($filename = @readdir($current_dir)))
		{
			if ($filename != "." and $filename != "..")
			{
				$file = $path.DIRECTORY_SEPARATOR.$filename;
				if(time() - filemtime($file)> $this->time_out){//判断文件是否超时
					unlink($file);
				}
			}
		}
		@closedir($current_dir);


		return TRUE;
	}
	/**
	 * 
	 * 缓存路径
	 */
	private function get_cache_path(){
		if(strtoupper(substr(PHP_OS,0,3))=='WIN'){
			$path = WORKFLOW_BASE."/log/db_cache";
		}else{
			$path = "/mnt/erp/workfolw_log/db_cache" ;
		}
		return $path;
	}
}
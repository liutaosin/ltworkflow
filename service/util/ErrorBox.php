<?php
/**
 * 
 * 错误类
 * 错误编码$code
 * 0  未定义错误类型
 * 1  sql语句执行错误
 * 2  工作流检查错误
 * 3  自定义脚本校验错误
 *
 */
class ErrorBox{
	var $code = "";//错误编码
	var $message = "";//错误描述
	
	public function __construct($message,$code){
		$this->message = $message;
		$this->code = $code;
		
	}
}

/**
 * 错误记录
 */
class ErrorList{
	private static $errorList = array();
	/**
	 * 
	 * 添加错误记录
	 */
	public static function add($errorBox) {
		if(get_class($errorBox) == 'ErrorBox'){
			array_push(self::$errorList, $errorBox);
			return true;
		}else{
			return false;
		}
	}
	/**
	 * 
	 * 取出错误记录
	 */
	public static function getList() {
		return self::$errorList;
	}
	/**
	 * 
	 * 是否有错误信息
	 */
	public static function haveError() {
		if(empty(self::$errorList)){
			return false;
		}else{
			return true;
		} 
	}
	
	
}

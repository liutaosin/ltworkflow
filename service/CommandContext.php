<?php 
class CommandContext{
	private static $_instance;
	
	private $wfuid = "";//流程id
	private $etuid = "";//单据id
	private $actionid = "";//动作id
	private $stepid = "";//当前环节id
	private $nextstepid = "";//下一环节id
	private $nextstepname = "";//下一环节名称
	private $comment = array();//审批意见
	private $router = array();//会签人
	private $orgcreateuser = "";//按此人组织结构审批，默认为单据起草人
	private $sessionSsn = "";//系统当前登录人工号
	private $sessionName = "";//系统当前登录人姓名
	private $sessionAccredit = "";//系统当前登录人姓名
	private $flowStatus = "";//流程状态
	
	private $nextuser = array();//下一处理人
	private $email = array();//email配置
	private $responseBox = array();//返回结果
	private $benchmark = array();//执行效率
	private $sysVar = array();//系统变量
	private $tempVar = array();//临时变量，用于替换##内容
	//private标记的构造方法
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
	//清空数据
	public function clear(){
		$this->wfuid = "";//流程id
		$this->etuid = "";//单据id
		$this->actionid = "";//动作id
		$this->stepid = "";//当前环节id
		$this->nextstepid = "";//下一环节id
		$this->nextstepname = "";//下一环节名称
		$this->comment = array();//审批意见
		$this->router = "";//会签人
		$this->orgcreateuser = "";//按此人组织结构审批，默认为单据起草人
		$this->sessionSsn = "";//系统当前登录人工号
		$this->sessionName = "";//系统当前登录人姓名
		$this->sessionAccredit = "";//系统当前登录人姓名
		$this->flowStatus = "";//流程状态
		
		$this->nextuser = array();
		$this->email = array();
		$this->responseBox = array();
		$this->benchmark = array();
		$this->sysVar = array();
		$this->tempVar = array();
		
	}
	/**
	 * @return unknown
	 */
	public function getTempVar($key="") {
		$value = "";
		if($key != ""){
			if (array_key_exists ( "#" . $key . "#", $this->tempVar )) {
				$value = $this->tempVar["#" . $key . "#"];
			}else{
				//如果key在变量数组中不存在，检查延时加载项
				$configContext = ConfigContext::getInstance();
				$initArray = $configContext->getInitElement();
				foreach ($initArray as $initKey => $init){
					if (!empty($init)){
						$init_sub = &class_load ( $init, "init", $this->etuid );
						if(method_exists($init_sub,'lazyLoad')){
							$value = $init_sub->lazyLoad ($key);
							wf_debug ( 'lazyLoad:['.$key.']='.$value.' 自定义脚本..' . simplexml_import_dom ( $init )->saveXML ());
							if ($initKey == $this->wfuid){//如果是当前流程的init跳出循环，这里用于子流程覆盖主流程存在相同key的变量
								break;
							}
						}
					}
					
				}
			}
		}else{
			$value = $this->tempVar;
		}
		return $value;
	}
	/**
	 * @param unknown_type $sysVar
	 */
	public function setTempVar($key, $val) {
		$this->tempVar["#" . $key . "#"] = $val;
	}
	/**
	 * 
	 * 初始化添加变量
	 * @param unknown_type $varArray
	 */
	public function initTempVar($varArray){
		foreach ($varArray as $key => $var){
			$this->tempVar["#" . $key . "#"] = $var;
		}
	}
	/**
	 * @return unknown
	 */
	public function getSysVar($key) {
		$value = "";
		if (array_key_exists ( $key, $this->sysVar )) {
			$value = $this->sysVar[$key];
		}
		return $value;
	}
	/**
	 * @param unknown_type $sysVar
	 */
	public function setSysVar($key, $val) {
		$this->sysVar[$key] = $val;
	}
	
	/**
	 * @return unknown
	 */
	public function getResponseBox($key="") {
		$value = "";
		if($key != ""){
			if (array_key_exists (  $key, $this->responseBox )) {
				$value = $this->responseBox[$key];
			}
		}else{
			$value = $this->responseBox;
		}
		return $value;
	}
	
	/**
	 * @param unknown_type $responseBox
	 */
	public function setResponseBox($key, $val) {
		$this->responseBox[$key] = $val;
	}
	
	/**
	 * @return unknown
	 */
	public function getNextuser() {
		return $this->nextuser;
	}
	/**
	 * 加工下一处理人信息
	 */
	public function getNextuserList() {
		$configContext = ConfigContext::getInstance();
		$userDB = $configContext->getCustomObj('userinfo');
		$userList = $userDB->getUserList($this->nextuser);
		return $userList;
	}
	/**
	 * @param unknown_type $nextuser
	 */
	public function setNextuser($nextuser) {
		$this->nextuser = $nextuser;
	}
	
	/**
	 * @return unknown
	 */
	public function getEmail() {
		return $this->email;
	}
	
	/**
	 * @param unknown_type $email
	 */
	public function setEmail($key,$val) {
		$this->email[$key] = $val;
	}
	/**
	 * @return unknown
	 */
	public function getBenchmark() {
		return $this->$benchmark;
	}
	
	/**
	 * @param unknown_type $email
	 */
	public function setBenchmark($benchmark) {
		array_push($this->$benchmark, $benchmark);
	}
	/**
	 * @return unknown
	 */
	public function getActionid() {
		return $this->actionid;
	}
	
	/**
	 * @param unknown_type $actionid
	 */
	public function setActionid($actionid) {
		$this->actionid = $actionid;
	}
	/**
	 * @return unknown
	 */
	public function getStepid() {
		return $this->stepid;
	}
	
	/**
	 * @param unknown_type $stepid
	 */
	public function setStepid($stepid) {
		$this->stepid = $stepid;
	}
	/**
	 * @return unknown
	 */
	public function getNextStepid() {
		return $this->nextstepid;
	}
	
	/**
	 * @param unknown_type $stepid
	 */
	public function setNextStepid($nextstepid) {
		$this->nextstepid = $nextstepid;
	}
	/**
	 * @return unknown
	 */
	public function getNextStepname() {
		return $this->nextstepname;
	}
	
	/**
	 * @param unknown_type $stepid
	 */
	public function setNextStepname($nextstepname) {
		$this->nextstepname = $nextstepname;
	}
	/**
	 * @return unknown
	 */
	public function getEtuid() {
		return $this->etuid;
	}
	
	/**
	 * @param unknown_type $etuid
	 */
	public function setEtuid($etuid) {
		$this->etuid = $etuid;
	}
	/**
	 * @return unknown
	 */
	public function getWfuid() {
		return $this->wfuid;
	}
	
	/**
	 * @param unknown_type $wfuid
	 */
	public function setWfuid($wfuid) {
		$this->wfuid = $wfuid;
	}
	/**
	 * @return unknown
	 */
	public function getComment() {
		return $this->comment;
	}
	
	/**
	 * @param unknown_type $wfuid
	 */
	public function setComment($wfuid,$actionid,$stepid,$stepName,$comment) {
		$this->comment['wfuid'] = $wfuid;
		$this->comment['actionid'] = $actionid;
		$this->comment['stepid'] = $stepid;
		$this->comment['stepName'] = $stepName;
		$this->comment['comment'] = $comment;
	}
	/**
	 * @return unknown
	 */
	public function getRouter() {
		return $this->router;
	}
	
	/**
	 * @param unknown_type $wfuid
	 */
	public function setRouter($router) {
		if(is_array($router)){
			$router_array = $router;
		}else{
			$router_array = explode(",", $router);
		}
		//排除重复值、空值
		$router_array = array_unique($router_array);
		$routers = array();
		foreach ($router_array as $var){
			if(!empty($var)){
				array_push($routers, $var);
			}
		}
		$this->router = $routers;
	}
	/**
	 * @return unknown
	 */
	public function getOrgcreateuser() {
		return $this->orgcreateuser;
	}
	
	/**
	 * @param unknown_type $wfuid
	 */
	public function setOrgcreateuser($orgcreateuser) {
		$this->orgcreateuser = $orgcreateuser;
	}
	/**
	 * @return unknown
	 */
	public function getFlowStatus() {
		return $this->flowStatus;
	}
	
	/**
	 * @param unknown_type $wfuid
	 */
	public function setFlowStatus($flowStatus) {
		$this->flowStatus = $flowStatus;
	}
	/**
	 * @return unknown
	 */
	public function getSessionSsn() {
		return $this->sessionSsn;
	}
	
	/**
	 * @param unknown_type $wfuid
	 */
	public function setSessionSsn($ssn) {
		$this->sessionSsn = $ssn;
	}
	/**
	 * @return unknown
	 */
	public function getSessionName() {
		return $this->sessionName;
	}
	
	/**
	 * @param unknown_type $wfuid $sessionAccredit
	 */
	public function setSessionName($name) {
		if($name==''){
			$configContext = ConfigContext::getInstance();
			$commandContext = CommandContext::getInstance();
			$userDB = $configContext->getCustomObj('userinfo');
			$user = $userDB->getUser($commandContext->getSessionSsn());
			$name = @$user['u_name'];
		}
		$this->sessionName = $name;
	}
	/**
	 * @return unknown
	 */
	public function getSessionAccredit() {
		return $this->sessionAccredit;
	}
	
	/**
	 * @param unknown_type $wfuid $sessionAccredit
	 */
	public function setSessionAccredit($accredit) {
		$this->sessionAccredit = $accredit;
	}
}
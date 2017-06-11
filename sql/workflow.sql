-- MySQL dump 10.13  Distrib 5.1.73, for Win64 (unknown)
--
-- Host: 127.0.0.1    Database: workflow
-- ------------------------------------------------------
-- Server version	5.1.73-community

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `t_purchase_contract`
--

DROP TABLE IF EXISTS `t_purchase_contract`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `t_purchase_contract` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `et_uid` varchar(50) DEFAULT NULL,
  `userno` varchar(50) DEFAULT NULL COMMENT '起草人工号',
  `username` varchar(50) DEFAULT NULL COMMENT '起草人',
  `createtime` datetime DEFAULT NULL COMMENT '创建时间',
  `contract_name` varchar(255) DEFAULT NULL COMMENT '合同',
  `contract_type` varchar(50) DEFAULT NULL COMMENT '合同类型',
  `goods_type` varchar(50) DEFAULT NULL COMMENT '采购物品类型',
  `contract_sum` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COMMENT='采购';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_purchase_contract`
--

LOCK TABLES `t_purchase_contract` WRITE;
/*!40000 ALTER TABLE `t_purchase_contract` DISABLE KEYS */;
INSERT INTO `t_purchase_contract` VALUES (7,'88a0919c-caa2-1d76-f9be-d7ba6c63d231','s001','刘明天','2017-06-03 21:09:11','流程测试','实物','办公用品','1000.00');
/*!40000 ALTER TABLE `t_purchase_contract` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `t_wf_currentstep`
--

DROP TABLE IF EXISTS `t_wf_currentstep`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `t_wf_currentstep` (
  `uid` varchar(50) NOT NULL DEFAULT '',
  `et_uid` varchar(50) DEFAULT NULL,
  `cs_salarysn` varchar(20) DEFAULT NULL,
  `cs_id` varchar(50) DEFAULT NULL,
  `cs_status` varchar(50) DEFAULT NULL,
  `cs_updateby` varchar(20) DEFAULT NULL,
  `steplock` varchar(50) DEFAULT NULL,
  `cs_endTime` datetime DEFAULT NULL,
  `cs_parentuid` varchar(50) DEFAULT NULL,
  `cs_parentid` varchar(50) DEFAULT NULL,
  `cs_orgid` varchar(50) DEFAULT NULL,
  `wf_uid` varchar(50) DEFAULT NULL,
  `cs_prestatus` varchar(50) DEFAULT NULL,
  `cs_nodename` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_wf_currentstep`
--

LOCK TABLES `t_wf_currentstep` WRITE;
/*!40000 ALTER TABLE `t_wf_currentstep` DISABLE KEYS */;
INSERT INTO `t_wf_currentstep` VALUES ('65a07689-367c-dd94-9031-096588425d54','88a0919c-caa2-1d76-f9be-d7ba6c63d231','s007','2','Dealing','s007','locked','2017-06-03 21:09:11',NULL,NULL,NULL,'1ba74cea-cbaf-cd6f-cb5d-0924a4e6e3c0',NULL,'研发经理');
/*!40000 ALTER TABLE `t_wf_currentstep` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `t_wf_entry`
--

DROP TABLE IF EXISTS `t_wf_entry`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `t_wf_entry` (
  `et_uid` varchar(50) NOT NULL DEFAULT '',
  `wf_uid` varchar(50) DEFAULT NULL,
  `et_title` varchar(350) DEFAULT NULL,
  `et_state` smallint(6) DEFAULT NULL,
  `et_createuser` varchar(50) DEFAULT NULL,
  `et_createdate` datetime DEFAULT NULL,
  `et_handle` varchar(1000) DEFAULT NULL,
  PRIMARY KEY (`et_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_wf_entry`
--

LOCK TABLES `t_wf_entry` WRITE;
/*!40000 ALTER TABLE `t_wf_entry` DISABLE KEYS */;
INSERT INTO `t_wf_entry` VALUES ('88a0919c-caa2-1d76-f9be-d7ba6c63d231','1ba74cea-cbaf-cd6f-cb5d-0924a4e6e3c0','采购审批流程',2,'s001','2017-06-03 21:09:11',NULL);
/*!40000 ALTER TABLE `t_wf_entry` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `t_wf_flowstack`
--

DROP TABLE IF EXISTS `t_wf_flowstack`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `t_wf_flowstack` (
  `fs_uid` varchar(50) NOT NULL DEFAULT '',
  `fs_puid` varchar(50) DEFAULT NULL,
  `et_uid` varchar(50) DEFAULT NULL,
  `wf_uid` varchar(50) DEFAULT NULL,
  `fs_pcsid` varchar(50) DEFAULT NULL,
  `fs_createdate` datetime DEFAULT NULL,
  PRIMARY KEY (`fs_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_wf_flowstack`
--

LOCK TABLES `t_wf_flowstack` WRITE;
/*!40000 ALTER TABLE `t_wf_flowstack` DISABLE KEYS */;
/*!40000 ALTER TABLE `t_wf_flowstack` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `t_wf_log`
--

DROP TABLE IF EXISTS `t_wf_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `t_wf_log` (
  `wflg_uid` varchar(50) NOT NULL DEFAULT '',
  `et_uid` varchar(50) DEFAULT NULL,
  `wflg_type` varchar(50) DEFAULT NULL,
  `wflg_salarysn` varchar(50) DEFAULT NULL,
  `wflg_date` datetime DEFAULT NULL,
  `wflg_startDate` datetime DEFAULT NULL,
  `wflg_finishDate` datetime DEFAULT NULL,
  `wflg_dueDate` datetime DEFAULT NULL,
  `wf_status` varchar(50) DEFAULT NULL,
  `wf_actionid` varchar(50) DEFAULT NULL,
  `cs_id` smallint(6) DEFAULT NULL,
  `cs_name` varchar(50) DEFAULT NULL,
  `wf_uid` varchar(50) DEFAULT NULL,
  `wflg_comment` varchar(5000) DEFAULT NULL,
  `wflg_accredit` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`wflg_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_wf_log`
--

LOCK TABLES `t_wf_log` WRITE;
/*!40000 ALTER TABLE `t_wf_log` DISABLE KEYS */;
INSERT INTO `t_wf_log` VALUES ('02b16c11-337f-cd7b-b6bb-d04de3f96685','88a0919c-caa2-1d76-f9be-d7ba6c63d231','Handle','s001','2017-06-03 21:09:11','2017-06-03 21:09:11','2017-06-03 21:09:11',NULL,'Submit','0',1,NULL,'1ba74cea-cbaf-cd6f-cb5d-0924a4e6e3c0','测试','s001');
/*!40000 ALTER TABLE `t_wf_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `t_wf_steps_condition`
--

DROP TABLE IF EXISTS `t_wf_steps_condition`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `t_wf_steps_condition` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `wf_uid` varchar(50) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `step_id` varchar(50) DEFAULT NULL,
  `next_step_id` varchar(50) DEFAULT NULL,
  `expression` varchar(2000) DEFAULT NULL,
  `group_uid` varchar(50) DEFAULT NULL,
  `sort` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_wf_steps_condition`
--

LOCK TABLES `t_wf_steps_condition` WRITE;
/*!40000 ALTER TABLE `t_wf_steps_condition` DISABLE KEYS */;
INSERT INTO `t_wf_steps_condition` VALUES (3,'d85ca61d-b9c9-4e86-62e4-978be88a125a','custom_role',NULL,NULL,'测试角色','4c45289d-4b97-c3d8-6424-a520d5c963a8',NULL),(5,'1ba74cea-cbaf-cd6f-cb5d-0924a4e6e3c0','role','5',NULL,'1','afe5cf26-564b-9a86-9aff-1d1ff420e541',0),(6,'1ba74cea-cbaf-cd6f-cb5d-0924a4e6e3c0','condition','5','7','',NULL,0),(7,'1ba74cea-cbaf-cd6f-cb5d-0924a4e6e3c0','role','7',NULL,'1','fadd7866-5ae3-04e4-19b1-ca094165d77e',0),(8,'1ba74cea-cbaf-cd6f-cb5d-0924a4e6e3c0','role','9',NULL,'1','ea59911c-b87d-15ef-b512-9ccb24616f04',0),(9,'1ba74cea-cbaf-cd6f-cb5d-0924a4e6e3c0','role','2',NULL,'1','3804e98e-4968-7cbb-8d17-187a62543e37',0),(10,'1ba74cea-cbaf-cd6f-cb5d-0924a4e6e3c0','role','3',NULL,'#合同类型# == \'服务\' || #合同类型# == \'市场\'','16288190-a703-c61c-50a4-1e3c49cd348c',1),(11,'1ba74cea-cbaf-cd6f-cb5d-0924a4e6e3c0','role','4',NULL,'1','5cb0c2b9-d44d-02ea-0ef2-2649a57a68b2',0),(12,'1ba74cea-cbaf-cd6f-cb5d-0924a4e6e3c0','role','3',NULL,'#合同类型# == \'实物\'','fd5d22ef-5eea-8eb4-9157-d0593545cfcf',2),(13,'1ba74cea-cbaf-cd6f-cb5d-0924a4e6e3c0','condition','2','3','#物品类型# == \'服务器\'',NULL,0);
/*!40000 ALTER TABLE `t_wf_steps_condition` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `t_wf_steps_condition_var`
--

DROP TABLE IF EXISTS `t_wf_steps_condition_var`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `t_wf_steps_condition_var` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `wf_uid` varchar(50) DEFAULT NULL,
  `expression_key` varchar(50) DEFAULT NULL,
  `expression_value` varchar(500) DEFAULT NULL,
  `expression_description` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_wf_steps_condition_var`
--

LOCK TABLES `t_wf_steps_condition_var` WRITE;
/*!40000 ALTER TABLE `t_wf_steps_condition_var` DISABLE KEYS */;
INSERT INTO `t_wf_steps_condition_var` VALUES (1,'d85ca61d-b9c9-4e86-62e4-978be88a125a','@测试@','刷刷刷s',NULL);
/*!40000 ALTER TABLE `t_wf_steps_condition_var` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `t_wf_steps_user`
--

DROP TABLE IF EXISTS `t_wf_steps_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `t_wf_steps_user` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `group_uid` varchar(50) DEFAULT NULL,
  `usercode` varchar(50) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_wf_steps_user`
--

LOCK TABLES `t_wf_steps_user` WRITE;
/*!40000 ALTER TABLE `t_wf_steps_user` DISABLE KEYS */;
INSERT INTO `t_wf_steps_user` VALUES (4,'4c45289d-4b97-c3d8-6424-a520d5c963a8','s001','刘三'),(9,'afe5cf26-564b-9a86-9aff-1d1ff420e541','s003','张强'),(10,'afe5cf26-564b-9a86-9aff-1d1ff420e541','s007','张国栋'),(11,'fadd7866-5ae3-04e4-19b1-ca094165d77e','s008','李菲'),(12,'ea59911c-b87d-15ef-b512-9ccb24616f04','s009','黄土良'),(17,'3804e98e-4968-7cbb-8d17-187a62543e37','s007','张国栋'),(20,'5cb0c2b9-d44d-02ea-0ef2-2649a57a68b2','s009','黄土良'),(21,'5cb0c2b9-d44d-02ea-0ef2-2649a57a68b2','s003','张强'),(29,'16288190-a703-c61c-50a4-1e3c49cd348c','s006','张爽'),(30,'fd5d22ef-5eea-8eb4-9157-d0593545cfcf','s004','赵丽颖');
/*!40000 ALTER TABLE `t_wf_steps_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `t_wf_testcase`
--

DROP TABLE IF EXISTS `t_wf_testcase`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `t_wf_testcase` (
  `tc_uid` varchar(50) NOT NULL DEFAULT '',
  `tc_name` varchar(100) DEFAULT NULL,
  `tc_joinmark` varchar(50) DEFAULT NULL,
  `tc_xmlcontent` text,
  `tc_updateuser` varchar(50) DEFAULT NULL,
  `tc_updatetime` datetime DEFAULT NULL,
  `tc_discript` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`tc_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_wf_testcase`
--

LOCK TABLES `t_wf_testcase` WRITE;
/*!40000 ALTER TABLE `t_wf_testcase` DISABLE KEYS */;
/*!40000 ALTER TABLE `t_wf_testcase` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `t_wf_testcase_result`
--

DROP TABLE IF EXISTS `t_wf_testcase_result`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `t_wf_testcase_result` (
  `tcr_uid` int(11) NOT NULL AUTO_INCREMENT,
  `tcr_pid` varchar(50) DEFAULT NULL,
  `tcr_authoruser` varchar(50) DEFAULT NULL,
  `tcr_condtion` varchar(1000) DEFAULT NULL,
  `tcr_approvelist` varchar(1000) DEFAULT NULL,
  `tcr_createuser` varchar(50) DEFAULT NULL,
  `tcr_createtime` datetime DEFAULT NULL,
  PRIMARY KEY (`tcr_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_wf_testcase_result`
--

LOCK TABLES `t_wf_testcase_result` WRITE;
/*!40000 ALTER TABLE `t_wf_testcase_result` DISABLE KEYS */;
/*!40000 ALTER TABLE `t_wf_testcase_result` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `t_wf_workflow`
--

DROP TABLE IF EXISTS `t_wf_workflow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `t_wf_workflow` (
  `wf_uid` varchar(50) NOT NULL DEFAULT '',
  `wf_name` varchar(50) DEFAULT NULL,
  `wf_createtime` datetime DEFAULT NULL,
  `wf_filename` text,
  `wf_layout` text,
  `wf_version` varchar(50) DEFAULT NULL,
  `wf_handle` varchar(100) DEFAULT NULL,
  `wf_superior` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`wf_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_wf_workflow`
--

LOCK TABLES `t_wf_workflow` WRITE;
/*!40000 ALTER TABLE `t_wf_workflow` DISABLE KEYS */;
INSERT INTO `t_wf_workflow` VALUES ('1ba74cea-cbaf-cd6f-cb5d-0924a4e6e3c0','采购审批流程','2017-03-19 17:55:16','<?xml version=\"1.0\" encoding=\"utf8\"?>\r\n<workflow title=\"采购审批流程\">   \r\n	<global>\r\n		<init path=\"test\"/>\r\n		<email type=\"html\" doctype=\"测试邮件\"/>\r\n		<reject name=\"驳回\" path=\"test/Reject\"/>\r\n		<countersign name=\"会签\" path=\"test/Countersign\"/>\r\n	</global>\r\n	<initial-actions id=\"1\">\r\n		<condition/>\r\n		<actions>\r\n			<action id=\"0\" name=\"送审\" >\r\n				<validate type=\"beanshell\" path=\"test/main_before\">\r\n					<type>submit</type>\r\n				</validate>\r\n				<results>\r\n					<unconditional-result step=\"2\">\r\n						<roles>\r\n								<role type=\"2\" >\r\n								</role>\r\n						</roles>\r\n					</unconditional-result>\r\n				</results>\r\n				<post-function type=\"beanshell\" path=\"test/main_after\">\r\n					<type>submit</type>\r\n				</post-function>\r\n			</action>\r\n		</actions>\r\n	</initial-actions>\r\n	<steps>\r\n		<step id=\"2\" name=\"研发经理\">\r\n			<actions>\r\n				<action id=\"1\" name=\"通过\" >\r\n					<results>\r\n						<result step=\"3\">\r\n							<roles>\r\n								<role type=\"2\" ></role>\r\n							</roles>\r\n							<condition type=\"config\">\r\n							</condition>\r\n						</result>\r\n						<unconditional-result  step=\"4\">\r\n							<roles>\r\n								<role type=\"2\"></role>\r\n							</roles>\r\n						</unconditional-result>\r\n					</results>\r\n				</action>\r\n			</actions>\r\n		</step>\r\n		<step id=\"3\" name=\"财务\">\r\n			<actions>\r\n				<action id=\"1\" name=\"通过\" >\r\n					<validate type=\"beanshell\" path=\"test/main_before\">\r\n						<type>act2</type>\r\n					</validate>\r\n					<results>\r\n						<unconditional-result  step=\"4\">\r\n							<roles>\r\n								<role type=\"2\" >\r\n								</role>\r\n							</roles>\r\n						</unconditional-result>\r\n					</results>\r\n					<post-function type=\"beanshell\" path=\"test/main_after\">\r\n						<type>act2</type>\r\n					</post-function>\r\n				</action>\r\n			</actions>\r\n		</step>\r\n		<step id=\"4\" name=\"法务\">\r\n			<actions>\r\n				<action id=\"1\" name=\"通过\" >\r\n					<validate type=\"beanshell\" path=\"test/main_before\">\r\n						<type>act3</type>\r\n					</validate>\r\n					<results>\r\n						<unconditional-result end=\"500\">\r\n							<roles>\r\n								<role type=\"2\"></role>\r\n							</roles>\r\n						</unconditional-result>\r\n					</results>\r\n				</action>\r\n			</actions>\r\n		</step>\r\n	</steps>\r\n	<splits/>\r\n	<joins/>\r\n	<end-nodes>\r\n		<node id=\"500\" name=\"结束\" path=\"test/main_after\">\r\n			<type>complete</type>\r\n		</node>\r\n	</end-nodes>\r\n</workflow>','{ \"class\": \"go.GraphLinksModel\",\n  \"linkFromPortIdProperty\": \"fromPort\",\n  \"linkToPortIdProperty\": \"toPort\",\n  \"nodeDataArray\": [ \n{\"key\":\"1\", \"id\":\"1\", \"text\":\"开始\", \"category\":\"Start\", \"loc\":\"0 0\"},\n{\"key\":\"2\", \"id\":\"2\", \"text\":\"研发经理\", \"loc\":\"-1.4210854715202004e-14 71.99999999999997\"},\n{\"key\":\"3\", \"id\":\"3\", \"text\":\"财务\", \"loc\":\"126 111.99999999999987\"},\n{\"key\":\"4\", \"id\":\"4\", \"text\":\"法务\", \"loc\":\"0 180\"},\n{\"key\":\"500\", \"id\":\"500\", \"text\":\"结束\", \"category\":\"End\", \"loc\":\"3.552713678800501e-15 251.00000000000003\"}\n ],\n  \"linkDataArray\": [ \n{\"from\":1, \"to\":\"2\", \"fromPort\":\"B\", \"toPort\":\"T\", \"condition_type\":\"true\", \"condition_value\":\"true\", \"role_type\":\"2\", \"role_value\":\"\", \"points\":[0,21.808122679244644,0,31.808122679244644,0,38.75406133962232,0,38.75406133962232,0,45.699999999999996,0,55.699999999999996]},\n{\"from\":\"2\", \"to\":\"3\", \"fromPort\":\"R\", \"toPort\":\"T\", \"condition_type\":\"config\", \"condition_value\":\"\", \"role_type\":\"2\", \"role_value\":\"\", \"points\":[37.819976806640625,72,47.819976806640625,72,126,72,126,78.84999999999994,126,85.69999999999987,126,95.69999999999987]},\n{\"from\":\"3\", \"to\":\"4\", \"fromPort\":\"B\", \"toPort\":\"T\", \"condition_type\":\"true\", \"condition_value\":\"true\", \"role_type\":\"2\", \"role_value\":\"\", \"points\":[126,128.3,126,138.3,126,146,0,146,0,153.7,0,163.7]},\n{\"from\":\"4\", \"to\":\"500\", \"fromPort\":\"B\", \"toPort\":\"T\", \"condition_type\":\"true\", \"condition_value\":\"true\", \"role_type\":\"\", \"role_value\":\"\", \"points\":[0,196.29999999999998,0,206.29999999999998,0,212.74593866037767,0,212.74593866037767,0,219.19187732075537,0,229.19187732075537]},\n{\"from\":\"2\", \"to\":\"4\", \"fromPort\":\"B\", \"toPort\":\"T\", \"condition_type\":\"true\", \"condition_value\":\"true\", \"role_type\":\"2\", \"role_value\":\"\", \"points\":[0,88.3,0,98.3,0,126,0,126,0,153.7,0,163.7]}\n ]}','1.0',NULL,NULL);
/*!40000 ALTER TABLE `t_wf_workflow` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `t_wf_workflow_xmllog`
--

DROP TABLE IF EXISTS `t_wf_workflow_xmllog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `t_wf_workflow_xmllog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `wf_uid` varchar(50) DEFAULT NULL,
  `wf_name` varchar(200) DEFAULT NULL,
  `wf_filename` text,
  `wf_layout` text,
  `wf_version` decimal(10,4) DEFAULT NULL,
  `logtime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_wf_workflow_xmllog`
--

LOCK TABLES `t_wf_workflow_xmllog` WRITE;
/*!40000 ALTER TABLE `t_wf_workflow_xmllog` DISABLE KEYS */;
/*!40000 ALTER TABLE `t_wf_workflow_xmllog` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `v_wf_role`
--

DROP TABLE IF EXISTS `v_wf_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `v_wf_role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(100) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `descript` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `v_wf_role`
--

LOCK TABLES `v_wf_role` WRITE;
/*!40000 ALTER TABLE `v_wf_role` DISABLE KEYS */;
INSERT INTO `v_wf_role` VALUES (1,'fina','财务经理',''),(2,'law','法务经理',NULL),(3,'development','研发经理',NULL);
/*!40000 ALTER TABLE `v_wf_role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `v_wf_role_user`
--

DROP TABLE IF EXISTS `v_wf_role_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `v_wf_role_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `roleid` varchar(50) DEFAULT NULL,
  `code` varchar(100) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `salarysn` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `v_wf_role_user`
--

LOCK TABLES `v_wf_role_user` WRITE;
/*!40000 ALTER TABLE `v_wf_role_user` DISABLE KEYS */;
INSERT INTO `v_wf_role_user` VALUES (1,'1','fina','张爽','s006'),(2,'1','fina','赵丽颖','s004'),(3,'2','law','张强','s003'),(4,'3','development','张国栋','s007');
/*!40000 ALTER TABLE `v_wf_role_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `v_wf_user`
--

DROP TABLE IF EXISTS `v_wf_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `v_wf_user` (
  `u_usercode` varchar(50) NOT NULL DEFAULT '',
  `u_name` varchar(160) DEFAULT NULL,
  `u_englishname` varchar(50) DEFAULT NULL,
  `u_telephone` varchar(50) DEFAULT NULL,
  `u_departmentId` varchar(50) DEFAULT NULL,
  `u_company` varchar(100) DEFAULT NULL,
  `u_area` varchar(100) DEFAULT NULL,
  `u_email` varchar(100) DEFAULT NULL,
  `u_opptime` datetime DEFAULT NULL,
  PRIMARY KEY (`u_usercode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `v_wf_user`
--

LOCK TABLES `v_wf_user` WRITE;
/*!40000 ALTER TABLE `v_wf_user` DISABLE KEYS */;
INSERT INTO `v_wf_user` VALUES ('s001','刘明天',NULL,NULL,NULL,'sina','北京','liusan@sina.com','1899-12-29 00:00:00'),('s002','刘四',NULL,NULL,NULL,'ali','浙江','liusi@ali.com',NULL),('s003','张强',NULL,NULL,NULL,'','上海',NULL,NULL),('s004','赵丽颖',NULL,NULL,NULL,NULL,'北京',NULL,NULL),('s006','张爽',NULL,NULL,NULL,NULL,'上海',NULL,NULL),('s007','张国栋',NULL,NULL,NULL,NULL,'北京',NULL,NULL),('s008','李菲',NULL,NULL,NULL,NULL,'北京',NULL,NULL),('s009','黄土良',NULL,NULL,NULL,NULL,'北京',NULL,NULL);
/*!40000 ALTER TABLE `v_wf_user` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-06-04  9:24:46

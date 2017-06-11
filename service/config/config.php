<?php
//用户定义文件夹
$wf_custom_folder = "custom";

//用户邮件处理类,如果不需要可注释$email数组
//必需实现service/inheritance/InterfaceEmail接口
$email['default']['class'] = "MailService";//类名
$email['default']['path'] = $wf_custom_folder."/email/mailservice.php";//类路径

$email['app']['class'] = "AppService";//类名
$email['app']['path'] = $wf_custom_folder."/email/appservice.php";//类路径

//用户信息类
// userinfo必需实现service/inheritance/InterfaceUserDB接口
$custom['userinfo']['class'] = "UserDB";
$custom['userinfo']['path'] = $wf_custom_folder."/database/UserDB.php";



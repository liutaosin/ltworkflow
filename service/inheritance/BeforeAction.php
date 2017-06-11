<?php
//定义下一节点前处理
abstract class BeforeAction extends WorkflowBase{

  abstract function action();
}
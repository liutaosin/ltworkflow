<?php
//定义流程校验类模板
abstract class Restrict extends WorkflowBase{

  abstract function validate();
}
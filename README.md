# ltworkflow 1.0
===========
Copyright 2016-2017 liutao,http://phpworkflow.cn

ltworkflow 是轻量级PHP工作流引擎，需要嵌入到已有项目中使用,本项目遵循开源协议 Apache License V2。
It's open-source and distributed under the Apache license Version 2.0. 

============================================================================
适应中国国情的PHP工作流引擎，开放源代码

一、功能介绍
本工作流引擎支持的功能

1、图形化配置审批条件、审批人
2、支持串行审批、并行审批
3、支持会签、驳回
4、支持多级组织结构
5、支持自定义PHP脚本，在审批前或审批后调用
二、基本原理
1、原型参考java开源工作流引擎osworkflow
2、通过xml配置流程节点
3、使用PHP解析XML并在数据库中记录操作状态
三、环境需求
PHP5.2或以上
mysql5 / sqlserver2008

使用说明及示例程序详见网站 http://phpworkflow.cn/

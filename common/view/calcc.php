<?php
require_once __DIR__.'/../../base.php';
echo "<pre>";
//*
//读取开始日期参数
$zqrs = DbConfig::getParam('zqrs',PSPACE);
$totalr = DbConfig::getParam('totalr',PSPACE);
$rrsyje = DbConfig::getParam('rrsyje',PSPACE);
$presyje = DbConfig::getParam('presyje',PSPACE);
$sdate = $totalr['date'] ;
$cond['$and'][] = (object)array('0'=>array('$gt'=>$sdate));

require_once ROOT.'/data/config_calcc.php';

<?php
require_once(__DIR__.'/../../base.php');

$zqrs = DbConfig::getParam('zqrs',PSPACE);
$jgrq = DbConfig::getParam('prejgrq',PSPACE);
$sshq = DbConfig::getParam('sshq',PSPACE);
$i = 0;
$list = '';
echo "<pre>\n";
//*
foreach($zqrs as $k=>$row){
    $info = Crawler_Xueqiu::getGupiaoDay($k,$prejgrq);
    if($info)
        $sshq[$k] = $info['close'];
}
DbConfig::saveParam('sshq',$sshq,PSPACE);
// */


<?php

$coll = $this->getParam('coll');
$collconf = DbConfig::getParam("gridconfig.$coll",'grid');
$collheader = $collconf['header'];

$id = $cond['_id'] = $this->getParam('id');
$mc = DbConfig::getMongodb($coll);
$mc->findAndModify($cond,array('$set'=>array('_byone'=>'del')));
?>
<h3><?php echo $collconf['name']."  id 为 $id"; ?> 标记删除数据</h3>
<hr/>
<div>查看选择显示id，添加到，双击标记删除，将&id=xxx加到url后面删除</div>
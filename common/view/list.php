<?php
$dconf = $show_config[$coll];
$tconf['title'] = $dconf['name'];
$tconf['cols'] = array();
$cols = &$tconf['cols'];
foreach((array)$dconf['aheader'] as $k=>$v){
    $type = 'string';
    if(in_array($k,(array)$dconf['numtpye']))
        $type = 'number';
    $cols[]= array('id'=>$k,'label'=>$v,'type'=>$type);
}
foreach($dconf['header'] as $k=>$v){
    if(!isset($tconf['uheader'])){
        $tconf['uheader'] = $k;
        $tconf['uvalue'] = $v;
    }
    $type = 'string';
    if(in_array($k,(array)$dconf['numtpye']))
        $type = 'number';
    $cols[]= array('id'=>$k,'label'=>$v,'type'=>$type);
}

$cond = array();
$mon = new PL_Db_Mongo(DbConfig::getMongodb($coll)); 

$limit = $this->getParam('limit',30);
$page = $this->getParam('page',0);
$sort = $this->getParam('sort',0);
$sortt = $this->getParam('sortt',-1);
$rows = $mon->findByIndex($coll,$cond,$limit,$limit * $page,array(),(object)array($sort=>(int)$sortt));
//$rows = $mon->findByIndex($coll,$cond,$limit,$limit * $page,array(),array(0=>-1));
echo DTable::buildtablehtml($rows,$tconf,$table);


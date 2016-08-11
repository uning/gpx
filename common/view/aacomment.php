<?php
require_once __DIR__.'/../../base.php';
$coll = 'calcc';
$mon = new PL_Db_Mongo(DbConfig::getMongodb($coll)); 


$cond['$or'][] = array('date'=>'20150520','istotal'=>0);
$cond['$or'][] = array(6=>0);


$cond = array('date'=>'lastest');
$cond[6]['$ne'] = 0;


$cond = array('bz'=>array('$exists'=>true));
$cond = array('istotal'=>1);

$sort = (object)array(8=>-1,'date'=>-1);
$limit = 1;
$c = $mon->findByIndex($coll,(object)$cond,$limit,0,array(),array(),true);
while($row = $c->getNext()){
    print_r($row);
    //$mon->findAndModify(array('_id'=>$id),array('$set'=>array('bz'=>$bz)));

    /*
    $id = $row['2'].'_zero';
    $row['date'] = 'lastest';
    unset($row['_id']);
    $mon->findAndModify(array('_id'=>$id),array('$set'=>$row));
     */
}

//print_r($bzs);

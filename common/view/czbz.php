<?php
//add comment
$zqdm = $this->getParam('zqdm');
$content = $this->getParam('content');
$doq = $this->getParam('doq');

$coll = 'czbz';
$dconf = &App::getDataconf($coll);

$mc = DbConfig::getMongodb('czbz'); 
if($zqdm && $content ){
    $zqmc = $this->getParam('zqmc');
    $ts = $this->getParam('ts');
    $oper = $this->getParam('oper');
    $cond = array('_id'=>$ts);
    if($oper == 'del'){
        $mc->del($cond);
        exit;
    }
    if($content){
        if(!$ts)
            $ts = $_SERVER['REQUEST_TIME'];
        $datestr = @date('Ymd H:i:s',$ts);
        $bzo = array('time'=>$datestr,'ts'=>$ts,'zqmc'=>$zqmc,'zqdm'=>$zqdm,'content'=>$content);
        $mc->findAndModify($cond,array('$set'=>$bzo),array(),array('upsert'=>true));
    }
    exit;
}elseif($doq){
    static::processGridAjaxParams($sort,$cond,$limit,$skip,$filterstr ,$sidx);
    $mon = new PL_Db_Mongo($mc);
    $c = $mon->findByIndex($coll,(object)$cond,$limit,$skip,array(),(object)$sort,true);
    while($row = $c->getNext()){
        $rows[] = $row;

    }
    $records = $c->count();
    $total = ceil($records/$limit);
    echo json_encode(array(
        'rows'=>$rows
        ,'page'=> 1
        ,'records'=>$records
        ,'total'=>$records
        ,'req'=>$_REQUEST
    ));
}

$colModel = &$jqconf['colModel'];
$datepos = -1;
$colModel = App::getColModel($coll,'header',$datepos);
$jqconf['edit'] = true;
include  __DIR__.'/part_grid.php';



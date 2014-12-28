<?php
//add comment
$prid = $this->getParam('prid');
$prid = $this->getParam('prid');
$content = $this->getParam('content');

if($prid){
    $mc = DbConfig::getMongodb($coll); 
    $oper = $this->getParam('oper');

    if(  $content){
        if($oper == 'add'){
            $now = $_SERVER['REQUEST_TIME'];
        }else{
            $now = $this->getParam('id');
        }
        $datestr = @date('Ymd H:i:s',$now);
        $upo = array('bz.'.$now=>array('time'=>$datestr,'content'=>$content));
        $mc->findAndModify(array('_id'=>$prid),array('$set'=>$upo),array(),array('upsert'=>true));
    }elseif($oper == 'del'){
            $now = $this->getParam('id');

            //not work
            //$mc->findAndModify(array('_id'=>$prid),array('$unset'=>array('bz'.$now=>0)));
    }else{
        $bzrow = $mc->findOne(array('_id'=>$prid));
        $bzs = $bzrow['bz'];
        $rows = array();
        $records = 0;
        if($bzs){
            foreach($bzs as $k=>$v){
                $v['id'] = $k;
                $rows[] = $v;
                $records += 1;
            }
        }
    }
}
echo json_encode(array(
    'rows'=>$rows
    ,'page'=> 1
    ,'records'=>$records
    ,'total'=>$records
    ,'req'=>$_REQUEST
));



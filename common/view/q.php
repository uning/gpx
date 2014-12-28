<?php

$cond = array();
$mon = new PL_Db_Mongo(DbConfig::getMongodb($coll)); 


$limit = $this->getParam('rows',20);
$page = $this->getParam('page',0);

//prcoess sort as string "0 dec,1"
$sort = array();
$sidx = $this->getParam('sidx');
$sord = $this->getParam('sord');
if($sidx){
    $sidx .= $sord;
    preg_match_all('/[\s]*([\w]+)[\s]+([\w]+)[\s]*/',$sidx,$mout);
    foreach((array)$mout[1] as $k=>$v){
        if($mout[2][$k] == 'asc'){
            $sort[$v] = 1;
        }else
            $sort[$v] = -1;
    } 
}else{
        if($sord == 'desc')
            $sort[0] = -1;
        else if ($sord == 'asc')
            $sort[0] = 1;

}



//process query
//todo procecess all ops
$optomon = array('le'=>'$lte','eq'=>'$eq','lt'=>'$lt','gt'=>'$gt','ge'=>'$gte','ne'=>'$ne');
$filterstr =$this->getParam('filters');
if($filterstr){
    $filters = json_decode($filterstr,true);
    if($filters['groupOp'] == 'AND'){
        foreach($filters['rules'] as $ru){
            $op = $ru['op'];
            //if(in_array($op,array ('lte','eq','ne'))
            $dbop = $optomon[$op];
            if($dbop){
                $cond[$ru['field']][$dbop] = $ru['data'];
            }else if($op == 'bw'){
                $cond[$ru['field']] = new MongoRegex("/^{$ru['data']}/");
            }
        }
    }
}



//echo " $limit $page" ,print_r($sort);
$skip = $page < 1 ? 0 : ($page - 1)*$limit;

//持仓处理
$chich = $this->getParam('chich');
if($chich){
    $unidf = $this->getParam('unidf');
    //use sidx
//    $sortf = $this->getParam('sortf');
//    $sort = array($datef=>-1);
    $numf = $this->getParam('numf');
    $skip = 0;
    $limit = 100000;//全部
}
$c = $mon->findByIndex($coll,(object)$cond,$limit,$skip,array(),(object)$sort,true);
$collconf = $show_config[$coll];


while($row = $c->getNext()){
    if($chich){
        $unid = $row[$unidf];
        if($unid && $appeared[$unid] < 1){
            $appeared[$unid] = 1;
            if($row[$numf] >0)
                $rows[] = $row;
        }
    }else{
        $rows[] = $row;
    }
}
$records = $c->count();
$total = ceil($records/$limit);

echo json_encode(array(
    'rows'=>$rows
    ,'page'=> $page
    ,'records'=>$records
    ,'total'=>$total
    ,'req'=>$_REQUEST
    ,'sidx'=>$sidx
    ,'sort'=>(object)$sort
    ,'cond'=>(object)$cond
    ,'filters'=>$filters
    ,'filterstr'=>$filterstr
));


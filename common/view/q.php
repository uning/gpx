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
$c = $mon->findByIndex($coll,(object)$cond,$limit,$skip,array(),(object)$sort,true);
$headerr = $show_config[$coll]['header'][0];
while($row = $c->getNext()){
    if($row[0] == $headerr)
        continue;
    $rows[] = $row;
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


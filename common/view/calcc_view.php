<?php

//$coll = 'calcc';
$sheader = $this->getParam('header','header');
$doq = $this->getParam('doq');
$lastest = static::getParam('lastest');

//返回表数据
if($doq){
    $mon = new PL_Db_Mongo(DbConfig::getMongodb($coll)); 
    $prid = static::getParam('prid');
    if($prid){//请求交割记录
        $parr= explode('_',$prid);
        $zqdm = $parr[0];
    }
    if($zqdm && $zqdm != 'total'){
        $limit = 10000;
        $skip = 0;
        $sort =array(0=>-1,16=>-1);//交易倒序
        $cond = array(2=>$zqdm);
        $c = $mon->findByIndex('jgd',(object)$cond,$limit,$skip,array(),(object)$sort,true);
    }else{
        static::processGridAjaxParams($sort,$cond,$limit,$skip,$filterstr ,$sidx);
        if($lastest == 'lastest'){
            $c = $mon->findByIndex($coll,(object)$cond,1,0,array(),array('date'=>-1),true);
            $row = $c->getNext();
            $cond['date'] = $row['date'];
        }elseif($lastest)
            $cond['date'] = $lastest; 

        if(static::getParam('except0')){
            $cond[6]['$ne'] = 0;
        }


        if($sheader == 'theader'){
            $cond['istotal'] = 1;
        }else{
            if(!$lastest)
                $cond['istotal'] = 0;
        }


        if($zqdm == 'total'){
            $cond = array('date'=> $parr[1]);
            $cond['istotal'] = 0;
        }
        $c = $mon->findByIndex($coll,(object)$cond,$limit,$skip,array(),(object)$sort,true);
    }
    while($row = $c->getNext()){
        if($prid){
            $row['chtime'] = App::dateDifference($row[0],'today');
        }
        if($row['istotal'] == 0 || $cond['istotal'])
            $rows[] = $row;
        else{//userData
            $userData = $row;
            $dconf = App::getDataconf('calcc');
            $header = $dconf['theader'];
            
            foreach($header as $k=>$v){
                $str.= $v.':'.$row[$k].',';
            }
            $userData['date'] = $str;
            $userData[2] = '汇总';
            $userData[3] = '资产总额:';
            $userData[6] = $row['zc'];
            $userData[4] = '可用资金:';
            $userData['pdate'] = $row['kyye'];
        }
    }
    $records = $c->count();
    $total = ceil($records/$limit);

    echo json_encode(array(
        'rows'=>$rows
        ,'page'=> $page
        ,'records'=>$records
        ,'userdata'=>$userData
        ,'total'=>$total
        ,'req'=>$_REQUEST
        ,'sidx'=>$sidx
        ,'sort'=>(object)$sort
        ,'cond'=>(object)$cond
        ,'filters'=>$filters
        ,'filterstr'=>$filterstr
    ));
    return;
}
$subConf = array();
$colModel = &$jqconf['colModel'];
$jqconf['multiSort'] = static::getParam('multiSort',false);

$jqconf['footerrow'] = false;
if($lastest){
    $jqconf['loadonce'] = true;
    $jqconf['footerrow'] = true;
    $jqconf['rowNum'] = 50000;//
    $jqconf['subGrid'] =true;
    $subConf['colModel'] = App::getColModel($coll,'jgdheader' ,-1,9);
    $subConf['me_edit'] = false;
    $subConf['loadonce'] = true;
    $subConf['subGrid'] = true;
}


$param = $_REQUEST;
$param['__nl'] = 1;
$param['doq'] = '1';
$jqconf['url'] = url($param);
$datepos = 0;

$colModel = App::getColModel($coll,$sheader,$datepos,-1);
$subConf['loadonce'] = true;

if($sheader == 'theader'){
    $subConf['colModel'] = App::getColModel($coll,'header',-1);
    $subConf['me_edit'] = false;
    $jqconf['footerrow'] = false;
    //$subConf['datatype'] = 'local';
}
$subConf['urlp'] = url($param).'&prid=';

include  __DIR__.'/part_grid.php';

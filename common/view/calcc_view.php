<?php

$sheader = $this->getParam('header','header');
$doq = $this->getParam('doq');
$lastest = static::getParam('lastest');
$dconf = &App::getDataconf($coll);

//返回表数据
if($doq){
    $mon = new PL_Db_Mongo(DbConfig::getMongodb($coll)); 
    $prid = static::getParam('prid');
    if($prid){//请求交割记录
        $parr= explode('_',$prid);
        $zqdm = $parr[0];
        $rowdata = json_decode(static::getParam('rowdata'),true);
    }

    //请求具体证券的交割记录
    if($zqdm && $zqdm != 'total'){
        $limit = 10000;
        $skip = 0;
        $sort =array(0=>-1,16=>-1);//交易倒序
        $cond = array(2=>$zqdm);
        $ccdate = $parr[1];
        $ccdate = $rowdata['date'];
        $c = $mon->findByIndex('jgd',(object)$cond,$limit,$skip,array(),(object)$sort,true);
    }else{
        //请求calcc
        static::processGridAjaxParams($sort,$cond,$limit,$skip,$filterstr ,$sidx);


        if($lastest == 'lastest'){
            $c = $mon->findByIndex($coll,(object)$cond,1,0,array(),array('date'=>-1),true);
            $row = $c->getNext();
            $cond['date'] = $row['date'];
        }elseif($lastest){
            $cond['date'] = $lastest; 
        }

        //获取为0的
        if($include0 = static::getParam('include0')){
            //$cond[6]['$ne'] = 0;
            $ocond = $cond;
            $cond = array();
            $cond['$or'][] = array(6=>0);
            $cond['$or'][]= $ocond;

            $ocond = $cond;
            $cond = array();
            $cond['$and'][] = array(8=>array('$ne'=>0)); //去掉申购的，认为申购未中签的,清算额为0
            $cond['$and'][]= $ocond;
        }


        if($sheader == 'theader'){
            $cond['istotal'] = 1;
            if(!$sort){
                $sort['date'] = -1;
            }
        }else{
            if(!$lastest){
                $cond['istotal'] = 0;
                //股票聚合
                if(!$sort){
                    $sort = array('date'=>-1);
                }
            }
        }

        if($zqdm == 'total'){
            $cond = array('date'=> $parr[1]);
            $cond['istotal'] = 0;
        }

        $sort['istotal'] = -1;
        $sort[6] = -1;
        $c = $mon->findByIndex($coll,(object)$cond,$limit,$skip,array(),(object)$sort,true);
    }


    $zqdms = array();
    while($row = $c->getNext()){
        if($lastest && $include0 && !$prid){
            $zqdm = $row[2];
            if($zqdms[$zqdm] )
                continue;
            $zqdms[$zqdm] = 1;
        }
        //交割单处理
        if($ccdate)
            $row['chtime'] = App::dateDifference($row[0],$ccdate); 

        if($row['istotal'] == 0 || isset($cond['istotal'])){
            $row['_forsum'] = '_forsum';
            $rows[] = $row;
        }else{//userData
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


$param = $_REQUEST;
$param['__nl'] = 1;
$param['doq'] = '1';
$jqconf['url'] = url($param);
$datepos = 0;

$colModel = App::getColModel($coll,$sheader,$datepos,-1);
$subConf['loadonce'] = true;
$subConf['me_edit'] = false;
$jqconf['subGrid'] = true;
$subConf['subGrid'] = true;
if($sheader == 'theader'){
    $subConf['colModel'] = App::getColModel($coll,'header',-1,-1);
}else{
    if($lastest){
        $jqconf['loadonce'] = true;
        $jqconf['rowNum'] = 50000;//
        $jqconf['footerrow'] = true;
        
        $colModel[] = array('name'=>'_forsum','label'=>'求和','width'=>20);
        $colModel[] = array('name'=>'ssjg','label'=>'实时价格','width'=>70,'sorttype'=>'number');
        $colModel[] = array('name'=>'sszf','label'=>'今日涨幅%','width'=>70,'sorttype'=>'number');
        $colModel[] = array('name'=>'ssyk','label'=>'实时总盈亏','width'=>70,'sorttype'=>'number');
       $colModel[] = array('name'=>'jryk','label'=>'今日盈亏','width'=>70,'sorttype'=>'number');
        $jqconf['sshqColModel'] = App::getColModel('sshq','header',-1,-1);
        include __DIR__.'/part_refresh.php';
        
    }
    $subConf['colModel'] = App::getColModel($coll,'jgdheader' ,-1,9);
}
$subConf['urlp'] = url($param).'&prid=';

include  __DIR__.'/part_group.php';
include  __DIR__.'/part_grid.php';

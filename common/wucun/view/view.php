<?php
$vfile = $this->viewRoot."/{$coll}_view.php";
if(file_exists($vfile)){
    include($vfile);
    return;
}

$dconf = &App::getDataconf($coll);
$doq = $this->getParam('doq');

$psidx = $this->getParam('psidx');//this param is overwrite by get data,but initGrid not suupport multiSort
$sord = $this->getParam('sord','desc');
$multiSort = $this->getParam('multiSort',true);
$sheader = $this->getParam('header','header');

$startdate = $this->getParam('startdate');
$enddate = $this->getParam('enddate');

$datepos = $dconf['datepos'];
//返回表数据
if($doq){
    static::processGridAjaxParams($sort,$cond,$limit,$skip,$filterstr ,$sidx);
    $mon = new PL_Db_Mongo(DbConfig::getMongodb($coll));
    $sort['_fnorder']=1;

   
    if($startdate){
        $cond[$datepos]['$gte'] = $startdate;
    }
    if($endate){
        $cond[$datepos]['$lte'] = $enddate;
    }
    if(in_array($sheader,array('group1header'))){
        $limit = 10000;
    }

    $cond['_byone']['$ne'] = 'del';

    $c = $mon->findByIndex($coll,(object)$cond,$limit,$skip,array(),(object)$sort,true);

    $cmodel  = $dconf['colModel'];
    $rowidx = -1;

    if ($sheader == 'group1header') {
        while ($row = $c->getNext()) {
            $day = $row[$datepos];
            if($startdate  > $day || $startdate == ''){
               $startdate = $day;
            }
            if($enddate <$day || $enddate == ''){
               $enddate = $day;
            }
            if ($row[2] < 0) {
                $costAll += $row[2];
                $costStat[$row[1]] += $row[2];
            } else {
                $incomeAll += $row[2];
                $incomeStat[$row[1]] += $row[2];
            }
        }
    } else {
        while ($row = $c->getNext()) {
            $day = $row[$datepos];
            if($startdate  > $day || $startdate == ''){
               $startdate = $day;
            }
            if($enddate <$day || $enddate == ''){
               $enddate = $day;
            }
            $rows[] = $row;
        }
    }

        if ($sheader == 'group1header') {
            foreach($incomeStat as $k=>$v){
                $rows[] = array('xm'=>$k,'sum'=>$v,'ratio'=>round($v*100/$incomeAll,3),'_forsum'=>'sum');
            }
            foreach($costStat as $k=>$v){
                $rows[] = array('xm'=>$k,'sum'=>$v,'ratio'=>round($v*100/$costAll,3),'_forsum'=>'sum');
            }

        }
                $rows[] = array('xm'=>'总开销','sum'=>$costAll,'ratio'=>100);
                $rows[] = array('xm'=>'总收入','sum'=>$incomeAll,'ratio'=>100);


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
        ,'startdate'=>$startdate
        ,'enddate'=>$enddate
        ,'filters'=>$filters
        ,'filterstr'=>$filterstr
    ));
    return;
}

$subConf = array();

$colModel = &$jqconf['colModel'];
$datepos = -1;

$colModel = App::getColModel($coll,$sheader,$datepos);

$jqconf['multiSort'] = $multiSort;

$groupfs = $this->getParam('groups','');
$gps = array();
if($groupfs !== ''){
    $jqconf['grouping'] = true;
    $gv = &$jqconf['groupingView'];
    $gps = $gv['groupField'] = explode(',',$groupfs);
    foreach($gps as $g){
        $gv['groupSummary'][] = true;
        $gv['groupSummaryPos'][] = 'footer';
        $gv['groupColumnShow'][] = true;
        $gv['groupOrder'][] = 'desc';
    }
    //$multiSort = $jqconf['multiSort'] = false;
    $jqconf['rowNum'] = 2000;//太多了浏览器处理不来
    $jqconf['loadonce'] = 'true';
    $jqconf['groupDataSorted'] = true;

}

if ($sheader === 'group1header') {
    $jqconf['loadonce'] = 'true';
    $jqconf['rowNum'] = 50000;
}


$jqconf['psidx'] = $psidx;
if(!$psidx && !$groupfs){
    $sortname = $this->getParam('sortname','0');
    $jqconf['sortname'] = $sortname;
    $jqconf['sortorder'] = $sord;
}

//$jqconf['subGrid'] = true;
$param = $_REQUEST;
$param['__nl'] = 1;
$param['doq'] = '1';
$param['multiSort'] = $multiSort;
$jqconf['url'] = url($param);


echo "<a href='".$jqconf['url']."&bz=1'>原始数据</a><br/>";
//if($sheader == 'header')
 include __DIR__.'/part_group.php';

include  __DIR__.'/part_grid.php';


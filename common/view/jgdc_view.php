<?php
$dconf = &App::getDataconf($coll);
$doq = $this->getParam('doq');
$multiSort = $this->getParam('multiSort',false);
$sheader = $this->getParam('header','header');
//返回表数据
if($doq){
    $zqrs = DbConfig::getParam('zqrs',PSPACE);
    $sshq = DbConfig::getParam('sshq',PSPACE);
    $i = 0;
    foreach($zqrs as $k=>$row){
        if($row[8] == 0 && $row[6] == 0){
            continue;//清算额为0
        }
        $row['_id'] = $k;
        $row['_forsum'] = 'sum';
        $price = $sshq[$k];
        if($price < 1){
            $price = $row[4];
        }else{
            $row[4] = $price;
            $row['pdate'] = 'new';
        }
        $zxsz = $row['zxsz'] = $row[6] * $price;
        $i += 1;
        $fdyk = $row['fdyk'] = $row[8] + $zxsz;
        if($zxsz >  0 ){
            $row['ykbl'] = round($fdyk*100/$zxsz,3);
        }


        $rows[]= $row;
        $numarr[] = $row[6];

        $userData['zxsz'] +=  $zxsz;
        $userData['8'] +=  $row['8'];
        $userData['fdyk'] +=  $row['fdyk'];
    }

    array_multisort($numarr,SORT_DESC,SORT_NUMERIC,$rows);
    $str = '总计<br>';
    $dconf = App::getDataconf('jgdc');
    $header = $dconf['header'];
    foreach($userData as $k=>$v){
        $str.= $header[$k].':'.$v.',';
    }
    $userData['2'] = $str;
    $records = $i;
    echo json_encode(array(
        'rows'=>$rows
        ,'page'=> $page
        ,'records'=>$records
        ,'total'=>$total
        ,'userdata'=>$userData
        ,'req'=>$_REQUEST
        ,'sidx'=>$sidx
        ,'sort'=>(object)$sort
        ,'cond'=>(object)$cond
        ,'filters'=>$filters
        ,'filterstr'=>$filterstr
    ));
    return;
}

$jqconf['colModel'] = App::getColModel($coll,$sheader,-1,-1);
$colModel = &$jqconf['colModel'];
$jqconf['loadonce'] = true;
$jqconf['rowNum'] = 50000;//
$jqconf['grouping'] = true;

$param = $_REQUEST;
$param['__nl'] = 1;
$param['doq'] = '1';
$param['multiSort'] = false;

$jqconf['url'] = url($param);


$subConf['loadonce'] = true;
$subConf['me_edit'] = false;
$jqconf['subGrid'] = true;
$subConf['subGrid'] = true;
$jqconf['footerrow'] = true;


$subConf['colModel'] = App::getColModel('calcc','jgdheader' ,-1,9);


$param['coll'] = 'calcc';
$subConf['urlp'] = url($param).'&prid=';


//*
$colModel[] = array('name'=>'_forsum','label'=>'求和','width'=>20);
$colModel[] = array('name'=>'ssjg','label'=>'实时价格','width'=>70,'sorttype'=>'number');
$colModel[] = array('name'=>'sszf','label'=>'今日涨幅%','width'=>70,'sorttype'=>'number');
$colModel[] = array('name'=>'ssyk','label'=>'实时总盈亏','width'=>70,'sorttype'=>'number');
$colModel[] = array('name'=>'jryk','label'=>'今日盈亏','width'=>70,'sorttype'=>'number');
 //*/
$jqconf['sshqColModel'] = App::getColModel('sshq','header',-1,-1);
include __DIR__.'/part_refresh.php';

include __DIR__.'/part_group.php';
include  __DIR__.'/part_grid.php';


<?php
$vfile = $this->viewRoot."/{$coll}_view.php";
if(file_exists($vfile)){
    include($vfile);
    return;
}

$dconf = &App::getDataconf($coll);
$doq = $this->getParam('doq');
//持仓处理
$chich = $this->getParam('chich');


$psidx = $this->getParam('psidx');//this param is overwrite by get data,but initGrid not suupport multiSort 
$sord = $this->getParam('sord','desc');
$multiSort = $this->getParam('multiSort',true);
$sheader = $this->getParam('header','header');

//返回表数据
if($doq){
        static::processGridAjaxParams($sort,$cond,$limit,$skip,$filterstr ,$sidx);
$mon = new PL_Db_Mongo(DbConfig::getMongodb($coll)); 

if($chich){
    $unidf = $this->getParam('unidf');
    $numf = $this->getParam('numf');
    if($unidf !== null){
        $skip = 0;
        $limit = 100000;//全部
    }else
        $chich = false;
}


if($coll == 'zjgf'){
    $cond['istotal'] = 0;
    if($sheader == 'theader'){
        $cond['istotal'] = 1;
        //$limit = 50000;
    }
    $prid = $this->getParam('prid');
    if($prid)
        $cond['istotal'] = 0;
    
}

///*
$c = $mon->findByIndex($coll,(object)$cond,$limit,$skip,array(),(object)$sort,true);

$cmodel  = $dconf['colModel'];
$rowidx = -1;

//echo "<pre>";
while($row = $c->getNext()){
    if($chich){
        $unid = $row[$unidf];
        $rowid = $row['_id'];

        $aidx = $appeared[$unid];

        if(!$aidx){
            if( $numf !== null){
                if($row[$numf] < 1){
                    $appeared[$unid]  = -1 ;
                    continue;
                }
            }
            $rowidx += 1;
            $rows[] = $row;
            $hrow = &$rows[$rowidx];

            $appeared[$unid]  = 1 + $rowidx;

            $hrow[0] = $unid;
            $hrow[3] = '总计：';
            $hrow['subg'][$rowid] = $row;
        }elseif($aidx > 0){
            
            $idx = $aidx - 1;
            $rows[$idx]['subg'][$rowid] = $row;
            foreach($cmodel as $k=>$v){
                if($v['summaryType'] == 'sum'){
                    $rows[$idx][$k] += $row[$k];
                }
            }
             
        }
    }else if($coll == 'zjgf' && $sheader == 'theader'){
        $rows[] = $row;
    }else{
        $rows[] = $row;
    }
}
$records = $c->count();
$total = ceil($records/$limit);
//*/

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
return;
}

$subConf = array();

$colModel = &$jqconf['colModel'];
if($coll == 'jgd' || $coll == 'dzd')
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
    $jqconf['rowNum'] = 50000;//
    $jqconf['loadonce'] = 'true';
    $jqconf['groupDataSorted'] = true;

}

if($chich){
    $jqconf['rowNum'] = 50000;//
    $jqconf['loadonce'] = 'true';
    $jqconf['subGrid'] = true;
    $jqconf['chich'] = 1;
    $multiSort = $jqconf['multiSort'] = false;
    //add hidden row
    $colModel[] = array('name'=>'subg','hidden'=>true);
    //$subConf['colModel'] = $colModel;
    $subConf['datatype'] = 'local';
    $subConf['me_edit'] = false;
}

$jqconf['psidx'] = $psidx;
if(!$psidx && !$groupfs){
    $sortname = $this->getParam('sortname','0');
    $jqconf['sortname'] = $sortname;
    $jqconf['sortorder'] = $sord;
}

$bz = $this->getParam('bz');
if($bz){//可编辑
}
$jqconf['subGrid'] = true;
$param = $_REQUEST;
$param['__nl'] = 1;
$param['doq'] = '1';
$param['multiSort'] = $multiSort;
$jqconf['url'] = url($param);

if($sheader == 'theader'){
    $subConf['colModel'] = App::getColModel($coll,'header');
    $subConf['urlp'] = url($param).'&prid=';
    $subConf['me_edit'] = false;
    $subConf['loadonce'] = true;
}
if(!$bz){
?>
<style>
  #selectable .ui-selecting { background: #FECA40; }
  #selectable .ui-selected { background: #F39814; color: white; }
  #selectable { list-style-type: none; margin: 0; padding: 0; width: 90%; }
  #selectable li { margin: 3px; padding: 1px; float: left; text-align: center; }
  </style>
<div class ='group-selector'>
 <ol id="selectable">
<?php 
    $groups = $dconf['groups'];
    $group = -1;
    foreach($groups as $k=>$v){
        $sel = 'ui-state-default';
        foreach($gps as $kk=>$vv){
            //$bbb = $vv == $k;//bug php '0' == 'date' is true
            //echo " {$bbb} [$k] [$vv]\n";#print_r($gps);
            if($vv === $k){
                $sel = 'ui-selected';
            }
        }
      echo "<li ddvalue='$k' class='$sel'>$v</li>\n";
    }
?>
      <li ddvalue='gempty' class='ui-state-default'>清除Group</li>
</ol>
</div>
<div><button title='按住ctrl 选择多个,聚合是在浏览器做的，按回车搜索'   id="chngroup">Group By:</button></div>
<hr/>
<?php 
}?>
<?php 
include  __DIR__.'/part_grid.php';


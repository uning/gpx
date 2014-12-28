<?php
$dconf = &$show_config[$coll];
$doq = $this->getParam('doq');
//持仓处理
$chich = $this->getParam('chich');


$psidx = $this->getParam('psidx');//this param is overwrite by get data,but initGrid not suupport multiSort 
$sord = $this->getParam('sord','desc');
$multiSort = $this->getParam('multiSort',true);
//返回表数据
if($doq){
$sort = array();
//prcoess sort as string "0 dec,1 asc"
$sidx = $this->getParam('sidx');
if(!$sidx && $sidx !== '0'){
    if($psidx)
        $sidx = $psidx;
}else{
    $sidx .=' '.$sord;
}
preg_match_all('/[\s]*([\w]+)[\s]+([\w]+)[\s]*/',$sidx,$mout);
foreach((array)$mout[1] as $k=>$v){
    if($v == 'asc' || $v == 'desc')
        continue;
    if($mout[2][$k] == 'asc'){
        $sort[$v] = 1;
    }else
        $sort[$v] = -1;
} 


$cond = array();
$mon = new PL_Db_Mongo(DbConfig::getMongodb($coll)); 


$limit = $this->getParam('rows',20);
$page = $this->getParam('page',0);



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
if($chich){
    $unidf = $this->getParam('unidf');
    $numf = $this->getParam('numf');
    if($unidf !== null){
        $skip = 0;
        $limit = 100000;//全部
    }else
        $chich = false;
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

            $hrow[0] .= $unid;
            $hrow[1] = '总计：';
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
    $multiSort = $jqconf['multiSort'] = false;
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


$colMap = $dconf['colModel'];
$colModel = array();
foreach($colMap as $k=>$v){
    $colModel[] = $v;
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
        if(in_array($k,$gps)){
          $sel = 'ui-selected';
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


          <table class='jqgrid' id='grid_<?php echo $coll?>'></table>
          <div id='pager_<?php echo $coll?>'></div>


<script>

<?php 

 echo 'var cjqconf = '.json_encode($jqconf,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE).';'."\n";
 echo 'var colModel = '.json_encode($colModel,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE).';'."\n";
 echo  "var COLL = '$coll';\n";
 
    include __DIR__.'/view.js';
?>
</script>


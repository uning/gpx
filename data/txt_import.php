<?php
include __DIR__.'/../base.php';
$dataconf  = include './dataconf.php';
//$mc = DbConfig::getMongodb($coll);

foreach (glob(__DIR__."/raw/newq/*.txt") as $afn) {
    echo "$afn  \n";
    $fn = basename($afn,'.txt');
    $finfo = explode('_',$fn);
    $coll = $finfo[0];
    $collconf = $dataconf[$coll];
    if(!$finfo[1] || !$collconf){
        echo " ignore [name not get coll] $fn \n";
        continue;
    }
    $lines = file($afn);

    $idfs = $collconf['idfs'];
    $fsid = array_flip($idfs);
    $header = $collconf['header'];
    $colcnt = count($header);
    $headerfind = false;
    $mc = DbConfig::getMongodb($coll);

    //这种格式zhenj8 郁闷
    foreach($lines as $linenu=>$l){
        $row= preg_split ('/[ ]{8}/',trim($l));
        $nc = 0;
        //去除多余空格,生成id
        $vstr = '';
        foreach($row as $k=>&$cellv){
            $cellv = trim($cellv);
            $nc += 1;
            if(isset($fsid[$k])){
                $vstr.=$cellv;
            }
        }
        if($nc != $colcnt){
            echo "$fn nc $nc not match colcnt $colcnt: $l";
            if($nc >3){
                //for new header
                echo "$fn header \n";
                var_export($row);
            }
            break; //or continue
        }
        if(!$headerfind){
            echo "header  $fn ";
            var_export($row);
            $headerinvalid = false;
            foreach($header as $k=>$v){
                if($v != $row[$k]){
                    echo "$fn header not match ignore $k=>$v {$row[$k]} \n";
                    var_export($row);
                    $headerinvalid = true;
                    break;
                }
            }
            if($headerinvalid){
                break;
            }
            $headerfind = true;
        }else{
            $id = $row['_id'] = md5($vstr);
            $mc->findAndModify(array('_id'=>$id),array('$set'=>$row),array(),array('upsert'=>true));
        }
    }
    echo " $fn processed\n";
    return;
}



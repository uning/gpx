<?php
include __DIR__.'/../base.php';
$dataconf  = include './dataconf.php';

$files = glob(__DIR__."/raw/newq/*.csv");



foreach($files as $afn){
    echo "$afn  \n";
    $fn = basename($afn,'.txt');
    $finfo = explode('_',$fn);
    $coll = $finfo[0];

    $collconf  = &$dataconf[$coll];
    if(!$finfo[1] || !$collconf){
        echo "$fn ignore [name not get coll] \n";
        continue;
    }

    $mc = DbConfig::getMongodb($coll);
    $fd =  fopen($afn,"r");
    if(!$fd){
        echo "import $coll -- {$collconf['name']} failed!!!! -- ";
        echo "file $afn not open\n";
        continue;
    }


    $idfs = $collconf['idfs'];
    $header = $collconf['header'];
    $colcnt = count($header);
    $headerfind = false;
    while( $row = fgetcsv($fd)){
        $vstr = '';
        foreach( $idfs as $v){
            $vstr .=$row[$v];
        }
        if(!$headerfind){
            $headerinvalid = 0;
            ///允许一个不同，因为该死的头部两字节不好去除
            foreach($header as $k=>$v){
                if($v != $row[$k]){
                    echo "$fn header checknot  $k=>$v {$row[$k]} \n";
                    $headerinvalid +=1;
                    if($headerinvalid > 1)
                        break;
                }
            }
            if($headerinvalid > 1){
               echo "$fn header notmatch  \n";
                var_export($row);
                break;
            }
            $headerfind = true;
            echo "$fn match\n";
        }else{
            $id = $row['_id'] = md5($vstr);
            App::normalTodb($row,$collconf['numfields']);
            $mc->findAndModify(array('_id'=>$id),array('$set'=>$row),array(),array('upsert'=>true));
            if($i%100 == 1){
                echo "$fn import $i records \n";
            }
            $i += 1;
        }
    }
    fclose($fd);
    echo "$fn import $coll -- {$collconf['name']} ok\n";
}



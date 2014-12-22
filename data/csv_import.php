<?php
include __DIR__.'/../base.php';
$dataconf  = include './dataconf.php';
$coll = $argv[1];
$collconf = $dataconf[$coll];
if((!$coll || !$collconf ) && 'all' != $coll){
    echo <<<USAGE
    导入文件数据到数据库，必须utf8编码的csv文件,导入会根据表字段去重
    php {$argv[0]}  all|collname
    
       all   -- 表全部导入
       cjjl  -- 成交记录表导入

USAGE;
    die();
}
if('all' == $coll ){
    $colls = array_keys($dataconf);
}else{
$colls = array($coll);
}

$i = 0;
foreach($colls as $coll){
    $collconf  = &$dataconf[$coll];
    $mc = DbConfig::getMongodb($coll);
    $dfilename = __DIR__."/raw/$coll.csv";
    if(!file_exists($dfilename)){
        echo "import $coll -- {$collconf['name']} failed!!!! -- ";
        echo "file $dfilename not exists\n";
        continue;
    }
    $fd =  fopen($dfilename,"r");
    if(!$fd){
        echo "import $coll -- {$collconf['name']} failed!!!! -- ";
        echo "file $dfilename not open\n";
        continue;
    }


    $idfs = $collconf['idfs'];
    while( $row = fgetcsv($fd)){
        $vstr = '';
        foreach( $idfs as $v){
            $vstr .=$row[$v];
        }
        $id = $row['_id'] = md5($vstr);
        //if($mc->findOne(array('_id'=>$id))
        $mc->findAndModify(array('_id'=>$id),array('$set'=>$row),array(),array('upsert'=>true));

        if($i%100 == 1){
            echo "import $i records \n";
        }
        $i += 1;
    }
    fclose($fd);
    echo "import $coll -- {$collconf['name']} ok\n";
}



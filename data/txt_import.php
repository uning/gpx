<?php
include __DIR__.'/../base.php';
$dataconf  = include './dataconf.php';
//$mc = DbConfig::getMongodb($coll);

//print_r($files);
$files = array(
    'raw/newq/jgd_20130921.txt'
);
$files = glob(__DIR__."/raw/newq/*.txt");
/**
 *  处理空格分隔的数据
 *
 */
function line2arr($l,&$ffcnt,$fcnt){
    $mlen = mb_strwidth($l);
    if($fieldLen == 0 && $fcnt > 0 ){
        $flen = round(($mlen / $fcnt));
    }
    $csnum = 0  ;//continue space num
    $fsp  = -1;  //field start p
    $ffcnt = 0; //find field num;
    $gapnum = 5; //分隔空格长度
    $row = array();
    for($i = 0 ; $i < $mlen ; $i += 1){
        $c = mb_substr($l,$i,1,'UTF-8');
        if($c == ' '){//field start
            $csp += 1;
            if($fsp > -1){
                if($i > $fsp && $csp >3){
                    $cell = mb_substr($l,$fsp,$i - $fsp);
                    $row[] = $cell;
                    $fsp = -1;
                    $ffcnt += 1;
                }
            }else{
                //处理连续空白字段情况
                if($csp >$flen ){
                    $row[] = '';
                    $ffcnt += 1;
                    $csp -= $flen;
                }
            }
        }elseif($c == "\n" || $c == "\r" ){
            if($i > $fsp && $fsp > -1){
                $cell = mb_substr($l,$fsp,$i - $fsp);
                $row[] = $cell;
                $ffcnt += 1;
            }
            $fsp = -1;
            break;
        }else{
            if($fsp == -1) $fsp = $i;
            $csp = 0;
        }
    }
    if($fsp > -1){
        $cell = mb_substr($l,$fsp,$i - $fsp);
        $row[] = $cell;
        $fsp = -1;
        $ffcnt += 1;
    }
    //处理连续空白字段情况
    if($csp >$flen && $ffcnt < $fcnt ){
        $row[] = '';
        $ffcnt += 1;
        $csp -= $flen;
    }
    return $row;
}

foreach($files as $afn) {
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
        //$row= preg_split ('/[ ]{8}/',trim($l));
        $row = line2arr($l,$nc,$colcnt);
        $vstr = '';
        foreach($idfs as $k){
            $vstr.=$row[$k];
        }
        if($nc != $colcnt){
            if($nc >3){
                //for new header
                if(!$headerfind){
                    echo "$fn header notconfig \n";
                    var_export($row);
                }else{
                    echo "$fn [line $linenu] nc $nc not match colcnt $colcnt: $l";
                    var_export($row);
                }
                break;
            }
            continue;
        }

        if(!$headerfind){
            #echo "header  $fn";var_export($row);
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
   // return;
}



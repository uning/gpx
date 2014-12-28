<?php
/**
 * 直接粘贴的excel内容
 */
function clipbordExcelImport($content){
    global $coll;
    $dataconf = include(ROOT.'/data/dataconf.php');

    $headerfind = false;
    $lines = explode("\n",$content);
    $i = 0 ;
    $findi = 0;
    foreach($lines as $l){
        $row = explode("\t",$l);
        $fnum = count($row);
        
        if($fnum < 8){
            $counterror = " collnum < 8 ";
            continue;
        }
        $lastcell = &$row[$fnum-1];
        $lastcell = trim($lastcell);

        if(!$headerfind){
            foreach($dataconf as $c=>$collconf){
                $header = $collconf['header'];
                $headerinvalid = 0;
                ///允许一个不同，因为该死的头部两字节不好去除
                foreach($header as $k=>$v){
                    if($v != $row[$k]){
                        $error .=  "$fn $c header checknot  $k=>[$v] [{$row[$k]}] \n";
                        $headerinvalid +=1;
                        if($headerinvalid > 1)
                            break;
                    }
                }
                if($headerinvalid > 1){
                    $error .= "$fn header notmatch $c \n";
                    continue;
                }else{
                    $headerfind = true;
                    $error .= "$fn match $c \n";
                    $rowcnt = count($header);
                    $idfs = $collconf['idfs'];
                    $coll = $c;
                    $mc = DbConfig::getMongodb($coll);
                    break;
                }
            }
        }else{
            if($fnum < $rowcnt)
                continue;
            $vstr = '';
            foreach( $idfs as $v){
                $vstr .=$row[$v];
            }
            if(!$vstr ){
                continue;
            }
            $id = $row['_id'] = md5($vstr);
            $cond =  array('_id'=>$id);
            if($mc->findOne($cond)){//
                $findi += 1;
            }else{
                $i += 1;
            }
            $mc->findAndModify($cond,array('$set'=>$row),array(),array('upsert'=>true));
        }
    }
    $error .=  "\n $counterror improt [$i] new records, [$findi] old records [$coll]\n";
    return $error;
}


//echo clipbordExcelImport($coll,$collconf,$content);

$content = $this->getParam('content');
if($content){
    $error = clipbordExcelImport($content);
    $datestr = @date('YmdHis');
    file_put_contents(ROOT."/data/raw/httppost/{$coll}_{$datestr}.txt",$content);
}
?>
    <div>从excel copy后粘贴到文本框</div>
    <hr/>
<pre>
<?php echo $error;?>
</pre>
    <form method='POST' action='?action=import'>
    <input type=hidden  name='coll' value="<?php echo $coll?>"/>
    <input type="submit" value="提交"/>
    <br/>
    <textarea name='content' cols='100' rows='30'><?php echo $content;?></textarea>
</form>

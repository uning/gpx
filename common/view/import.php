<?php
/**
 * 直接粘贴的excel内容
 */

function clipbordExcelImport($content,&$coll,&$myerrorno){
    $dataconf = &App::getDataconf();

    $headerfind = false;
    $lines = explode("\n",$content);
    $i = 0 ;
    $findi = 0;
    foreach($lines as $ln => $l){
        $row = explode("\t",$l);
        $fnum = count($row);
        
        $lastcell = &$row[$fnum-1];
        $lastcell = trim($lastcell);

        if($ln == 0){
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
            if(!$headerfind){
                $myerrorno = 'noheader';
                return $error;
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
            App::normalTodb($row,$collconf['numfields']);
            $mc->findAndModify($cond,array('$set'=>$row),array(),array('upsert'=>true));
        }
    }
    $error .=  "\n $counterror improt [$i] new records, [$findi] old records [$coll]\n";
    return $error;
}


function clipbordExcelImportTotal($content,&$coll,&$myerrorno){
    $coll = 'zjgf';
    $dataconf = &App::getDataconf();
    $collconf = $dataconf['zjgf'];
    $lines = explode("\n",$content);
    $mc = DbConfig::getMongodb('zjgf');
    $datestr = date('Ymd');
    foreach($lines as $k=>$l){
        $row = explode("\t",trim($l));
        $fnum = count($row);
        if($k == 0){
            foreach($collconf['theader'] as $kk=>$v){
                if($v != $row[$kk]){
                    $myerrorno = 'noheader';
                    return $error = "theader not match $kk [$v] [{$row[$kk]}]";
                }
            }
        }elseif(1 == $k){
            $id = $datestr.'_total';
            $row['date'] = $datestr;
            $row['istotal'] = 1;
            $cond['_id'] = $id;
            $rid = md5($datestr.$row[0].$row[1].$row[2].$row[3].$row[4].$row[5]);

            $old = $mc->findOne($cond);
            if($old){//合并
                if($old['rids'][$rid] == 1){
                    return $error.="$id imported";
                }
                for($i = 1;$i<6;$i++){
                    $row[$i] += $old[$i];
                }
            }
            $row['rids'] = $old['rids'];
            $row['rids'][$rid] = 1;
            $mc->findAndModify($cond,array('$set'=>$row),array(),array('upsert'=>true));
            
        }elseif(3 == $k){
            if($row[5] != '参考保本价'){// rr
                $isrr = 1;
                $coll = 'zjgf_rr';
                $error .="isrr 1\n";
            }
            if($isrr){
                foreach($collconf['rr'] as $mk=>$v){
                   // echo "$mk $v\n";
                    $nrow[$mk] = $row[$v];
                }
                $row = $nrow;
            }
            foreach($collconf['header'] as $kk=>$v){
                $nk = $kk;
                if($isrr){
                    $nk = $collconf['rr'][$kk]; 
                }
                if($nk === '')
                    continue;
                if($v != $row[$kk]){
                    if($kk < 10){
                        $myerrorno = 'noheader';
                        $error .="header not compact kk[$kk] nk[$nk] [$v] [{$row[$kk]}]\n";
                        return $error;
                    }
                }
            }
        }else{
            if($fnum < 8)
                continue;
            if($isrr){
                foreach($collconf['rr'] as $mk=>$v){
                    $nrow[$mk] = $row[$v];
                }
                $row = $nrow;
            }

            $id = $datestr.'_'.$row[12];//股东代码
            $rid = md5($row[0].$row[1].$row[3].$row[4].$row[7].$row[10]);
            $row['date'] = $datestr;
            $row['istotal'] = 0;
            $cond['_id'] = $id;
            $old = $mc->findOne($cond);
            if($old){
                $findi += 1;
            }
            if($old && $old['rids'][$rid] == 1){//合并
                continue;
            }
            if($old){
                $mergei += 1;
                $totalcb  = ($row[4]*$row[2] + $old[4]*$old[2]);

                $row[1]+=$old[1];
                $row[2]+=$old[2];//库存数量
                $row[3]+=$old[3];
                $row[7]+=$old[7];
                $row[8]+=$old[8];
                $row[10]+=$old[10];
                $row[11]+=$old[11];
                $row['chengben'] = $totalcb;
                $row[4] = number_format($totalcb/$row[2],3);//成本价
                $row[5] = number_format($row[4]*1.002,3);
                $row[9] = number_format($row[8]*100/$totalcb,3);//算盈亏比例
            }else
                $newi += 1;
            $row['rids'] = $old['rids'];
            $row['rids'][$rid] = 1;
            App::normalTodb($row,$collconf['numfields']);
            $mc->findAndModify($cond,array('$set'=>$row),array(),array('upsert'=>true));
        }
    }
    $error .=  "\n $counterror improt [$newi] new records, [$findi] old records ,[$mergei] merge $coll\n";
    return  $error;
}

//echo clipbordExcelImport($coll,$collconf,$content);

$content = $this->getParam('content');
if($content){
    $error = clipbordExcelImportTotal($content,$coll,$myerrorno);
    if($myerrorno == 'noheader'){
        $coll = 'unknown';
        $error = clipbordExcelImport($content,$coll,$myerrorno);
    }
    $datestr = @date('YmdHis');
    file_put_contents(ROOT."/data/raw/httppost/{$coll}_{$datestr}.txt",$content);
}
?>
    <div>从excel copy后粘贴到文本框</div>
    <hr/>
<pre>
<?php echo "[$myerrorno]\n$error";?>
</pre>
    <form method='POST'>
    <input type=hidden  name='coll' value="<?php echo $coll?>"/>
    <input type="submit" value="提交"/>
    <br/>
    <textarea name='content' cols='100' rows='30'><?php echo $content;?></textarea>
</form>

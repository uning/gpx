<?php
/**
 * 直接粘贴的excel内容
 */

function clipbordExcelImportTotal($content){
    global $coll;
    $dataconf = &App::getDataconf();
    $dconf = $dataconf['zjgf'];
    $lines = explode("\n",$content);
    $mc = DbConfig::getMongodb('zjgf');
    $datestr = date('Ymd');
    foreach($lines as $k=>$l){
        $row = explode("\t",trim($l));
        $fnum = count($row);
        if($fnum < 3){
            echo $l;
            continue;
        }
        if($k == 0){
            foreach($dconf['sheader'] as $kk=>$v){
                if($v != $row[$kk]){
                    return $error = "sheader not compact $kk [$v] [{$row[$kk]}]";
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
                echo $error .="isrr 1\n";
            }
            if($isrr){
                foreach($dconf['rr'] as $mk=>$v){
                   // echo "$mk $v\n";
                    $nrow[$mk] = $row[$v];
                }
                $row = $nrow;
            }
            foreach($dconf['header'] as $kk=>$v){
                $nk = $kk;
                if($isrr){
                    $nk = $dconf['rr'][$kk]; 
                }
                if($nk === '')
                    continue;
                if($v != $row[$kk]){
                    if($kk < 10){
                        $error .="header not compact kk[$kk] nk[$nk] [$v] [{$row[$kk]}]\n";
                        return $error;
                    }
                }
            }
        }else{
            if($fnum < 8)
                continue;
            if($isrr){
                foreach($dconf['rr'] as $mk=>$v){
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
                $row[1]+=$old[1];
                $row[2]+=$old[2];
                $row[3]+=$old[3];
                $row[7]+=$old[7];
                $row[8]+=$old[8];
                $row[10]+=$old[10];
                $row[11]+=$old[11];
                $row[4] = ($row[4]*$row[2] + $old[4]*$old[2])/($old[2] + $row[2]);
                $row[5] = ($row[5]*$row[2] + $old[5]*$old[2])/($old[2] + $row[2]);
                $row[9] = number_format($row[8]*100/$row[4],3);//算盈亏比例
            }else
                $newi += 1;
            $row['rids'] = $old['rids'];
            $row['rids'][$rid] = 1;
            $mc->findAndModify($cond,array('$set'=>$row),array(),array('upsert'=>true));
        }
    }
    $error .=  "\n $counterror improt [$newi] new records, [$findi] old records ,[$mergei] merge $coll\n";
    return  $error;

}


$content = $this->getParam('content');
if($content){
    $error = clipbordExcelImportTotal($content);
    $datestr = @date('YmdHis');
    file_put_contents(ROOT."/data/raw/httppost/total_{$datestr}.txt",$content);
}
?>
    <div>从excel copy后粘贴到文本框</div>
    <hr/>
<pre>
<?php echo $error;?>
</pre>
    <form method='POST'>
    <input type=hidden  name='coll' value="zjgf"/>
    <input type="submit" value="提交"/>
    <br/>
    <textarea name='content' cols='100' rows='30'><?php echo $content;?></textarea>
</form>

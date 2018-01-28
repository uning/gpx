<?php
/**
 * Tool.php
 *
 * Developed by tingkun <z@tingkun.com>
 * Copyright (c) 2016 .
 *
 * Changelog:
 * Fri, 12 Aug 2016 - created
 *
 */

class Crawler_Tool{
    static public function postImportHx(&$content,&$coll,&$error){
        $lines = explode("\n",$content);
        if($coll == 'zjgf')
            return self::ceImportHxZjgf($lines,$error);
        $myerr=self::ceImportHxZjgf($lines,$error);
        $coll = 'zjgf';
        if($error == 'noheader'){
            $coll = 'unknown';
            return self::ceImportHxOther($lines,$coll,$error);
        }
        return $myerr;
    }
    /**
     * 导入hx资金股份
     * 对资金账户和信用账户数据做合并
     */
    static public function ceImportHxZjgf(&$lines,&$myerrorno){
        $coll = 'zjgf';
        $dataconf = &App::getDataconf();
        $collconf = $dataconf['zjgf'];
        $mc = DbConfig::getMongodb('zjgf');
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
                //最后处理

                $ttrow = $row;
            }elseif(3 == $k){
                foreach($collconf['header'] as $kk=>$v){
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

                if(!$datestr)
                    $datestr = $row[0];

                $id = $row[0].'_'.$row[12];//证券代码
                $rid = md5($row[5].$row[1].$row[3].$row[8].$row[7].$row[9]);
                $row['date'] = $row[0];
                $row['istotal'] = 0;
                $cond['_id'] = $id;
                $old = $mc->findOne($cond);
                if($old){
                    $findi += 1;
                }
                if($old && $old['rids'][$rid] == 1){//合并
                    $oldi += 1;
                    continue;
                }
                if($old){
                    $mergei += 1;
                    $totalcb  = ($row[5]*$row[2] + $old[5]*$old[2]);

                    $row[2]+=$old[2];//库存数量
                    $row[3]+=$old[3];
                    $row[4]+=$old[4];
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
        //处理汇总
        $row = $ttrow;
        $id = $datestr.'_total';
        $row['date'] = $datestr;
        $row['istotal'] = 1;
        $cond['_id'] = $id;
        $rid = md5($datestr.$row[0].$row[1].$row[2].$row[3].$row[4].$row[5]);

        $old = $mc->findOne($cond);
        if($old){//合并
            if($old['rids'][$rid] == 1){
                $notsave = true;
                $error.="total $id imported";
            }
            for($i = 1;$i<6;$i++){
                $row[$i] += $old[$i];
            }
        }
        if(!$notsave){
            $row['rids'] = $old['rids'];
            $row['rids'][$rid] = 1;
            $mc->findAndModify($cond,array('$set'=>$row),array(),array('upsert'=>true));
        }
        $error .=  "\n $counterror improt [$newi] new records, [$findi]  old records[$oldi] ,[$mergei] merge $coll\n";
        return  $error;
    }

    /**
     * 华西证券导入粘贴的导出到excel的数据
     * 根据dataconf配置导入
     * ce  clipbordExcel excel copy到剪贴板的数据
     */
    static public function ceImportHxOther(&$lines,&$coll = null,&$myerrorno = null){
        $dataconf = &App::getDataconf();

        $headerfind = false;
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
                    $ni += 1;
                }
                $i += 1;
                App::normalTodb($row,$collconf['numfields']);
                $row['_fnorder'] = $i;
                $mc->findAndModify($cond,array('$set'=>$row),array(),array('upsert'=>true));
            }
        }
        $error .=  "\n $counterror improt [$ni] new records, [$findi] old records [$coll]\n";
        return $error;
    }


    /**导入五村表格 */
    static public function ceImportShimoExcel(&$lines,&$coll = null,&$myerrorno = null){
        $dataconf = DbConfig::getParam('gridconfig', 'grid');
        $headerfind = false;
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
                //哪里来的日期
                array_splice($row,$rowcnt);
                foreach($header as $k=>$v){
                    if($row[$k] == '日期')
                    $row[$k] = '';
                }
                
                if ($fnum < $rowcnt) {
                  //  continue;
                }
                $vstr = trim( $row[1].$row[2]);
                if(!$vstr ){
                    echo "ignore $l\n<br/>";
                    continue;
                }
                if($coll == 'wucunLs'){
                    if($row[0]){
                    $date = $row[0];
                    $arr = explode('.',$date);
                    $year = $arr[0];
                    $month = $arr[0].'.'.$arr[1];
                    }
                    $row[0] = $date;
                    $row['year'] = $year.'';
                    $row['month'] = $month;
                }
                $id = $row['_id'] = $date.'.'.$i;
                $cond =  array('_id'=>$id);
                if($mc->findOne($cond)){//
                    $findi += 1;
                }else{
                    $ni += 1;
                }
                $i += 1;
                App::normalTodb($row,$collconf['numfields']);
                $row['_fnorder'] = $i;
                $mc->findAndModify($cond,array('$set'=>$row),array(),array('upsert'=>true));
            }
        }
        $error .=  "\n $counterror improt [$ni] new records, [$findi] old records [$coll]\n";
        return $error;
    }

}

<?php
//todo: 持仓汇总记录计算相关的量放到展示时计算
/**
 * 保存一天的持仓记录
 * 累计计算，后续改成增量计算的，多个值计算
 *
 * 汇总头部见 dateconf[calcc]
 *
 * @param $jgrq     交割日期
 * @param $mon      mongo 连接
 * @param $totalr   整体汇总记录,每天一条
 * @param $zqrs     所有证券汇总记录,每天一条
 * @param $getXq    获取雪球收盘价
 */
$failedinfos['603999'] = true; //记录雪球失败记录
function saveDayN($jgrq,&$mon,&$totalr,&$zqrs,$getXq = false){
    global $failedinfos;
    //获取股票当天价格
    /*
    $curcc = $mon->findByIndex('zjgf',array('date'=>$jgrq),10000,0,array());
    foreach($curcc as $k=>$v){
        if($v['istotal'] == 1){
            $okyye = $v[2] + 0; //可用余额
            $otzxsz = $v[3]; //市值
        }
        $mmgu[$v[12]] = $v;
    }
    // */
    foreach($zqrs as $k=>$v){
        $ids[] = $k."_$jgrq";
    }
    //获取开盘收盘价
    $curcc = $mon->findByIndex('dayklineinfo',array('_id'=>array('$in'=>$ids)),10000,0,array());
    foreach($curcc as $k=>$v){
        $infos[$v['zqdm']] = $v;
    }
    $tzxsz = 0 ;//总的最新市值
    $tfdyk = 0; //浮动盈亏
    foreach($zqrs as $k=>$v){
        $v['istotal'] = 0;
        $zqnum = $v[6]; //证券数量
        $zxsz = 0;
        $fdyk = 0;
        $zqs = $v[8];//最终清算额度

        if($zqnum != 0 && $v['pdate'] != $jgrq ){
            $info = $infos[$k];
            if(!$info && $failedinfos[$k] !=1){
                if($getXq){
                    $info=Crawler_Xueqiu::getGupiaoDay($k,$jgrq); //not coment after all callc
                    if(!$info){
                        echo "==getInfo failed $k ".$v[3]."\n";
                        $failedinfos[$k] = 1;//不去尝试获取已经失败的
                    }
                }
                if($info){
                    $v[4] = $info['close'];
                    $v['pdate'] = $info['day'];
                }
            }
        }

        if($zqnum > 0){ //有的持仓
            $pre3zqdm = substr($k,0,3);
            if($pre3zqdm == '204' || $pre3zqdm == '131'){
                $zxsz = 0 - $v[8];//回购市值用清算额
            }else{
                $zxsz = round($zqnum * $v[4],3);
            }

            //echo $pre3zqdm." preddfd $zxsz\n";
            $v['cbj'] = round(-$zqs/$zqnum,3);
            //if($otzxsz){
            //    $v['zcbl'] = round(100*$zxsz/$otzxsz,3);
            //}
        }elseif($zqnum < 0){//融券卖出的情况
            //暂时不处理
            //$zxsz = $zqs;
        }else{
            $zxsz = 0;
            $v[6] = 0;//设置为0，为显示
        }

        $fdyk = $v['fdyk'] = round($zqs + $zxsz,3);//浮动盈亏

        $tfdyk += $fdyk;
        $tzxsz += $zxsz;

        $ljmr = $v['ljmr'];
        $v['zxsz'] = $zxsz;

        if($zxsz != 0)
            $v['ykbl'] = round($fdyk*100/$zxsz,3);
        elseif($ljmr > 0 ){//用累计投入做分母比较靠谱
            $v['ykbl'] = round($fdyk*100/$ljmr,3);
        }else{
            // $v['ykbl'] = round($fdyk*100/$ljmr,3);
        }
        //$v[8] = round($v[8],3);

        if($v['cdate']){
            $v['chtime'] = App::dateDifference($v['cdate'],$v['ldate']);
        }
        //可以只存有改变的,为方便全部保存
        if($v['ldate'] == $jgrq || $zqnum != 0){

            //存一个最新的,保持id不变，用zero,保持一致，好添加评论
            $id  = $k.'_zero';
            $v['caldate'] = $jgrq;
            $v['date'] = 'lastest';
            $mon->findAndModify(array('_id'=>$id),array('$set'=>$v));

            if($zqnum != 0 ){
                $v['date'] = $jgrq;
                $id = $k.'_'.$jgrq;
                $mon->findAndModify(array('_id'=>$id),array('$set'=>$v));
            }
        }
    }
    $pretid = "total_$jgrq";
    $tr = &$totalr;

    //$tr[8] = round($tr[8],3);
    $tr['date'] = $jgrq;


    $kyye = $tr[8];
    $tr['zxsz'] = $tzxsz;//参考市值
    $tr['rzye'] = round($tr['rzye'],3);
    $tr['rqye'] = round($tr['rqye'],3);
    $tr['kyye'] = $kyye;
    $tr['zc'] = $tr['zxsz'] + $kyye;//资产
    $touru = $tr['yinhangzr'] + $tr['yinhangzc'];
    $tr['yinhangtr'] = $touru;
    $tr['jsyk'] = $tr['zc'] - $touru - $tr['rzye'];//盈亏
    $tr['ljyk'] = $tfdyk;
    $tr['ykbl'] = round(($tr['jsyk'])*100/$touru,3); //没有处理融券余额
    $tr['cw'] = round($tr['zxsz']*100/($tr['zc'] - $tr['rzye']),3);
    $tr['_id']  = $pretid;
    $tr['istotal'] = 1;

    //$mon->save($tr);

    $info = '';
    $info .= "$jgrq\n清算[{$tr[8]}]   可用 [$kyye] okyye[$okyye]\n";
    $info .= "融资余额 [{$tr['rzye']}]  融券卖出量[{$tr['rqye']}]\n";
    $info .= "市值o[$otzxsz]   最新市值 [$tzxsz]\n";
    $info .= "盈亏计算[{$tr['jsyk']}] 累计[$tfdyk]\n";

    $tr['_info'] = $info;
    $mon->findAndModify(array('_id'=>$pretid),array('$set'=>$tr));

    $tr['date'] = 'lastest';
    unset($tr['_id']);
    $mon->findAndModify(array('_id'=>'total_lastest'),array('$set'=>$tr));
    $tr['date'] = $jgrq;

    echo "\n\n======\n$info";
}


/**
 *
 * 处理交割单,计算出每天市值状况
 *
 * @param $recalcc true--重新计算,false 不重新计算
 * @param $toDate 处理到什么日期YYYYMMDD,默认处理到当前日期
 *
 */
function processJgd($recalcc = false,$getXq = false ,$toDate=null){
    if(!$recalcc){
        $zqrs = DbConfig::getParam('zqrs',PSPACE);
        $totalr = DbConfig::getParam('totalr',PSPACE);
        $rrsyje = DbConfig::getParam('rrsyje',PSPACE);
        $presyje = DbConfig::getParam('presyje',PSPACE);
        $sdate = $totalr['date'] ;
        $cond['$and'][] = (object)array('0'=>array('$gt'=>$sdate));
    }
    //转债到股票代码转换
    $zh2gu = array(
        '110018'=>'600795',//国电电力
        '113001'=>'601988',
    );
    //申购代码到正式代码转换
    $sgdm2zqdm = require __DIR__.'/config_sgdm2zqdm.php';

    $sg2gu = array();
    $prejgrq = '';

    //证券公司记录错误
    $yefixed['20140819-245743.08-600704'] = 3;
    $yefixed['20140820-2722.48-300145'] = -3;


    $coll = 'jgd';
    $mon = new PL_Db_Mongo(DbConfig::getMongodb('jgd'));
    $sort = array('0'=>1,'_fnorder'=>1);
    $limit = 1000000;
    $skip = 0;
    $datestr = $toDate;
    if(!$datestr){
        $datestr = PL_Server::getParam('date',date('Ymd'));
    }


    $cond['$and'][] = (object)array('0'=>array('$lte'=>$datestr));
    $c = $mon->findByIndex($coll,(object)$cond,$limit,$skip,array(),(object)$sort,true);


    //
    //申购代码和正式上市代码统一转换后处理
    //
    //
    //按日期正序
    $mon = new PL_Db_Mongo(DbConfig::getMongodb('calcc'));
    while($row = $c->getNext()){
        $ywmc = $row[1];//业务名称
        $zqdm = $row[2];//证券代码
        $jgrq = $row[0];//交割日期
        //echo "$jgrq \n";continue;

        if(!$ywmc){
            echo "no ywmc $jgrq $ywmc $zqdm \n";
            continue;
        }
        if( $prejgrq != $jgrq){ //
            if($prejgrq){
                saveDayN($prejgrq,$mon,$totalr,$zqrs,$getXq);
            }
            $prejgrq = $jgrq;
        }

        //*处理申购代码
        if($zqdm[0] == '7'){
            if($sgdm2zqdm[$zqdm]){
                $zqdm = $sgdm2zqdm[$zqdm];
            }else{
                $zqdm[0] = '6';
                $i1 = $zqdm[1];
                $i2 = $zqdm[2];
                if($i1 == '3'  && $i2 == '2'){//32
                    $zqdm[1] = '0';
                    $zqdm[2] = '3';
                }elseif( ($i1 == '3' || $i1 == '4')  && $i2 == '0'){//30 40
                    $zqdm[1] = '0';
                    $zqdm[2] = '0';
                }elseif($i1 >= 8  && $i2 == '0'){//80 90
                    $zqdm[1] = '0';
                    $zqdm[2] = '1';
                }elseif($i1 == '3'  && $i2 == '4'){//34
                    $zqdm[1] = '0';
                    $zqdm[2] = '3';
                }elseif($i1 == '9'  && $i2 == '9'){//34
                }else{
                    echo "证券代码 $zqdm not_convert exit $ywmc\n";//exit(0);
                }
            }

        }
        //*/

        $zqr =  null;
        if($zqdm){//有证券代码的
            $zqr = $zqrs[$zqdm];
            if(!$zqr){
                $zqr['cdate'] = $jgrq; //最开始操作日期
            }

            $zqr[2] = $zqdm;//证券代码
            $zqr[3] = $row[3];//证券名称
            $zqr['ldate'] = $jgrq;//表明今天变化过
            if($row[4] > 0){
                $zqr[4] = $row[4];//使用最新价格
                $zqr['pdate'] = $jgrq;//使用最新价格
            }
            if($ywmc != '偿还融资负债本金'){
                //借入时没有证券代码
                $zqr[8] += $row[8]; //清算金额
                $zqr[10] += $row[10];//佣金
                $zqr[11] += $row[11];//印花税
                $zqr[12] += $row[12];//过户费
                $zqr[13] += $row[13];//结算费
                $zqr[14] += $row[14];//附加费
            }
        }


        switch($ywmc){
        case '还券划出':
            $totalr['rqye'] -= $row[5];
            break;
        case '买券还券':
            $totalr['rqye'] -= $row[5];
            $zqr[6] += $row[5];   //剩余数量
            break;
        case '融券卖出':
            $zqr[6] -= $row[5];   //剩余数量
            $totalr['rqye'] += $row[5];
            break;

        case '证券买入':
        case '融券回购':
        case '融资买入':
        case '担保品买入':
        case '红股入账':
        case '股份转入':
        case '担保物转入':
        case '新股入帐':
        case '转股入帐':
        case '新股申购'://不计算申购新股，盈亏可用不准
            $zqr[6] += $row[5];   //剩余数量
            $zqr['ljmr'] -= $row[8];//累计买入多少钱
            break;
        case '证券卖出':
        case '担保品卖出':
        case '融券购回':
        case '担保物转出':
        case '股份转出':
        case '卖券还款':
            $zqr[6] -= $row[5];   //剩余数量
            break;
        case '申购还款':
            $zqr[6] -= $row[5];   //剩余数量
            if($row[5] == 0){
                $zqr[6] = 0; //处理申购取消
            }
            break;
        case '债券转股回售转出':
            //*
            //暂时不处理这个
            $gp = $zh2gu[$zqdm];
            if(!$gp){
                die("没有配置转股代码 $zqdm");
            }
            $gpr = &$zqrs[$gp];
            $cb = $zqr[8]*$row[5]/($row[6] + $row[5]);//用清算值作为证券价值，不是转股价
            if(!$gpr){
                $gpr['cdate'] = $jgrq;//表明今天变化过
            }
            $gpr['ljmr'] -= $cb;//累计买入多少钱
            $gpr['ldate'] = $jgrq;//表明今天变化过
            $gpr[2] = $gp;
            $gpr[8] += $cb;//成本到股票记录去了
            $zqr[8] -= $cb;
            //*/
            $zqr[6] -= $row[5];   //剩余数量
            break;
        case '银行转证券':
            $totalr['yinhangzr'] += $row[8];
            break;
        case '证券转银行':
            $totalr['yinhangzc'] += $row[8];
            break;
        case '融资借入':
            $totalr['rzye'] += $row[8];
            $totalr['rongzijr'] += $row[8];
            break;
        case '偿还融资负债本金':
            $totalr['rzye'] += $row[8];
            $totalr['rongzich'] += $row[8];
            break;
        case '偿还融资利息':
            //$totalr['rongzich'] += $row[8];
            $totalr['rongzilx'] += $row[8];
            break;
        case '偿还融券费用':
            $totalr['rongquanlx'] += $row[8];
            break;

        case '证券分拆记减/基':
        case '开放基金合并减股':
        case '开放基金拆分减股':
            $zqr[6] -= $row[5];   //剩余数量
            break;
        case '证券分拆记增/基':
        case '开放基金拆分增股':
        case '开放基金合并增股':
            $zqr[6] += $row[5];
            break;
        case '基金申购拨出':
            $zqr[6] += $row[5];
            $zqr['ljmr'] -= $row[8];//累计买入多少钱
            //申购其他基金,计入市值
            if($row[4] == 0 && $row[8] < 0){
                $zqr[6] = 1;
                $zqr[4] -= $row[8]; //单价等于清算额
                //方便算市值;

            }
            break;
        case '基金赎回拨入':
            $zqr[6] += $row[5];
            //申购其他基金,计入市值
            if($row[4] == 0 && $row[8] > 0){
                $zqr[4] -= $row[8]; //单价等于清算额
                $zqr[6] = 1;
                //认为基金保有量小于10%时,亏损完,后面改成不计算其盈亏
                if($zqr[4] <= $zqr['ljmr']*0.1)
                    $zqr[6] = 0;
                //方便算市值;
            }
            break;

        case '开放基金赎回':
            $zqr[6] -= $row[5];
            break;
        case '申购中签': //新股入账处理了,不用加数量
            $zqr['ljmr'] -= $row[8];//累计买入多少钱
            break;

        case 'ETF 申购退款':
        case '债券兑息':
        case '利息归本':
        case '指定交易':
        case '红利入账':
        case '融券借入':
        case '转股零款':
        case '配售缴款':
        case '偿还融券负债':
        case '债券兑息兑付扣税':
        case '股息红利差异扣税':
            break;
        default:
            echo  "$ywmc not case_process\n";
        }

        if($zqdm){//有证券代码的
            $zqr['lastop'] = $ywmc;
            $zqrs[$zqdm] = $zqr;
        }


        $totalr[8] += $row[8];//清算金额
        $totalr[10] += $row[10];//佣金
        $totalr[11] += $row[11];//印花税
        $totalr[12] += $row[12];//过户费
        $totalr[13] += $row[13];//结算费
        $totalr[14] += $row[14];//附加费


        //验证清算余额 == 可用余额
        $qse = $totalr[8];
        $qsje = $row[8];
        $r9 = $row[9];

        $fkk = "$jgrq-$r9-$zqdm";
        $fixed += $yefixed[$fkk];
        $syje = $r9 + $rrsyje  +$fixed; //剩余金额
        $diff1 = abs(round($qse - $syje));

        $syje = $presyje + $r9 + $fixed;
        $diff2 = abs(round($qse - $syje));
        /*
           echo "$fkk [{$row['3']}][$ywmc] qse[$qse]!=syje[$syje] = presyje[$presyje] or rrsyje[$rrsyje]  + r9[$r9] diff1[$diff1] diff2[$diff2] fixed[$fixed]\n";$is_exit = true;continue;
        //*/
        if($diff1 > 1){
            if($diff2 > 1){
                if(($r9 == 0 && $row[8] == 0)||$ywmc == '指定交易'){
                    continue;
                }
                echo "!!!notequal $fkk [$ywmc] qse[$qse]!=syje[$syje] = presyje[$presyje] or rrsyje[$rrsyje]  + r9[$r9] diff1[$diff1] diff2[$diff2] fixed[$fixed]\n";
                print_r($row);
                return;
                break;
            }else{
                #echo "$jgrq [$ywmc] [$qse]==[$syje] = [$presyje]  + [$r9] rr\n";
                $rrsyje = $r9;
            }
        }else{
            $presyje = $r9;
        }


        if($is_exit){
            echo  "\n  exit\n";
            exit();
        }
    }

    echo "清算额:[$qse] ==  [$syje] = [{$row[9]}]  [$rrsyje] [$presyje]\n";
    if(!$jgrq){
        $prejgrq = $jgrq = $datestr;
        echo "没有交割记录\n";return;
    }
    if($is_exit){
        echo  "\n  exit\n";
        exit();
    }
    //*
    saveDayN($jgrq,$mon,$totalr,$zqrs,$getXq);

    $hisdata = array(
        'prejgrq'=>$prejgrq,
        'presyje'=>$presyje,
        'rrsyje'=>$rrsyje,
        'zqrs'=>$zqrs,
        'totalr'=>$totalr,
    );
    DbConfig::saveParam($hisdata,'',PSPACE);
    #echo json_encode($hisdata,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
    //   */
}

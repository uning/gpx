<?php
require_once __DIR__.'/../../base.php';
echo "<pre>";
$coll = 'jgd';
$mon = new PL_Db_Mongo(DbConfig::getMongodb($coll)); 
$sort = array('0'=>1,'_fnorder'=>1);
$limit = 1000000;
$skip = 0;
$datestr = $argv[1];
$datestr = static::getParam('date',date('Ymd'));
if(!$datestr){
    $datestr = date('Ymd');
}
$cond = array('0'=>array('$lte'=>$datestr));
$c = $mon->findByIndex($coll,(object)$cond,$limit,$skip,array(),(object)$sort,true);

/**
 * 保存一天的持仓记录
 * 累计计算，后续改成增量计算的，多个值计算
 *
 * 汇总头部见 dateconf[calcc]
 *
 */
function saveDay($prejgrq,&$mon,&$totalr,&$zqrs,$checked = true){
    //获取股票当天价格
    $curcc = $mon->findByIndex('zjgf',array('date'=>$prejgrq),10000,0,array());
    foreach($curcc as $k=>$v){
        if($v['istotal'] == 1){
             $okyye = $v[2] + 0; //可用余额
             $otzxsz = $v[3]; //市值

        }
        $mmgu[$v[12]] = $v;
    }


    $tzxsz = 0 ;
    $tfdyk = 0;
    foreach($zqrs as $k=>$v){
        $v['istotal'] = 0;
        $zqnum = $v[6]; //证券数量
        $zxsz = 0;
        $fdyk = 0;
        $zqs = $v[8];//最终清算额度
        $ov = $mmgu[$k];
        if(!$ov && $zqnum != 0){
            echo "[$k] [{$v[3]}] notfind in result\n";
            //print_r($v);
        }
        //check?
        if($ov[2] != $zqnum ){//库存数量不相等
            echo "[$k] [{$ov[0]}] [{$ov[2]}] [$zqnum] 证券数量 noteq in result\n";
            //not save this;
            if($checked){
             //   continue;
            }
        }
        if($ov){
            $v[4] = $ov[6]; //当前价
            $zxsz = $v['zxsz'] = $ov[7];//最新市值
            $v['pdate'] = $prejgrq;
        }
        if($zqnum > 0){ //有的持仓
            if($zxsz < 1){
                $zxsz = $zqnum * $v[4];
                echo "[$k] [{$ov[0]}] [{$ov[2]}] [$zqnum] 最新市值<1\n";
            }
            //
            $v['cbj'] = round(-$zqs/$zqnum,3);
            if($otzxsz){
                $v['zcbl'] = round(100*$zxsz/$otzxsz,3);
            }
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
        $v['date'] = $prejgrq;
        $id = $v['_id'] = $k.'_'.$prejgrq;
        $mon->save($v);
    }
    $pretid = "total_$prejgrq";
    $tr = &$totalr;

    //$tr[8] = round($tr[8],3);
    $tr['date'] = $prejgrq;
    $tr['zqids'] = $zqids;
    if($otzxsz)
        $tr['zxsz'] = $otzxsz;//参考市值
    else
        $tr['zxsz'] = $tzxsz;//参考市值
    if($okyye){
        $kyye = $okyye;
    }else{
        $kyye = $tr[8];
    }

    $tr['kyye'] = $kyye;
    $tr['zc'] = $tr['zxsz'] + $kyye;//资产

    $touru = $tr['yinhangzr'] + $tr['yinhangzc'];

    $tr['yinhangtr'] = $touru;

    $tr['jsyk'] = $tr['zc'] - $touru;//盈亏
    $tr['ljyk'] = $tfdyk;

    $tr['ykbl'] = round($tr['jsyk']*100/$touru,3);
    $tr['cw'] = round($tr['zxsz']*100/$tr['zc'],3);
    $tr['_id']  = $pretid;
    $tr['istotal'] = 1;

    $mon->save($tr);

    $info .= "$prejgrq\n清算 [{$tr[8]}]   可用 [$kyye] okyye[$okyye]\n";
    $info .= "市值o [$otzxsz]   累计 [$tzxsz]\n";
    $info .= "盈亏计算[{$tr['jsyk']}] 累计[$tfdyk]\n";
    echo "\n\n======\n$info";
}

$zh2gu = array(
    '110018'=>'600795',//国电电力
    '113001'=>'601988',
);
$sg2gu = array();
$totalrs = array();
$prejgrq = null;
$zqrs  = array();

//证券公司记录错误
$yefixed['20140819-245743.08-600704'] = 3;
$yefixed['20140820-2722.48-300145'] = -3;
//按日期正序
$mon = new PL_Db_Mongo(DbConfig::getMongodb('calcc')); 
while($row = $c->getNext()){
    $ywmc = $row[1];//业务名称
    $zqdm = $row[2];//证券代码
    $jgrq = $row[0];//交割日期

    if(!$ywmc){
        echo "no ywmc $jgrq $ywmc $zqdm \n";
        continue;
    }
    if( $prejgrq != $jgrq){ //
        if($prejgrq){
            //saveDay($prejgrq,$mon,$totalr,$zqrs);
        }
        $prejgrq = $jgrq;
    }

    //*处理申购代码
    if($zqdm[0] == '7'){
        $zqdm[0] = '6';
        $zqdm[1] = '0';
        $zqdm[2] = '3';
    }
     //*/

    $zqr =  null;
    if($zqdm){//有证券代码的
        $zqr = $zqrs[$zqdm];
        if(!$zqr){
            $zqr['cdate'] = $jgrq;
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
    case '证券买入':
    case '融资买入':
    case '申购中签':
    case '融券回购':
    case '担保品买入':
    case '红股入账':
    case '转股入帐':
        $zqr[6] += $row[5];   //剩余数量
        $zqr['ljmr'] -= $row[8];//累计买入多少钱
        break;
    case '证券卖出':
    case '担保品卖出':
    case '融券购回':
    case '卖券还款':
    case '融券卖出':
        $zqr[6] -= $row[5];   //剩余数量
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
        $totalr['rongzijr'] += $row[8];
        break;
    case '偿还融资负债本金':
        $totalr['rongzich'] += $row[8];
        break;
    case '偿还融资利息':
        //$totalr['rongzich'] += $row[8];
        $totalr['rongzilx'] += $row[8];
        break;
    default:
    }

    if($zqdm){//有证券代码的
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
    if($diff1 > 1){
        $syje = $presyje + $r9 + $fixed;
        $diff2 = abs(round($qse - $syje));
        if($diff2 > 1){
            if(($r9 == 0 && $row[8] == 0)||$ywmc == '指定交易'){
                continue;
            }
            echo "$fkk [$ywmc] [$qse]!=[$syje] = [$presyje] or [$rrsyje]  + [$r9] [$diff1] [$diff2] [$fixed]\n";
            print_r($row);
            break;
        }else{
            #echo "$jgrq [$ywmc] [$qse]==[$syje] = [$presyje]  + [$r9] rr\n";
            $rrsyje = $r9;
        }
    }else{
        $presyje = $r9;
        #echo "$jgrq [$ywmc] ] [$qse]==[$syje]  [$rrsyje] = [$r9]\n";
    }
}
echo "清算额:[$qse] ==  [$syje] = [{$row[9]}]  [$rrsyje] [$presyje]\n";


//*
saveDay($jgrq,$mon,$totalr,$zqrs);
echo json_encode(array(
    'totalr'=>$totalr,
    'zqrs'=>$zqrs,
    'date'=>$jgrq
),JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
//   */

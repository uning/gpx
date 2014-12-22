<?php
/**
 *
 *
 */
class DTable{
	static function buildtablehtml(&$rows,&$conf,$k,$withdate=true,$dttable = 'dt-table'){
		$cols = $conf['cols'];
		$preshow = $conf['preshow'];
        $cellnum = $conf['cellnum'];
        if($conf['cellnotnum'])
               $cellnum =false;

		$mheader = $conf['mheader'];
		$table.="<div>";// <h2>{$conf['title']}</h2>";
		$table .="<table class='$dttable table table-bordered' id='table-$k'>";
		$table .="<caption> {$conf['title']}";
		$table .="</caption>";

		if($withdate)
			$table .= "<form method=post class='form-inline'>
			<input name='table' type='hidden' value='$k' />
			开始：<input name='dstart' class='input-small'  type='text' id='start' /> 
			结束：<input name='dstop' type='text' id='end' class='input-small'/>
			<button type='submit' class='btn'>查看</button>
			</form>";
		$table .="<thead><tr>";
        
		foreach($cols as $k=>$v){
			if(!$mheader){
				$table.="<th  {$v['width']}  title='{$v['desc']}'>{$v['label']}</th>";
			}else{
				if($v['scols']){
					$scols = $v['scols'];
					$table.="<th colspan='$scols' title='{$v['desc']}'>{$v['label']}</th>";
				}else{
					if($scols < 1){
						$table.="<th rowspan='2' title='{$v['desc']}'>{$v['label']}</th>";
					}
					$scols -= 1 ;//跳过合并行
				}

			}
		}
		if($mheader){
			$scols = 0;
			 $table .="</tr><tr>";
			foreach($cols as $k=>$v){
					if($v['scols']){
						$scols = $v['scols'];
					}else{
						if($scols > 0){
							$table.="<th {$v['width']} title='{$v['desc']}'>{$v['label']}</th>";
						}
						$scols -= 1 ;//跳过合并行
				}
			}
		}
		$table .="</tr></thead>";

		$table .="<tbody>";
		foreach($rows as &$row){
            $ucheck = $row[$conf['uheader']];
            if($ucheck && $ucheck  == $conf['uvalue'])
                continue;
            $line = '';
			if($preshow)$preshow($row,$rows);
			$line.="<tr>";
			$hasline = false;
			foreach($cols as $k=>$v){
				if($v['scols'])
					continue;
				if($row[$v['id']]){
					$hasline = true;
				}
                $svv = $row[$v['id']];
                if(!$svv ){
                    if($cellnum)
                        $svv = 0;
                }

				$line.="<td>$svv</td>";
			}
			$line.="</tr>";
			if($hasline) $table.=$line;
		}
		$table .="</tbody>";
		$table .="</table>";
		if($withdate){

		}
		$table.="<div class='alert alert-info '><pre>备注:{$conf['desc']}</pre></div></div>";
		return $table;
	}
	/**
	 * 显示百分比
	 */
	static function topercent($float,$with='',$withs='',$w=2){
		return $withp.(round($float,$w+2)*100).$withs;
	}


	/**
	 * 数据转换,按日期做key
	 */
	static function toDateArr($data,&$maxy = 0,&$chartdata = array(),&$dates){
		$rows = array();
		foreach($data as $k=>$vv){
			$v = & $vv['value'];
			$date = $v['compute_date'];
			$row = &$rows[$date];
			$vname = $v['vname'];
			$vvalue = $v['vvalue'];

			//chart data
			$chart = &$chartdata[$vname];
			$chart[$date] = $vvalue;
			if($vvalue> $maxy){
				$maxy = $vvalue;
			}
			$dates[$date] = 1;
			$row[$vname] = $vvalue;
			$row['date'] = $date;
		}
		return $rows;
	}

	/**
	 * 获取连接
	 */
	static function getMon($table='statresult'){
		$mon = new model_Stat();
		$mon->getmc()->switchColl($table);
		return $mon;
	}





	/**
	 * 标示不同颜色
	 */
	function showDiff(&$cell,$va,$with='%'){
        $va = round($va,4);
		if($va >0)
			$cell .= "<span class='green'>+$va$with</span>";
		else if($va < 0)
			$cell .= "<span class='red'>$va$with</span>";
		else{
			$cell .= "<span >$va$with</span>";
		}
	}





	/**
	 *
	 * 处理显示多个 标量，
	 * 同一日期在同一行
	 */
	static function showComScalarTable(&$tconf,$table){
		
		$sec = &$tconf['sec'];
		$dstart = $tconf['dstart'];
		$dstop = $tconf['dstop'];

		$cond = array(
			'value.compute_date'=>array('$gte'=>$dstart,'$lte'=>$dstop),
			'value._sec'=>$sec,
		);
		$mon = self::getMon();
		DTable::getvnameuery($cond,$tconf);
		//print_r($cond);
		$data = $mon->getByIdx($cond,10000);

		//最大值
		$maxy = 0;
		$chartdata = array();
		$dates = array();
		echo self::buildtablehtml(self::toDateArr($data,$maxy,$chartdata,$dates),$tconf,$table);

		$cols = $tconf['cols'];
		ksort($dates);
		$x = 1;
		//$xticks[] = array(0);
		$date2x = array();
		foreach($dates as $k=>$v){
			$xticks[] = array($x,$k);
			$date2x[$k] = $x;
			$x += 1;
		}
		foreach($cols as $k=>$v){
			$id = $v['id'];
			$ll= $chartdata[$id];
			$ldata = array();
			if($ll){
				foreach($date2x as $date=>$x){
					$ldata[] = array($x,$ll[$date]);
				}
                $lines[] = array('label'=>$v['label'],'data'=>$ldata
                    ,'points' => array( 'show' => true )
                    ,'lines' => array( 'show' => true )
                );
			}
		}
		$ytick = floor($maxy/5);
		for($i  = 0 ; $i < 6; $i ++){
			$yticks[] = $ytick*$i;
		}
		$charts['title'] = $tconf['title'];
		$charts['xaxis']['ticks'] = $xticks;
		$charts['xaxis']['max'] = $x;
		$charts['yaxis']['ticks'] = $yticks;
		$charts['yaxis']['max'] = $ytick*6;
		$charts['yaxis ']['max'] = $ytick*6;
		echo "<div id='chart-table' class=' chart '></div><script>";
		echo "var _cdata = ".json_encode($lines)."\n";
		echo "var _cconf = ".json_encode($charts)."\n";
		echo "

			";
		echo "Flotr.draw(document.getElementById('chart-table'),_cdata,_cconf);";
		echo "</script>";

	}


	/**
	 *
	 * 处理显示多个 标量，
	 * 同一日期在同一行
	 */
	static function showArrComTable(&$tconf,$dsource,$table,$view){
		$cols = &$tconf['cols'];
		$dstart = $tconf['dstart'];
		$dstop = $tconf['dstop'];
		//$view = $tconf['view'];
		$tconf['table'] = $table;

		$rows = array();
		$tconf['mheader'] = 1;
		$s2titles = $tconf['s2titles'];
		if($s2titles[$dsource])
			$tconf['title'] = $s2titles[$dsource] . '-' .$tconf['title'];
		else if($s2titles){
			//die('no dsource ' . $dsource);
		}


        $trowidx = $tconf['total'];
		//for($curd = $dstop ,$i = 0 ; $i < $daynum; $i++  ){
		for($curd = $dstop ,$i = 0 ; $curd >= $dstart; $i++  ){
			$row = &$rows[$curd];
			$row['date'] = $curd;
			//$cols[] = array('id'=>$i,'label'=>"第{$i}天");
			$cols[] = array('id'=>'date','scols'=>3,'label'=>$curd);//把日期带上
			$cols[] = array('id'=>$curd, 'label'=> '人数', 'type'=>'string');
			$cols[] = array('id'=>'ratio'.$curd, 'label'=>'比例 %', 'type'=>'string');
			$cols[]	 = array('id'=>'delta'.$curd, 'label'=>'比例变化%','desc'=>'性对于前一天比率变化', 'type'=>'string');
			$curd  = date('Ymd',strtotime("$curd -1 day"));
		}
		$dstart = $curd;

		$sec = &$tconf['sec'];
		$cond = array(
			'value.compute_date'=>array('$gte'=>$dstart,'$lte'=>$dstop),
			'value._sec'=>$sec,
			'value.vname'=>$dsource//array('$in'=>array('')),
		);
        

		$mon = DTable::getMon();
		$data = $mon->getByIdx($cond,1000);
        //echo "<pre> ";print_r($data);exit();

		$rows = array();
		$totals= array();
		foreach($data as $k=>$v){
			$vv = & $v['value'];
			$cdate = $vv['compute_date'];
			$rdaynum = $vv['vvalue'];

			foreach($rdaynum as $lvl=>$num){
				if( 1 || $lvl){
					$row = &$rows[$lvl];
					$row['lvl'] = $lvl;
					$row[$cdate] = $num;
			        $totals[$cdate] += $num;
				}
			}
		}

        $totals['lvl'] = 1000;
        if(isset($rows[$trowidx])){
            $totals = $rows[$trowidx];
            $noextra =  true;
        }else
            $rows[] = $totals;
		//比例
		foreach($rows as &$row){
            //	for($curd = $dstop ,$i = 0 ; $i < $daynum ; $i++  ){
            for($curd = $dstop ,$i = 0 ; $curd >= $dstart; $i++  ){
				$total = $totals[$curd];
				if($total < 1) $total = 1;
				$row['ratio'.$curd]  = DTable::topercent($row[$curd] /$total);//,'','%');
				$curd  = date('Ymd',strtotime("$curd -1 day"));
			}
		}
		//算隔天差
		foreach($rows as &$row){
			//for($curd = $dstop ,$i = 0 ; $i < $daynum - 1; $i++  ){
		    for($curd = $dstop ,$i = 0 ; $curd >= $dstart; $i++  ){
				$pred = date('Ymd',strtotime("$curd -1 day"));
				$ra = $row['ratio'.$curd];
				$pra = $row['ratio'.$pred];
				if($pra){
					$ra -= $pra;
					//$ra = self::topercent($ra);
					DTable::showDiff($row['delta'.$curd] ,$ra); 
				}
				$curd  = $pred;
			}
		}

        if(!$noextra)
            $tconf['desc'] .= "\n最后一行 1000 的是所有列总计，使用1000，是为了保持数字排序";
		if($s2titles){
			echo '<ul class="nav nav-tabs">';
			foreach($s2titles as $k=>$v){
				if($k !=$dsource){
					$active = '';
				}else
					$active = 'active';
				echo "<li class=' $active'><a href='".url(array('table'=>$table,'view'=>$view,'dsource'=>$k))."'>$v</a></li>";
			}
			echo "</ul>";
		}
		echo DTable::buildtablehtml($rows,$tconf,$table);
	}


	/**
	 * 给定一串变量名，展示
	 */
	static function showVTable($tconf,$vnames,$tname){
		$mon = DTable::getMon('vars_config');
		$vconf  = $mon->getByIdx(array('_id'=>array('$in'=>$vnames)),10000);
		$desc = &$tconf['desc'];
		$nokv = false ;
		$cols = &$tconf['cols'];
		foreach($vnames as $k){
			$vvconf = $vconf[$k];
			if(!$vvconf)
				$vvconf = $logconf[substr($k,4)];
			if(!$vvconf){
				echo "<div class='alert alert-error'>$k not found config </div>";
				continue;
			}

			$kdesc = $vvconf['desc'];
			$intro = $vvconf['intro'];
			//echo "$kdesc $intro  \n";
			if(!$kdesc) $kdesc = $k;
			if($vvconf['type'] == 'kv'){
				if($nokv == true)//如果已经是标量，忽略
					continue;
				$cols[] = array('id'=>'lvl','label'=>$kdesc);
				$dsource = $k;
				if($intro)
					$desc .= "\n$kdesc: -- $intro";
				break;
			}else{
				//加第一行
				if(!$nokv){
					$cols[] = array('id'=>'date','label'=>'日期/变量');
				}
                //if($intro)
                    $desc .= "$kdesc: -$k- $intro\n";
				$cols[] = array('id'=>$k,'label'=>$kdesc);
				$nokv = true;
			}
		}
		if($cols){
			if($nokv)
				DTable::showComScalarTable($tconf,'custom');
			else
				DTable::showArrComTable($tconf,$dsource,'custom',$view);
		}

	}

};





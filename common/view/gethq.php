<?php
$list = $_REQUEST['list'];
//$list = 'sh600837,sh600006,';


$zqrs = DbConfig::getParam('zqrs',PSPACE);
$i = 0;
$list = '';
echo "<pre>\n";
foreach($zqrs as $k=>$row){
    $pre = App::zqdmPre($k);
    if($pre){
        if($row[6] != 0)
            $list .="$pre$k,";
    }else{
        echo "[$k] not get pre [$pre] {$row[3]}\n";
    }
}

$url = 'http://hq.sinajs.cn/list='.$list;
$content = iconv('GB2312','UTF-8//IGNORE',file_get_contents($url));

$lines = explode("\n",$content);
$rows = array();
foreach($lines as $l){
    $l = trim($l);
    $arr = explode('"',$l);
    $zqdm = substr($arr[0],-7,6);
    //echo $zqdm."\n";
    $bl = $arr[1];
    $row = explode(',',$bl);
    $row['zqdm'] = $zqdm;
    if($row[2]){
        $ssjq[$zqdm] = $row[2]; 
    }
}
DbConfig::saveParam('sshq',$ssjq,PSPACE);
echo json_encode($ssjq,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
echo "\n$url\n";
echo $content;

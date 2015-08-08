<?php


//test
require_once(__DIR__.'/../base.php');
$dayf =  '2015320';
$daye =  '20150321';

$r = Crawler_Xueqiu::getDayK('SZ002465',$dayf,$daye);
//$r = Crawler_Xueqiu::getGupiaoDay('601688',$dayf);
print_r($r);
// */


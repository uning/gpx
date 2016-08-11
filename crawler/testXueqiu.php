<?php


//test
require_once(__DIR__.'/../base.php');
$dayf =  '20150320';
$daye =  '20150321';

$r = Crawler_Xueqiu::getDayK('SZ002465',$dayf,$daye,0);
//$r = Crawler_Xueqiu::getGupiaoDay('601688',$dayf);
print_r($r);
// */


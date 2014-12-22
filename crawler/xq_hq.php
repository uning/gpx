<?php
require_once(__DIR__.'/config.php');

$url = 'http://xueqiu.com/stock/screener/values.json?category=SH&field=pettm&_=1415875981426';


//return;
//获取表头
//http://xueqiu.com/hq/screener
$url = 'http://xueqiu.com/hq/screener';
$content = url_content($url);
//echo $content;
//$content = '<label title="每股收益"><input type="checkbox" value="eps"><span>每股收益</span></label><label title="每股净资产"><input type="checkbox" value="bps"><span>每股净资产</span></label>';
$headerRegex = '|<input type="checkbox" value="([^"]*)"><span>([^<]*)</span>|';
preg_match_all($headerRegex,$content,$mout);
print_r($mout);


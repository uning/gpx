<?php
/*
 * base.php
 *
 */


define('REQUEST_TIME_NS', microtime(true));
define('ROOT',__DIR__);

if (!function_exists('fastcgi_finish_request')){
	function  fastcgi_finish_request(){
		flush();
	}
}

function outdebug(){
	print new PL_View(PL_ROOT.'/PL/View/Debug.php');
}





define('COMM_ROOT',__DIR__.'/common');
define('LOG_ROOT',ROOT.'/log');
define('PUB_CONTROLLER',COMM_ROOT.'/api');
define('PUB_MODEL',COMM_ROOT.'/mongo/model/');
define('PUB_SERVER',COMM_ROOT.'/server');
define('PUB_COMMAND',COMM_ROOT.'/command');
define('CACHE_DIR',__DIR__.'/data/cache/');
define('TM','Ymd');
define('P_URLP','asset/common');

const PSPACE = 'calccp';

require_once __DIR__.'/App.php';

//记录脚本执行时间
//PL_Profile::run(false);//输出到error_log




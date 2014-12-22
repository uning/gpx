<?php


if(!defined("PL_ROOT")){
    define('PL_ROOT',__DIR__.'/plframework');
}
require PL_ROOT.'/bootstrap.php';
$loader = PL_ApcClassLoader::getInstance();
$loader->enableApc(false);
$loader->registerPrefixes(array(
	'model_' => __DIR__.'/common/'
));


$loader-> registerPrefixFallbacks(array(
      PUB_CONTROLLER
	  ,PUB_SERVER
      ,PUB_COMMAND
));

/**
 * 入口文件
 * 最后合并到app.inc里去 
 *
 *  
 */

class App extends PL_Application{
	protected function __construct(){
		parent::__construct();
		$app = $this;
		$app['mods'] = array(
			'index'=> ROOT.'/IndexServer.php'
		);
		PL_Session::$randsid = true;
		 $app->mshare('session',
			function () use($app){
				//$u = $app->vget('uid');
				//$sec = $app->vget('sec');
				return PL_Session::start();
			
			});
		$app->mshare('session.storage',   
   			function () use($app){
				//return new PL_Session_Redis();
				return new PL_Session_File();
			});
    }


};


/**
 *
 * 数据库配置
 */
class DbConfig extends   PL_Config_Db
{
    static $redises;
    static $caches;
	static $mongodb_def_cstr = 'mongodb://localhost:30001';
	static $mongodb_def_db = 'gupiaox';
	static $mongodb_def_option  = array();
	static $mongodbs ;

}

//init sub instance
App::getInstance();


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
    static  $dataconf ;
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


    //获取配置
    static function &getDataconf($coll = ''){
        if($coll)
            return static::$dataconf[$coll];
        return static::$dataconf;
    }
    //colModel
    static function getColModel($coll,$sheader='',$bzPos = 5){
        $dconf = &static::$dataconf[$coll];
        $colMap = $dconf['colModel'];
        $colModel = array();
        $i  = 0;
        if($colMap){
            foreach($colMap as $k=>$v){
                $colModel[] = $v;
                $i += 1;
                if($i == $bzPos){
                    $colModel[] =array (
                        'name' => 'bz',
                        'label' => '备注',
                        'width' => 75,
                        'editable' => 'true',
                        'edittype' => 'textarea',
                    );
                }
            }
            return $colModel;
        }
        $colModel[] = array('name'=>'_id','key'=>true,'hidden'=>true);
        $colModel[] = array('name'=>'date','label'=>'日期','width'=>70,'sorttype'=>'text');
        $i = 1;
        $txtname = ($sheader == 'header') ? 'txtfields':$sheader.'_txtfields';
        if($dconf[$txtname])
            $txtfields  = array_flip($dconf[$txtname]);
        $txtname = ($sheader == 'header') ? 'sumops':$sheader.'_sumops';
        $ops = $dconf[$txtname];
        foreach($dconf[$sheader] as $k=>$v){
            $colHeader = array('name'=>$k,'label'=>$v,'width'=>70,'sorttype'=>'number');
            if(isset($txtfields[$k]))
                $colHeader['sorttype'] = 'text';
            if($ops[$k]){
                $colHeader['summaryType'] = $ops[$k];
                $colHeader['summaryTpl'] = '{0}';
            }
            $colModel[] = $colHeader;
            $i += 1;
            if($i == $bzPos){
                $bzPos = false;
                $colModel[] =array (
                    'name' => 'bz',
                    'label' => '备注',
                    'width' => 75,
                    'editable' => 'true',
                    'edittype' => 'textarea',
                );
            }
        }
        if($bzPos > 0){
                $colModel[] =array (
                    'name' => 'bz',
                    'label' => '备注',
                    'width' => 75,
                    'editable' => 'true',
                    'edittype' => 'textarea',
                );
        }
        return $colModel;
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
App::$dataconf = require ROOT.'/data/dataconf.php';
//init sub instance
App::getInstance();


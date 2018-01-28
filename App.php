<?php


if(!defined("PL_ROOT")){
    define('PL_ROOT',__DIR__.'/plframework');
}
require PL_ROOT.'/bootstrap.php';
$loader = PL_ApcClassLoader::getInstance();
$loader->enableApc(false);
$loader->registerPrefixes(array(
    'model_' => __DIR__.'/common/',
    'Crawler_'=>__DIR__,
));


$loader-> registerPrefixFallbacks(array(
    PUB_CONTROLLER
    ,PUB_SERVER
    ,PUB_COMMAND
));


class App extends PL_Application{
    static  $dataconf ;
    protected function __construct(){
        parent::__construct();
        $app = $this;
        $app['mods'] = array(
            'index'=> ROOT.'/IndexServer.php',
            'wucun'=>ROOT.'/WucunServer.php'
        );
        PL_Session::$randsid = true;
        $app->mshare('session',
            function () use($app){
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
        if(!static::$dataconf){
            static::$dataconf = DbConfig::getParam('gridconfig','gupiaox');
        }
        if($coll)
            return static::$dataconf[$coll];
        return static::$dataconf;
    }





    const IDX_MAX = 99;

    /**
     * 加表头
     *
     * pos < 0 时不加
     * date 日期，id 对应表格的key和数据库_id字段
     */
    static function checkAddCol($i,&$cols,&$datePos,&$bzPos,&$idPos){
        $max = self::IDX_MAX;
        if($i == $idPos || ($idPos >= 0 && $i > $max)){
            $cols[] = array('name'=>'_id','key'=>true,'hidden'=>true);
            $idPos = -1;
        }
        if($i == $datePos || ($datePos >= 0 && $i > $max)){
            $cols[] =array('name'=>'date','label'=>'日期','stype'=>'text','width'=>70,'sorttype'=>'date');
            $datePos = -1;
        }
        if($i == $bzPos || ($bzPos >= 0 && $i > $max)){
            $cols[] = array('name' => 'bz','label' => '备注','width' => 100,'editable' => 'true', 'edittype' => 'textarea',);
            $bzPos = -1;
        }
    }
    /**
     * pos 为负时，不显示该collumn
     */
    static function getColModel($coll
        ,$sheader='header'
        ,$datePos = 1
        ,$bzPos = 5
        ,$idPos = 0
    ){
        $dconf = &static::$dataconf[$coll];
        if(!$dconf){
            $dconf = DbConfig::getParam('gridconfig.'.$coll,'grid');
        }
        $colMap = $dconf['colModel'];
        $colModel = array();
        $i  = 0;
        if($colMap){
            foreach($colMap as $k=>$v){
                static::checkAddCol($i,$colModel,$datePos,$bzPos,$idPos);
                $colModel[] = $v;
                $i += 1;
            }
        }else{
            $txtfields  = array_flip((array)$dconf['txtfields']);
            $txtname = ($sheader == 'header') ? 'txtfields':$sheader.'_txtfields';
            if ($dconf[$txtname]) {
                $txtfields  = array_flip($dconf[$txtname]);
            }
            $txtname = ($sheader == 'header') ? 'sumops':$sheader.'_sumops';
            $ops = $dconf['sumops'];
            if ($dconf[$txtname]) {
                $ops = $dconf[$txtname];
            }

            $stypes = $dconf['sfields'];
            $txtname = ($sheader == 'header') ? 'sfields':$sheader.'_sfields';
            if ($dconf[$txtname]) {
                $stypes = $dconf[$txtname];
            }
            $width = 70;
            $cwidth =ceil(700 /count($dconf[$sheader]));
            if($cwidth > $width)
               $width = $cwidth;
            foreach($dconf[$sheader] as $k=>$v){
                $colHeader = array('name'=>$k,'label'=>$v,'width'=>$width,'sorttype'=>'number');
                if($k == 'content'){
                   // $colHeader['width'] = 300;
                }
                if(isset($txtfields[$k]))
                    $colHeader['sorttype'] = 'text';

                if($ops[$k]){
                    $colHeader['summaryType'] = $ops[$k];
                    $colHeader['summaryTpl'] = '{0}';
               }
                if($stypes[$k]){
                    $colHeader['stype'] = $stypes[$k];
                }

                static::checkAddCol($i,$colModel,$datePos,$bzPos,$idPos);
                if($k[0] === '_'){
                    $colHeader['width'] = 10;
                }
                //$colHeader['cwidth'] = $cwidth;
                $colModel[] = $colHeader;
                $i += 1;
            }
        }



        static::checkAddCol(self::IDX_MAX,$colModel,$datePos,$bzPos,$idPos);
        return $colModel;
    }



    /**
     *
     * 转化为数字
     */
    static function normalTodb(&$row,&$options){
        if ($options) {
            foreach ($options as $k=>$v) {
                $row[$k] += 0;
            }
        }
    }


    //////////////////////////////////////////////////////////////////////
    //PARA: Date Should In YYYY-MM-DD Format
    //RESULT FORMAT:
    // '%y Year %m Month %d Day %h Hours %i Minute %s Seconds'        =>  1 Year 3 Month 14 Day 11 Hours 49 Minute 36 Seconds
    // '%y Year %m Month %d Day'                                    =>  1 Year 3 Month 14 Days
    // '%m Month %d Day'                                            =>  3 Month 14 Day
    // '%d Day %h Hours'                                            =>  14 Day 11 Hours
    // '%d Day'                                                        =>  14 Days
    // '%h Hours %i Minute %s Seconds'                                =>  11 Hours 49 Minute 36 Seconds
    // '%i Minute %s Seconds'                                        =>  49 Minute 36 Seconds
    // '%h Hours                                                    =>  11 Hours
    // '%a Days                                                        =>  468 Days
    //////////////////////////////////////////////////////////////////////
    static function dateDifference($date_1 , $date_2 , $differenceFormat = '%a' )
    {
        if($date_1 == 'lastest')
            $date_1 = date('Ymd');
        if($date_2 == 'lastest')
            $date_2 = date('Ymd');

        $datetime1 = date_create($date_1);
        $datetime2 = date_create($date_2);
        $interval = date_diff($datetime1, $datetime2);
        return $interval->format($differenceFormat);
    }



    static $_zqdm2pre = array(
           '110015'=>'sh',
           '110018'=>'sh',
           '110024'=>'sh',
           '112155'=>'sz',
           '112133'=>'sz',
           '112110'=>'sz',
           '113001'=>'sh',
           '113002'=>'sh',
           '122083'=>'sh',
           '122093'=>'sh',
           '122143'=>'sh',
           '122162'=>'sh',
           '124656'=>'sh',
           '128002'=>'sz',
    );
    /**
     * 获取代码前缀
     */
    static function zqdmPre($v){
        $first = substr($v,0,1);
        if($first == '6' || $first == '5'){
          return 'sh';
        }elseif($first === '0' || $first == '3'){
            return 'sz';
        }else{
            $sub3 = substr($v,0,3);
            if($sub3 == '131')
                return 'sz';
            else if($sub3 == '204')
                return 'sh';
            else if($sub3  >= 150 && $sub3 <= 161)
                return 'sz';

            return self::$_zqdm2pre[$v];
        }
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

    const SPACE_PREFIX = 'idpre_';


    static $paramsFromDb = array();
    /**
     * 一个space 存一条记录
     */
    static function getParam($name = '',$space = 'defaut'){
        $g = & static::$paramsFromDb[$space];
        if(!$g){
            $mc = static::getMongodb('progparams');
            $cond = array('_id'=>self::SPACE_PREFIX.$space);
            $g = $mc->findOne($cond);
        }
        if ($name) {
            $arr = explode('.',$name);
            foreach ($arr as $n) {
                if (!$g) {
                    break;
                }
                $g = $g[$n];
            }
        }
        return $g;
    }


    static function saveParam($name,$value = '',$space = 'defaut' ){
        $cond = array('_id'=>self::SPACE_PREFIX.$space);
        $mc = static::getMongodb('progparams');
        if($value){
            $name = array($name=>$value);
        }
        $mc->findAndModify($cond,array('$set'=>$name),array(),array('upsert'=>true));
    }
}
//App::$dataconf = require ROOT.'/data/dataconf.php';
//init sub instance
App::getInstance();


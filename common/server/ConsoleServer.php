<?php

require_once __DIR__."/../model/QueueService.php";
require_once __DIR__."/../command/NoticePushCommand.php";

ini_set('memory_limit','2G');

function sortRankByStar($p1,$p2){
    if($p1['max_floors'] != $p2['max_floors']){
        return $p1['max_floors'] < $p2['max_floors'];
    }else{
        return $p1['max_stars'] < $p2['max_stars'];
    }
}
function array2code($key,$value,$indent){
    $prefix = '';
    for($i=0;$i<$indent;$i++){
        $prefix .= '    ';
    }
    $suffix = $indent?',':';';
    $str = '';
    if($key!==null){
        //$key = addslashes($key);
        $key=str_replace("'","\'",$key);
        $str .= $prefix."'$key'=>";
    }
    if(is_array($value)){
        $str .= "array(\n";
        foreach($value as $k=>$v){
            $str .= array2code($k,$v,$indent+1);
        }
        $str .= $prefix.")$suffix\n";
    }else{
        if(is_numeric($value)){
            $str .= "$value$suffix\n";
        }else{
            //$value = addslashes($value);
            $value=str_replace("'","\'",$value);
            $str .= "'$value'$suffix\n";
        }
    }
    return $str;
}

class ConsoleServer{
    public $_params = null;
        public function init_param(){
        global $argc;
        global $argv;
        $this->_params = array();
        for($i=1;$i<$argc;$i++){
            foreach(explode(",",$argv[$i]) as $v){
                list($key,$value) = explode('=',$v);
                $this->_params[$key] = $value;
            }
        }
    }
    public function get_param($name){
        if(is_null($this->_params)){
            $this->init_param();
        }
        return $this->_params[$name];
    }

    public function run(){
        $action = $this->get_param('action');
        $method_name = 'action_'.$action;
        if(method_exists($this,$method_name)){
            $this->$method_name();
        }else{
            echo "action[$action] not defined in ConsoleServer\n";
            exit(1);
        }
    }


    //phperror数据同步到总服务器
    static function action_sync_errorreport(){
        PhpErrorStat::syncReportDetail();
        PhpErrorStat::syncRealTimeStat();
    }


    /**
     * 体验服充值问题
     * #desc 目前体验服老玩家v5升v6显示需要的金额不正确
     */
    public function action_bcreward2(){
        //分区
        $section_list = array_keys(getApp()->getSectionConfig());
        //print_r($section_list);
        $maxuid = model_LoginUser::maxuid();
        echo "总人数:";
        var_dump($maxuid);
        //所有分区
        //foreach((array)$section_list as $sec){
            //$um = new model_User(0,$sec);
            //echo $um->count()."\n"; 
        //}
        
        die;

        for($uid=1;$uid<=$maxuid;$uid++){
            $um = new model_LoginUser($uid);
            $um->get();
            $secs = $um['secs'];
            //print_r($um);
            //玩家登陆过的所有分区
            echo "======{$uid}========\n";
            foreach((array)$secs as $sec=>$intime){
                echo "======{$uid}===={$sec}====\n";
                $player = new model_Player($uid,$sec); 
                $data = $player->getFields(array('name','qiyu.activity_shengji'));
                //print_r($data);
                $shengji = $data['qiyu']['activity_shengji'];
                //print_r($shengji);
                $gem = 0;
                if(isset($shengji['10'])){//领了v5
                    $gem = 820;
                }elseif(isset($shengji['5'])){//刚领v3
                    $gem = 240;
                }
                if($gem > 0){
                    //更改reward2
                    //$player->numberIncr('base','total_gem_rewarded2',$gem);
                    //$player->commit();
                    echo "补偿数据reward2完毕：=========uid:{$uid}-sec:{$sec}补{$gem}元宝..\n";
                }
            }
        }
    }

    /**
     * 燕子坞
     * 修改名字
     */
    public function action_yanziwu_changename(){
        $sec = 's144';
        $uid = 1;
        $redis = DbConfig::getRedis('rank');
        $app = getApp();
        $config = $app->getActivityConfig('act_yanziwuqiangqin_20130425');
        $key = "act_yanziwuqiangqin_".date('md',$config['start_t'])."_".$sec."_data";
        $name = $redis->hget($key,$uid);
        $action_name = array('name'=>'众神•小小','last_pay'=>'1399869826');
        $redis->hset($key,$uid,$action_name);
        $name = $redis->hget($key,$uid);
        print_r($name);
    }


    //燕子坞 数据创造
    //托号复制
    public function action_cyanziwu_data(){
        //每个区的UID   只是复制一个号
        $act_uid = 2841673;

        //$filter_str = 's1,s28,s125,s127,s144,s91,s92,s93,s94,s95,s96,s97,s98,s99,s100,s101,s102,s103,s104,s105,s37,s38,s39,s40,s41,s42,s43,s44,s45,s47,s49,s53,s54,s55,s58';
        /*$all = array(
            array(
                'uid' => '3833018',
                'sec' => 's127',
                'sec_s' => '1',
                'sec_e' => '70',
            ),
            array(
                'uid' => '3949957',
                'sec' => 's135',
                'sec_s' => '71',
                'sec_e' => '120',
            ),
            array(
                'uid' => '630286',
                'sec' => 's145',
                'sec_s' => '121',
                'sec_e' => '144',
            ),
            array(
                'uid' => '4063728',
                'sec' => 's149',
                'sec_s' => '146',
                'sec_e' => '149',
            ),
        );*/

        //蜂巢  需要发送的区
        $normal = 's13,s15,s19,s20,s21,s26,s31,s32,s39,s44,s45,s46,s47,s48,s51,s52,s56,s57,s58,s59,s60,s64,s67,s68,s72,s74,s75,s76,s77,s79,s82,s83,s84,s86,s88,s89,s90,s92,s96,s97,s110,s111,s112,s116,s117,s118,s120,s123,s125,s126,s127,s129,s130,s131,s132,s133,s138,s139,s141,s143,s144,s145,s146,s147,s148,s150';
        
        //过滤分区
        $filter_str = "";
        //主账号 对应的复制分区
        $all = array(
            array(
                'uid' => '2841702',
                'sec' => 's99',
                'sec_s' => '13',
                'sec_e' => '13',
            ),
            array(
                'uid' => '2841702',
                'sec' => 's99',
                'sec_s' => '15',
                'sec_e' => '15',
            ),
            array(
                'uid' => '2841702',
                'sec' => 's99',
                'sec_s' => '19',
                'sec_e' => '21',
            ),
            array(
                'uid' => '2841702',
                'sec' => 's99',
                'sec_s' => '26',
                'sec_e' => '26',
            ),
            array(
                'uid' => '2841702',
                'sec' => 's99',
                'sec_s' => '31',
                'sec_e' => '32',
            ),
            array(
                'uid' => '2841702',
                'sec' => 's99',
                'sec_s' => '39',
                'sec_e' => '39',
            ),
            array(
                'uid' => '2841702',
                'sec' => 's99',
                'sec_s' => '44',
                'sec_e' => '48',
            ),
            array(
                'uid' => '2841702',
                'sec' => 's99',
                'sec_s' => '51',
                'sec_e' => '52',
            ),
            array(
                'uid' => '2841702',
                'sec' => 's99',
                'sec_s' => '56',
                'sec_e' => '60',
            ),
            array(
                'uid' => '2841702',
                'sec' => 's99',
                'sec_s' => '64',
                'sec_e' => '64',
            ),
            array(
                'uid' => '2841702',
                'sec' => 's99',
                'sec_s' => '67',
                'sec_e' => '68',
            ),
            array(
                'uid' => '2841702',
                'sec' => 's99',
                'sec_s' => '72',
                'sec_e' => '72',
            ),
            array(
                'uid' => '2841702',
                'sec' => 's99',
                'sec_s' => '74',
                'sec_e' => '77',
            ),
            array(
                'uid' => '2841702',
                'sec' => 's99',
                'sec_s' => '79',
                'sec_e' => '79',
            ),
            array(
                'uid' => '2841702',
                'sec' => 's99',
                'sec_s' => '82',
                'sec_e' => '84',
            ),
            array(
                'uid' => '2841702',
                'sec' => 's99',
                'sec_s' => '86',
                'sec_e' => '86',
            ),
            array(
                'uid' => '2841702',
                'sec' => 's99',
                'sec_s' => '88',
                'sec_e' => '90',
            ),
            array(
                'uid' => '2841702',
                'sec' => 's99',
                'sec_s' => '92',
                'sec_e' => '92',
            ),
            array(
                'uid' => '2841702',
                'sec' => 's99',
                'sec_s' => '96',
                'sec_e' => '97',
            ),
            array(
                'uid' => '2841702',
                'sec' => 's99',
                'sec_s' => '110',
                'sec_e' => '112',
            ),
            array(
                'uid' => '2841702',
                'sec' => 's99',
                'sec_s' => '116',
                'sec_e' => '118',
            ),
            array(
                'uid' => '2841702',
                'sec' => 's99',
                'sec_s' => '120',
                'sec_e' => '120',
            ),
            array(
                'uid' => '2841702',
                'sec' => 's99',
                'sec_s' => '123',
                'sec_e' => '123',
            ),
            array(
                'uid' => '2841702',
                'sec' => 's99',
                'sec_s' => '125',
                'sec_e' => '127',
            ),
            array(
                'uid' => '2841702',
                'sec' => 's99',
                'sec_s' => '129',
                'sec_e' => '133',
            ),
            array(
                'uid' => '2841702',
                'sec' => 's99',
                'sec_s' => '138',
                'sec_e' => '139',
            ),
            array(
                'uid' => '2841702',
                'sec' => 's99',
                'sec_s' => '141',
                'sec_e' => '141',
            ),
            array(
                'uid' => '2841702',
                'sec' => 's99',
                'sec_s' => '143',
                'sec_e' => '148',
            ),
            array(
                'uid' => '2841702',
                'sec' => 's99',
                'sec_s' => '150',
                'sec_e' => '150',
            ),
        );
            

        $filter_arr = explode(',',$filter_str);
        foreach($all as $k=>$v){
            $uid = $v['uid'];
            $sec = $v['sec'];
            $sec_s = $v['sec_s'];
            $sec_e = $v['sec_e'];
            $app = app();
            $uid = $app->getRealUid($uid, $sec);
            $sec = $app->getRealSec($sec);
            $coll = 'users';
            $um = $app->getum($uid,$coll,$sec);
            $ret[$coll] = $um->get();
            $coll = 'jianghu';
            $um = $app->getum($uid,$coll,$sec);
            $ret[$coll] = $um->getByIdx(array('_u'=>$uid));
            $coll = 'xunbao';
            $um = $app->getum($uid,$coll,$sec);
            $ret[$coll] = $um->getByIdx(array('_u'=>$uid));
            $coll = 'bloodybattle';
            $um = $app->getum($uid,$coll,$sec);
            $ret[$coll] = $um->get();
            echo "开始将uid:{$uid}，{$sec} 复制到：\n";

            if(empty($ret)){
                die("数据错误");
            }


            $do = false;
            echo "是否执行数据库操作".var_export($do)."\n";

            for($i=$sec_s;$i<=$sec_e;$i++){
                $tsec = 's'.$i;
                if(in_array($tsec,$filter_arr)){
                    continue;
                }
                $uid_new_renwuname = model_Util::randomName();
                $ret['users']['name'] = $uid_new_renwuname;
                echo "====uid:{$act_uid},分区:".$tsec."\n";

                $coll = 'users';
                $um = $app->getum($act_uid,$coll,$tsec);
                $do && $um->init($ret[$coll]);
                foreach((array)$ret['jianghu'] as $row ){
                    $id = $row['_id'];
                    $idarr = explode(':',$id);
                    $idarr[0] = $act_uid;
                    $id = implode(':',$idarr);
                    echo "jianghu-操作_id:{$id},_u:{$act_uid}\n";
                    $row['_id'] = $id;
                    $row['_u'] =  $act_uid;
                    $do && $um->saveRow($row,'jianghu');
                }
                foreach((array)$ret['xunbao'] as $row ){
                    $id = $row['_id'];
                    $idarr = explode(':',$id);
                    $idarr[0] = $act_uid;
                    $id = implode(':',$idarr);
                    echo "xunbao-操作_id:{$id},_u:{$act_uid}\n";
                    $row['_id'] = $id;
                    $row['_u'] = $act_uid;
                    $do && $um->saveRow($row,'xunbao');
                }
                $coll = 'bloodybattle';
                $um = $app->getum($act_uid,$coll,$tsec);
                $do && $um->init($ret[$coll]);
                echo "导入成功\n";

            }
        }

    }
    public function action_command(){
        $command_name = strtolower($this->get_param('command'));
        $command_name = ucfirst($command_name);
        $command_name = $command_name . "Command";
        $command = new $command_name();
        $command->execute($this->_params);
    }
    public function action_ad_callback_queue(){
        $max_running_time = 1800;
        $qs = new QueueService('notice_ad_callback_queue');
        $qsconfig = DbConfig::getDbConfig('queue');
        $qs->connect($qsconfig['host'],$qsconfig['port']);
        if($qs->connected){
            $start_t = time();
            $process_id = getmypid();
            glog::info("[$process_id] ad_callback worker started",'queue');
            while(1){
                try{
                    $a ++;
                    $now = date('Y-m-d H:i:s');
                    while($qs->length()){
                        $b ++;
                        $info = $qs->pop();
                        $uid = $info['uid'];
                        $type = $info['type'];
                        $mac = $info['mac'];
                        $init_t = $info['init_t'];
                        $end_t = $info['end_t']; 
                        $now_init_time = time();
                        
                        //超过一天直接丢掉
                        if($start_t - $init_t > 10*60*60 ){
                            continue;
                        }
                        $resend = 0;
                        if(!empty($end_t)){
                            //10秒，30秒，60秒，5*60，10*60，30*60，60*60，2*60*60，4*60*60，6*60*60 秒过后都要通知一次
                            $limit_time = array(10,30,60,5*60,10*60,30*60,60*60,2*60*60,4*60*60,6*60*60,8*60*60,10*60*60);
                            $first_time = $now_init_time - $init_t;
                            $last_time = $end_t - $init_t;
                            foreach($limit_time as $k=>$v){
                                if($k == 0)continue;
                                if($first_time > $limit_time[$k-1] && $first_time < $v && $last_time <= $limit_time[$k-1]){
                                    $resend = 1;
                                }
                            }
                        }
                        
                        $reSendQueue = array();
                        if(!$resend && !empty($end_t)){
                            $reSendQueue[] = $info;
                            continue;
                        }

                        $result = NoticePushCommand::handleAdCallback($type,$mac,$uid,$init_t,$now_init_time);
                        if(!empty($result)){
                            $reSendQueue[] = $result;
                        }
                    }
                    if(!empty($reSendQueue)){
                        $check = array();
                        foreach($reSendQueue as $value){
                            if($check[$value['type']][$value['mac']]) continue;
                            $check[$value['type']][$value['mac']] = 1;
                            $qs->push($value);
                        }
                    }
                    sleep(1);
                    if(time()-$start_t > $max_running_time){
                        glog::info("[$now][$process_id] ad_callback worker exit",'queue');
                        exit(0);
                    }
                }catch(Exception $ex){
                    glog::info("[$now][$process_id] exception: ".$ex->getMessage(). "\n".$ex->getTraceAsString(),'queue');
                    $qs->connect($qsconfig['host'],$qsconfig['port']);
                    if($qs->connected){
                        glog::info("[$now][$process_id] ad_callback redis re-connected",'queue');
                    }else{
                        glog::info("[$now][$process_id] ad_callback redis connection lost",'queue');
                        break;
                    }
                }
            }
        }
    }
    public function action_notice_queue(){
        $warning_threshold = 256;
        $max_running_time = 3600;

        $qs = new QueueService('notice_queue');
        $qsconfig = DbConfig::getDbConfig('queue');
        $qs->connect($qsconfig['host'],$qsconfig['port']);
        if($qs->connected){
            $start_t = time();
            $process_id = getmypid();
            glog::info("[$process_id] worker started",'queue');
            while(1){
                try{
                    $now = date('Y-m-d H:i:s');
                    $queue_len = $qs->length();
                    if($queue_len > $warning_threshold){
                        glog::fatalerror(__METHOD__.", 队列中待处理的任务过多[{$queue_len}], 请考虑增加worker数量");
                    }
                    while($qs->length()){
                        $info = $qs->pop();
                        $uid = $info["uid"];
                        $sec = $info["sec"];
                        $msg = $info["msg"];
                        $league = $info["league_id"];
                        $action = $info['action'];
                        glog::info("[$now][$process_id] worker handle [$action][$sec][$uid]",'queue');
                        if($action == 'addRemoteNotification'){
                            NoticePushCommand::push($uid,$sec,$msg);
                        }else if($action=='mijicanzhang_rm' || $action=='mijicanzhang_add'){
                            NoticePushCommand::handleCanZhangMsg($uid, $sec, $msg, $action);
                        }else if($action=="update_cache"){
                            $fields = $info['fields'];
                            if(isset($info['data'])){
                               $userdata = $info['data'];
                            }else{
                               $userdata = null;
                            }
                            model_CacheUtil::updateCache($uid,$sec,$fields,$userdata);
                        }else if($action=="send_chenghao"){
                            //世界boss
                            //发送称号
                            $type = $info['type'];
                            $tag = $info['tag'];
                            $level = $info['level'];
                            NoticePushCommand::handleChenghao($uid,$sec,$type,$tag,$level);
                        }else if($action=="league_cache"){
                            $fields = $info['fields'];
                            model_CacheUtil::updateLeagueCache($league,$sec,$fields);
                            glog::info("[league_cache][".print_r($fields, true)."][lid = $league][$sec] ".date('H:i:s',time()."\n"),'leaugecache');
                        }else if($action=='league_modify'){
                            $fields = $info['fields'];
                            $operation = $info['operation'];
                            glog::info("[modify_cache][".print_r($fields, true)."][lid = $league][$sec][option = $operation] ".date('H:i:s', time()."\n"),'leaugecache');
                            model_CacheUtil::modifyLeagueCache($league,$sec,$fields,$operation);
                        }else if($action=='createLunjian'){
                            $sec = $info['sec'];
                            glog::info("[createLunjian][".$sec."] ".date('H:i:s', time())."\n");
                            model_PVPUtil::createLunjian($sec);
                        
                        }else if($action=='api'){
                            $cc = $info['class'];
                            $mm = $info['function'];
                            if($cc && $mm){
                                $instance = new $cc();
                                $param = $info['param'];
                                try{
                                    $instance->$mm($param);
                                }catch(exception $api_ex){
                                    $subject = "api_queue_error_".P_PLATFORM."_".date('Y-m-d H:i:s', time());
                                    $email_content = "API队列错误: ". $api_ex->getMessage() . "\n" .
                                        $api_ex->getTraceAsString()."\n".json_encode($info);
                                    //model_Util::sendemail('wangkun@playcrab.com',$subject,$email_content);
                                    echo $email_content;
                                }
                                //glog::info("[runapi] c=$cc m=$mm param=".json_encode($param).";\n");
                            }
                        }else if($action == 'activity'){
                            try{
                                model_ActivityUtil::processActMsg($info);
                            }catch(Exception $ee){
                                $subject = "activity_queue_error_".P_PLATFORM."_".date('Y-m-d H:i:s', time());
                                $email_content = "活动发奖队列错误: ". $ee->getMessage() . "\n" . 
                                    $ee->getTraceAsString()."\n".json_encode($info);
                                model_Util::sendemail('wangkun@playcrab.com',$subject,$email_content);
                                echo $email_content;
                            }
                        }
                    }
                    sleep(1);
                    if(time()-$start_t > $max_running_time){
                        glog::info("[$now][$process_id] worker exit",'queue');
                        exit(0);
                    }
                }catch(Exception $ex){
                    glog::info("[$now][$process_id] exception: ".$ex->getMessage(). "\n".$ex->getTraceAsString(),'queue');
                    $qs->connect($qsconfig['host'],$qsconfig['port']);
                    if($qs->connected){
                        glog::info("[$now][$process_id] redis re-connected",'queue');
                    }else{
                        glog::info("[$now][$process_id] redis connection lost",'queue');
                        break;
                    }
                }
            }
        }
    }

    /**
     * 队列消息处理总控方法
     */
    public function action_queue_main(){
        $qname = $this->get_param('qname');
        $qserver = $this->get_param('qserver');

        $warning_threshold = 256;//队列任务警戒值
        $max_running_time = 3600;//脚本执行时长  

        $qs = new QueueService($qname);
        $qsconfig = DbConfig::getDbConfig($qserver);
        $qs->connect($qsconfig['host'],$qsconfig['port']);
        if($qs->connected){
            $start_t = time();
            $process_id = getmypid();
            glog::info("[$process_id] worker started",'queue');
            while(1){
                try{
                    $now = date('Y-m-d H:i:s');
                    $queue_len = $qs->length();
                    if($queue_len > $warning_threshold){
                        glog::fatalerror(__METHOD__.", 队列中待处理的任务过多[{$queue_len}], 请考虑增加worker数量");
                    }
                    while($qs->length()){
                        try{
                            $info = $qs->lrange(0,0); //只是读，在成功处理以后才把消息删除。
                            $result = model_ActivityUtil::processAction($info);
                            if($result['s'] == StatusCode::ok){
                                $qs->pop();
                            }else{
                                glog::fatalerror(__METHOD__.", 消息处理失败：{$result['s']} msg:{$result['msg']}");   
                                $subject = "activity_queue_error_".P_PLATFORM."_".date('Y-m-d H:i:s', time());
                                $email_content = "消息处理失败: s:{$result['s']} msg:{$result['msg']}"."\n".json_encode($info);
                                model_Util::sendemail(dzm_base::load_config('system','dev_mail_user'),$subject,$email_content);

                                //剔除
                                $qs->pop();
                            }
                        }catch(Exception $ee){
                            glog::info("[$now], msg = [ ".json_encode($info)." ] exception: ".$ee->getMessage(). "\n".$ee->getTraceAsString(),"active_error");
                            $subject = "activity_queue_error_".P_PLATFORM."_".date('Y-m-d H:i:s', time());
                            $email_content = "活动发奖队列错误: ". $ee->getMessage() . "\n" . $ee->getTraceAsString()."\n".json_encode($info);
                            model_Util::sendemail(dzm_base::load_config('system','dev_mail_user'),$subject,$email_content);
                        }
                    }
                    sleep(1);
                    if(time()-$start_t > $max_running_time){
                        exit(0);
                    }
                }catch(Exception $ex){
                    glog::info("[$now] exception: ".$ex->getMessage(). "\n".$ex->getTraceAsString(),"active_error");
                    $qs->connect($qsconfig['host'],$qsconfig['port']);
                    if($qs->connected){
                        glog::info("[$now] re-connected", "active_error");
                    }else{
                        glog::info("[$now] connection lost","active_error");
                        break;
                    }
                }
            }
        }
    }


    /**
     * 活动队列消息处理
     */
    public function action_activity_queue(){
        $max_running_time = 1800;
        $qs               = new QueueService('notice_activity_queue');
        $qsconfig         = DbConfig::getDbConfig('queue');
        $qs->connect($qsconfig['host'],$qsconfig['port']);
        if($qs->connected){
            $start_t    = time();
            $process_id = getmypid();
            while(1){
                try{
                    $now   = date('Y-m-d H:i:s');
                    while($qs->length()){
                        try{
                            $info = $qs->lrange(0,0); //只是读，在成功处理以后才把消息删除。不成功会继续重试
                            model_ActivityUtil::processActMsg($info);
                            $qs->pop();
                        }catch(Exception $ee){
                            glog::info("[$now], msg = [ ".json_encode($info)." ] exception: ".$ee->getMessage(). "\n".$ee->getTraceAsString(),"active_error");
                            $subject = "activity_queue_error_".P_PLATFORM."_".date('Y-m-d H:i:s', time());
                            $email_content = "活动发奖队列错误: ". $ee->getMessage() . "\n" . $ee->getTraceAsString()."\n".json_encode($info);
                            model_Util::sendemail('wangkun@playcrab.com',$subject,$email_content);
                        }
                    }
                    sleep(1);
                    if(time()-$start_t > $max_running_time){
                        exit(0);
                    }
                }catch(Exception $ex){
                    glog::info("[$now] exception: ".$ex->getMessage(). "\n".$ex->getTraceAsString(),"active_error");
                    $qs->connect($qsconfig['host'],$qsconfig['port']);
                    if($qs->connected){
                        glog::info("[$now] re-connected", "active_error");
                    }else{
                        glog::info("[$now] connection lost","active_error");
                        break;
                    }
                }
            }
        }
    }
    public function action_lunjian(){
        $section = $this->get_param('sec');
        if(empty($section)){
            die("需要分区参数sec");
        }
        model_PVPUtil::createLunjian($section);
    }


         /**
	 * 盟战的三个奖励，最终击杀奖励，门派积分排名奖励，联盟积分排名奖励
	 * @return type
	 * @throws Exception 
	 */
	public function action_leaguewar_award(){

		//分区
                $section_list = array_keys(getApp()->getSectionConfig());
		
		$now = getApp()->now;
		
		//平台
		$platform = $this->get_param('platform');
		
		if(empty($section_list)){
			throw new Exception('section error');
			return;
		}
		
                //当前周
		//$week = date("W",$now);
                
		$join_end_time = model_LeagueDataBase::getBaoMingEndTime();
		$week = date("W", $join_end_time);

                 $box = PL_Config_Numeric::get('jiaofeilixiang');
                 $item = PL_Config_Numeric::get('item');
                 $translate = PL_Config_Numeric::get('translate');
                $redis = model_LeagueDataBase::getLeagueWarRedis();
                
                
                foreach($section_list as $sec){
                    
                    $men_key = LeagueWar::menpai_score_rank_rpre.$sec."_".$week;
		
                    $league_key = LeagueWar::league_score_rank_rpre.$sec."_".$week;

                    $boss_key = LeagueWar::leaguewar_info_pre.$sec."_".$week."_".$lid;
         
                    $boss_info_key = LeagueWar::bossinfo.$sec."_".$week;
                    $men_rank_result = $redis->zRevRange($men_key, 0, 9);
                    $league_rank_result = $redis->zRevRange($league_key, 0, 4);
                    $killer_info = $redis->hmget($boss_info_key,array('killer_uid','killer_time','killer_name','damage_total')); 

					if(!empty($killer_info['killer_uid'])){
						$tag = $box['kill']['boxtag'];
						$killer_awards = array( 
							array('tag'=>$tag, 'num'=>1)
						);
						
                                                $rsend_key = "leaguewar_rsend_killer"."_".date("Y")."_".$week;
                                                $rsend_data = $redis->hget($rsend_key,$killer_info['killer_uid']);
                                                
                                                if(!$rsend_data){
                                                    
                                                    $tname = $item[$tag]['name'];
                                                    $tnametid = "tid#jishalixiang";
                                                    //exit;
                                                    $desc_str = model_Cdkey::descriptItems($killer_awards);
                                                    //$content='恭喜掌门在此次剿寇中最终击杀敌人帮助大家取得胜利，点击按钮领取【'.$tname.'】。（领取后请去包裹里查看）';
                                                    $content = model_Translate::getContent('tid#jiaofei_finial',array(model_Translate::getContent($tnametid)));
                                                    
                                                    //eval("\$content = \"$msg_str\";");
                                                    $rank_msg = array(
                                                            'type'=>'system',
                                                            'time'=>$now,
                                                            'status'=>'award',
                                                            'content'=>$content,
                                                    );

                                                    $rank_msg['key'] = uniqid();
                                                    $cdkey = model_Cdkey::gen($killer_awards, $desc_str);
                                                    $rank_msg['cdkey'] = $cdkey;
                                                    
                                                    $aret = model_Chat::sendMsg($rank_msg, $killer_info['killer_uid'], 'origin', $sec);

                                                    echo "type:killer,tag:$tag,uid:".$killer_info['killer_uid'].",section:$sec,cdkey:$cdkey\n";
                                                    
                                                    if($aret){
                                                        
                                                        $redis->hset($rsend_key,$killer_info['killer_uid'],$now);

                                                        $expire_time = strtotime(date("Y-m-d 00:00:00")) + 24 * 60 * 60 * 14;

                                                        if(!empty($rsend_key)){

                                                            $redis->expireAt($rsend_key, $expire_time);

                                                        }
                                                    }
                                                    
                                                }else{
                                                    
                                                    echo "rsend_killer:".$killer_info['killer_uid'].",$sec"."\n";
                                                    
                                                }
                                                
					 } 

					 $i = 1;	
					 foreach($men_rank_result as $key => $value){
						
						$uid = $value;
					    $box_key = 'personal_'.($key+1);
                                            
                                                $rsend_key = "leaguewar_rsend_men_rank"."_".$sec."_".date("Y")."_".$week;
                                                
                                                $rsend_data = $redis->hget($rsend_key,$uid);
                                                
                                                if($rsend_data){
                                                    
                                                    echo "rsend_menrank:$uid,$sec,$week";
                                                    continue;
                                                }
                                                
						$tag = $box[$box_key]['boxtag'];
						$tname = $item[$tag]['name'];
						$tnametid = "tid#menpailixiang{$i}";
						//$content="掌门在此次剿寇中大发神威，取得第".$i."名，点击按钮领取【".$tname."】。（领取后请去包裹里查看）";	
                                               
                                                $content = model_Translate::getContent('tid#jiaofei_p_rank',array($i,  model_Translate::getContent($tnametid)));
                                                //eval("\$content = \"$msg_str\";");
                                                
						$i++;
						$rank_msg = array(
							'type'=>'system',
							'time'=>$now,
							'status'=>'award',
							'content'=>$content,
						);
						$menpai_rank_awards = array( 
							array('tag'=>$tag, 'num'=>1)
						);
						$desc_str = model_Cdkey::descriptItems($menpai_rank_awards);

						$rank_msg['key'] = uniqid();
						$cdkey = model_Cdkey::gen($menpai_rank_awards, $desc_str);
						$rank_msg['cdkey'] = $cdkey;
						$xaret = model_Chat::sendMsg($rank_msg, $uid, 'origin', $sec);
                                                
                                                if($xaret){

                                                    $redis->hset($rsend_key,$uid,$now);
                                                
                                                    $expire_time = strtotime(date("Y-m-d 00:00:00")) + 24 * 60 * 60 * 14;

                                                    if(!empty($rsend_key)){

                                                        $redis->expireAt($rsend_key, $expire_time);

                                                    }
                                                       
                                                }

						echo "type:menpai,tag:$tag,uid:$uid,section:$sec,send_time:$now,cdkey:$cdkey\n";
       
                    }
                   
					if(!empty($killer_info['killer_uid'])){
					$j = 0;
                    foreach($league_rank_result as $key => $value){
                        
                        $lid =  $value;
                        $player_league = new model_LeagueDataBase($lid,$sec);
                        $league_data = $player_league->getFields(array("name","uids"));
						$box_key = 'league_'.($key+1);
						$j++;
						foreach($league_data["uids"] as $key => $value){
						
							$uid = $key;
                                                        
                                                        
                                                        $rsend_key = "leaguewar_rsend_league_rank"."_".$sec."_".date("Y")."_".$week;
                                                
                                                        $rsend_data = $redis->hget($rsend_key,$uid);
                                                        if($rsend_data){
                                                    
                                                            echo "rsend_leaguerank:$uid,$sec,$week";
                                                            continue;
                                                        }

							$tag = $box[$box_key]['boxtag'];
							//$tname = $item[$tag]['name'];
							$tnametid = "tid#lianmenglixiang{$j}";
							//$content="恭喜掌门所在联盟在此次剿寇中取得第".$j."名，点击按钮领取【".$tname."】。（领取后请去包裹里查看）";	
                                                        $content = model_Translate::getContent('tid#jiaofei_l_rank',array($j,  model_Translate::getContent($tnametid)));
                                                        //eval("\$content = \"$msg_str\";");
							$rank_msg = array(
								'type'=>'system',
								'time'=>$now,
								'status'=>'award',
								'content'=>$content,
							);
							$league_rank_awards = array( 
								array('tag'=>$tag, 'num'=>1)
							);
						
							$desc_str = model_Cdkey::descriptItems($league_rank_awards);

							$rank_msg['key'] = uniqid();
							$cdkey = model_Cdkey::gen($league_rank_awards, $desc_str);
							$rank_msg['cdkey'] = $cdkey;
							$laret = model_Chat::sendMsg($rank_msg, $uid, 'origin', $sec);
                                                        
                                                        if($laret){
                                                            $redis->hset($rsend_key,$uid,$now);

                                                            $expire_time = strtotime(date("Y-m-d 00:00:00")) + 24 * 60 * 60 * 14;

                                                               if(!empty($rsend_key)){

                                                                   $redis->expireAt($rsend_key, $expire_time);

                                                               }
                                                           
                                                        }

							echo "type:league,tag:$tag,uid:$uid,section:$sec,send_time:$now,cdkey:$cdkey\n";
						}
                        
                    }
					}
                    
                }
		
	}
        

	public function action_init_world_boss(){
        $section = $this->get_param('sec');

        $specify_date = $this->get_param('date');//指定日期，格式：20121030，代表2012年十月30号

		if(empty($section)){
			$section_list = array_keys(getApp()->getSectionConfig());
		}else{
			$section_list = (array)$section;
		}
		if(empty($section_list)){
			throw new Exception('section error');
			return;
		}
		$now = getApp()->now;
        $redis = DbConfig::getRedis('worldboss');
		$redis_set = DbConfig::getRedis('cache');
		$world_boss_conf =  PL_Config_Numeric::get('worldboss');
		if(empty($specify_date)){
			$today = strtotime(date('Ymd', strtotime("today")));
			$yesterday = date('md', $today - 24 * 60 * 60);
		}else{
			$today = strtotime($specify_date);
			$yesterday = date('md', $today - 24 * 60 * 60);
		}

		$config['worldboss_levelup_live_time'] = PL_Config_Numeric::get('setting', 'worldboss_levelup_live_time');
		$config['worldboss_hp_increase_live_time'] = PL_Config_Numeric::get('setting', 'worldboss_hp_increase_live_time'); 
		$config['worldboss_hp_decrease_live_time'] = PL_Config_Numeric::get('setting', 'worldboss_hp_decrease_live_time'); 
		$config['worldboss_hp_max_ratio'] = PL_Config_Numeric::get('setting', 'worldboss_hp_max_ratio'); 
		$config['worldboss_hp_min_ratio'] = PL_Config_Numeric::get('setting', 'worldboss_hp_min_ratio'); 
		$config['worldboss_init_hp_args'] = PL_Config_Numeric::get('setting', 'worldboss_init_hp_args');

		$init_args = $config['worldboss_init_hp_args'];

		try{
			foreach($section_list as $section){
				foreach($world_boss_conf as $tag => $conf){
					$activityConfig = getApp()->getactivityconfig("act_worldboss_$tag");
					if($activityConfig){
						//print_r($activityConfig);
						if($now < $activityConfig['start_t'] || $now > $activityConfig['end_t']){
							echo "[分区: $section][时间: " . date('md', $today) ."][boss: $tag] 不在活动时间内,未初始化\n";
							continue;
						}
					}

					$boss_today_key  = model_Util::getBossKey($tag, $section, date('md', $today));

					$boss_yesterday_rank_key = "worldboss_rank_{$tag}_{$section}_{$yesterday}"; 
					$is_attacked = $redis->zcard($boss_yesterday_rank_key);

					//echo "boss_today_key = $boss_today_key\n boss_yesterday_rank_key = $boss_yesterday_rank_key\n is_attacked = $is_attacked\n";

					//活动时间
					$start_time = $conf['start_time'] + $today;
					$end_time   = $start_time + $conf['last_time'];

					//根据昨日情况计算今日的Boss属性
					$boss_yesterday_key  = model_Util::getBossKey($tag, $section, $yesterday);
					$boss_yesterday_info = $redis->hgetall($boss_yesterday_key);

					if(empty($boss_yesterday_info) || $boss_yesterday_info['boss_hp_max'] <= 0){ //第一次初始化
						//echo "sec = $section, tag = $tag, first init.\n";
                        if($tag == '1002'){
                            //年兽，血量同铜人
                            $ref_boss_today_key  = model_Util::getBossKey('1001', $section, date('md', $today));
error_log($ref_boss_today_key);
                            $ref_boss_info = $redis->hgetall($ref_boss_today_key);
                            $boss_level  = max(20, $ref_boss_info['boss_level']);
                            $boss_hp_max = max(1500000, $ref_boss_info['boss_hp_max']);
                        }else{
                            $boss_level  = $conf['default_level'];
                            $section_user_count = $redis_set->scard("total_user_count_device_{$section}");

                            if($section_user_count < 50){
                                $boss_hp_max = $conf['default_hp'];
                            }else{
                                $boss_hp_max = $section_user_count * ($init_args[0]/100000 * $init_args[1]/100000 * $init_args[2] * $init_args[3] 
                                    + $init_args[4]/100000 * $init_args[5]/100000 * $init_args[6] * $init_args[7]);
                            }
                        }
						//echo "max hp = $boss_hp_max\n";
					}else{
						$prev_level   = $boss_yesterday_info['boss_level'];
						$prev_hp_max  = $boss_yesterday_info['boss_hp_max'];
						$prev_kill_time  = $boss_yesterday_info['kill_time'];
						$prev_start_time = $boss_yesterday_info['start_time'];


						if(empty($prev_kill_time)){
							$prev_kill_time = $boss_yesterday_info['end_time'] + 1;//没有杀死boss
						}

						$boss_live_time = $prev_kill_time - $prev_start_time;

						if($boss_live_time < 0){
							return;
						}


						if($boss_live_time < $config['worldboss_hp_increase_live_time'] * 60){
							$boss_hp_max = $prev_hp_max * min($config['worldboss_hp_increase_live_time'] * 60 / $boss_live_time, $config['worldboss_hp_max_ratio']);
						}else if($boss_live_time > $config['worldboss_hp_decrease_live_time'] * 60 && $is_attacked){ //boss存活时间超过规定时间，且被人攻击过，血量会下降。
							$boss_hp_max = $prev_hp_max * max($config['worldboss_hp_decrease_live_time'] * 60 / $boss_live_time, $config['worldboss_hp_min_ratio']);
						}else{
							$boss_hp_max = $prev_hp_max;
						}

						if($boss_live_time <= $config['worldboss_levelup_live_time'] * 60){
							//15分内击杀，boss等级提升
							$boss_level = $prev_level + 1;
						}else{
							$boss_level = $prev_level;
						}
					}
                    $boss_level  = min($boss_level, 300); //限制boss不超过300级
					$boss_hp_max = max($boss_hp_max, 1500000);
					$boss_hp_max = ceil($boss_hp_max);

					$boss_init_info = array(
						'tag'           =>$tag,
						'start_time'    =>$start_time,
						'end_time'      =>$end_time,
						'boss_level'    =>$boss_level,
						'boss_hp_max'   =>$boss_hp_max,
						'boss_hp_left'  =>$boss_hp_max,
					);

					//echo "boss_today_key = $boss_today_key, boss_init_info = ";
					//print_r($boss_init_info);
					$redis->hmset($boss_today_key, $boss_init_info);
					echo "date: ".date('md', $today) ."; sec: $section; tag: $tag; start_time: $start_time; end_time: $end_time; boss_level: $boss_level; boss_hp_max: $boss_hp_max;\n";
				}

			}
		}catch(Exception $e){
			$ex_msg = $e->getMessage();
			$ex_trace_msg = $e->getTraceAsString();
			$excep_content = "{$ex_msg}\n{$ex_trace_msg}";
			$subject = "worldboss_init_bug_".P_PLATFORM."_".date('Y-m-d H:i:s', time());
			$email_content = "世界boss初始化错误. $excep_content";
			//model_Util::sendemail('op@playcrab.com,wangkun@playcrab.com,kf@playcrab.com',$subject,$email_content);
			model_Util::sendemail('wangkun@playcrab.com',$subject,$email_content);
		}

		$del_keys = $redis->keys("wor*");
		for($i=0; $i<=7; $i++){
			$day = date('md', strtotime("$i days ago"));
			$reserve_days[$day] = 1;
		}
		$redis->multi(Redis::MULTI);
		foreach($del_keys as $k){
			$sub_date = substr($k, -4);
			if($reserve_days[$sub_date]){
			}else{
				$redis->del($k);
			}
		}
		$redis->exec();
	}
	//

    public function action_check_cideng(){
        $maxuid = model_LoginUser::maxuid();
        $yesterday = date('ymd',strtotime('-1 days'));
        $today = date('ymd');

        $total_auth_yesterday = 0;
        $total_auth_yesterday_and_login_today = 0;
        for($uid=1;$uid<$maxuid;$uid++){
            $login_model = new model_LoginUser($uid);
            $login_model->get(array('pid'=>1,'_ct'=>1));
            $pid = $login_model['pid'];
            $auth_time = $login_model['_ct'];
            $auth_day = date('ymd',$auth_time);
            $player = new model_Player($uid,'s1');
            $login_t = $player->numberGet('base','login_t');
            $login_day = date('ymd',$login_t);
            echo "{$pid}\t\t{$uid}\t$auth_day\t$login_day\n";
            if($auth_day == $yesterday){
                $total_auth_yesterday += 1;
                if($login_day == $today){
                    $total_auth_yesterday_and_login_today += 1;
                }
            }
        }
        $radio = 100 * $total_auth_yesterday_and_login_today / $total_auth_yesterday ;
        print "$total_auth_yesterday_and_login_today / $total_auth_yesterday = $radio\n";
    }
    public function action_canzhang_set(){
        $section_config = getApp()->getSectionConfig();
        $section_ids = array_keys($section_config);
        $maxuid = model_LoginUser::maxuid();
        $redis = DbConfig::getRedis('duomiji');
        foreach($section_ids as $section_id){
            for($uid=1;$uid<=$maxuid;$uid++){
                $player = new model_Player($uid,$section_id);
                $data = $player->getFields(array(
                    'level',
                    'canzhang'
                ));
                if(is_array($data['canzhang'])){
                    foreach($data['canzhang'] as $canzhang_tag => $canzhang_num){
                        if($canzhang_num > 0){
                            $key = 'mj_'.$section_id.'_'.$canzhang_tag.'_'.$data['level'];
                            $redis->sadd($key,$uid);
                            echo "$uid\t$key\n";
                        }
                    }
                }
            }
        }
    }
    public function action_stat(){
        $pids = array(
            'jianghaiwunn',
            'mrh19840623',
            '953953',
        );
        //$maxuid = model_LoginUser::maxuid();
        $section_id = 's3';
        foreach($pids as $pid){
            $um = model_LoginUser::searchUniq('pid',$pid,true);
            if($um){
                $d = &$um->doc();
                $uid = $d['_id'];
                $player = new model_Player($uid,$section_id);
                $data = $player->getFields(array(
                    'level',
                    '_ll',
                ));
                echo "$pid,{$data['level']},{$data['_ll']['cd']}\n";
            }else{
                echo "$pid,0,0\n";
            }
        }
    }

    public function action_fill_stat(){
        global $redis2 ;
        $redis2 = DbConfig::getRedis('cache');
        $maxuid = model_LoginUser::maxuid();
        //$today = date('ymd',getApp()->now);
        $today = $this->get_param('day');
        if(empty($today)){
            die("need param [day]");
        }
        function add2set($key,$value){
            global $redis2;
            echo "$key,$value\n";
            $redis2->sadd($key,$value);
        }
        for($uid=1;$uid<=$maxuid;$uid++){
            $login_model = new model_LoginUser($uid);
            $login_model->get(array('pid'=>1,'_ld'=>1,'secs'=>1,'_ct'=>1,'_at'=>1));
            $d = $login_model->doc();

            $pid = $d['pid'];
            $device_id = $d['_ld']['device_id'];
            $create_day = date('ymd',$d['_ct']);
            $last_login_day = date('ymd',$d['_at']);

            echo "\n\n=========\n$uid,$pid,$device_id,$create_day,$last_login_day\n\n";
            add2set('total_user_count_pid_all',$pid);
            add2set('total_user_count_device_all',$device_id);
            if($create_day==$today){
                add2set("auth_pid_all_{$today}", $pid);
                add2set("auth_device_all_{$today}", $device_id);
            }
            if($create_day==$today || $last_login_day == $today){
                add2set("active_pid_all_{$today}", $pid);
                add2set("active_device_all_{$today}", $device_id);
            }
            echo "\n";
            if(empty($d['secs'])){
                $d['secs'] = array();
            }
            foreach($d['secs'] as $section_id=>$timestamp){
                $day = date('ymd',$timestamp);
                echo "$section_id,$day; ";
            }
            echo "\n";
            echo "\n";

            foreach($d['secs'] as $section_id=>$timestamp){
                $day = date('ymd',$timestamp);
                add2set("total_user_count_pid_{$section_id}",$pid);
                add2set("total_user_count_device_{$section_id}",$device_id);
                if($day==$today){
                    add2set("active_pid_{$section_id}_{$today}", $pid);
                    add2set("active_device_{$section_id}_{$today}", $device_id);
                    if($create_day == $today){
                        add2set("auth_pid_{$section_id}_{$today}", $pid);
                        add2set("auth_device_{$section_id}_{$today}", $device_id);
                    }
                }
            }
        }
    }

    /**
     * 血战排行奖励发送
     */
    public function action_bloodyrank(){
        $app = app();
        $now = $app->now;
        $last_day = date("Ymd",$now-86400);
        $section_config = $app->getSectionConfig();
        //繁体、简体都上血战
        $plats = array("appstore","gamecomb","ios91","iosky","iospp","iostb","mix","qqandroid","wuxia","kunlun","appstoretw","dev","winphone","xp");
        
        $section_ids = array_keys($section_config);
        $rank_reward_configs = PL_Config_Numeric::get("xuezhan/xuezhanrankreward");
        $zhenrong_hash = array(
            '5'=>"五人阵",
            '6'=>"六人阵",
            '7'=>"七人阵",
            '8'=>"八人阵",
        );
        if(P_PLATFORM == "kunlun" || P_PLATFORM == 'appstoretw'){
            $zhenrong_hash = array(
                '5'=>"五人陣",
                '6'=>"六人陣",
                '7'=>"七人陣",
                '8'=>"八人陣",
            );
        }
        if(P_PLATFORM == "vietnam"){
            $zhenrong_hash = array(
               '5'=>"5 người",
               '6'=>"6 người",
               '7'=>"7 người",
               '8'=>"8 người",
            );
        }

            
        $cday = 59;
            
        
        $hmyredis = model_HeiMuYa::getRedis();

        ob_start();
        $xuezhan_chenghao = PL_Config_Numeric::get("chenghaosetting","xuezhan_rank");
        $redis = DbConfig::getRedis("lunjian");
        foreach($section_ids as $sec){
            
            $mergesec_info_key = "merge_sec_info_".$sec;

            $mergeinfo = $hmyredis->get($mergesec_info_key);
            
            //todo是否合区过
            
            if(!empty($mergeinfo)){
                
                $is_merge_sec = true;
                
            }else{
                
                $is_merge_sec = false;
            }
            
            
            if((P_PLATFORM == "gamecomb" && $sec == "s1000") || (P_PLATFORM == "ios91" && $sec == "s1000")){

                $cday = 63;

            }
            
            
            //合区的时间和距离的天数
            $merge_time = $mergeinfo['merge_end_time'];
            
            $dis_day = floor(($now-$merge_time)/86400);
            
            echo "==============   $sec:   =================\n\n";
            foreach($zhenrong_hash as $zhenrongsize=>$zhenrongname){
                
                $rank_tag = "bloodyrank_{$zhenrongsize}_{$last_day}_{$sec}";
                
                if($is_merge_sec == true && $dis_day <= $cday){
                    
                    $rank_reward_configs = PL_Config_Numeric::get("xuezhan/xuezhanrankrewardhefu");

                    $ranklist = $redis->zrevrange($rank_tag,0,39,"withscores");
                    
                    
                }else{
                    
                    $rank_reward_configs = PL_Config_Numeric::get("xuezhan/xuezhanrankreward");

                    $ranklist = $redis->zrevrange($rank_tag,0,19,"withscores");
                    
                }
                
                $current_place = 1;
                foreach($ranklist as $uid=>$score){

                    $continue_reward = 0;
                    
                    $ready_send_key = "bloodyrank_rsend_{$zhenrongsize}_{$last_day}_{$sec}";
                    $rsend_data = $redis->hget($ready_send_key,$uid);
                     
                    if($rsend_data){
                        echo "ready_send,$uid,$sec,$zhenrongsize\n";
                        continue ;  
                    }
                    
                     
                    try{
                        $player = new model_Player($uid,$sec);
                        //------
                        if($current_place == 1){
                            //check possible
                            $player_d = $player->getFields(array('vip','reslimit'));
                            if(($player_d['vip']['lvl'] < 3 && $player_d['reslimit']['count_dizi_4'] < 2) || model_Util::inBlacklist($uid)){
                                glog::info("$uid,$sec,$score",'bloodyrankwarning');
                                //$redis->zadd($rank_tag,-$score,$uid);
                                //model_Util::addBlacklist($uid,"血战数据有疑问，vip{$player_d['vip']['lvl']}, 排$current_place名");
                                $warningcontent= "[$uid]血战数据有疑问，vip{$player_d['vip']['lvl']}, 甲弟子数量{$player_d['reslimit']['count_dizi_4']}，排{$current_place}名 or 在黑名单中\n";
                                $mtitle = "xuezhan warning:".P_PLATFORM.";{$sec}";
                                model_Util::sendemail('op@playcrab.com,genshaomeng@playcrab.com,srgzyq@playcrab.com,wangyucheng@playcrab.com,kf@playcrab.com',$mtitle,$warningcontent);
                                //continue;
                            }
                        }
                        //------
                        $battleinfo = $player->objectGet('base','bloodybattle');
                        if(!isset($battleinfo['historyinfo']['last_rank_d'])){
                            $last_rank_d = date("Ymd",$battleinfo['historyinfo']['start_t']);
                        }else{
                            $last_rank_d = $battleinfo['historyinfo']['last_rank_d'];
                        }
                        $days = (strtotime($last_day)-strtotime($last_rank_d))/86400;
                        $battleinfo['historyinfo']['fighting_days'] = $days;
                        $battleinfo['historyinfo']['last_rank_d'] = $last_day;
                        if($days == 1){
                            $battleinfo['historyinfo']['continus_rank_days']++;
                        }else{
                            $battleinfo['historyinfo']['continus_rank_days'] = 0;
                        }

                        //连续进榜奖励
                        if(P_PLATFORM != 'gamevil'){//沒有翻譯，韓國
                        $continue_days =  $battleinfo['historyinfo']['continus_rank_days'];
                        if($continue_days > 0 && $continue_days < 5){
                           $continue_reward = 5;
                        }else if($continue_days >= 5){
                           $continue_reward = 10;
                        }
                        }

                        $battleinfo['historyinfo']['last_rank_d'] = $last_day;
                        $player->objectPut('bloodybattle','historyinfo',$battleinfo['historyinfo']);

                        //todo add xuezhan rank chenghao, with time
                        foreach($xuezhan_chenghao as $xuezhan_rank_chenghao){
                            if($current_place <= $xuezhan_rank_chenghao['rank']){
                                break;
                            }
                        }
                        $chenghao = array(
                            'tag'=>$xuezhan_rank_chenghao['chenghaotag'],
                            'level'=>1,
                            'create_t'=>$app->now,
                        );
                        $player->objectPut("chenghao",$chenghao['tag'],$chenghao);
                        $player->commit();
                        
                        //if($is_merge_sec && $dis_day <= $cday && $current_place > 20){
                          //  $awards = $rank_reward_configs[20]["reward_for_".$zhenrongsize];
                        //}else{
                            $awards = $rank_reward_configs[$current_place]["reward_for_".$zhenrongsize];
                        //}
                        
                        if($is_merge_sec && $dis_day <= $cday){
                            $econtent = "额外奖励20%元宝";
                            foreach($awards as $ak =>$av){
                                if($av['tag'] == "gem"){
                                    $awards[$ak]['num'] = $awards[$ak]['num'] + ceil($awards[$ak]['num'] * 0.2);
                                }
                            }
                        }
                        
                        $desc_str = model_Cdkey::descriptItems($awards);
                        $key = uniqid();
                        echo "$uid: ";
                        
                        //如果有额外奖励元宝，需要从$awards中减去额外元宝，但是真实增加并不改变
                        //下面获取描述时是没有加进榜前
                        $msg_str = model_Translate::get('tid#xuezhan_reward');
                        eval("\$content = \"$msg_str\";");

                        //连续进榜奖励增加
                        if($continue_reward){
                            foreach($awards as $ak =>$av){
                                if($av['tag'] == "gem"){
                                    $awards[$ak]['num'] += $continue_reward;
                                }
                            }
                        }
                        //进榜奖励在这里拼装描述
                        if($continue_reward){
                           $extra_xuezhan_str = model_Translate::get('tid#act_xuezhan_continue_reward');
                           eval("\$extra_xuezhan_str = \"$extra_xuezhan_str\";");
                           $content .= $extra_xuezhan_str;
                        }
                        echo $content."\n";
                        //$now = getApp()->now;
                        $msg = array(
                            'key'=>$key,
                            'time'=>$now,
                            'content'=>$content,
                            'type'=>'system',
                        );
                        $msg['status'] = 'award';
                        //$awards包含进帮奖励的元宝
                        $cdkey = model_Cdkey::gen($awards,$desc_str,1,"bloodyrank_{$last_day}_{$sec}");
                        $msg['cdkey'] = $cdkey;
                        model_Chat::sendMsg($msg,$player->uid,'origin',$sec);

                        
                    
                        if(in_array(P_PLATFORM, $plats)){
                        
                            //开服活动血战排名奖励 start
                            $flag = 1;  //标记活动延后一天发放
                            $config = model_ActivityUtil::getBooldRankBonusData($sec,$flag);
                            //活动开启后推迟一天发放活动奖励  结束后保证发放最后一次活动奖励
                            $config['start_t'] = $config['start_t'] + 86400;
                            $config['end_t'] = $config['end_t'] + 86400;
                            //if(($app->now - $config['start_t']) >= 86400 && ($app->now - $config['end_t']) < 86400){
                            //print_r($config);exit();
                            if($now > $config['start_t'] && $now < $config['end_t']){
                                $awards_boold = array();
                                //只发排名前20
                                if($current_place <= 20){
                                    if(array_key_exists($current_place,$config['bonus'])){
                                        $awards_boold = $config['bonus'][$current_place];
                                    }else{
                                        if($current_place >=6 && $current_place <= 10)
                                            $config_key = '6-10';
                                        elseif($current_place >=11 && $current_place <= 15)
                                            $config_key = '11-15';
                                        elseif($current_place >=16 &&  $current_place <= 20)
                                            $config_key = '16-20';
                                        $awards_boold = $config['bonus'][$config_key];
                                    }
                                }  
                                $rmsg_str= model_Translate::get('tid#bloody_act_rank_award');
                                eval("\$rcontent = \"$rmsg_str\";");
                                model_Award::awardToUser($player->uid,$sec,$rcontent,$awards_boold);       
                            }
                            //开服活动血战排名奖励 end
                            
                        }
                        

                        $redis->hset($ready_send_key, $uid, $now); 
                        
                        $expire_time = strtotime(date("Y-m-d 00:00:00")) + 24 * 60 * 60 * 7;
                        $redis->expireAt($ready_send_key, $expire_time);

                        $current_place ++;
                        
                    }catch(Exception $excep){
                        
                        $ex_msg = $excep->getMessage();
                        $ex_trace_msg = $excep->getTraceAsString();
                        $excep_content = "{$ex_msg}\n{$ex_trace_msg}";
                        $subject = "血战发放出错_".P_PLATFORM."_".$rank_tag."_".$uid;
                        model_Util::sendemail('op@playcrab.com,wangyucheng@playcrab.com,srgzyq@playcrab.com,kf@playcrab.com',$subject,$content);
                        
                    }
                }
            }
        }

        // del ranks a week ago
        $last_week = $now - 86400*7;
        $last_week_str = date("Ymd",$last_week);
        echo ">>>>>>>>>>>>>>>>>>>>>>>>>delete ranks a week ago<<<<<<<<<<<<<<<<<<<<\n";
        foreach($section_ids as $sec){
            echo "==============   $sec:   =================\n\n";
            foreach($zhenrong_hash as $zhenrongsize=>$zhenrongname){
                $rank_tag = "bloodyrank_{$zhenrongsize}_{$last_week_str}_{$sec}";
                
                $ready_send_key = "bloodyrank_rsend_{$zhenrongsize}_{$last_week_str}_{$sec}";
                
                if($redis->del($ready_send_key)){
                    
                   echo "[delete_rsend] :$ready_send_key\n";

                }
                
                if($redis->del($rank_tag))
                    echo "[delete] :$rank_tag\n";
            }
        }
        $output_content = ob_get_contents();
        ob_end_clean();
        echo $output_content;
        $weekday = date("w",strtotime($last_day));
        $file_name = LOG_ROOT."/bloodyrank.$weekday.log";
        file_put_contents($file_name,$output_content);
        
        $config = getApp()->getActivityConfig("act_kfxuezhan");
        
        if($now >= ($config['start_t'] + 86400) && $now <= ($config['end_t'] + 86400) ){
            
             model_Crontab::kfbloodyrank();
             
        }
        
        
        
    }
    
    
    /**
     * 跨服血战排行奖励发送
     */
    function action_kfbloodyrank(){
        
        $config = getApp()->getActivityConfig("act_kfxuezhan");
        
        if($config['started']){
            model_Crontab::kfbloodyrank();
        }

    }

    public function action_test_reset_worldboss(){
        $redis = DbConfig::getRedis('worldboss');
        $world_boss_conf = PL_Config_Numeric::get('worldboss');

        $sections = array_keys(getApp()->getSectionConfig());

        foreach($world_boss_conf as $tag=>$conf){
            foreach($sections as $section){
                $yesterday = strtotime(date('Ymd',strtotime('-1 day')));
                $start_time = $conf['start_time'];
                $last_time  = $conf['last_time'];
                $now = getApp()->now;
                if($now > $yesterday + $start_time + $last_time){
                    $date = date("md", strtotime("today"));
                }else{
                    $date = date("md", strtotime("-1 days"));
                }

                $boss_key = "worldboss_boss_{$tag}_{$section}_{$date}";
                $rank_key = "worldboss_rank_{$tag}_{$section}_{$date}";
                $msg_key  = "worldboss_msg_{$tag}_{$section}_{$date}";

                $redis->del($boss_key);
                $redis->del($rank_key);
                $redis->del($msg_key);
            }
        }
        self::action_init_world_boss();
	}
	public function action_chongzhi(){
        $mon = getApp()->getPaymentMongoConnection();
        //$begin = 1351530000;
        $begin = strtotime("2012-12-02 01:00:00");
        $rows = $mon->find(array('status'=>StatusCode::payment_finished,'action'=>'recharge_gem','_sec'=>'s1','finish_t'=>array('$gte'=>$begin)),array(),array('_tm'=>1));
        //$rows = $mon->find(array('status'=>StatusCode::payment_finished,'action'=>'recharge_gem'),array(),array('_tm'=>1));
        $payments = array();
        $payments_check = array();
        foreach($rows as $r){
            $uid = $r['_u'];
            $section_id = $r['_sec'];

            if(0){
                if($r['finish_t']>=$begin){
                    if(empty($payments[$section_id][$uid])){
                        $player = new model_Player($uid,$section_id);
                        $payments[$section_id][$uid] = $player->numberGet('base','total_gem_added');
                    }
                    $payments[$section_id][$uid] += $r['agem'];
                }
                $payments_check[$section_id][$uid] += $r['agem'];
            }else{
                //echo json_encode($r)."\n";
                $player = new model_Player($uid,$section_id);
                $data = $player->getFields(array(
                    'vip','gem','total_gem_added','total_gem_rewarded','total_gem_rewarded2','total_gem_used'
                ));
                echo "before: $uid,$section_id,".json_encode($data)."\n";
                $player->process_payment2($r);
                foreach(array('vip','gem','total_gem_added','total_gem_rewarded','total_gem_rewarded2','total_gem_used') as $k){
                    $data2[$k] = $player->objectGet('base',$k);
                }
                echo "after: ".json_encode($data2)."\n";
                $player->commit();
                echo "======\n\n";
            }
        }
        /*
        foreach($payments as $section_id=>$xxx){
            foreach($xxx as $uid=>$added_gem){
                if($payments_check[$section_id][$uid]!=$added_gem){
                    echo "$section_id,$uid, right :{$payments_check[$section_id][$uid]}, $added_gem\n";
                }
            }
        }
         */
        /*
         */
        /*
        var_dump($payments);
        foreach($payments as $section_id=>$xxx){
            foreach($xxx as $uid=>$added_gem){
                $player = new model_Player($uid,$section_id);
                $total_gem_added = $player->numberGet('base','total_gem_added');
                //$total_gem_added + $added_gem 

                $rows = $mon->find(array('_sec'=>$section_id),'status'=>StatusCode::payment_finished,'action'=>'recharge_gem','finish_t'=>array('$gte'=>$begin)),array(),array('_tm'=>1));

            }
        }
         */
    }
	public function action_check_chongzhi(){
        $mon = getApp()->getPaymentMongoConnection();
        $rows = $mon->find(array('status'=>StatusCode::payment_finished,'action'=>'recharge_gem'),array(),array('_tm'=>1));
        $payments = array();
        $payments_check = array();
        foreach($rows as $r){
            $uid = $r['_u'];
            $section_id = $r['_sec'];

            $payments_check[$section_id][$uid] += $r['agem'];
        }
        foreach($payments_check as $section_id=>$xxx){
            foreach($xxx as $uid=>$added_gem){
                $player = new model_Player($uid,$section_id);
                $total_gem_added = $player->numberGet('base','total_gem_added');
                if($total_gem_added != $added_gem){
                    $login_model = new model_LoginUser($uid);
                    $login_model->get(array('pid'=>1,'istest'=>1));
                    $d = $login_model->doc();
                    echo "$uid,$section_id,$total_gem_added,$added_gem,{$d['pid']},{$d['istest']}\n";
                }
            }
        }
    }

    public function action_add_zhidian(){
        $uid = $this->get_param('uid');
        $sec = $this->get_param('sec');
        $items = array(
            array('tag'=>"qiyu_zhidian",'level'=>45,'num'=>1),
        );
        $desc = model_Cdkey::descriptItems($items);
        $msg = array(
            'type'=>'system',
            'key'=>uniqid(),
            'time'=>getApp()->now,
            'content'=>"$desc",
        );
        $msg['status'] = 'award';
        $cdkey = model_Cdkey::gen($items,$desc);
        $msg['cdkey'] = $cdkey;
        model_Chat::sendMsg($msg,$uid,'origin',$sec);
        echo "发送成功";
    }


    public function action_get_all_userlogininfo(){
        $maxuid = model_LoginUser::maxuid();
        for($uid=1;$uid<=$maxuid;$uid++){
            $login_model = new model_LoginUser($uid);
            $login_model->get(array('pid'=>1,'_ld'=>1,'secs'=>1,'_ct'=>1,'_at'=>1));
            $d = $login_model->doc();

            $pid = $d['pid'];
            $device_id = $d['_ld']['device_id'];
            $create_day = date('ymd',$d['_ct']);
            $last_login_day = date('ymd',$d['_at']);

            //echo "\n\n=========\n$uid,$pid,$device_id,$create_day,$last_login_day\n\n";

            if(!is_array($d['secs'])){
                continue;
            }
            foreach($d['secs'] as $section_id=>$timestamp){
                $player = new model_Player($uid,$section_id);
                $player_d = $player->getFields(array("_it","login_t","level","name","vip"));
                echo "uid=$uid,pid=$pid,sec=$section_id,device_id=$device_id,_ct={$d['_ct']},_it={$player_d['_it']},_at={$d['_at']},_lt={$player_d['login_t']},name={$player_d['name']},level={$player_d['level']},vip={$player_d['vip']['lvl']}\n";
            }
        }
    }



	//世界boss奖励发放
	public function action_worldboss_reward(){
        $section = $this->get_param('sec');
        $today   = $this->get_param('date');//指定日期，格式：1030，代表十月30号
		if(empty($section)){
			$section_list = array_keys(getApp()->getSectionConfig());
		}else{
			$section_list = (array)$section;
		}
		if(empty($section_list)){
			throw new Exception('section error');
			return;
		}
		$now = getApp()->now;
        $app = getApp();
        $redis = DbConfig::getRedis('worldboss');

		$redis_set = DbConfig::getRedis('cache');
		if(empty($today)){
			$today = date('md', $now);
		}
		$rank_keys = $redis->keys("worldboss_rank_*_{$today}");
		$redis->multi(Redis::MULTI);
		foreach($rank_keys as $rank_key){
			$redis->zrevrange($rank_key, 0, 9);
		}
		$ranks_data = $redis->exec();
		echo "====[ ranklist $today ]===\n";
		print_r($rank_keys);
		print_r($ranks_data);
		echo "====[ end ranklist ]===\n";


		$world_boss_conf = PL_Config_Numeric::get('worldboss');
        $pozhen_chenghao = PL_Config_Numeric::get("chenghaosetting","pozhen_rank");
		//echo "================[ date = $today ]===============\n";

        //新年活动，培养丹翻倍
        $activity = $app->getActivityConfig('act_worldboss_double');
        if($activity['started']){
            $gain_factor = 2; //双倍奖励
        }else{
            $gain_factor = 1;
        }

		try{
		foreach($section_list as $section){
			foreach($world_boss_conf as $tag => $conf){
				if($tag != '1001'){
					//continue;
				}
				$boss_key = "worldboss_boss_{$tag}_{$section}_{$today}";
				$rank_key = "worldboss_rank_{$tag}_{$section}_{$today}";
				echo "================[ $boss_key ]===============\n";
				$boss_info = $redis->hgetall($boss_key);

				if($boss_info['boss_hp_left'] > 0 || $boss_info['reward_time'] ){
					$dead = $boss_info['boss_hp_left'] > 0 ? "boss未死亡":"boss死亡";
					if($boss_info['reward_time']){
						$dead .= "补偿时间 : ". date('Y-m-d H:i:s', $boss_info['reward_time']) . "\n";
					}
					echo $dead."\n";
					continue;
				}
                $boss_name = model_Translate::getTransConfig($conf['bossname']);
				//排行榜奖励 传书
				$rank_list = $redis->zrevrange($rank_key, 0, 9);
				$boss_level    = $boss_info['boss_level'];
				$default_level = $conf['default_level'];
				//echo "boss_level = $boss_level, default_level = $default_level\n";
				foreach($rank_list as $top_rank => $top_uid){
                    if($top_rank === 0){ //新年活动，首名培养丹翻倍
                        $reward_rank_pyd   = $conf['rank_pyd'][$top_rank] * $gain_factor;
                        echo "reward_rank_pyd_top = $reward_rank_pyd\n";
                        echo "gain_factor = $gain_factor\n";
                    }else{
                        $reward_rank_pyd   = $conf['rank_pyd'][$top_rank];
                    }
					$reward_rank_money = $conf['rank_money'][$top_rank] * ($boss_level - $default_level + 5);
					$wb_rank = $top_rank+1;

					$top_user_info = model_CacheUtil::getInfoByIdUseCache($top_uid,$section,array('level'));
					print_r($top_user_info);
					$worldboss_zhidian_offset = PL_Config_Numeric::get('setting','worldboss_zhidian_level_offset');
                                        
                    $qzhidian_level = max(1, $top_user_info['level'] * 3 - 34);
                    $qzhidian_level = min($qzhidian_level, 140);
                                        
					$rank_awards = array(
						array('tag'=>'601019','num'=>$reward_rank_pyd),
						array('tag'=>'money' ,'num'=>$reward_rank_money),
						//array('tag'=>'qiyu_zhidian','level'=>max(1, $top_user_info['level']*3+$worldboss_zhidian_offset),'num'=>1),
						array('tag'=>'qiyu_zhidian','level'=>$qzhidian_level,'num'=>1),
					);
					echo "[reward] Date = $today; sec = $section; Rank = $wb_rank; uid = $top_uid; 培养丹 = $reward_rank_pyd; money = $reward_rank_money; zhidian = {$rank_awards[2]['level']}; \n";
					//print_r($rank_awards);
					//echo "[reward] <<<< \n";

					$desc_str = model_Cdkey::descriptItems($rank_awards);
					$rank_msg = array(
						'type'=>'system',
						'key'=>uniqid(),
						'time'=>getApp()->now,
						//'content'=>"恭喜掌门在群雄破阵[$boss_name]时表现神勇，进入排行榜第{$wb_rank}名。额外奖励：$desc_str",
					);
                    /*
                    if(P_PLATFORM == "kunlun"){
                        //$rank_msg['content'] = "恭喜掌門在群雄破陣[$boss_name]時表現神勇，進入排行榜第{$wb_rank}名。額外獎勵：$desc_str";
                    }
                     */
                    $msg_str = model_Translate::get('tid#worldboss_reward');
                    eval("\$content = \"$msg_str\";");
                    $rank_msg['content'] = $content;
					$rank_msg['status'] = 'award';
					$cdkey = model_Cdkey::gen($rank_awards,$desc_str);
					$rank_msg['cdkey'] = $cdkey;
					model_Chat::sendMsg($rank_msg, $top_uid,'origin',$section);

                    //
                    foreach($pozhen_chenghao as $pozhen_rank_chenghao){
                        if($top_rank < $pozhen_rank_chenghao['rank']){
                            break;
                        }
                    }
                    $chenghao = array(
                        'tag'=>$pozhen_rank_chenghao['chenghaotag'],
                        'create_t'=>$now,
                        'level'=>1,
                    );
                    $player = new model_Player($top_uid,$section);
                    if($tag != '1002'){
                        $player->objectPut('chenghao',$pozhen_rank_chenghao['chenghaotag'],$chenghao);
                    }
                    $player->commit();
                    echo "add chenghao [{$pozhen_rank_chenghao['chenghaotag']}]\n";
				}
				if($redis->exists($boss_key))
					$redis->hset($boss_key, 'reward_time', $now);
				//echo " end ====\n";
			}
		}
		}catch(Exception $e){
			$ex_msg = $e->getMessage();
			$ex_trace_msg = $e->getTraceAsString();
			$excep_content = "{$ex_msg}\n{$ex_trace_msg}";
			$subject = "worldboss_reward_bug_".P_PLATFORM."_".date('Y-m-d H:i:s', time());
			$email_content = "世界boss发奖励错误"." $excep_content\n";
			//model_Util::sendemail('op@playcrab.com,wangkun@playcrab.com,kf@playcrab.com',$subject,$email_content);
			model_Util::sendemail('wangkun@playcrab.com',$subject,$email_content);
		}
    }


    //Temp
    public function action_do_something(){
        $section = $this->get_param('sec');
        $redis = DbConfig::getRedis('lunjian');
        $rank_key = model_PVPUtil::getLunjianListID($section);
        echo "rank_key:$rank_key\n";
        $rank_max_key = model_PVPUtil::getLunjianMaxUserID($section);
        $fill_id = $redis->hget($rank_max_key, 'npc_max');
        $fill_id++;
        $rank = 1625;
        $bool = self::makeNpc($redis,$rank,$section,$fill_id);
        var_dump($bool);
        $redis->hset($rank_max_key, 'npc_max', $fill_id);
        echo "Console:设置NPC最大：{$fill_id}\n";
    }

    //检测论剑中玩家排名数据不对应
    public function action_check_rank_error(){
        $section = $this->get_param('sec');
        if(!$section){
            throw new Exception("section = $section error");
            return;
        }
        $redis = DbConfig::getRedis('lunjian');
        $now = time();

        echo "******* section = $section *******\n";
        $rank_key = model_PVPUtil::getLunjianListID($section);
        echo "rank_key:$rank_key\n";
        $rank_max_key = model_PVPUtil::getLunjianMaxUserID($section);

        $total = $redis->hget($rank_max_key, 'max');
        $fill_id = $redis->hget($rank_max_key, 'npc_max');
        $fill_id = $fill_id?:0;

        echo "npc最大：fill id = $fill_id ,论剑数量：total = $total\n";
        if(!$total){
            die("错误的玩家max");
        }

        $list = $redis->hgetall( $rank_key );
        $sum = count($list);
        echo "论剑现总数：".$sum."\n";
        ksort($list);


        $record = array();
        $total = 1000;//只校验1000名内的
        for($i=1;$i<=$total;$i++){
            $uid = $list[$i];
            if(empty($uid)){//空位置
                echo "--Error:找到了空位置：{$i}\n"; 
                continue;
            }
            //对比自己的数据
            $action_user_info_key = model_PVPUtil::getLunjianUserID($section, $uid);
            $action_user_info = $redis->hgetall($action_user_info_key);
            if($action_user_info['rank'] != $i){
                echo "KEY = $action_user_info_key\n";
                echo "检测到玩家{$uid}数据异常，排名为{$i},但个人数据为{$action_user_info['rank']},开始修复...\n";
                $action_user_info['rank'] = $i;
                $redis->hmset($action_user_info_key, $action_user_info);//设置自己的信息 论剑位置为NPC位置
                $redis->hset($rank_key, $i, $uid);//设置论剑排行 
                echo "成功{$rank_key}设置{$i}位置为{$uid}\n";
            }
        }
    }

    //查看重复的uid
    public function action_show_repeat_lunjian(){
        $section = $this->get_param('sec');
        if(!$section){
            throw new Exception("section = $section error");
            return;
        }
        $redis = DbConfig::getRedis('lunjian');
        $now = time();

        echo "******* section = $section *******\n";
        $rank_key = model_PVPUtil::getLunjianListID($section);
        echo "rank_key:$rank_key\n";
        $rank_max_key = model_PVPUtil::getLunjianMaxUserID($section);

        $total = $redis->hget($rank_max_key, 'max');
        $fill_id = $redis->hget($rank_max_key, 'npc_max');
        $fill_id = $fill_id?:0;

        echo "npc最大：fill id = $fill_id ,论剑数量：total = $total\n";
        if(!$total){
            die("错误的玩家max");
        }

        $list = $redis->hgetall( $rank_key );
        $sum = count($list);
        echo "论剑现总数：".$sum."\n";
        ksort($list);


        $record = array();
        for($i=1;$i<=$total;$i++){
            $uid = $list[$i];
            if(empty($uid)){//空位置
                echo "--Error:找到了空位置：{$i}\n"; 
                continue;
            }
            //记录每个人物出现的次数 和 当前rank
            $record[$uid]['cnt']++;
            $record[$uid]['rank'][] = $i;
        }

        foreach($record as $uid=>$v){
            if($v['cnt']>=2){
                echo "玩家{$uid}出现{$v['cnt']}次.\n";
            }
        }

    }


    /**
     * 检测论剑中重复的人 or NPC
     */
    public function action_check_repeat_lunjian(){
        $section = $this->get_param('sec');
        $action_uid = $this->get_param('uid');
        if(!$section){
            throw new Exception("section = $section error");
            return;
        }
        $redis = DbConfig::getRedis('lunjian');
        $now = time();

        echo "******* section = $section *******\n";
        $rank_key = model_PVPUtil::getLunjianListID($section);
        echo "rank_key:$rank_key\n";
        $rank_max_key = model_PVPUtil::getLunjianMaxUserID($section);

        $total = $redis->hget($rank_max_key, 'max');
		$fill_id = $redis->hget($rank_max_key, 'npc_max');
        $fill_id = $fill_id?:0;

		echo "npc最大：fill id = $fill_id ,论剑数量：total = $total\n";
        if(!$total){
            die("错误的玩家max");
        }

        //var_dump($redis->hget($rank_key,109));

        $list = $redis->hgetall( $rank_key );
        $sum = count($list);
        echo "论剑现总数：".$sum."\n";
        ksort($list);

        $temp_fill_id = $fill_id;//验证用

        if($fill_id == 0){//NPCmax没有设置需要查询
            for($i=1;$i<=$total;$i++){
                $uid = $list[$i];
                if(strpos($uid,'npc') !== false){
                    $fill_id++;
                }
            }
            $fill_id+=200;//为了避免重复 统一+200
            echo "重新统计NPC最大：fill id = $fill_id ,论剑数量：total = $total\n";
        }

        //配置是否在论剑增加玩家
        $action = false;
        if($action_uid){
            $action = true;
        }

        $flag = 0;
        $record = array();
        for($i=1;$i<=$total;$i++){
            $uid = $list[$i];
            if(empty($uid)){//空位置
                echo "--Error:找到了空位置：{$i}\n"; 
                continue;
            }

            //将不再排行的玩家加入最靠前的位置，替换掉一个NPC
            if($action && strpos($uid,'npc') !== false && $flag == 0){
                echo "位置：{$i}为NPC {$uid}\n";
                $flag = 1;
                $action_user_info_key = model_PVPUtil::getLunjianUserID($section, $action_uid);
                echo "KEY = $action_user_info_key\n";
                $action_user_info = $redis->hgetall($action_user_info_key);
                //print_r($user_info);
                $action_user_info['rank'] = $i;
                $redis->hmset($action_user_info_key, $action_user_info);//设置自己的信息 论剑位置为NPC位置
                $redis->hset($rank_key, $i, $action_uid);//设置论剑排行 
                echo "成功设置{$i}位置为{$action_uid}\n";
                $uid = $action_uid; 
            }
            if($action_uid) continue;
    

            //记录每个人物出现的次数 和 当前rank
            $record[$uid]['cnt']++;
            $record[$uid]['rank'][] = $i;
                
            if($record[$uid]['cnt'] >= 2 && count($record[$uid]['rank'])>1){
                echo "=======当前位置{$i}\n";
                echo "--Warning:{$uid}在".json_encode($record[$uid]['rank'])."多个位置出现\n";
                $user_info_key = model_PVPUtil::getLunjianUserID($section, $uid);
                //echo "KEY = $user_info_key\n";
                $user_info = $redis->hgetall($user_info_key);
                echo "玩家{$uid}的真实数据：".json_encode($user_info)."\n";
                
                //判断玩家当前rank是否在重复列表里
                if(!in_array($user_info['rank'],$record[$uid]['rank'])){
                    echo "rank没在列表中,将列表的rank都重置为新的NPC\n";
                    foreach($record[$uid]['rank'] as $k=>$rank){
                        $fill_id++;
                        if(!self::makeNpc($redis,$rank,$section,$fill_id)){
                            die("--Error!");
                        }
                        unset($record[$uid]['rank'][$k]);//删除已经修复的
                        $record[$uid]['cnt']--;

                        echo "--Finish!设置完成\n";
                        $tuid = $redis->hget($rank_key,$rank);
                        echo "--DEBUG:位置{$rank}--UID:{$tuid}\n";
                    }
                }else{
                    $fill_id++;
                    $delkey = array_search($user_info['rank'],$record[$uid]['rank']);
                    unset($record[$uid]['rank'][$delkey]);
                    $rank = array_shift($record[$uid]['rank']);
                    if(!$rank){
                        echo "delkey:{$delkey}---".$rank."\n";
                        die("rank:{$rank}错误!!");
                    }
                    echo "根据玩家数据，将{$rank}位置置为NPC...\n";
                    if(!self::makeNpc($redis,$rank,$section,$fill_id)){
                        die("--Error!");
                    }
                    echo "--Finish!设置完成\n";
                    $record[$uid]['cnt']--;
                    $tuid = $redis->hget($rank_key,$rank);
                    echo "--DEBUG:位置{$rank}--UID:{$tuid}";
                }
                echo "=======\n";
            }
        }

        if($temp_fill_id != $fill_id){
            //保存NPC最大
            $redis->hset($rank_max_key, 'npc_max', $fill_id);
            echo "Console:设置NPC最大：{$fill_id}\n";
        }
    }
    
    //添加NPC到论剑数据
    public function action_check_npc_lunjian(){
        $section = $this->get_param('sec');
		if(!$section){
			throw new Exception("section = $section error");
			return;
		}
        $redis = DbConfig::getRedis('lunjian');
		$now = time();

		echo "******* section = $section *******\n";
		$rank_key = model_PVPUtil::getLunjianListID($section);
        echo "rank_key:$rank_key\n";
		$rank_max_key = model_PVPUtil::getLunjianMaxUserID($section);
        
        $total = $redis->hget($rank_max_key, 'max');
		$fill_id = $redis->hget($rank_max_key, 'npc_max');
		if(!$fill_id){
			$fill_id = 0;
		}
		echo "cpc最大：fill id = $fill_id ,玩家最大：total = $total\n";
        if(!$total){
            die("错误的玩家max");
        }
        $list = $redis->hgetall( $rank_key );
        $sum = count($list);
        echo "论剑现总数：".$sum."\n";
        ksort($list);
        //将无人占位的位置 填充NPC
        for($i=1;$i<=$total;$i++){
            if(empty($list[$i])){//空位置
                echo "位置{$i}为空\n";
            }
        }


    }
    //添加NPC到论剑数据
    public function action_insert_npc_lunjian(){
        $section = $this->get_param('sec');
		if(!$section){
			throw new Exception("section = $section error");
			return;
		}
        $redis = DbConfig::getRedis('lunjian');
		$now = time();

		echo "******* section = $section *******\n";
		$rank_key = model_PVPUtil::getLunjianListID($section);
        echo "rank_key:$rank_key\n";
		$rank_max_key = model_PVPUtil::getLunjianMaxUserID($section);
        
        $total = $redis->hget($rank_max_key, 'max');
		$fill_id = $redis->hget($rank_max_key, 'npc_max');
		if(!$fill_id){
			$fill_id = 0;
		}
		echo "cpc最大：fill id = $fill_id ,玩家最大：total = $total\n";
        if(!$total){
            die("错误的玩家max");
        }

        //var_dump($redis->hget($rank_key,109));

        $list = $redis->hgetall( $rank_key );
        $sum = count($list);
        echo "论剑现总数：".$sum."\n";
        ksort($list);
        //将无人占位的位置 填充NPC
        for($i=1;$i<=$total;$i++){
            if(empty($list[$i])){//空位置
                echo "设置{$i}为NPC...\n";
                $rank = $i;//每个空位置 都是一个新的论剑排名
                $fill_id++;
                $uid_new = "npc.".$fill_id;
                echo "uid new = $uid_new\n";
                //设置论剑排名位置为新NPC
                $redis->hset($rank_key, $rank, $uid_new);
                $uid_new_renwuname = model_Util::randomName();

                //获取NPC配置
                $renwutag_conf = PL_Config_Numeric::get('lunjian-npc-rule');
                $renwu_ranks = array_keys($renwutag_conf);
                array_push($renwu_ranks, $rank);
                sort($renwu_ranks);
                $index = min(array_search($rank, $renwu_ranks), count($renwu_ranks));
                $index = max(0, $index -1);
                $renwutag = $renwutag_conf[$renwu_ranks[$index]]['renwutag'];

                $uid_new_info = array(
                    'rank'=>$rank,
                    'rank_t'=>$now,
                    'name'=>$uid_new_renwuname,
                    'renwutag'=>$renwutag,
                );
                //echo "新NPC info new = \n";
                //print_r($uid_new_info);

                $new_user_info_key = model_PVPUtil::getLunjianUserID($section, $uid_new);
                echo "new user info key = $new_user_info_key\n";
                $redis->hmset($new_user_info_key, $uid_new_info);
                echo "--Finish:设置完毕位置{$rank}设置为:{$renwutag};name:{$uid_new_renwuname} \n";
                $fill_id++;
            }
        }
        echo "NPC现在数量：$fill_id\n";
        $redis->hset($rank_max_key, 'npc_max', $fill_id);
        echo "设置NPC完毕\n"; 
        //print_r($list);
    
    }

    //获取NPC配置
    private static function getNpcConfig($rank){
        $renwutag_conf = PL_Config_Numeric::get('lunjian-npc-rule');
        $renwu_ranks = array_keys($renwutag_conf);
        array_push($renwu_ranks, $rank);
        sort($renwu_ranks);
        $index = min(array_search($rank, $renwu_ranks), count($renwu_ranks));
        $index = max(0, $index -1);
        $renwutag = $renwutag_conf[$renwu_ranks[$index]]['renwutag'];
        return $renwutag;
    } 

    //创建一个NPC
    private static function makeNpc($redis,$rank,$section,$fill_id){
        $rank_max_key = model_PVPUtil::getLunjianMaxUserID($section);
        $rank_key = model_PVPUtil::getLunjianListID($section);
        $uid_new = "npc.".$fill_id;
		//echo "uid new = $uid_new\n";
		$redis->hset($rank_key, $rank, $uid_new);
		$uid_new_renwuname = model_Util::randomName();

        $renwutag = self::getNpcConfig($rank);  
        $now = time();
		$uid_new_info = array(
            'uid'=>$uid_new,
            'enter_t'=>$now,
			'rank'=>$rank,
			'rank_t'=>$now,
			'name'=>$uid_new_renwuname,
			'renwutag'=>$renwutag,
		);
        //echo "info new =";
		//print_r($uid_new_info);

        foreach($uid_new_info as $k=>$v){
            if(empty($v)){
                echo "数据错误：{$k}\n";
                return false;
            }
        }

		$new_user_info_key = model_PVPUtil::getLunjianUserID($section, $uid_new);
		echo "new user info key = $new_user_info_key\n";
		$redis->hmset($new_user_info_key, $uid_new_info);
        return true;
    }


	//替换论剑某个位置为npc
	public function action_replace_lunjian(){
        $section = $this->get_param('sec');
		$rank = $this->get_param('rank');
		if(!$section || !$rank){
			throw new Exception("section = $section ; rank = $rank error");
			return;
		}
        $redis = DbConfig::getRedis('lunjian');
		$now = time();

		echo "******* section = $section *******\n";
		$rank_key = model_PVPUtil::getLunjianListID($section);
		$rank_max_key = model_PVPUtil::getLunjianMaxUserID($section);
		$uid = $redis->hget($rank_key, $rank); // rank->uid
		$user_info_key = model_PVPUtil::getLunjianUserID($section, $uid);
		$udata = $redis->hgetall($user_info_key);

		$rank_max_key = model_PVPUtil::getLunjianMaxUserID($section);
		$total = $redis->hget($rank_max_key, 'max');
		$fill_id = $redis->hget($rank_max_key, 'npc_max');
		if(!$fill_id){
			$fill_id = 0;
		}
		echo "fill id = $fill_id ,total = $total\n";

		if($udata['rank'] !== $rank){
			echo "uid : $uid => rank = $rank, rank[in user info] = {$udata['rank']}\n";
			var_dump($udata['rank']);
			var_dump($rank);
			var_dump($udata['rank'] !== $rank);
			return;
		}
		$redis->del($user_info_key);

		$uid_new = "npc.".$fill_id++;
		echo "uid new = $uid_new\n";
		$redis->hset($rank_key, $rank, $uid_new);
		$uid_new_renwuname = model_Util::randomName();

		$renwutag_conf = PL_Config_Numeric::get('lunjian-npc-rule');
		$renwu_ranks = array_keys($renwutag_conf);

		array_push($renwu_ranks, $rank);
		sort($renwu_ranks);
		$index = min(array_search($rank, $renwu_ranks), count($renwu_ranks));
		$index = max(0, $index -1);
		$renwutag = $renwutag_conf[$renwu_ranks[$index]]['renwutag'];

		$uid_new_info = array(
			'rank'=>$rank,
			'rank_t'=>$now,
			'name'=>$uid_new_renwuname,
			'renwutag'=>$renwutag,
		);
		echo "info new = \n";
		print_r($uid_new_info);

		$new_user_info_key = model_PVPUtil::getLunjianUserID($section, $uid_new);
		echo "new user info key = $new_user_info_key\n";
		$redis->hmset($new_user_info_key, $uid_new_info);
		$redis->hset($rank_max_key, 'npc_max', $fill_id);
	}

	//删除世界boss里面的作弊玩家
	public function action_del_worldboss_user(){
        $section = $this->get_param('sec');
        $uid = $this->get_param('uid');
        $today   = $this->get_param('date');//指定日期，格式：20121030，代表2012年十月30号
        $tag = $this->get_param('tag'); //boss tag
		if(!$tag){
			$tag = '1001';
		}
		if(empty($section) || empty($uid)){
			throw new Exception("param  uid = [$uid] , section = [$section] error");
			return;
		}
		$now = getApp()->now;
        $redis = DbConfig::getRedis('worldboss');
		$redis_set = DbConfig::getRedis('cache');
		if(empty($today)){
			$today = date('md', $now);
		}
		$rank_key = "worldboss_rank_{$tag}_{$section}_{$today}";
		$rank = $redis->zrevrank($rank_key, $uid);
		if($rank !== false){
			echo "删除玩家: uid = $uid, 排名: $rank\n";
			$redis->zrem($rank_key, $uid);
		}else{
			echo "玩家: uid = $uid 不在表内\n";
		}
	}

    public function action_redis_status(){
        foreach(DbConfig::$redises as $key=>$config){
            if($config['host'] && $config['port']){
                $redis = new Redis();
                echo "redis[$key] {$config['host']}:{$config['port']}\n";
                $redis->connect($config['host'],$config['port']);
                $info = $redis->info();
                echo "$key\t{$config['host']}, {$config['port']},\t{$info['db0']},\t{$info['used_memory_human']}\n";
            }
        }
    }


    public function action_check_buchang(){
        $mon = getApp()->getPaymentMongoConnection();
        $rows = $mon->findByIndex("gemorder",array('source'=>'buchang'),100000,0,array(),array(),true);
        foreach($rows as $row){
            $uid = $row['_u'];
            echo "$uid,{$row['finish_t']},{$row['product_id']}\n";
            $login_model = new model_LoginUser($uid);
            $login_model->opOne('buchange_gem_t',$row['finish_t']);
            $login_model->save();
        }
    }


	/**
	 * 给黑卡用户发传书
	 */
	public function action_notice_blackcard(){
		$list = model_BlackList::getBlackListUids();
		if(empty($list)){
			echo "list empty!\n";	
			return;
		}
		foreach($list as $uid){
			$login_model = new model_LoginUser($uid);
			$login_model->get(array('secs'=>1));
            $d = $login_model->doc();
            if(empty($d['secs'])){
                $d['secs'] = array();
            }
            foreach($d['secs'] as $section_id=>$timestamp){
                $day = date('ymd',$timestamp);
                echo "uid:$uid, sec:$section_id, time:$day;\n";

				$msg1 = array(
					'type'=>'system',
					'key'=>uniqid(),
					'time'=>getApp()->now,
					'content'=>"大掌门运营团队郑重提醒：
					1、大掌门没有任何通过淘宝充值的渠道，请不要通过淘宝进行非法充值。使用非法充值将有可能造成您的帐号密码被盗取，并会出现数据异常而被永久性封停。"
				);
				$msg2 = array(
					'type'=>'system',
					'key'=>uniqid(),
					'time'=>getApp()->now,
					'content'=>"2、为了确保您的帐户安全及避免不必要的损失，请您务必只通过点击游戏内的“集市”-“充值”的正规官方渠道进行充值操作。"
				);
				model_Chat::sendMsg($msg1, $uid,'origin',$section_id);
				model_Chat::sendMsg($msg2, $uid,'origin',$section_id);
			}
		}
	}
    /**
     * 检查支付信息
     *
     */
    public function action_check_payment(){
        $mon = getApp()->getPaymentMongoConnection();
        $rows = $mon->findByIndex("gemorder",array('action'=>'recharge_gem','status'=>1),0,0,array(),array(),true);
        foreach($rows as $row){
            $uid = $row['_u'];
            $section_id = $row['_sec'];
            $time = $row['_tm'];
            $vip = $row['_vip'];
            $device_id = $row['device_id'];
            $mac_address = $row['mac_address'];
            $cash = $row['cash'];
            echo "insert into payment values($uid,'$section_id',$time,$vip,'$device_id','$mac_address',$cash);\n";
        }
    }
    public function action_export_payment(){
        $mon = getApp()->getPaymentMongoConnection();
        $rows = $mon->findByIndex("gemorder",array('action'=>'recharge_gem','status'=>1),0,0,array(),array(),true);
        foreach($rows as $row){
            $uid = $row['_u'];
            $section_id = $row['_sec'];
            $time = $row['_tm'];
            //$vip = $row['_vip'];
            //$device_id = $row['device_id'];
            //$mac_address = $row['mac_address'];
            $cash = $row['cash'];
            echo "$uid,$section_id,$time,$cash\n";
            //echo "insert into payment values($uid,'$section_id',$time,$vip,'$device_id','$mac_address',$cash);\n";
            //echo "$uid,'$section_id',$time,$vip,'$device_id','$mac_address',$cash);\n";
        }
    }
    public function action_clean_mac(){
        $uids = model_BlackList::getBlackListUids();
        foreach($uids as $uid){
            $login_model = new model_LoginUser($uid);
            $login_model->opOne('mac_address','');
            $login_model->save();
        }
    }

	public function action_change_mac(){
        $now = getApp()->now;
        $uid = $this->get_param('uid');
		$uid = (int)$uid;
        $mac = $this->get_param('mac');

		$model = new model_LoginUser($uid);
		$model->get(array('pid'));
		$pid = $model['pid'];

		$logRecharge = new model_LogRecharge($uid);
		$ret = $logRecharge->changeLogMac($mac, $uid);
		echo "uid = $uid after change:\n";
		print_r($ret);

		$login_data_db = $logRecharge->getBindInfoByUid($uid);

		$content = "申诉更换充值设备:\n
			申诉人：uid [$uid], 数据库：login_mac: [{$login_data_db['login_mac']}], payment_mac: [{$login_data_db['payment_mac']}], 更换的新mac: [$mac]\n";

		$msg1 = array(
			'type'=>'system',
			'key'=>uniqid(),
			'time'=>getApp()->now,
			'content'=>"亲爱的掌门，我们已经帮您处理了您之前提交常用设备更换申请，您可以尝试进行充值了。"
		);

		$login_model = new model_LoginUser($uid);
		$login_model->get(array('secs'=>1));
		$d = $login_model->doc();
		if(empty($d['secs'])){
			$d['secs'] = array();
		}
		
		foreach($d['secs'] as $section_id=>$timestamp){
			$day = date('ymd',$timestamp);
			echo "uid:$uid, sec:$section_id, time:$day;\n";
			model_Chat::sendMsg($msg1, $uid,'origin',$section_id);
		};

		if(count($d['secs']))
			model_Util::sendemail('wangkun@playcrab.com,kf@playcrab.com,op@playcrab.com', 'blacklist_user_apply_checked', $content);
	}



    /*
     * 可以用 begin 和 end 指定开始结束时间，那么就不用再指定period参数了
     */
    public function action_import_realtime(){
/*        $section = $this->get_param('sec');
        $source = $this->get_param('src');*/
        $period = $this->get_param('period');
        $bDate = $this->get_param('begin');
        $eDate = $this->get_param('end');
        $secList = array_keys(getApp()->getSectionConfig());
        $srcList = array_keys(getApp()->getSourceConfig());
        $allList = array($secList, $srcList, array("all"));
		$login_model = new model_LoginUser(1);// 1?
        for($i = 0, $len1 = count($allList); $i < $len1; $i ++){
            $tmpList = $allList[$i];
            for($j = 0, $len2 = count($tmpList); $j < $len2; $j ++){
                $elem = $tmpList[$j];
                echo $elem."\n";
                if($i == 0){//分区
                    $ret = model_Util::get_realtime_login_stat_new($period, $elem, $bDate, $eDate);
                    $tmp = array('Section'=>array($elem=>$data));
                    $data = &$tmp['Section'][$elem];
                }else if($i == 1){//渠道
                    $ret = model_Util::get_realtime_login_stat_new($period, $elem, $bDate, $eDate);
                    $tmp = array('Source'=>array($elem=>$data));
                    $data = &$tmp['Source'][$elem];
                }else if($i == 2){//总表
                    $ret = model_Util::get_realtime_login_stat_new($period, $elem, $bDate, $eDate); 
                    $tmp = array('All'=>$data);
                    $data = &$tmp['All'];
                } 

                $datas = $ret["data"];
                foreach($datas as $day=>$vals){
                    $login_model->id($day,'realtime_stat');
                    $data = array(
                         'date'=>$vals['date']
                        ,'active_today_pid'=>$vals['active_today_pid']
                        ,'auth_today_pid'=>$vals['auth_today_pid']
                        ,'auth_yesterday_pid'=>$vals['auth_yesterday_pid']
                        ,'auth_reserve_pid'=>$vals['auth_reserve_pid']
                        ,'active_today_device'=>$vals['active_today_device']
                        ,'auth_today_device'=>$vals['auth_today_device']
                        ,'auth_yesterday_device'=>$vals['auth_yesterday_device']
                        ,'auth_reserve_device'=>$vals['auth_reserve_device']
                    );
                    echo $vals["date"]."\n";
                    var_dump($tmp);
                    $login_model->opMulti($tmp);
                    $login_model->save();
                }
            }
        }
/*
        if($section && !$source){//分区
            $ret = model_Util::get_realtime_login_stat_new($period, $section);
            $tmp = array('Section'=>array($section=>$data));
            $data = &$tmp['Section'][$section];
        }else if($source && !$section){//渠道
            $ret = model_Util::get_realtime_login_stat_new($period, $source);
            $tmp = array('Source'=>array($source=>$data));
            $data = &$tmp['Source'][$source];
        }else if(!$section && !$source){//总表
            $ret = model_Util::get_realtime_login_stat_new($period, 'all'); 
            $tmp = array('All'=>$data);
            $data = &$tmp['All'];
        } 

		$datas = $ret["data"];
        var_dump($ret);
		foreach($datas as $day=>$vals){
            $login_model->id($day,'realtime_stat');
            $data = array(
                 'date'=>$vals['date']
                ,'active_today_pid'=>$vals['active_today_pid']
                ,'auth_today_pid'=>$vals['auth_today_pid']
                ,'auth_yesterday_pid'=>$vals['auth_yesterday_pid']
                ,'auth_reserve_pid'=>$vals['auth_reserve_pid']
                ,'active_today_device'=>$vals['active_today_device']
                ,'auth_today_device'=>$vals['auth_today_device']
                ,'auth_yesterday_device'=>$vals['auth_yesterday_device']
                ,'auth_reserve_device'=>$vals['auth_reserve_device']
            );
            $login_model->opMulti($tmp);
            $login_model->save();
		}
        echo "finished...";
 */



//        var_dump($datas);
/*		$login_model->switchColl('realtime_stat');
        $ret = $login_model->getByIds(array('2013-01-08'));
        var_dump($ret);*/
//        $ret = model_Util::realtime_stat_by_sec($period, $sec);
       // echo $ret;
        //$ret = $ret["data"];//["2013-01-07"];
//        $cnt = 0;
/*        foreach ($ret as $k=>$v){
            echo $k." === ".$v; 
            echo "\n";
            $cnt ++;
}*/
//        echo $cnt;
      //  var_dump($total_user_count_pid);
/*
        $login_model = new model_LoginUser(1);
        $login_model->id('2013-01-08','realtime_stat');
        $login_model->opOne('name','owen');
        $login_model->opMulti(array('age'=>100,'xxx'=>'yyy'));
        $login_model->save();

        $login_model = new model_LoginUser(1);
        $login_model->switchColl('realtime_stat');
        $ret = $login_model->getByIds(array('2013-01-08'));
        var_dump($ret);*/
    }
    
    public function action_find_realtime(){
        $date = $this->get_param('date');
        $section = $this->get_param('sec');
        $source = $this->get_param('src');
        $login_model = new model_LoginUser(1);
        $login_model->switchColl("realtime_stat");
//        $mc = $login_model->getmc()->getmc();
//        $ret = $mc->find(array('_id'=>array('$in'=>array('2013-01-10','2013-01-11'))),array('Section.s1'=>1,'_id'=>0));
//        $ret = $mc->count();
        $ret = $login_model->getByIds(array($date));
//        $tmp = iterator_to_array($ret); 
        var_dump($ret);
    }

    /*
     * 用 days_before 指定 删除多少天以前的数据
     * 用 days_count 指定一共删除多少天的数据
     */
    public function action_remove_realtime(){
        $section_config = getApp()->getSectionConfig();
        $section_ids = array_keys($section_config);
        if(time() < strtotime('2013-12-16 8:0:0')){
            $redis = DbConfig::getRedis('cache');
        }else{
            //realtime_wangkun
            $redis = DbConfig::getRedis('realtime_stat');
        }
        $days_before = $this->get_param('days_before');
        $days_count = $this->get_param('days_count');
        if($days_before > 0 && $days_count > 0){
            foreach(array('pid','device') as $type){
                for($i=$days_before+$days_count-1;$i>=$days_before;$i--){
                    $today = date('ymd',strtotime("- $i days"));
                    $key = "active_{$type}_all_{$today}";
                    echo "$key\t" .$redis->del($key) ."\n";
                    $key = "auth_{$type}_all_{$today}";
                    echo "$key\t" .$redis->del($key) ."\n";
                    foreach($section_ids as $section_id){
                        $key = "active_{$type}_{$section_id}_{$today}";
                        echo "$key\t" .$redis->del($key) ."\n";
                        $key = "auth_{$type}_{$section_id}_{$today}";
                        echo "$key\t" .$redis->del($key) ."\n";
                    }
                }
            }
        }else{
            echo "need days_before > 0 and days_count >0\n";
        }
    }

	public function action_auto_create_blacklist(){
		if(P_PLATFORM !== 'appstore' && P_PLATFORM !== 'dev'){
			echo "platform is " . P_PLATFORM . ", only appstore is enabled\n";
			return;
		}
        $mon = getApp()->getPaymentMongoConnection();
		$mc  = $mon->getmc();
		$data = $mc->find(array('action'=>'recharge_gem', 'status'=>1));
		$redis = DbConfig::getRedis('cache');
		$keys = $redis->keys("blacklist*");
		$redis->multi();
		foreach($keys as $k){
			$redis->del($k);
		}
		$ret = $redis->exec();
		if($ret === false){
			echo "del error\n";
			return;
		}

		foreach($data as $r){
			$uid = $r['_u'];
			$device_id = $r['device_id'];
			$cash = $r['cash'];
			$redis->multi();
			$redis->hincrby("blacklist_sum_$uid", "recharge_count", 1);
			$redis->hincrby("blacklist_sum_$uid", "cash_sum", $cash);
			$redis->sadd("blacklist_uids", $uid);
			$redis->sadd("blacklist_dids", $device_id);
			$redis->sadd("blacklist_$uid", $device_id);
			$redis->sadd("blacklist_$device_id", $uid);
			$rt = $redis->exec();
		}

		$dids = $redis->smembers("blacklist_dids");
		$redis->multi();
		foreach($dids as $did){
			$redis->scard("blacklist_$did");
		}
		$did_counts = $redis->exec();
		$did_static = array_combine($dids, $did_counts);
		unset($did_counts);
		foreach($did_static as $d=>$s){
			if($s >= 5){
				$black_list_device[$d] = 1;
				$uids_use_blackdevice = $redis->smembers("blacklist_$d");
				foreach($uids_use_blackdevice as $buid){
					$black_list_uid[$buid] = 1;
				}
			}
		}

		$uids = $redis->smembers("blacklist_uids");
		$redis->multi();
		foreach($uids as $uid){
			$redis->scard("blacklist_$uid");
		}
		$uid_counts = $redis->exec();
		$uid_static = array_combine($uids, $uid_counts);
		foreach($uid_static as $u=>$s){
			if($s >= 3){
				$black_list_uid[$u] = 1;
			}
		}
		ksort($black_list_uid);
		ksort($black_list_device);

		$black_list_uid_path = LOG_ROOT . "blacklist_uids.php";
		$black_list_did_path = LOG_ROOT . "blacklist_dids.php";
		echo "blacklist path = $black_list_uid_path\n";

		if(!$black_list_uid){
			$black_list_uid = array();
			echo "uid黑名单为空";
		}
		$out = var_export($black_list_uid, true);
		$out = "<?php\nreturn " .$out. ";\n?>";
		file_put_contents($black_list_uid_path, $out);

		if(!$black_list_device){
			$black_list_device = array();
			echo "设备黑名单为空";
		}
		$out = var_export($black_list_device, true);
		$out = "<?php\nreturn " .$out. ";\n?>";
		file_put_contents($black_list_did_path, $out);
	}

    public function get_and_print_user_info($login_model){
        $login_model->get(array('pid'=>1,'email'=>1,'secs'=>1,'_ct'=>1,'_at'=>1));
        $uid = $login_model->id();
        $pid = $login_model['pid'];
        $email = $login_model['email'];
        $create_t = $login_model['_ct'];
        $create_day = date("Y-m-d",$create_t);

        if(empty($login_model['secs'])){
            $login_model['secs'] = array();
        }
        echo <<<XXX
============================================
uid:$uid
pid: $pid
邮箱: $email
注册时间: $create_day

XXX;
        foreach($login_model['secs'] as $section_id=>$timestamp){
            $day = date('Y-m-d',$timestamp);
            $player = new model_Player($uid,$section_id);
            $data = $player->getFields(array(
                'name',
                'vip',
                'level',
            ));
            echo <<<XXX
    =================
    分区: $section_id
        上次登录时间: $day
        门派名: {$data['name']}
        等级: {$data['level']}
        VIP: {$data['vip']['lvl']}

XXX;
        }
        echo "\n";
    }
    public function action_reset_account(){
        $uid = $this->get_param('uid');
        $email = $this->get_param('email');
        $password = $this->get_param('password');
        $login_model1 = new model_LoginUser($uid);
        if(!$login_model1){
            echo "用户[uid:$uid]不存在\n";
            return;
        }
        $this->get_and_print_user_info($login_model1);
        $login_model2 = model_LoginUser::searchUniq('email',$email,true);
        if(!$login_model2){
            echo "=============================================\n";
            echo "用户[email:$email]不存在\n";
        }else{
            if($login_model2->id() == $login_model1->id()){
                echo "不需要绑定\n";
                return;
            }
            $this->get_and_print_user_info($login_model2);
        }

        if(!empty($password)){
            if($login_model2){
                echo "先禁用掉 原来 [$email] 映射的用户\n";
                $login_model2->opOne('email','__disabled__'.$email);
                $login_model2->save();
            }
            echo "绑定 uid[$uid] email[$email] password[$password]\n";
            $login_model1->setPass($password);
            $login_model1->opOne('email',$email);
            $login_model1->save();
        }
    }

	public function action_buchang_worldboss(){
        $section = $this->get_param('sec');
        $today   = $this->get_param('date');//指定日期，格式：1030，代表十月30号
		if(empty($section)){
			$section_list = array_keys(getApp()->getSectionConfig());
		}else{
			$section_list = (array)$section;
		}
		if(empty($section_list)){
			throw new Exception('section error');
			return;
		}
		$now = getApp()->now;
        $redis = DbConfig::getRedis('worldboss');
		$redis_set = DbConfig::getRedis('cache');
		if(empty($today)){
			$today = date('md', $now);
		}
		if(P_PLATFORM == "kunlun" || P_PLATFORM == 'qqandroid'){
			echo "昆仑平台暂无补偿";
			return;
		}
		$world_boss_conf = PL_Config_Numeric::get('worldboss');
		$worldboss_zhidian_offset = PL_Config_Numeric::get('setting','worldboss_zhidian_level_offset');
		echo "================[ 补偿日期 : $today ]===============\n";
		foreach($section_list as $section){
			echo "===== 分区： $section =====\n";
			foreach($world_boss_conf as $tag => $conf){
			echo "===== boss： $tag =====\n";

				$activityConfig = getApp()->getactivityconfig("act_worldboss_$tag");
				if($activityConfig){
					if($now < $activityConfig['start_t'] || $now > $activityConfig['end_t']){
						echo "[$tag] 不在活动时间内\n";
						continue;
					}
				}

				$boss_key = "worldboss_boss_{$tag}_{$section}_{$today}";
				$boss_info = $redis->hgetall($boss_key);
                    
                if($boss_info['buchang_reward_time'] ){
					$msg = "补偿时间 : ". date('Y-m-d H:i:s', $boss_info['buchang_reward_time']) . "\n";
                    echo $msg . "\n";
                    continue;
                }

				$rank_key = "worldboss_rank_{$tag}_{$section}_{$today}";

                $boss_name = model_Translate::getTransConfig($conf['bossname']);

				$rank_list = $redis->zrevrange($rank_key, 0, -1);
				if(!$rank_list){
					continue;
				}
				$rank_uids = array_values($rank_list);
				$user_info = model_CacheUtil::getUserInfoByIdsWithFileds($rank_uids, $section, array('level'));

				foreach($user_info as $r_uid => $r_udata){
					$rank_awards = array(
						array('tag'=>'601001', 'num'=>4),//叫花鸡
						array('tag'=>'601002', 'num'=>4),//补元丹
						array('tag'=>'601019', 'num'=>200),//培养丹
						array('tag'=>'money',  'num'=>500000),//
						array('tag'=>'qiyu_zhidian','level'=>max(1, $r_udata['level']*3+$worldboss_zhidian_offset), 'num'=>1),
						array('tag'=>'qiyu_zhidian','level'=>max(1, $r_udata['level']*3+$worldboss_zhidian_offset), 'num'=>1),
						array('tag'=>'qiyu_zhidian','level'=>max(1, $r_udata['level']*3+$worldboss_zhidian_offset), 'num'=>1),
						array('tag'=>'qiyu_zhidian','level'=>max(1, $r_udata['level']*3+$worldboss_zhidian_offset), 'num'=>1),
						array('tag'=>'qiyu_zhidian','level'=>max(1, $r_udata['level']*3+$worldboss_zhidian_offset), 'num'=>1),
					);
					$desc_str = model_Cdkey::descriptItems($rank_awards);
					$rank_msg = array(
						'type'=>'system',
						'key'=>uniqid(),
						'time'=>getApp()->now,
						'content'=>"由于3月15日掌门参加群雄破阵时出现了游戏意外退出的情况。大掌门团队对此表示歉意并补偿给掌门：叫花鸡x4, 补元丹x4, 培养丹x200, 银两x500000, 指点x5",
					);
					$rank_msg['status'] = 'award';
					//print_r($rank_awards);
					$cdkey = model_Cdkey::gen($rank_awards,$desc_str);
					$rank_msg['cdkey'] = $cdkey;
					model_Chat::sendMsg($rank_msg, $r_uid,'origin',$section);
					echo "uid = [$r_uid], 获取补偿，cdkey = [$cdkey]\n";
				}

				$redis->hset($boss_key, 'buchang_reward_time', $now);
			}
		}
	}
	

    //读取各个分区其中一个活动用户有效用户数据，然后记录下来，等迁移完了再看数据是否一致
    public function action_get_sec_user_snap(){
        $app = getApp();
		$section_list = $app->getSectionConfig();
		$section_list = array_keys($section_list);
        $file_name = P_PLATFORM . "_" . date('Ymd', $_SERVER['REQUEST_TIME']) . "_usersnap.php";
        foreach($section_list as $sec){
            $sec_user_db = new PL_Db_Mongo(DbConfig::getSecMongodb("users", $sec));
            $sec_user_db->switchColl("{$sec}_users");
            $data['user'][$sec] = $sec_user_db->findOne(array('login_t'=>array('$gt'=>strtotime('12 hours ago'))));
            $data['count'][$sec] = $sec_user_db->count(array(),'');
        }
		file_put_contents($file_name, "<?php return ".var_export($data, true) . ";");
    }


    //检查迁移后的数据一致
	public function action_check_sec_user_snap(){
        $date = $this->get_param('date');
		if(!$date){
            $date = date('Ymd', time());
        }
        $file_name = P_PLATFORM . "_" . $date . "_usersnap.php";
        $snap = include($file_name);
        foreach($snap['user'] as $sec => $user_data){

            $uid = $user_data['_id'];
            $um = new model_User($uid, $sec);
            $ud = $um->get();
            $login_t = $ud['login_t'];
            if(count($user_data) !== count($ud) || count(array_diff((array)$ud, (array)$user_data)) != 0){
                 echo "sec = $sec, uid = $uid 数据不一致!\n";
			}else{
                 echo "sec = $sec, uid = $uid, login_t = ". date('Y-m-d H:i:s', $login_t) ."\n";
            }
        }
        foreach($snap['count'] as $sec => $count){
            $sec_user_db = new PL_Db_Mongo(DbConfig::getSecMongodb("users", $sec));
            $sec_user_db->switchColl("{$sec}_users");
            $get_count = $sec_user_db->count(array(),'');
            if($count !== $get_count){
                echo "迁移以前分区[$sec]总用户数: $count, 迁移以后：$get_count; 用户数量不一致！\n";
			}else{
                echo "迁移以前分区[$sec]总用户数: $get_count\n";
            }
        }
    }


    function action_check_dbconfig(){
        $secs = include CONFIG_ROOT.'/secs.php'; 
        $secdbs = include CONFIG_ROOT.'/secdbs.php'; 
        var_dump($secs);
        var_dump($secdbs);
        var_dump(DbConfig::$redises);
        var_dump(DbConfig::$caches);
        var_dump(DbConfig::$mongodb_def_cstr); 
        var_dump(DbConfig::$mongodb_def_db);
        var_dump(DbConfig::$mongodb_def_option);
        var_dump(DbConfig::$mongodbs);
    }

    function action_gen_leaguewar(){
        $section_list = array_keys(getApp()->getSectionConfig());
        $lw = new LeagueWar();
        try{
            foreach($section_list as $sec){
                $lw->genBattleConf($sec);
            }
		}catch(Exception $e){
			$ex_msg = $e->getMessage();
			$ex_trace_msg = $e->getTraceAsString();
			$excep_content = "{$ex_msg}\n{$ex_trace_msg}";
			$subject = "leaguewar_init_bug_".P_PLATFORM."_".date('Y-m-d H:i:s', time());
			$email_content = "盟战初始化错误. $excep_content";
			model_Util::sendemail('wangkun@playcrab.com',$subject,$email_content);
		}
    }

    function action_month_stat(){


        $start_day = $this->get_param('start_day');
        $end_day = $this->get_param('end_day');

	    $js_code = <<<EOF

var map_step1 = function() {
  var start_time = (new Date("$start_day 00:00 GMT+08:00")).getTime()/1000;
  var end_time = (new Date("$end_day 00:00 GMT+08:00")).getTime()/1000;
  var status = parseInt(this.status);
  if(this.create_t >= start_time && this.create_t < end_time && this.action == 'recharge_gem' && status == 1 && this.cash >0){
    var date = new Date(this.create_t*1000);
    var year = date.getFullYear();
    var month = date.getMonth() + 1;
    if(month>9){
      month = year + "" + month;
    }else{
      month = year + "0" + month;
    }
    emit({month:month,uid:this._u},{count:1,cash:parseInt(this.cash)});
  }
};


var reduce_step1 = function(key,values){
    var result = {count:0,cash:0};
    values.forEach(function(value) {
result.count += value.count;
result.cash += value.cash;
    });
    return result;
}
db.month_stat_step1.drop();
db.month_stat_step2.drop();
db.gemorder.mapReduce(map_step1,reduce_step1, {out:"month_stat_step1"});

var map_step2 = function() {
  emit(this['_id']['month'], {user_count: 1,pay_count:this['value']['count'], cash:this['value']['cash']});
};

var reduce_step2 = function(month,values){
    var result = {user_count:0,pay_count:0,cash:0};
    values.forEach(function(value) {
result.user_count += value.user_count;
result.pay_count += value.pay_count;
result.cash += value.cash;
    });
    return result;
}
db.month_stat_step1.mapReduce(map_step2,reduce_step2, {out:"month_stat_step2"});

EOF;
        $js_file = LOG_ROOT.'/month_stat.js';
        file_put_contents($js_file,$js_code);

        $game = app_config("game");
        $platform = app_config("platform");
        $mongo_bin = "/usr/bin/mongo";
        if($game=='ares' && $platform=='qqandroid'){
            $mongo_bin = "/usr/local/app/sys/mongodb/bin/mongo";
        }

        $config = DbConfig::$mongodbs['userlogin'];
        $cstr = $config['cstr'];
        $db = $config['db'];
        $cstr = str_replace('mongodb://','',$cstr);
        if($i = strpos($cstr,',')){
            $cstr = substr($cstr,0,$i);
        }
        $cstr = $cstr.'/'.$db;
        $success = false;
        // js_code 执行时间会比较长，所以 MongoDB->execute($js_code) 的方式执行 js 代码
        if(is_executable($mongo_bin)){
            $command = "$mongo_bin $cstr <$js_file 2>&1";
            echo "$command \n";
            system($command);
            $success = true;
        }else{
            echo "no mongo\n";
        }


        if($success){
            $mongo_collection = DbConfig::getMongodb('userlogin');
            $mc = $mongo_collection->db->selectCollection("month_stat_step2");
            $ret = $mc->find();
            $data = array();
            foreach($ret as $v){
                if($game == 'ares' && $platform == 'kunlun'){
                    $v['value']['cash'] = $v['value']['cash']*2;
                }
                if($game == 'ares' && $platform == 'appstoretw'){
                    $v['value']['cash'] = $v['value']['cash']*0.2;
                }
                echo "$game\t$platform\t{$v['_id']}\t{$v['value']['user_count']}\t{$v['value']['pay_count']}\t{$v['value']['cash']}\n";
                $data[] = array(
                    'game'=>$game,
                    'platform'=>$platform,
                    'month'=>$v['_id'],
                    'user_count'=>$v['value']['user_count'],
                    'pay_count'=>$v['value']['pay_count'],
                    'cash'=>$v['value']['cash'],
                );
            }
            self::post('http://log.playcrab.com/stat.php',$data);
        }
    }
    function action_month_stat2(){

        //$start_day = $this->get_param('start_day');

	    $js_code = <<<EOF

var month_names = ['201209','201210','201211','201212','201301','201302','201303','201304','201305','201306','201307','201308','201309','201310'];

var month_times = [];
for(var i = 0;i<month_names.length;i++){
  var y = month_names[i].substring(0,4);
  var m = month_names[i].substring(4,6);
  var t = (new Date(y+'/'+ m +'/'+'01 00:00 GMT+08:00')).getTime() /1000;
  month_times[i] = t;
}

var mapf = function() {
  for(var i = 0; i< month_times.length;i++){
    if(this._ct>=month_times[i] && this._ct<month_times[i+1]){
      // 第 i 月注册，计算一次活跃和新增
      emit(month_names[i],{reg:1,active:1,ret:0});
    }
    if(this._at>=month_times[i] && month_times[i]>this._ct){
      // 注册之后的下个月开始，到最后一次活跃以内
      if(i>0 && this._ct >=month_times[i-1] && this._ct < month_times[i]){
        // 是上个月注册的
        emit(month_names[i],{reg:0,active:1,ret:1});
      }else{
        // 不是上个月注册的
        emit(month_names[i],{reg:0,active:1,ret:0});
      } 
    }
  }
};

var reducef = function(day,values){
    var result = {reg:0,active:0,ret:0};

    values.forEach(function(value) {
      result.reg += value.reg;
      result.active += value.active;
      result.ret += value.ret;
    });
    return result;
}
db.stat_user.drop();

db.runCommand({
mapreduce:'userlogin',
map:mapf,
reduce:reducef,
out:'stat_user',
scope:{month_times:month_times,month_names:month_names}
});




var cur = db.stat_user.find();
while(cur.hasNext()){
  var o = cur.next();
  print(o._id,'\t',o.value.reg,'\t',o.value.active,'\t',o.value.ret);
}
EOF;
        $js_file = LOG_ROOT.'/month_stat.js';
        file_put_contents($js_file,$js_code);

        $game = app_config("game");
        $platform = app_config("platform");
        $mongo_bin = "/usr/bin/mongo";
        if($game=='ares' && $platform=='qqandroid'){
            $mongo_bin = "/usr/local/app/sys/mongodb/bin/mongo";
        }

        $config = DbConfig::$mongodbs['userlogin'];
        $cstr = $config['cstr'];
        $db = $config['db'];
        $cstr = str_replace('mongodb://','',$cstr);
        if($i = strpos($cstr,',')){
            $cstr = substr($cstr,0,$i);
        }
        $cstr = $cstr.'/'.$db;
        $success = false;
        // js_code 执行时间会比较长，所以 MongoDB->execute($js_code) 的方式执行 js 代码
        if(is_executable($mongo_bin)){
            $command = "$mongo_bin $cstr <$js_file 2>&1";
            echo "$command \n";
            system($command);
            $success = true;
        }else{
            echo "no mongo\n";
        }

        if($success){
        }
    }
    static public function post($url,$data){
        $post = json_encode($data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$post);
        curl_setopt($ch,CURLOPT_USERAGENT,'post test robot');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        if (P_PLATFORM=='qqandroid'){
            curl_setopt($ch, CURLOPT_PROXY, '10.172.48.92:3300');
        }
        $response= curl_exec ($ch);
        return $response;
	}
    
    /**
     * @author 符璨
     * @desc
     *      从cdkey_task取出待处理的cdkey任务进行处理 
     *      批量生成cdkey
     */
    static public function action_gen_cdkey(){
        $redis = DbConfig::getRedis('realtime_stat');

        $process_id = getmypid();

        //从cdkey中取出一条状态为0（未处理cdkey生成记录）,并更新为1（正在处理)
        $mc = new PL_Db_Mongo(DbConfig::getMongodb('userlogin'));
		$mc->switchColl('cdkey_task');
        $cdkey_task = $mc->findOne(array('status'=>0));

        //没有待处理的任务
        if($cdkey_task == NULL){
            return;
        }

        glog::info("[$process_id] gen cdkey worker started", 'cdkey');

        $task_id = $cdkey_task['_id'];
        $mc->update(array('_id'=>$task_id), array('$set'=>array('status'=>1))); 

        $pa = $cdkey_task;
        unset($pa['status']);
        $num = $pa['num'];
        $task_id = $cdkey_task['_id'];

        //根据cdkey_task的内容生成cdkey
		$mc->switchColl('cdkey');
        while($num--){
            $pa['_id'] = model_Cdkey::genCdkey(13); 
            $pa['task_id'] = $task_id->__toString(); 
            
            //try以防有重复的cdkey
            try{
                $cdkey = $mc->insert($pa);

                //redis中统计当前已生成的cdkey数目
                $redis->hIncrBy('cdkey_task', $task_id, 1);
                $redis->lpush("cdkey_{$task_id}", $pa['_id']);
            }
            catch(Exception $ex){
                $exception_cnt--;
                $num++;
                glog::info("[$process_id] gen cdkey worker:Duplicated _id {$pa['_id']}", 'cdkey');
                if(!$exception_cnt){
                    break;
                }
                continue;
            }
            $exception_cnt = 10;
		}

		$mc->switchColl('cdkey_task');
        $mc->update(array('_id'=>$task_id), array('$set'=>array('status'=>2))); 
        $redis->hDel('cdkey_task', $task_id);

        glog::info("[$process_id] gen cdkey worker completed", 'cdkey');
    }
    
    /**
     * @author 符璨
     * @desc
     *      执行给福利号用户发送奖励的定时任务
     */
    static public function action_awarduser_timer(){
        if(!dzm_base::load_config('system','open_fulihao_awards')){
            return false;
        }
        //error_log("定时任务开始：");
        $lm = new model_AwardUser(1);
        $lm->sendAwardByTask();
    }


    /**
     * @auther lifei@playcarb.com
     * 发放燕子坞活动 未领奖励
     */
    static public function action_award_yanziwu(){
        $app = getApp();
        $now = $app->now;
        //活动界面关闭后才发送奖励
        $config = $app->getActivityConfig('act_yanziwuqiangqin_20130425');
        if($now <= $config['end_t']){
            //self::showError("",__LINE__);
            self::showError("activity not finished!",__LINE__);
        }
        $sections = array_keys(getApp()->getSectionConfig());
        if(empty($sections)){
            self::showError("get section config error!",__LINE__);
        }
        $start_md = date('md',$config['start_t']);
        $redis = DbConfig::getRedis('rank');
        //时间肯定超过12、21、24了不判断时间
        //所有区
        foreach($sections as $sec){
            echo "===============分区:{$sec}==========================\n";
            //检查各个时段的排行榜奖励
            for($i=1;$i<=3;$i++){
                $redis_key = "act_yanziwuqiangqin_".$start_md."_".$sec."_".$i;
                //查询各个时段排行榜
                $list = self::getQiangQinRank(0,19,$redis,$redis_key,$config,$sec);
                if(empty($list)){
                    echo "warning:第{$i}段，分区:{$sec}的排行为空！\n";
                    continue;
                }
                echo "=============当前时段：{$i}=============";
                echo $redis_key."\n";
                print_r($list);
                foreach((array)$list as $v){
                    //判断玩家是否领取过
                    $uid = $user_key = $v['id'];
                    $player = new model_Player($uid,$sec);
                    //玩家存的是带年的
                    $start_ymd = date('Ymd',$config['start_t']);
                    $player_data = $player->getFields(array('qiyu.yanziwuqiangqin_'.$start_ymd));
                    $player_pos = $player_data['qiyu']['yanziwuqiangqin_'.$start_ymd];
                    if(!empty($player_pos[$i])){//领过了
                        echo "warning:====玩家{$uid}/{$sec}领过第{$i}段奖励了!\n";
                        continue;
                    }
                    //判断奖励
                    //玩家自己的排名
                    $rank = $redis->zrevrank($redis_key,"$user_key");
                    echo "====玩家{$uid}/{$sec}===当前排名：{$rank}\n";
                    if($rank === false){
                        echo "warning:====玩家{$uid}/{$sec}排名错误为假!\n";
                        continue;
                    }

                    // 确定该玩家是否在前20，计算并列20的情况
                    $my_num = 0;
                    if($rank >= 20){
                        // 玩家自己的积分
                        $my_num = $redis->zscore($redis_key,$user_key);
                        // 玩家正好与第20名的积分相同
                        if($my_num == $list[count($list)-1]['num']){
                            // 奖励list中最后一名相同的奖励
                            $award_config = PL_Config_Numeric::get('yanziwuaward',$list[count($list)-1]['rank'],false);
                        }
                    }else{ 
                        // 如果在前20名，那么list中一定存在该玩家信息
                        foreach($list as $val){
                            if($val['id'] == $uid){
                                $my_rank = $val['rank'];
                                $my_num = $val['num'];
                            }
                        }
                        $award_config = PL_Config_Numeric::get('yanziwuaward',$my_rank,false);
                    }
                    //24点必须大于2000
                    if($i == 3 && $my_num < 2000){
                        continue;
                    }
                    echo "{$sec}=={$uid}开始发第{$i}段奖..\n";
                    $log['action'] = __CLASS__.'.'.__FUNCTION__;
                    //print_r($award_config);
                    self::awardQiangQin($award_config,$i,$my_num,$player_pos,&$player,$config,&$log,$rank,$uid,$sec);

                    // 标记已经领取
                    $player_pos[$i] = 1;
                    $player->objectPut('qiyu','yanziwuqiangqin_'.date('Ymd',$config['start_t']),$player_pos);
                    $result = $player->commit();
                    $log['ret'] = $result;
                    glog::stat($log);
                }
                //print_r($list);
            }
        }

    }
    
    /**
     * 抢亲 发奖
     */
    private static function awardQiangQin($award_config,$tag,$my_num = 0,$player_pos = array(),&$player,$config,&$log,$rank,$uid,$sec){
        if(empty($award_config)){
            $awards = array();
        }else{
            $awards = $award_config['awards'.$tag];
        }

        $send_awards = array();
        if($tag == 3){
            if($my_num >= 2000){
                foreach($awards as $value){
                    if($value['tag'] == 102001){
                        $is_have = $player->objectGet('peoples',102001);
                        //没有王语嫣
                        if(empty($is_have)){
                            //$object = array('tag'=>102001,'level'=>1,'create_t'=>$now);
                            //$player->objectPut('peoples',102001,$object);
                            $send_awards[] = array('tag'=>$value['tag'],'num'=>$value['num']);
                        }else{
                            //有王语嫣发魂魄
                            //$player->numberIncr('soul',112001,1000);
                            $send_awards[] = array('tag'=>112001,'num'=>1000);
                        }
                    }else{//加道具
                        //$player->addItem($value['tag'],$value['num']);
                        $send_awards[] = array('tag'=>$value['tag'],'num'=>$value['num']);
                    }
                } 
            } 
        }else{
            foreach($awards as $value){
                //$player->addItem($value['tag'],$value['num']);
                $send_awards[] = array('tag'=>$value['tag'],'num'=>$value['num']);
            } 
        }

        // 如果是最后一次奖励既24点奖励，根据比例返回一定元宝 
        if($tag == 3 && !empty($my_num)){
            // 防止小数点，返回比例是500 表示 50%返还
            if(!empty($award_config)){
                $return_gem = ceil($my_num * $award_config['fanhuan'] / 1000);
            }else{
                $return_gem = ceil($my_num * 50 / 1000);
            }
            //$player->rewardGem($return_gem,'燕子坞抢亲活动奖励');
            $send_awards[] = array('tag'=>'gem','num'=>$return_gem);
        }

        $date_rever = array('12:00','21:00','24:00');
        $value = array(
            'msg_date'=>$date_rever[$tag-1],
            'msg_rank'=>$rank+1,
            'msg_awards'=>model_Util::returnMsgStr($send_awards)
        );
        $content = model_Translate::getTrans("tid#act_yanziwu_before20_msg",$value);
        //echo $content."\n";
        //发传书奖励
        model_Award::awardToUser($uid,$sec,$content,$send_awards);
        echo "{$uid}-{$sec}-{$content}奖励如下：\n";
        print_r($send_awards);
        $log['uid'] = $uid;
        $log['sec'] = $sec;
        $log['tag'] = $tag;
        $log['rank'] = $rank;
        $log['player_pos'] = $player_pos;
        $log['my_num'] = $my_num;
        $log['awards'] = $send_awards;
    }

    /**
     * 抢亲 获取处理后的排行
     */
    private static function getQiangQinRank($start,$end,$redis,$redis_key,$config,$sec){
        $i = 1; // 排名偏移量
        $list = array();
        $ret = $redis->zrevrange($redis_key,$start,$end,true);
        // 用于计算并列情况的临时变量
        $num_temp = -1;

        // 拆解数据
        foreach($ret as $key=>$value){
            $id = $key;
            $name = $redis->hget("act_yanziwuqiangqin_".date('md',$config['start_t'])."_".$sec."_data",$key);
            $name = !empty($name['name'])?$name['name']:'';

            $key_num = $i-1;

            // 第一次赋值
            $list[$key_num] = array('id'=>$id,'rank'=>$start+$i++,'name'=>$name,'num'=>$value);
       
            // 处理并列情况
            if($value == $num_temp){
                $list[$key_num]['rank'] =  $list[$key_num-1]['rank'];
            }
            $num_temp = $value;
        }

        // 排除并列第一的情况
        foreach($list as $key2=>$value2){
            if($value2['rank'] == 1 && $key2 != 0){
                $new_last = $redis->hget("act_yanziwuqiangqin_".date('md',$config['start_t'])."_".$sec."_data",$list[$key2]['id']);
                $old_last = $redis->hget("act_yanziwuqiangqin_".date('md',$config['start_t'])."_".$sec."_data",$list[0]['id']);
                if($new_last['last_pay'] < $old_last['last_pay']){
                    $list[0]['rank'] = 2;
                    $list[$key2] = $list[0];
                    $list[0] = $value2;
                }else{
                    $list[$key2]['rank'] = 2;
                }
            }
        } 
        return $list;
    }

    static private function showError($s,$l){
        exit("\n LINE:$l==".$s."\n");
    }

    /* 找出 vip等级和实际充钱金额对不上的用户 */
    public function action_check_vip() {

        echo date('Y-m-d H:i:s')."统计充值\n";
        // 读取所有的充值信息，计算每个账号的真实充值
        $mon = getApp()->getPaymentMongoConnection();
        $rows = $mon->find(array('status' => StatusCode::payment_finished, 'action' => 'recharge_gem'), array(), array('_tm' => 1));
        $payments = array();
        $payments_check = array();
        foreach ($rows as $r) {
            $uid = $r['_u'];
            $section_id = $r['_sec'];

            $payments_check[$section_id][$uid] += $r['agem'];
        }
        echo date('Y-m-d H:i:s')."统计充值完毕\n";

        $maxuid = model_LoginUser::maxuid();
        for ($uid = $maxuid; $uid >=1; $uid--) {
            $login_model = new model_LoginUser($uid);
            $login_model->get(array('pid' => 1, 'istest' => 1, 'secs' => 1, 'isdev' => 1, 'isban'=>1));
            $d = $login_model->doc();

            $pid = $d['pid'];
            $istest = $d['istest']==1?'test':'';
            $isdev = $d['isdev']==1?'dev':'';
            $isban = $d['isban']==1?'enable':'disabled';

            if (empty($d['secs'])) {
                $d['secs'] = array();
            }
            foreach ($d['secs'] as $section_id => $timestamp) {
                $player = new model_Player($uid, $section_id);
                $data = $player->getFields(array(
                    'vip', 'gem', 'total_gem_added', 'total_gem_rewarded', 'total_gem_rewarded2', 'total_gem_used'
                ));
                $gemorder_agem = $payments_check[$section_id][$uid];
                if($gemorder_agem>0 || $data['total_gem_added']>0 || $data['vip']['lvl'] > 4 ){
                    $gemorder_cash = $gemorder_agem / 10;
                    $db_cash = $data['total_gem_added'] / 10;
                    if($db_cash != $gemorder_cash){
                        echo "E,$pid,$uid,$section_id,$istest,$isdev,        ";
                        echo "{$data['vip']['lvl']}, $gemorder_cash != {$db_cash}    $isban\n";
                    }else{
                        //echo "O,$uid,$section_id\n";
                    }
                }else{
                    //echo "O,$uid,$section_id\n";
                }
            }
            if($uid % 1000 == 0){
                echo date('Y-m-d H:i:s')."\t current uid: $uid\n";
            }
        }
    }

}

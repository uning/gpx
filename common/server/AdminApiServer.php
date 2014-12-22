<?php
/**
 * Short description for AdminApiServer.php
 *
 * @package AdminApiServer
 * @author playcrab <playcrab@ares_dev_27>
 * @copyright PlayCrab (C) 2014-05-15
 */

class AOpLog extends model_OpLog {
	function __construct(){
		$this->_mc = new PL_Db_Mongo(DbConfig::getMongodb('log'));
		$this->_mc -> switchColl('gmoplog');
	}
	/**
	 * 从最新的获取
	 */
	static function getRecent($cond = array(),$num = 100,$start = 0,$date = '',$sec = false){
		if(!$inst)
			$inst = new static($date,'',$sec);
		$mc = $inst->_mc;
		$sort = array('_tm'=>-1);
		//return $inst->_mc->findByIndex('',$cond,$num,$start,array(),$sort);
		//return $mc->findByIndex('',$cond,$num,$start,array(),$sort);
        return $mc->find($cond)->limit($num)->skip($start)->sort($sort);
	}
}
register_shutdown_function(function(){
	$app = app();
	$logp = &$app->vget('LOGP');
	if($logp && $logp['msg']){
		$logp['_tm'] = $_SERVER['REQUEST_TIME'];
		$logp['_au'] = $_SERVER['PHP_AUTH_USER'] ? $_SERVER['PHP_AUTH_USER'] : $_SESSION['gm_admin'];
		AOpLog::add($logp);
        $glog = $logp;
        if(!$glog['action'])
            $glog['action'] = 'AdminServer';
        $glog['type'] = 'gmoplog';
        //不要记log了，不然日志不规范
        //glog::stat($glog);
	}

});

class AdminApiServer extends PL_Server_Page{

	/**
	 * 根据 action 执行不同的方法
	 * 默认为index
	 */
	function handle(&$ret = null){
		$action = $this->getParam('action');
		$logmsg = $this->getParam('logmsg');
		if($logmsg){
			$app = app();
			$logp = &$app->ref('LOGP');
			$logp['action'] = $action;
			$logp['msg'] = $logmsg;
			$logp['uid'] = self::sessionP('uid',0);
			$logp['sec'] = self::getParam('sec','');
		}
        $method = 'action'.ucfirst($action);
        if(method_exists($this,$method)){
            $this->$method();
		}
	}

    function actionSortNotice(){
        $order = $_POST['order'];
        $key   = $_POST['key'];
        $nm    = new model_Notice();
        $ret   = $nm->modifySort($key, $order);

        if($ret !== false){
           echo "公告修改成功！请手动刷新页面查看结果";
        }else{
           echo "公告修改失败！";
        }
    }

    function actionSetAllNotice(){
        $idx           = $_POST['idx'];
        $d             = $_POST['d'];
        $d['start_t']  = strtotime($d['start_t']);
        $d['finish_t'] = strtotime($d['finish_t']);
        $nm            = new model_Notice();
        $ckey          = "fileconfigs::config_notice";

        if ($_POST['op'] === 'del') {
           $rt = $nm->delMatchTitle($ckey, $d);
        }else{
           $rt = $nm->modifyMatchTitle($ckey, $d);
        }
    }

    function actionSendSystemMsg(){
        //$uid = (int)self::getParam('uid');
        $userstr = self::getParam('uid');
        $sec = self::getParam('sec');


        $content = self::getParam('content');
        $desc = self::getParam('desc');
        $items = self::getParam('items');
        $ready_id = self::getParam('ready_id');

		foreach((array)$items as $value){
			$tag = $value['tag'];
			$num = intval($value['num']);
			if($tag == 'gem' && $num > 100000){
				echo "error, 元宝不能大于100000";
				exit;
			}
		}
        $key = uniqid();
        $msg = array(
            'key'=>$key,
            'time'=>app()->now,
            'content'=>$content,
            'type'=>'system',
        );
        if($userstr != "all"){
            $uids = explode(',',$userstr);
            $itemmsg = "";
            foreach($uids as $uid){
                
                $uid = getApp()->getRealUid($uid, $sec);
                $sec = getApp()->getRealSec($sec);
                $player = new model_Player($uid,$sec);
                $level = $player->objectGet("base", "level");
                foreach((array)$items as $key => $value){

                    $tag = $value['tag'];
                    $num = intval($value['num']);

                    if($tag == 'qiyu_zhidian'){

                        $zhidian_lvl =  max(1, $level * 3 - 34);

                        for($i = 0; $i<$num; $i++){
                            $items[] =  array('tag' => 'qiyu_zhidian', 'level' => $zhidian_lvl, 'num' => 1);
                        }
                        unset($items[$key]);

                    }

                }
			if(is_array($items) && count($items) > 0){
                $msg['status'] = 'award';
                #$msg['content'].="\n$desc";
                $cdkey = model_Cdkey::gen($items,$desc,1,'def');
                $msg['cdkey'] = $cdkey;
            }
                
                $r = model_Chat::sendMsg($msg,$uid,'origin',$sec);
            }
        }else{
            $r = model_Chat::send($msg,2,"","toall",$sec);
        }
        if($r){
            $mon = new  PL_Db_Mongo(DbConfig::getMongodb('userlogin'));
            $mon->switchColl('msgready');
            $mon = $mon->getmc();
            $id = $mon->update(array('ready_id'=>(int)$ready_id),array('$set'=>array('applicant_status'=>0,'check_man'=>$_SERVER['PHP_AUTH_USER'])));
            echo json_encode(array('s'=>'OK'));
        }
    }


    function actionSendMsgToAwardUsers(){
        $uid_secs = self::getParam('uid_secs');
        $content = self::getParam('content');
        $desc = self::getParam('desc');
        $items = self::getParam('items');
        $task_num = self::getParam('num');
        error_log("====接受到奖励信息==".json_encode($uid_secs));
        if(!$uid_secs){
            return ;
        }

        foreach((array)$items as $value){
            $tag = $value['tag'];
            $num = intval($value['num']);
            if($tag == 'gem' && $num > 100000){
                echo "error, 元宝不能大于100000";
                exit;
            }
        }

        $key = uniqid();
        $msg = array(
            'key'=>$key,
            'time'=>app()->now,
            'content'=>$content,
            'type'=>'system',
        );

        //福利号总服务器地址
        $all_config = include_once(ROOT."/admin/view/public_const_config.php");
        //设置作废福利号接口
        $server_url = $all_config['server_api_url']."?action=setRepeal&mod=admin";

        $DEBUG = true;
        foreach((array)$uid_secs as $uid => $sec){
            //if( $uid != 3879133 )continue;//temp
            //error_log("send[$uid][$sec]");

            //check 是否合服 改变UID
            $sec_config = getApp()->getSectionConfig($sec);
            if(isset($sec_config['merge'])){
                $uid = "{$sec}_{$uid}";
                $sec = $sec_config['merge'];
            }
            if($DEBUG) error_log("开始发送元宝：{$uid}--{$sec}");

            $player = new model_Player($uid,$sec);
            $player->getFields(array('level', 'test_status','login_t','gem'));
            $level = $player->numberGet("base", "level");
            $gem = $player->objectGet("base", "gem");
            $login = $player->objectGet("base", "login_t");
            //只执行一次  执行一次没有句号的描述 就可以将大于20w的置为20w
            $bool = ($content=="鉴于掌门对武林做出了卓越贡献，特此奖励掌门一些物件，方便行走江湖，还望掌门笑纳")?true:false;
            if($bool && $gem>200000){//当前元宝大于20w 且 不是单次发送 将该玩家元宝置为20w 不给发此次元宝
                error_log("change_gem:uid:$uid,$sec,gem:$gem");
                $player->numberPut('base','gem',200000);
                $player->commit();
                if($DEBUG) error_log("error.20w");
                continue;
            }
            if($task_num!=1 && $gem>50000){//现有元宝数量大于N不给发 且 不是单次发送
                if($DEBUG) error_log("{$gem}元宝大于5w，{$task_num}");
                continue;
            }
                
            if(($_SERVER['REQUEST_TIME'] - $login) > 10*86400){//10天没登陆 设置为作废福利号 
                model_Util::send_post($server_url,array('uid'=>$uid,'sec'=>$sec));
                if($DEBUG) error_log("大于10天没登录");
                continue;
            }

            foreach($items as $key => $value){
                $tag = $value['tag'];
                $num = intval($value['num']);
                if($tag == 'qiyu_zhidian'){
                    $zhidian_lvl =  max(1, $level * 3 - 34);
                    for($i = 0; $i<$num; $i++){
                        $items[] =  array('tag' => 'qiyu_zhidian', 'level' => $zhidian_lvl, 'num' => 1);
                    }
                    unset($items[$key]);
                }
            }

    
            if($DEBUG) error_log("====={$value['tag']}={$value['num']}");
            if($value['tag'] == 'gem'){
                if($DEBUG) error_log("enter");
                $player->checkVipUpgradeAward($value['num']);
                $player->commit();
            }

            if(is_array($items) && count($items) > 0){
                $msg['status'] = 'award';
                #$msg['content'].="\n$desc";
                $cdkey = model_Cdkey::gen($items,$desc,1,'def');
                $msg['cdkey'] = $cdkey;
            }

            $r = model_Chat::sendMsg($msg,$uid,'origin',$sec);
        }
    }

}


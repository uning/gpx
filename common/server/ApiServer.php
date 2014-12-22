<?php

//global function
/**
 * 从数组读取参数
 *
 **/
class ApiServer extends PL_Server_Json {


	//添加log等信息
	public function __construct(){

		$ser = $this;
		//读取请求之前，验证是否多点登录
		//在执行controller 逻辑之前判断
		self::rEvent('E_BCR',function() use($ser){
			if($_REQUEST['__noauth__'] == 1){
				return;
			}
			$app = app();
			$sec = $app->getsec();
            $uid = $app->getuid();
            $um = new model_LoginUser($uid);
            $d  = $um->get();
            if($d['isban']){
               exit(0);
            }

            //合服阻挡进入
            $filter_config = dzm_base::load_config('mergesec','prevent_enter');  
            model_Util::preventEnter($filter_config,$sec,$uid);

			$cm  = $app->vget('CM');
			$cc  = $app->vget('CC');
			if($app->vget('RESOVER')){
				return;
			}

			$iccs = $app->vget('ICCS');
			$icms = $app->vget('ICMS');

			$skip_sig_check_method = $app->vget('skip_sig_check_method');
			$skip_sig_check_controller = $app->vget('skip_sig_check_controller');
            if(!isset($skip_sig_check_method[$cm]) && !isset($skip_sig_check_controller[$cc])){
                // 验证数据签名
                $req = &$app->vget('REQARR');
                $ret = $ser->authSig($req);
                if($ret){
                    //glog::info(json_encode($ret),'sign');
                    //glog::info(json_encode($req),'sign');
                    $app->vset('RESARR',$ret);
                    $ser->finishResponse();
                    exit(0);
                }
            }


			//do nothing , 登陆，打点，都没有session
			if($cc == 'System' || $cc == 'Statistic')
				return;
			if(
				 isset($iccs[$cc]) 
				|| isset($icms[$cm])
			    ){
					$app->vset('NOLOG',true);
				return ;
			}
			$sess = $app['session'];
			if($sess->isDupLogin()){
				$app->vset('RESARR',array('s'=>'dup'));
				$ser->finishResponse();//
				exit(0);
			}

		});

		//退出之前，结果已经返回客户端
		self::rEvent('E_QUIT',function() {
			//记录日志，增加统计
			$app = app();

			$req = &$app->vget('REQARR');
			$res = &$app->vget('RESARR');
			$ss = &$app->vget('session');
			$uid = $app->getuid();
			$sec = $app->getsec();
			$ltime = $app->getltime(); //本次登录时间
			$cm  = $app->vget('CM');
			$s   = $res['s'];
			$time = $_SERVER['REQUEST_TIME'];

			//全日志
            /* TODO 做个开关，只针对一小部分人开启全日志
			$hour = date('Ymd',$time);
            $request_end_time = microtime(true);
            glog::detail($uid,$sec,$cm,$request_end_time-$app->request_start_time,$req,$res);
            */
            if($uid == 68321 || $uid == 5225){
                $req['_SERVER'] = $_SERVER;
                glog::detail($uid,$sec,$cm,$request_end_time-$app->request_start_time,$req,$res);
            }




			//处理统计，
			//todo:实时统计的东西也放到这里来
			$stats = &$app->ref('STATS');
			if($stats){
				foreach($stats as &$object){
					$object['_tm'] = $time;
					$object['_sec'] = $sec;
					$object['_u'] = $uid;
					$object['_cm'] = $cm;
					$object['_s'] = 'OK';
					$object['_ver'] = P_VERSION;
					$object['_lvl'] = $_SESSION['lvl'];
					$object['_it'] = $_SESSION['init_time'];
					$object['_vip'] = $_SESSION['vip'];
                    $object['istest'] = $_SESSION['istest'];
                    $object['_source'] = $_SESSION['source'];
                    try{
//                        model_OpLog::add($object);
                    }catch(Exception $ex){
                        //error_log($ex->getMessage());
                        error_log("记录日志出错");
                        error_log(json_encode($object));
                    }
                }

                // scribe关闭连接
                try{
                    model_StatLog::close();
                }catch(Exception $ex){
                    error_log("scribe日志关闭出错");
                }

				if($s == 'OK')
					return;
			}

			if(!$cm){
				return;
			}

			//明确的设置了不记录log
            if($app->vget('NOLOG')){
                return;
            }


			if($app->vget['DEBUG'])
				error_log("session: ".print_r($_SESSION,true)."\n",3,LOG_ROOT."/debug.log");

			$logp = &$app->vget('LOGP');
			if(!$logp){
				$logp = &$req['p'];
			}

			$logp['_cm']  = $cm;
			$logp['_u']   = $uid;
			$logp['_sec'] = $sec;
			$logp['_s']   = $s;
			$logp['_tm']  = $time;
			$logp['msg']  = $res['msg'];
//			model_OpLog::add($logp);
            
		});
	}


	/**
	 * 检查参数签名
	*/
	function authSig($request){
        $method = $request['m'];
        $param = $request['p'];
		if($param['signature']){
			$signature = $param['signature'];
			unset($param['signature']);

			$keys = array_keys($param);
			sort($keys);
			$s = '';
			foreach($keys as $key){
                $value = $param[$key];
                if(is_string($value) || is_numeric($value) || is_bool($value)){
                    $s .= '&'.$key.'='.$value;
                }
			}
			$s = substr($s,1) . $method . P_VERSION . '~@#1xdaf,dmuopamie%%123.';
			$mysig = md5($s);
			if($mysig!=$signature){
                glog::info("签名不对[$mysig]!=[$signature], ".json_encode($request),'sign');
				return array('s'=>StatusCode::invalid_siginature,'msg'=>'invalid signature');
			}
			$timestamp = $param['timestamp'];

            if($method=='System.login'){
                // 手机设备时间和服务器时间可能有差异，所以第一次请求 System.login 时不验证时间。
                return false;
            }
            
            // 1. 对 timestamp 做验证 与 服务器当前时间相差太多的 认为是不合法的请求
            $now = getApp()->now;
            if(abs($now - $timestamp) > 300){
                $ts1 = date('Y-m-d H:i:s',$now);
                $ts2 = date('Y-m-d H:i:s',$timestamp);
                glog::info("请求时间异常 server[$ts1], client[$ts2], ".json_encode($request),'sign');
                return false;
                return array('s'=>StatusCode::invalid_request_time,'msg'=>'invalid timestamp');
            }
            // 2. 记录用户上次调用这个接口的时间戳，如果新的 timestamp <= old_timestamp 则认为是不合法的请求
            $uid = getApp()->getuid();
            $section_id = getApp()->getsec();
            if($uid && $section_id){
                if(model_Util::inBlacklist($uid)){
                    //黑名单访问，禁止
                    glog::info("黑名单中玩家[$uid]访问分区[$section_id] ".json_encode($request),'blacklist');
                    return array('s'=>StatusCode::invalid_request_time,'msg'=>"uid[$uid] is in blacklist");
                }
                try{
                    $redis = DbConfig::getRedis('cache');
                    $timestamp_key = "sig_{$section_id}_{$uid}_{$method}";
                    $old_timestamp = $redis->get($timestamp_key);
                    if(is_numeric($old_timestamp)){
                        // 手机端网络超时后重试，后台可能会收到两次同样时间戳的请求 所以 $timestamp == $old_timestamp 还是很有可能的
                        if($timestamp < $old_timestamp){
                            $ts1 = date('Y-m-d H:i:s',$old_timestamp);
                            $ts2 = date('Y-m-d H:i:s',$timestamp);
                            glog::info("请求时间异常, 上次请求[$ts1], 本次请求[$ts2], ".json_encode($request),'sign');
                            return false;
                            return array('s'=>StatusCode::outdated_siginature,'msg'=>'outdated signature','debug'=>"old_timestamp: $old_timestamp");
                        }else{
                            $redis->multi();
                            $redis->set($timestamp_key,$timestamp);
                            $redis->expire($timestamp_key,360);
                            $redis->exec();
                            return false;
                        }
                    }else{
                        $redis->multi();
                        $redis->set($timestamp_key,$timestamp);
                        $redis->expire($timestamp_key,360);
                        $redis->exec();
                        return false;
                    }
                }catch(Exception $ex){
                    error_log("无法连接 cache redis ");
                    return false;
                }
            }
			return false;
		}
        glog::info("没有签名，".json_encode($request),'sign');
		return array('s'=>StatusCode::invalid_siginature,'msg'=>'no signature');
	}

	
}

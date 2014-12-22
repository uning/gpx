<?php
//不要放在base.php
function pcdecrypt_ecb($data,$key){
    //echo "==============\n pcdecrypt \n==============\n";
    $msg = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $data, MCRYPT_MODE_ECB);
    //echo "msg: ".bin2hex($msg)."\n";
    //echo "paddign char: ". bin2hex(substr($msg,-1)) ."\n";
    $padding_length = ord(substr($msg,-1));
    //echo "pl: ".$padding_length."\n";
    $msg = substr($msg,0,-$padding_length);
    //echo "msg: ".bin2hex($msg)."\n";
    return $msg;
}
function pcencrypt_ecb($data,$key){
    //echo "==============\n pcencrypt \n==============\n";
    # Add PKCS7 padding.
    $block = 16;
    $pad = $block - (strlen($data) % $block);
    //echo "padding length: $pad\n";
    $data .= str_repeat(chr($pad), $pad);
    $msg = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data, MCRYPT_MODE_ECB);
    return $msg;
}
/**
 * 公匙解密
 * @param unknown_type $crypttext
 * @param unknown_type $publickey
 * @author andsky 669811@qq.com
 */
function pp_pay_decode($crypttext, $publickey)
{
    $crypttext = base64_decode($crypttext);
    $pubkeyid = openssl_get_publickey($publickey);
    if (openssl_public_decrypt($crypttext, $sourcestr, $pubkeyid, OPENSSL_PKCS1_PADDING))
    {
        return $sourcestr;
    }
    return FALSE;
}
/*从xml中获取节点内容*/
function getDataForXML($res_data,$node)
{
    $xml = simplexml_load_string($res_data);
    $result = $xml->xpath($node);

    while(list( , $node) = each($result)) 
    {   
        return $node;
    }   
}

class JSApiServer extends PL_Server_Page{
    /*
     * 审计数据接口，读取 order_stat 中的数据
     * 
     * 参数：
     *
     *      group_by_month  不传，或者传任何非'no'的值时，结果按月度汇总
     *      start_date
     *      end_date
     *          [start_date, end_date] 闭区间
     *      txt 不传，或者传任何非'no'的值时，结果按照文本输出
     *
     */
    public function actionReport(){
        $txt = self::getParam('txt','no');
        $group_by_month = self::getParam('group_by_month','no');
        $start_date = self::getParam('start_date','');
        $end_date = self::getParam('end_date','');
        $mon = new  PL_Db_Mongo(DbConfig::getMongodb('userlogin'));
        $mon->switchColl('order_stat');
        $condition = array();
        if(!empty($start_date)){
            $condition['_id']['$gte'] = $start_date;
        }
        if(!empty($end_date)){
            $condition['_id']['$lte'] = $end_date;
        }
        $rows = $mon->find($condition);
        $rows->sort(array('_id' => 1));
        $data = array();
        foreach($rows as $row){
            $day = $row['_id'];
            $count = $row['value']['count'];
            $alipay_cash = $row['value']['alipay_cash'];
            $cash = $row['value']['cash'];
            $month = date("Y-m",strtotime($day));
            if($group_by_month != 'no'){
                $key_name = 'month';
            }else{
                $key_name = 'day';
            }
            if(!isset($data[$$key_name])){
                $data[$$key_name] = array($key_name => $$key_name,'count'=>0,'cash'=>0,'alipay_cash'=>0);
            }
            $data[$$key_name]['count'] += $count;
            $data[$$key_name]['cash'] += $cash;
            $data[$$key_name]['alipay_cash'] += $alipay_cash;
        }
        if($txt != 'no'){
            header('content-type: text/plain; charset=utf8');
            foreach($data as $key => $r){
                echo "$key\t{$r['count']}\t{$r['alipay_cash']}\t{$r['cash']}\n";
            }
        }else{
            die(json_encode(array('s'=>'ok','data'=>$data)));
        }
    }

    /*
     * 统计gemorder中的数据，写入 order_stat
     *
     *      start_date
     *      end_date
     *          [start_date, end_date] 闭区间
     *      txt 不传，或者传任何非'no'的值时，结果按照文本输出
     */
    public function actionCreateReport(){
        $txt = self::getParam('txt','no');
        $start_date = self::getParam('start_date','');
        $end_date = self::getParam('end_date','');

        $start_time = strtotime($start_date);
        $end_time = strtotime($end_date) + 86400;
        $mon = new  PL_Db_Mongo(DbConfig::getMongodb('userlogin'));
        $mon->switchColl('gemorder');
        $condition = array('status'=>StatusCode::payment_finished,'action'=>'recharge_gem','finish_t'=>array('$gte'=>$start_time,'$lt'=>$end_time),'istest'=>array('$ne'=>1));
        //die(var_export($condition,1));
        $rows = $mon->find($condition);

        $data = array();
        foreach($rows as $row){
            $day = date('Y-m-d',$row['finish_t']);
            if(!isset($data[$day])){
                $data[$day] = array('day' => $day,'count'=>0,'cash'=>0,'alipay_cash'=>0);
            }
            if($row['source'] == 'internal'){
                $data[$day]['alipay_cash'] += $row['cash'];
            }else{
                $data[$day]['cash'] += $row['cash'];
            }
            $data[$day]['count'] += 1;
        }
        $mon2 = new  PL_Db_Mongo(DbConfig::getMongodb('userlogin'));
        $mon2->switchColl('order_stat');
        foreach($data as $day=>$d){
            $insert_data = array('_id'=>$day,'value'=>$d);
            $mon2->update(array('_id'=>$day),$insert_data,array('upsert'=>true));
        }
        if($txt != 'no'){
            header('content-type: text/plain; charset=utf8');
            foreach($data as $key => $r){
                echo "$key\t{$r['count']}\t{$r['alipay_cash']}\t{$r['cash']}\n";
            }
        }else{
            die(json_encode(array('s'=>'ok','data'=>$data)));
        }
    }


    /*
     * 本接口目前废弃了 owen 2013年12月12日00:31:27
     */
	public function actionReportbysec(){
        $period = self::getParam('period',7);
        $sec = self::getParam('sec');
        if(!is_numeric($period) || $period <= 0){
            $period = 7;
        }
        $ret = model_Util::realtime_stat_by_sec($period, $sec);
        die(htmlentities($_GET['callback']).'('.json_encode($ret).')');

	}
    /*
     * 获取全服、或者某个分区、或者某个渠道的在一段时间内的统计数据。
     *
     * 输入：
     *
     *      sec     分区编号
     *      src     渠道编号
     *      period  查询日期跨度，默认是7天
     *      begin   查询日期区间的最后一天
     *
     *  当 sec 存在，且 src 不存在时，则查询 sec 对应的分区统计数据。
     *  当 src 存在，且 sec 不存在时，则查询 src 对应的渠道统计数据。
     *  当 sec 和 src 都为空时，查询全服的统计数据。
     *
     *  假设，begin 是 2013-12-12，period 是 3，那么返回的下面几天的统计数据：
     *
     *      [ 2013-12-12, 2013-12-11, 2013-12-10 ]
     *
     *  返回值格式：
     *
     *      参见 model_Util:realtime_stat_new
     *
     */
    public function actionReportNew(){
        $period = self::getParam("period",7);
        $section = self::getParam("sec");
        $source = self::getParam("src");
        $begin = self::getParam("begin");
        if(!is_numeric($period) || $period <= 0){
            $period = 7;
        }

        if($section && !$source){
            $loginOpt = $section;
            $payOpt = array("_sec"=>$section);
            $dispField = "Section.$section";
        }else if($source && !$section){
            $loginOpt = $source;
            $dispField = "Source.$source";
            $payOpt = array("source"=>$source);
        }else{
            $loginOpt = "all"; 
            $dispField = "All";
            $payOpt = null;
        }
        $ret = model_Util::realtime_stat_new($period, $loginOpt, $dispField, $payOpt, $begin);
        die(htmlentities($_GET['callback']).'('.json_encode($ret).')');

	}

    /**
     * 给运维部门的分区、服务器角色数据
     *
Array
(
    [type] => Array
    (
        [0] => db
    )

    [dists] => Array
    (
        [0] => Array
        (
            [code] => s1
            [name] => 华山论剑1
            [ips] => Array
            (
                [type] => db
                [db_type] => mongodb
                [ip] => 127.0.0.1
                [port] => 35050
                [memo] => 数据库
            )

        )))
     *
     */
    public function actionGetDist(){
        $auth_key = self::getParam("key",'');
        $check_key = md5($_SERVER['HTTP_HOST'].'ares');
        if(empty($auth_key) or $auth_key != $check_key){
            die(json_encode(array('flag'=>0,'data'=>'失败了!')));
        }

        $all_config = getApp()->getSecDbConfig();
        $dists = array();
        foreach($all_config['secs'] as $sec=>$item){
            $dists[] = array(
                'code'=>$sec,
                'name'=>$item['name'],
                'ips'=>array(
                    array(
                        'type'=>'db',
                        'db_type'=>$item['db_type'],
                        'ip'=>$item['ip'],
                        'port'=>$item['port'],
                        'memo'=>'数据库'
                    ),
                    array(
                        'type'=>'web',
                        'ip'=>$item['api_url'],
                        'port'=>80,
                        'memo'=>'web服务器'
                    ),
                    array(
                        'type'=>'chat',
                        'ip'=>$item['socket_host'],
                        'port'=>$item['socket_port'],
                        'memo'=>'聊天服务器',
                    )

                ),
            );
        }
        $global = array();
        foreach((array)$all_config['rediss'] as $name=>$item){
            $global[] = array(
                'type'=>'db',
                'ip'=>$item['ip'],
                'port'=>$item['port'],
                'name'=>$name,
                'db_type'=>'redis',
                'memo'=>'redis数据地址-'.self::getEffect('redis',$name),
            );
        }
        foreach((array)$all_config['mongos'] as $key=>$item){
            $one = array('memo'=>'mongo公用数据库-'.self::getEffect('mongo',$item['name']),'type'=>'db');
            $global[] = array_merge($item,$one);    
        }
        $result = array(
            'type'=>array('db','web','chat'),
            'dists'=>$dists,
            'global'=>$global,
        );
        //echo "<pre>";
        //print_r($result);
        echo json_encode(array('flag'=>1,'data'=>$result));
    }
    private static function getEffect($type, $name){
        $redis_all_kv = array(
            'cache'=>'缓存数据1',
            'worldboss'=>'铜人数据',
            'league'=>'联盟数据',
            'leaguewar'=>'盟战数据',
            'serializer_cache'=>'缓存数据2',
            'award'=>'传书数据',
            'activity'=>'活动数据',
            'duomiji'=>'夺秘籍',
            'realtime_stat'=>'实时统计',
            'token_badge'=>'IOS设备token',
            'queue'=>'消息队列',
            'chat'=>'传书',
            'lunjian'=>'论剑',
            'notice'=>'公告',
            'macblacklist'=>'设备黑名单',
            'rank'=>'各功能排行数据',
            'heimuya'=>'黑木崖',
            'record'=>'记录',
            'session'=>'session',
        );
        $mongo_all_kv = array(
            'users'=>'用户默认数据库',
            'userlogin'=>'用户登录数据库',
            'log'=>'log数据库',
            'stat'=>'统计数据库',
            'msg'=>'聊天消息',
        );
        //echo $type."===".$name."<br>";
        if($type == 'redis'){
            return $redis_all_kv[$name];
        }else{
            return $mongo_all_kv[$name];
        }
    }

    public function actionTodaySum(){
        $type = self::getParam("type");
        $ret = model_Util::get_today_summary($type);
        die(htmlentities($_GET['callback']).'('.json_encode($ret).')');
    }

    public function response($arr){
        global $_PAYMENT_RESPONSE;
        $_PAYMENT_RESPONSE = $arr;
        $jsonp_response = self::getParam('jsonp_response',0);
        if($jsonp_response){
            die(htmlentities($_GET['callback']).'('.json_encode($arr).')');
        }else{
            echo json_encode($arr);
        }
    }

	/**
	 * 验证91签名
	 */
	public function verifySign($pid_prefix){
		if($pid_prefix == '91_'){//android
            if($_REQUEST['AppId'] == '100829'){
                $app_key = 'ca60cc3417984196d6713463e37095129efd606bf991bdaf';
            } else if($_REQUEST['AppId'] == '106796'){//91中清版
                $app_key = '3f7886cc66dad1655f4788f9e50d1e8481a7ff7213221b78';
            }

		}else if($pid_prefix == 'ios91_'){//ios
			$app_id = '107367';
			$app_key = 'c8cb61dd00dc2f973320042271e520f7b864cc15f7362709';
		}
		$string = "{$_REQUEST['AppId']}{$_REQUEST['Act']}{$_REQUEST['ProductName']}{$_REQUEST['ConsumeStreamId']}{$_REQUEST['CooOrderSerial']}{$_REQUEST['Uin']}{$_REQUEST['GoodsId']}{$_REQUEST['GoodsInfo']}{$_REQUEST['GoodsCount']}{$_REQUEST['OriginalMoney']}{$_REQUEST['OrderMoney']}{$_REQUEST['Note']}{$_REQUEST['PayStatus']}{$_REQUEST['CreateTime']}{$app_key}";
		$result = hash('md5', $string);
		if($result === $_REQUEST['Sign']){
			return true;
		}
		return false;
	}
        /*
            [2012-09-10 13:57:09] paycallback begin
            array (
                'AppId' => '109129',
                'ProductId' => '109129',
                'Act' => '1',
                'ProductName' => '测试游戏应用',
                'ConsumeStreamId' => '5-24831-20120910135707-5000-7949',
                'CooOrderSerial' => '27451df7-e8bc-400c-a6be-e0949eb10b0e',
                'Uin' => '320884802',
                'GoodsId' => 'airmud.ares.g760',
                'GoodsInfo' => '550元宝',
                'GoodsCount' => '1',
                'OriginalMoney' => '50.00',
                'OrderMoney' => '50.00',
                'Note' => 's1',
                'PayStatus' => '1',
                'CreateTime' => '2012-09-10 13:57:07',
                'Sign' => 'd73cab5c070c74324614e8596606b941',
            )
            action: recharge_gem
            _tm : 时间
            _sec : 分区编号
            _u : 用户uid
            _ver : 游戏版本号
            _vip : 用户vip等级
            _it : 用户数据初始化时间
            _lvl : 用户等级
            device_id : 用户设备id
            pid : 用户平台id
            ogem : 充值前宝石数量
            create_t : 账单创建时间
            product_id : 充值选项
            status : 账单状态，0：开始；1：结束；2：被用户取消；3：失败；
            agem : 本次充值增加的宝石数量
            gem : 充值后宝石数量
            finish_t : 充值账单结束的时间
            cash : 本次充值消耗的现金
            msg : 其他说明
         */
    public function nd91_payment($pid_prefix){
        // todo 先验证签名
		$valid = self::verifySign($pid_prefix);

		if(!$valid){
            return $this->response(array('ErrorCode'=>0,'ErrorDesc'=>"签名验证失败！"));
		}

        $pid = $pid_prefix.$_REQUEST['Uin'];
        $um = model_LoginUser::searchUniq('pid',$pid);
        if(is_null($um)){
            return $this->response(array('ErrorCode'=>0,'ErrorDesc'=>"用户[{$pid}]不存在"));
        }
        if(!$um){
            return $this->response(array('ErrorCode'=>0,'ErrorDesc'=>"用户[{$pid}]不存在"));
        }
        $uid   = $um->id();
        if($uid<1){
            return $this->response(array('ErrorCode'=>0,'ErrorDesc'=>"用户[{$pid}]不存在"));
        }
        //合服之后在note中添加uid字段 by zhangjun
        list($section_id,$uid) = explode(",",$_REQUEST['Note']);
        //切版本的时候做兼容 稳定之后可以删除 by zhangjun
        if(empty($uid)){
            $uid   = $um->id();
        }

        PL_Session::$usecookie = false;
        $_REQUEST['cid'] = PL_Session::gencid($uid,$section_id);

        $product_id = $_REQUEST['GoodsId'];

        $player = getApp()->getPlayer();
        $lock = DbConfig::getRedis('rank');
        $lk = "pay_lock_{$player->uid}_{$player->section_id}";
        $now = time();
        $res = $lock->SETNX($lk, $now);
        if($res){
            $lock->SETEX($lk, 20, $now);
        }else{
            return $this->response(array('ErrorCode'=>0,'ErrorDesc'=>"订单处理锁定！"));
        }


        $transaction_id = $_REQUEST['CooOrderSerial'];
        $mon = getApp()->getPaymentMongoConnection();
        $payment_info = $mon->findOne(array('_u'=>$player->uid,'transaction_id'=>$transaction_id));

        if(empty($payment_info)){
            // 生成新的订单信息

            $payment_info = $player->createPaymentInfo($product_id);
            $payment_info['transaction_id'] = $transaction_id;
            $payment_info['create_t'] = $_REQUEST['CreateTime'];

            if($_REQUEST['PayStatus']!=1){
                $payment_info['status'] = StatusCode::payment_failed;
                $mon->insert($payment_info);
                return $this->response(array('ErrorCode'=>1,'ErrorDesc'=>"订单已经记录"));
            }
        }else{
            if($payment_info['status'] == StatusCode::payment_finished || $payment_info['status'] == StatusCode::payment_failed ){
                return $this->response(array('ErrorCode'=>1,'ErrorDesc'=>"重复订单{$transaction_id}已经处理"));
            }
        }

        // 订单上次处理异常 或者 新的订单
        $ret = $player->process_payment($payment_info);
        $lock->del($lk);
        if($ret['s'] == StatusCode::ok){
            return $this->response(array('ErrorCode'=>1,'ErrorDesc'=>"订单{$transaction_id}已经处理"));
        }else{
            return $this->response(array('ErrorCode'=>0,'ErrorDesc'=>"订单{$transaction_id}处理失败,".$ret['msg']));
        }
    }
    //91中清包
    public function actionPaycallback_91SD(){
        $this->nd91_payment('91_');
    }
    public function actionPaycallback_91(){
        $this->nd91_payment('91_');
    }
    public function actionPaycallback_ios91(){
        $this->nd91_payment('ios91_');
    }
    public function checkCash($product_id,$cash){
        try{
            $product_config = getApp()->getPaymentConifg($product_id);
        }catch(Exception $ex){
            return false;
        }
        return $cash == $product_config['cash'];
    }
    public function actionPaycallback_iospp(){
            //return $this->response('success');
        $key = '
-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA4OfPwhelsw+S1i5w81fT
jxW9y3qsNENgUmJu9IzhoPmW/pAweHDdVuGCCOgBSpw4xNUhkJF8Re7bCJTGmniW
jB57qqVCYCMix5tJ8Orgy/iyhcrx75ME0HsigI0kbGXn3MaUib7bNShR1eEDGh7I
8b6vsUFaawoVHGcO5QIt76nEwaCavk2psHZ0JBxZLg2vEVfkhKUX9Dz6EuNa+xS+
Q7AtYpd9UWAendErTwbFll+3K2kGVBCYI0zTYSVBS8SX81cQ86I/X3+ZxAz84iyn
JqiFG3PDJtMi0oI3L55gmsy47T/1uBPBhgKiuZ0rdXjiuA8NLvpeGf89FH2QfqCs
XQIDAQAB
-----END PUBLIC KEY-----';
        $data = pp_pay_decode($_REQUEST['sign'], $key);//need urldecode? formate data
        $data = json_decode($data,true);
        $isDataCorrect = false;
        if($data != FALSE){
            if($data['billno'] == $_REQUEST['billno'] && $data['amount'] == $_REQUEST['amount']
               && $data['status'] == $_REQUEST['status'] && $data['app_id'] == $_REQUEST['app_id']){//验签成功
                  $isDataCorrect  = true;
            }
        }

        if(!$isDataCorrect){
            return $this->response(array('ErrorCode'=>0,'ErrorDesc'=>"签名验证失败！"));
        }
        /*2014-5-16 iospp平台合服后从支付回调中无法获取uid信息，现在修改成从订单信息中获取 by zhangjun
        $pid = $_REQUEST['roleid'];
        $um = model_LoginUser::searchUniq('pid',$pid);
        if(is_null($um) || !$um || $um->id() < 1){
            return $this->response(array('ErrorCode'=>1,'ErrorDesc'=>"用户[{$pid}]不存在"));
        }
        $section_id = 's'.$_REQUEST['zone'];
         */

        $transaction_id = $_REQUEST['billno'];
        $mon = getApp()->getPaymentMongoConnection();
        $payment_info = $mon->findOne(array('transaction_id'=>$transaction_id));
        $uid = $payment_info['_u'];
        $section_id = $payment_info['_sec'];

        PL_Session::$usecookie = false;
        //$_REQUEST['cid'] = PL_Session::gencid($um->id(),$section_id);
        $_REQUEST['cid'] = PL_Session::gencid($uid,$section_id);
        $player = getApp()->getPlayer();
        $lock = DbConfig::getRedis('rank');
        $lk = "pay_lock_{$player->uid}_{$player->section_id}";
        $now = time();
        $res = $lock->SETNX($lk, $now);
        if($res){
            $lock->SETEX($lk, 20, $now);
        }else{
            return $this->response(array('ErrorCode'=>0,'ErrorDesc'=>"订单处理锁定！"));
        }
        /*
        $transaction_id = $_REQUEST['billno'];
        $mon = getApp()->getPaymentMongoConnection();
        $payment_info = $mon->findOne(array('_u'=>$player->uid,'transaction_id'=>$transaction_id));
         */
        if(empty($payment_info)){
            return $this->response(array('ErrorCode'=>3,'ErrorDesc'=>"账单[{$transaction_id}]不存在"));
        }else{
            if($payment_info['status'] == StatusCode::payment_finished || $payment_info['status'] == StatusCode::payment_failed ){
                //重复订单
                return $this->response('success');
            }
            if(!$this->checkCash($payment_info['product_id'],$_REQUEST['amount'])){
                //充值数据有误
                return $this->response(array('ErrorCode'=>6,'ErrorDesc'=>"账单[{$transaction_id}]充值金额有误"));
            }
        }
 //       return $this->response(array('ErrorCode'=>1,'ErrorDesc'=>$payment_info));
        // 订单上次处理异常 或者 新的订单
        $ret = $player->process_payment($payment_info);
        $lock->del($lk);
        if($ret['s'] == StatusCode::ok){
            return $this->response('success');
        }else{
            return $this->response(array('ErrorCode'=>5,'ErrorDesc'=>"订单{$transaction_id}处理失败,".$ret['msg']));
        }
    }
    public function actionPaycallback_iosky(){
        global $_PAYMENT_RESPONSE;
        $publicKey = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCtuwU2nr5zmVXtIqHQEyyOI4mOS+pBxWsAHWd38sY8ylJBmJE5ygRwQhQQubhd15zKSS/117Cca+UlLf5LraDqOeo//X9MU78l8ncn/U6+wey3CD5Yi7Vrxz1X8ESkjKRKu27KKOu10RAqCQoFzxul3GwRZxtr8UrOTiMcK0fbxwIDAQAB';
        $sign = base64_decode($_REQUEST['sign']);
        $isDataCorrect = false;
        //对输入参数根据参数名排序，并拼接为key=value&key=value格式；
        $parametersArray = array();
        $parametersArray['notify_data'] = $_REQUEST['notify_data'];
        $parametersArray['orderid'] = $_REQUEST['orderid'];
        $parametersArray['dealseq'] = $_REQUEST['dealseq'];
        $parametersArray['uid'] = $_REQUEST['uid'];
        $parametersArray['subject'] = $_REQUEST['subject'];
        $parametersArray['v'] = $_REQUEST['v'];
        ksort($parametersArray);
        $sourcestr="";
        foreach ($parametersArray as $key => $val) {
            $sourcestr==""?$sourcestr=$key."=".$val:$sourcestr.="&".$key."=".$val;
        }

        //对数据进行验签，注意对公钥做格式转换
        $publicKey = model_Util::rsa_convert_publicKey($publicKey);
        $verify = model_Util::rsa_verify($sourcestr, $sign, $publicKey);

        if($verify == 1){
            //对加密的notify_data进行解密
            $data = pp_pay_decode($_REQUEST['notify_data'], $publicKey);
            parse_str($data);

            if($data != FALSE && $dealseq==$_REQUEST['dealseq']){
                  $isDataCorrect  = true;
            }
        }

        if(!$isDataCorrect){
            $_PAYMENT_RESPONSE= array('ErrorCode'=>1000,'ErrorDesc'=>"签名验证失败！");
            echo 'failed';
            return ;
        }

        echo 'success';
        /* 快用平台sdk更新后不做pid验证 by zhangjun 2014-4-15
        $pid = $_REQUEST['uid'];
        $um = model_LoginUser::searchUniq('pid',$pid);
        if(is_null($um) || !$um || $um->id() < 1){
            $_PAYMENT_RESPONSE=array('ErrorCode'=>1001,'ErrorDesc'=>"用户[{$pid}]不存在");
            return ;
        }
         */

        $extInfo = explode('_', $_REQUEST['dealseq']);//uid_section_orderid
        /* 快用平台sdk更新后不做pid验证 by zhangjun 2014-4-15
        if($um->id() != $extInfo[0]){
            $_PAYMENT_RESPONSE=array('retcode'=>1002,'retmsg'=>"用户[{$pid}]不存在");
            return ;
        }
         */
        PL_Session::$usecookie = false;
        $_REQUEST['cid'] = PL_Session::gencid($extInfo[0],$extInfo[1]);
        $player = getApp()->getPlayer();
        $lock = DbConfig::getRedis('rank');
        $lk = "pay_lock_{$player->uid}_{$player->section_id}";
        $now = time();
        $res = $lock->SETNX($lk, $now);
        if($res){
            $lock->SETEX($lk, 20, $now);
        }else{
            return $this->response(array('ErrorCode'=>0,'ErrorDesc'=>"订单处理锁定！"));
        }
        $transaction_id = $extInfo[2];
        $mon = getApp()->getPaymentMongoConnection();
        $payment_info = $mon->findOne(array('_u'=>$player->uid,'transaction_id'=>$transaction_id));
        if(empty($payment_info)){
            $_PAYMENT_RESPONSE=array('ErrorCode'=>1003,'ErrorDesc'=>"账单[{$transaction_id}]不存在");
            return ;
        }else{
            if($payment_info['status'] == StatusCode::payment_finished || $payment_info['status'] == StatusCode::payment_failed){
                $_PAYMENT_RESPONSE=array('ErrorCode'=>1004,'ErrorDesc'=>"重复账单[{$transaction_id}]已经处理");
                return;
            }

        /* 快用新版sdk卡类支付允许支付金额与下单金额不一致 by zhangjun 2014-5-31
            if(!$this->checkCash($payment_info['product_id'],$fee)){
                $_PAYMENT_RESPONSE=array('ErrorCode'=>1006,'ErrorDesc'=>"账单[{$transaction_id}]充值金额有误");
                return;
            }
        */
        }
        $payment_info['ky_order_id'] = $_REQUEST['orderid'];
        // 订单上次处理异常 或者 新的订单
        if(isset($payresult) && $payresult == 0 ){
            $ret = $player->process_payment($payment_info,true,$fee);
            $lock->del($lk);
            if($ret['s'] == StatusCode::ok){
                $_PAYMENT_RESPONSE=array('ErrorCode'=>0,'ErrorDesc'=>"账单[{$transaction_id}]处理成功");
                return;
            }else{
                $_PAYMENT_RESPONSE=array('ErrorCode'=>1005,'ErrorDesc'=>"订单{$transaction_id}处理失败,".$ret['msg']);
                return;
            }
        } else {
            $_PAYMENT_RESPONSE=array('ErrorCode'=>1006,'ErrorDesc'=>"账单[{$transaction_id}]支付超时或者失败",'payresult'=>$payresult);
            return;
        }
    }

    public function actionPaycallback_iostb(){
        $parametersArray = array();
        $parametersArray['source'] = $_REQUEST['source'];
        $parametersArray['trade_no'] = $_REQUEST['trade_no'];
        $parametersArray['amount'] = $_REQUEST['amount'];//金额是分
        $parametersArray['partner'] = $_REQUEST['partner'];

        //同步推3.01的版本在签名中增加了下面两个字段 by zhangjun
        if(!empty($_REQUEST['paydes'])){
            $parametersArray['paydes'] = $_REQUEST['paydes'];
            $parametersArray['debug'] = $_REQUEST['debug'];
        }
        if(isset($_REQUEST['tborder'])){//sdk 3.1版本（含3.1）tborder需加入验证
            $parametersArray['tborder'] = $_REQUEST['tborder'];
        }
        $parametersArray['key'] = 'y#T2jaCmbPIlchKE*rZv$CTFfQUDXxZy';

        $signstr="";
        foreach ($parametersArray as $key => $val) {
            $signstr=="" ? $signstr=$key."=".$val : $signstr.="&".$key."=".$val;
        }

        $sign = md5($signstr);
        if($sign != $_REQUEST['sign']){
            return $this->response(array('retcode'=>1000,'retmsg'=>"签名验证失败！"));
        }
        //pid有特殊字符线上玩家签名出错 所以删除pid
        //同步不允许有特殊字符客户端换成_分割，同时避免合服造成bug
        $extInfo = explode(',', $_REQUEST['trade_no']);//pid,uid,section,transaction_id
        $ext_cnt = count($extInfo);
        if ($ext_cnt == 1) {
            $extInfo = explode('_', $_REQUEST['trade_no']);
            $ext_cnt = count($extInfo);
        }   
        if ($ext_cnt == 1) {
            $transaction_id = $_REQUEST['trade_no'];
        }else{
            $transaction_id = $extInfo[$ext_cnt - 1]; 
        }
        /*
        $um = model_LoginUser::searchUniq('pid',$extInfo[0]);

        if(is_null($um) || !$um || $um->id() < 1){
            return $this->response(array('retcode'=>1001,'retmsg'=>"用户[{$pid}]不存在"));
        }
        if($um->id() != $extInfo[1]){
            return $this->response(array('retcode'=>1002,'retmsg'=>"用户[{$pid}]不存在"));
        }
        if(count($extInfo) == 3){
            array_unshift($extInfo,"tbpid");
        }
        */

        $mon = getApp()->getPaymentMongoConnection();
        $data = $mon->findOne(array('transaction_id'=>$transaction_id));
        $pid = $data['pid'];
        $uid = $data['_u'];
        $section_id = $data['_sec'];

        PL_Session::$usecookie = false;
        $_REQUEST['cid'] = PL_Session::gencid($uid, $section_id);
        $player = getApp()->getPlayer();
        $lock = DbConfig::getRedis('rank');
        $lk = "pay_lock_{$player->uid}_{$player->section_id}";
        $now = time();
        $res = $lock->SETNX($lk, $now);
        if($res){
            $lock->SETEX($lk, 20, $now);
        }else{
            return $this->response(array('ErrorCode'=>0,'ErrorDesc'=>"订单处理锁定！"));
        }
        $mon = getApp()->getPaymentMongoConnection();
        $payment_info = $mon->findOne(array('_u'=>$player->uid,'transaction_id'=>$transaction_id));
        if(empty($payment_info)){
            return $this->response(array('ErrorCode'=>1003,'ErrorDesc'=>"账单[{$transaction_id}]不存在"));
        }else{
            if($payment_info['status'] == StatusCode::payment_finished){
                return $this->response(array('status'=>'success'));
            }else if($payment_info['status'] == StatusCode::payment_failed ){
                return $this->response(array('status'=>'success','ErrorDesc'=>"重复订单{$transaction_id}已经处理"));
            }
            else if(!$this->checkCash($payment_info['product_id'],$_REQUEST['amount']/100)){
                return $this->response(array('ErrorCode'=>1006,'ErrorDesc'=>"订单{$transaction_id}充值金额有误"));
            }
        }
        // 订单上次处理异常 或者 新的订单
        $ret = $player->process_payment($payment_info);
        $lock->del($lk);
        if($ret['s'] == StatusCode::ok){
            return $this->response(array('status'=>'success'));
        }else{
            return $this->response(array('ErrorCode'=>1005,'ErrorDesc'=>"订单{$transaction_id}处理失败,".$ret['msg']));
        }

    }
    public function actionPaycallback_itools(){
        //签名
        $sign = $_REQUEST['sign'];
        //通知数据
        $notify_data = $_REQUEST['notify_data'];
        $key = '
-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC2kcrRvxURhFijDoPpqZ/IgPlA
gppkKrek6wSrua1zBiGTwHI2f+YCa5vC1JEiIi9uw4srS0OSCB6kY3bP2DGJagBo
Egj/rYAGjtYJxJrEiTxVs5/GfPuQBYmU0XAtPXFzciZy446VPJLHMPnmTALmIOR5
Dddd1Zklod9IQBMjjwIDAQAB
-----END PUBLIC KEY-----';

        //RSA解密
        $data = base64_decode($notify_data);
        $maxlength = 128;
        $output = '';
        while ($data) {
            $input = substr($data, 0, $maxlength);
            $data = substr($data, $maxlength);
            openssl_public_decrypt($input, $out, $key);
            $output .= $out;
        }

        $notify_data = $output;
        //验证签名
        if(!openssl_verify($notify_data, base64_decode($sign), $key)){
        //fail
            return $this->response(array('ErrorCode'=>0,'ErrorDesc'=>"签名验证失败！"));
        }
        //$notify_data: json数据(格式: {"order_id_com":"64,s1,530752c4dccd570214000000","amount":"0.10","account":"cocoax","order_id":"2014022100183954","result":"success","user_id":"376705"})
        $json_data = json_decode($notify_data, true);
        //判断订单状态
        if($json_data['result'] != "success"){
            //fail
            return $this->response(array('ErrorCode'=>6,'ErrorDesc'=>"订单状态错误"));
        }
        list($uid,$section_id,$transaction_id) = explode(',',$json_data['order_id_com']);

        PL_Session::$usecookie = false;
        $_REQUEST['cid'] = PL_Session::gencid($uid,$section_id);
        $player = getApp()->getPlayer();
        $lock = DbConfig::getRedis('rank');
        $lk = "pay_lock_{$player->uid}_{$player->section_id}";
        $now = time();
        $res = $lock->SETNX($lk, $now);
        if($res){
            $lock->SETEX($lk, 20, $now);
        }else{
            return $this->response(array('ErrorCode'=>0,'ErrorDesc'=>"订单处理锁定！"));
        }
        $mon = getApp()->getPaymentMongoConnection();
        $payment_info = $mon->findOne(array('_u'=>$player->uid,'transaction_id'=>$transaction_id));
        if(empty($payment_info)){
            return $this->response(array('ErrorCode'=>3,'ErrorDesc'=>"账单[{$transaction_id}]不存在"));
        }else{
            if($payment_info['status'] == StatusCode::payment_finished || $payment_info['status'] == StatusCode::payment_failed ){
                //重复订单
                //itools要求调整返回值by zhangjun 2014-3-26
                global $_PAYMENT_RESPONSE;
                echo "success";
                $_PAYMENT_RESPONSE=array('ErrorCode'=>4,'ErrorDesc'=>"重复账单[{$transaction_id}]已经处理");
                return ;
            }
            if(!$this->checkCash($payment_info['product_id'],$json_data['amount'])){
                //充值数据有误
                return $this->response(array('ErrorCode'=>6,'ErrorDesc'=>"账单[{$transaction_id}]充值金额有误"));
            }
        }
 //       return $this->response(array('ErrorCode'=>1,'ErrorDesc'=>$payment_info));
        // 订单上次处理异常 或者 新的订单
        $ret = $player->process_payment($payment_info);
        $lock->del($lk);
        if($ret['s'] == StatusCode::ok){

            //itools要求调整返回值by zhangjun 2014-3-26
            global $_PAYMENT_RESPONSE;
            echo "success";
            $_PAYMENT_RESPONSE=array('ErrorCode'=>0,'ErrorDesc'=>"账单[{$transaction_id}]处理成功");
            return;
        }else{
            return $this->response(array('ErrorCode'=>5,'ErrorDesc'=>"订单{$transaction_id}处理失败,".$ret['msg']));
        }
    }

    //越南zingcredits
    public function actionPaycallback_zingcredits() {
        
        require_once __DIR__ . "/../../../../vms/paytools/zingcredits/ZCypher2Lib.php";
        global $_PAYMENT_RESPONSE;
        $key = "ghaC08FsXwtVaVkXBU5YXWvBG/a4sDTG";//app_key2 解密
        //回调字段  只有加密的data
        $enc_data = $_REQUEST['data'];
        //zingcredits解密
        $transData = new ZC2_CallbackResultData();
        $ret = ZCypher2Lib::decodeDataForCallbackResult($key, $enc_data, $transData);

        if ($ret != 0) {
            $_PAYMENT_RESPONSE = array('retcode' => 1, 'retmsg' => "签名失败！");
            echo "-1001:check sign fail";
            return;
        }
        //解密后获取订单信息
        $transaction_id = $transData->billNo;
        $extern_transaction_id = $transData->txID_ZingCredits;
        //越南zingcredits订单号
        $payMoney = $transData->amount * 100;//单位zingxu 转换成vnd 1zingxu = 100vnd
        $mon = getApp()->getPaymentMongoConnection();
        $data = $mon->findOne(array('transaction_id'=>$transaction_id));
        $pid = $data['pid'];
        $uid = $data['_u'];
        $section_id = $data['_sec'];
        PL_Session::$usecookie = false;
        $_REQUEST['cid'] = PL_Session::gencid($uid,$section_id);
        $player = getApp()->getPlayer();
        $lock = DbConfig::getRedis('rank');
        $lk = "pay_lock_{$player->uid}_{$player->section_id}";
        $now = time();
        $res = $lock->SETNX($lk, $now);
        if($res){
            $lock->SETEX($lk, 20, $now);
        }else{
            $_PAYMENT_RESPONSE = array('ErrorCode'=>0,'ErrorDesc'=>"订单处理锁定！");
            echo "-1001:order locked";
            return;
        }
        $mon = getApp()->getPaymentMongoConnection();
        $payment_info = $mon->findOne(array('_u'=>$player->uid,'transaction_id'=>$transaction_id));
        if(empty($payment_info)){
            $_PAYMENT_RESPONSE = array('ErrorCode'=>3,'ErrorDesc'=>"订单{$transaction_id}不存在");
            echo "-1001:order not exist";
            return;
        }else{
            if($payment_info['status'] == StatusCode::payment_finished || $payment_info['status'] == StatusCode::payment_failed ){
                //重复订单
                $_PAYMENT_RESPONSE = array('ErrorCode'=>1004,'ErrorDesc'=>"重复订单{$transaction_id}已经处理");
                echo "1001:success";
                return;
            }
            //这里不知道怎么处理？？
            if(!$this->checkCash($payment_info['product_id'],$payMoney)){
                //充值数据有误
                $_PAYMENT_RESPONSE = array('ErrorCode'=>6,'ErrorDesc'=>"账单[{$transaction_id}]充值金额有误");
                echo "-1001:cash not equal";
                return;
            }
        }

        $payment_info['zing_order_id'] = $extern_transaction_id;
        // 订单上次处理异常 或者 新的订单
        $ret = $player->process_payment($payment_info);
        $lock->del($lk);
        if($ret['s'] == StatusCode::ok){
            $_PAYMENT_RESPONSE = array('ErrorCode'=>0,'ErrorDesc'=>"账单[{$transaction_id}]处理成功");
            echo "1001:success";
            return;
        }else{
            $_PAYMENT_RESPONSE = array('ErrorCode'=>5,'ErrorDesc'=>"订单{$transaction_id}处理失败,".$ret['msg']);
            echo "-1001:deal order fail";
            return;
        }

    }



    //财付通
    public function actionPaycallback_caifutong() {
        global $_PAYMENT_RESPONSE;
        $key = "61977da6ec544246605437667ef878cc";//md5加密key
        $parameters = array();
        foreach($_REQUEST as $k=>$v) {
            $parameters[$k] = $v;
        }
        if($parameters['pay_result'] != 0) {
            $_PAYMENT_RESPONSE = array('ErrorCode'=>6,'ErrorDesc'=>"订单状态错误");
            echo "fail";
            return;
        }
        $transaction_id = $parameters['sp_billno'];
        $extern_transaction_id = $parameters['transaction_id'];//财付通订单号
        $payMoney = $parameters['total_fee']/100;//单位分换成元
        $parameters['ver'] = $parameters['version'];
        $signPars = "";
        unset($parameters['mod']);
        unset($parameters['action']);
        unset($parameters['version']);//回调信息中的ver字段与vms系统冲突 by zhangjun
        ksort($parameters);
        foreach($parameters as $k=>$v) {
            if("sign" != $k && "" != $v) {
                $signPars .= $k . "=" .$v ."&";
            }
        }
        $signPars .= "key=" . $key;
        $sign = strtolower(md5($signPars));
        if($sign != strtolower($parameters['sign'])) {
            $_PAYMENT_RESPONSE = array('retcode' => 1, 'retmsg' => "签名失败！");
            echo "fail";
            return;
        }

        $extInfo = explode("_",$parameters['attach']);
        //mix平台合区修复支付不到账 by zhangjun
        if(count($extInfo) == 4){//合区前 1_s1_token 合区后 s1_1_s1000_token
            $new_id = array_shift($extInfo);
            $extInfo[0] = $new_id . '_'.$extInfo[0];
        }    
        $uid = $extInfo[0];
        $section_id = $extInfo[1];
        $transaction_id = $extInfo[2];
        if(is_null($uid)){
            $_PAYMENT_RESPONSE = array('retcode'=>1001,'retmsg'=>"用户不存在");
            echo "fail";
            return;
        }
        PL_Session::$usecookie = false;
        $_REQUEST['cid'] = PL_Session::gencid($uid,$section_id);
        $player = getApp()->getPlayer();
        $lock = DbConfig::getRedis('rank');
        $lk = "pay_lock_{$player->uid}_{$player->section_id}";
        $now = time();
        $res = $lock->SETNX($lk, $now);
        if($res){
            $lock->SETEX($lk, 20, $now);
        }else{
            $_PAYMENT_RESPONSE = array('ErrorCode'=>0,'ErrorDesc'=>"订单处理锁定！");
            echo "fail";
            return;
        }
        $mon = getApp()->getPaymentMongoConnection();
        $payment_info = $mon->findOne(array('_u'=>$player->uid,'transaction_id'=>$transaction_id));
        if(empty($payment_info)){
            $_PAYMENT_RESPONSE = array('ErrorCode'=>1003,'ErrorDesc'=>"账单[{$transaction_id}]不存在");
            echo "fail";
            return;
        }else{
            if($payment_info['status'] == StatusCode::payment_finished || $payment_info['status'] == StatusCode::payment_failed ){
                $_PAYMENT_RESPONSE = array('ErrorCode'=>1004,'ErrorDesc'=>"重复订单{$transaction_id}已经处理");
                echo "success";
                return;
            }
            if(!$this->checkCash($payment_info['product_id'],$payMoney)){
                $_PAYMENT_RESPONSE = array('ErrorCode'=>1006,'ErrorDesc'=>"订单{$transaction_id}充值金额有误");
                echo "fail";
                return;
            }
        }
        // 订单上次处理异常 或者 新的订单
        $payment_info['cft_order_id'] = $extern_transaction_id;
        $ret = $player->process_payment($payment_info);
        $lock->del($lk);
        if($ret['s'] == StatusCode::ok){
            echo "success";
            $_PAYMENT_RESPONSE=array('ErrorCode'=>0,'ErrorDesc'=>"账单[{$transaction_id}]处理成功");
            return;
        }else{
            $_PAYMENT_RESPONSE = array('ErrorCode'=>1005,'ErrorDesc'=>"订单{$transaction_id}处理失败,".$ret['msg']);
            echo "fail";
            return;
        }

        
    }
    //神州付
    public function actionPaycallback_shenzhoufu() {
        global $_PAYMENT_RESPONSE;
        $privateKey = "cGxheWNyYWI=";//md5私钥
        $certFile = __DIR__ . "/../../../../vms/paytools/shenzhoufu/ShenzhoufuPay.cer";//神州付证书文件路径,暂时存放在这

        $privateField = $_REQUEST['privateField'];
        $payResult = $_REQUEST['payResult'];
        $orderId = $_REQUEST['orderId'];//神州付订单号
        $md5String = $_REQUEST['md5String'];
        $signString = $_REQUEST['signString'];
        $payMoney = $_REQUEST['payMoney']/100;//单位是分转成元
        if ($payResult != 1) {
            $_PAYMENT_RESPONSE = array('ErrorCode'=>6,'ErrorDesc'=>"订单状态错误");
            return;
        }
        echo $orderId;
        $my_md5 = md5($_REQUEST['version'].$_REQUEST['merId'].$_REQUEST['payMoney'].$_REQUEST['orderId'].$_REQUEST['payResult'].$_REQUEST['privateField'].$_REQUEST['payDetails'].$privateKey);
        if ($my_md5 != $md5String) {
            $_PAYMENT_RESPONSE = array('retcode' => 1, 'retmsg' => "签名验证失败！");
            return;
        }
        $fp = fopen($certFile, "r");
        $cert = fread($fp, 8192);
        fclose($fp);
        $pubkeyid = openssl_get_publickey($cert);
        if (openssl_verify($my_md5, base64_decode($signString), $pubkeyid, OPENSSL_ALGO_MD5) != 1) {
            $_PAYMENT_RESPONSE = array('retcode' => 1, 'retmsg' => "二级签名验证失败");
            return;
        }

        $extInfo = explode("_",$privateField);
        //mix平台合区修复支付不到账 by zhangjun
        if(count($extInfo) == 4){//合区前 1_s1_token 合区后 s1_1_s1000_token
            $new_id = array_shift($extInfo);
            $extInfo[0] = $new_id . '_'.$extInfo[0];
        }
        $uid = $extInfo[0];
        if(is_null($uid)){
            $_PAYMENT_RESPONSE = array('retcode'=>1001,'retmsg'=>"用户不存在");
            return;
        }

        $section_id = $extInfo[1];
        $transaction_id = $extInfo[2];
        PL_Session::$usecookie = false;
        $_REQUEST['cid'] = PL_Session::gencid($uid,$section_id);
        $player = getApp()->getPlayer();
        $lock = DbConfig::getRedis('rank');
        $lk = "pay_lock_{$player->uid}_{$player->section_id}";
        $now = time();
        $res = $lock->SETNX($lk, $now);
        if($res){
            $lock->SETEX($lk, 20, $now);
        }else{
            $_PAYMENT_RESPONSE = array('ErrorCode'=>0,'ErrorDesc'=>"订单处理锁定！");
            return;
        }
        $mon = getApp()->getPaymentMongoConnection();
        $payment_info = $mon->findOne(array('_u'=>$player->uid,'transaction_id'=>$transaction_id));
        if(empty($payment_info)){
            $_PAYMENT_RESPONSE = array('ErrorCode'=>1003,'ErrorDesc'=>"账单[{$transaction_id}]不存在");
            return;
        }else{
            if($payment_info['status'] == StatusCode::payment_finished || $payment_info['status'] == StatusCode::payment_failed ){
                $_PAYMENT_RESPONSE = array('ErrorCode'=>1004,'ErrorDesc'=>"重复订单{$transaction_id}已经处理");
                return ;
            }
            if(!$this->checkCash($payment_info['product_id'],$payMoney)){
                $_PAYMENT_RESPONSE = array('ErrorCode'=>1006,'ErrorDesc'=>"订单{$transaction_id}充值金额有误");
                return ;
            }
        }
        // 订单上次处理异常 或者 新的订单
        $payment_info['szf_order_id'] = $orderId;
        $ret = $player->process_payment($payment_info);
        $lock->del($lk);
        if($ret['s'] == StatusCode::ok){
            $_PAYMENT_RESPONSE = array('ErrorCode'=>0,'ErrorDesc'=>"订单{$transaction_id}处理成功");
        }else{
            $_PAYMENT_RESPONSE = array('ErrorCode'=>1005,'ErrorDesc'=>"订单{$transaction_id}处理失败,".$ret['msg']);
        }
        return ;
    }

    
    public function actionPaycallback_Paypalm(){
        require_once __DIR__ . "/../../../../vms/paytools/paypalm/PaypalmSDK.php";

        $app_id = '319';
        $app_key = 'ebc6726422281c6754fcfb6563898389';
        $app_secret = '4b86295eb733199178696f31f3aa1ce5';

        $encType = $_REQUEST["encType"];
        $signType = $_REQUEST["signType"];
        $zipType = $_REQUEST["zipType"];
        $encode = $_REQUEST["encode"];
        $transData = $_REQUEST["transData"];

        $this->request = PaypalmSDK::unpackOrderResult ($encType, $signType, $zipType, $encode, $transData);
        
        //判断订单状态orderStatus $this->request取出来是object类型.''一下
        if($this->request['orderStatus'].'' != 1){
            //fail
            return $this->response(array('ErrorCode'=>6,'ErrorDesc'=>"订单状态错误"));
        }

        $extern_transaction_id = $this->request['orderNo'].'';//外部订单号
        $transaction_id = $this->request['merOrderNo'].'';
        $mon = getApp()->getPaymentMongoConnection();
        $data = $mon->findOne(array('transaction_id'=>$transaction_id));
        $pid = $data['pid'];
        $section_id = $data['_sec'];
        //initPlayer
        $um = model_LoginUser::searchUniq('pid', $pid);
        if (!$um || $um->id() < 1 || is_null($um)) {
            return $this->response(array('ErrorCode' => 0, 'ErrorDesc' => "用户[{$pid}]不存在"));
        }
        $uid = $um->id();
        PL_Session::$usecookie = false;
        $_REQUEST['cid'] = PL_Session::gencid($uid,$section_id);
        $player = getApp()->getPlayer();
        $lock = DbConfig::getRedis('rank');
        $lk = "pay_lock_{$player->uid}_{$player->section_id}";
        $now = time();
        $res = $lock->SETNX($lk, $now);
        if($res){
            $lock->SETEX($lk, 20, $now);
        }else{
            return $this->response(array('ErrorCode'=>0,'ErrorDesc'=>"订单处理锁定！"));
        }
        $mon = getApp()->getPaymentMongoConnection();
        $payment_info = $mon->findOne(array('_u'=>$player->uid,'transaction_id'=>$transaction_id));
        if(empty($payment_info)){
            return $this->response(array('ErrorCode'=>3,'ErrorDesc'=>"账单[{$transaction_id}]不存在"));
        }else{
            if($payment_info['status'] == StatusCode::payment_finished || $payment_info['status'] == StatusCode::payment_failed ){
                //重复订单
                global $_PAYMENT_RESPONSE;
                $_PAYMENT_RESPONSE = "success";
                echo PaypalmSDK::packNotifySuccess();
                return;
                //return $this->response('success');
            }
            //$this->request['payAmt']取出来是object,金额分
            $realCash = $this->request['payAmt'].'';
            if(!$this->checkCash($payment_info['product_id'],$realCash/100)){
                //充值数据有误
                return $this->response(array('ErrorCode'=>6,'ErrorDesc'=>"账单[{$transaction_id}]充值金额有误"));
            }
        }

        $payment_info['pp_order_id'] = $extern_transaction_id;
        // 订单上次处理异常 或者 新的订单
        $ret = $player->process_payment($payment_info);
        $lock->del($lk);
        if($ret['s'] == StatusCode::ok){
            //return $this->response('success');
            global $_PAYMENT_RESPONSE;
            $_PAYMENT_RESPONSE = "success";
            echo PaypalmSDK::packNotifySuccess();
            return;
        }else{
            return $this->response(array('ErrorCode'=>5,'ErrorDesc'=>"订单{$transaction_id}处理失败,".$ret['msg']));
        }
    }
//越南版本
    public function actionPaycallback_ssgroup() {
    
        global $_PAYMENT_RESPONSE;
        //取相应的参数验签，回调的参数不确定，暂时先写这样
        $key = 'YzflMxevHG6Tgpz5sfp52';
        $sign = md5($_REQUEST['time'] . $_REQUEST['orderId'] . $_REQUEST['userId'] . $_REQUEST['money'] . $_REQUEST['ssroll'] . $_REQUEST['ext'] . $key);
        if ($sign != $_REQUEST['sign']) {
            return $this->response(array('ErrorCode' => 0, 'ErrorDesc' => " 签名验证失败！")); 
        }
        $extInfo = explode('_',$_REQUEST['ext']);
        $pid = 'ssgroup_' . $_REQUEST['userId'];//userId用做pid？？？加不加前缀
        $um = model_LoginUser::searchUniq('pid', $pid);
        if (!$um || $um->id() < 1 || is_null($um)) {
            return $this->response(array('ErrorCode' => 0, 'ErrorDesc' => "用户[{$pid}]不存在"));
        }
        $uid = $um->id();
        $section_id = $extInfo[1];
        PL_Session::$usecookie = false;
        $_REQUEST['cid'] = PL_Session::gencid($uid, $section_id);
        $player = getApp()->getPlayer();
        $lock = DbConfig::getRedis('rank');
        $lk = "pay_lock_{$player->uid}_{$player->section_id}";
        $now = time();
        $res = $lock->SETNX($lk, $now);
        if($res){
            $lock->SETEX($lk, 20, $now);
        }else{
            return $this->response(array('ErrorCode'=>0,'ErrorDesc'=>"订单处理锁定！"));
        }
        $transaction_id = $extInfo[2];
        $mon = getApp()->getPaymentMongoConnection();
        $payment_info = $mon->findOne(array('_u'=>$player->uid,'transaction_id'=>$transaction_id));
        if (empty($payment_info)) {
            return $this->response(array('ErrorCode' => 3, 'ErrorDesc' => "账单[{$transaction_id}]不存在"));
        } else {
            if ($payment_info['status'] == StatusCode::payment_finished || $payment_info['status'] == StatusCode::payment_failed) {
                return $this->response(array('ErrorCode'=>1004,'ErrorDesc'=>"重复订单{$transaction_id}已经处理"));
            }
            $fee = $_REQUEST['money'];
            if (!$this->checkCash($payment_info['product_id'],$fee)) {
                return $this->response(array('ErrorCode'=>1006,'ErrorDesc'=>"订单{$transaction_id}充值金额有误"));
            }
        }
        $ret = $player->process_payment($payment_info);
        $lock->del($lk);
        if ($ret['s'] == StatusCode::ok) {
            return $this->response('success');
        } else {
            return $this->response(array('ErrorCode' => 5, 'ErrorDesc' => "订单{$transaction_id}处理失败," . $ret['msg']));
        }

    }
    public function actionPaycallback_alipay(){
        global $_PAYMENT_RESPONSE;
        $notify_data = $_REQUEST['notify_data'];
        $sign = base64_decode($_REQUEST['sign']);
        $src = $_REQUEST['source'];
        if(empty($src) || $src != 'changba'){
            $publicKey = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCRpkj69E+aMmGupqSSQkHlka2s8S8yJYT0Xnu/kH1yLRsgVFqsLdvtcJ87F0y4JMVVqZq2OhL7CY9WBXa3Jo5tdJii3ZFFX3k6e0C8Ksp6ihh/zVmsxgTJAHW+IUuQ8KJJFFiGHlUfG3/6BFk4CbsrhedFMFlGgGnXY/Et5jkM7QIDAQAB';
        } else{
            $publicKey = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDyCkLAAbis1kNwyfUy9cJtM+Sn314B/4xgH6mhksUlIGLQK4Ns+wa2+2V6/p5jkzelgvZymWdMJeSJkEu8zMVm5meuac1CHVesgXuR5rQLyO7QevRzxo1rTqkD5Fz7DxwezK/uMSgU9mbAqvc7QR9g2zSWod13zLqo70mHpWRBLwIDAQAB';
        }
        $publicKey = model_Util::rsa_convert_publicKey($publicKey);
        $verify = model_Util::rsa_verify("notify_data=$notify_data", $sign, $publicKey);

        if(!$verify){
            echo "fail";
            $_PAYMENT_RESPONSE = array('retcode'=>1000,'retmsg'=>"签名验证失败！");
            return ;
        }
        echo "success";
        $trade_status = getDataForXML($notify_data,"/notify/trade_status");
        if($trade_status != "TRADE_FINISHED" && $trade_status != "TRADE_SUCCESS"){
            //没有完成支付宝交易 如果这里不返回success是否会继续请求
            $_PAYMENT_RESPONSE = array('retcode'=>1006,'retmsg'=>"通知成功,交易状态:{$trade_status}");
            return ;
        }
        $out_trade_no = getDataForXML($notify_data,"/notify/out_trade_no");
        $extInfo = explode('_', $out_trade_no);//uid,section,transaction_id 这个字段只有64位估计放不下这么多数据
        //mix平台合区修复支付不到账 by zhangjun
        if(count($extInfo) == 4){//合区前 1_s1_token 合区后 s1_1_s1000_token
            $new_id = array_shift($extInfo);
            $extInfo[0] = $new_id . '_'.$extInfo[0];
        }
        $um = $extInfo[0];

        if(is_null($um)){
            $_PAYMENT_RESPONSE = array('retcode'=>1001,'retmsg'=>"用户不存在");
            return ;
        }

        PL_Session::$usecookie = false;
        $_REQUEST['cid'] = PL_Session::gencid($um,$extInfo[1]);
        $player = getApp()->getPlayer();
        $lock = DbConfig::getRedis('rank');
        $lk = "pay_lock_{$player->uid}_{$player->section_id}";
        $now = time();
        $res = $lock->SETNX($lk, $now);
        if($res){
            $lock->SETEX($lk, 20, $now);
        }else{
            return $this->response(array('ErrorCode'=>0,'ErrorDesc'=>"订单处理锁定！"));
        }
        $transaction_id = $extInfo[2];
        $mon = getApp()->getPaymentMongoConnection();
        $payment_info = $mon->findOne(array('_u'=>$player->uid,'transaction_id'=>$transaction_id));
        if(empty($payment_info)){
            $_PAYMENT_RESPONSE = array('ErrorCode'=>1003,'ErrorDesc'=>"账单[{$transaction_id}]不存在");
            return ;
        }else{
            if($payment_info['status'] == StatusCode::payment_finished || $payment_info['status'] == StatusCode::payment_failed ){
                $_PAYMENT_RESPONSE = array('ErrorCode'=>1004,'ErrorDesc'=>"重复订单{$transaction_id}已经处理");
                return ;
            }
            $fee = getDataForXML($notify_data,"/notify/total_fee");
            if(!$this->checkCash($payment_info['product_id'],$fee)){
                return $this->response(array('ErrorCode'=>1006,'ErrorDesc'=>"订单{$transaction_id}充值金额有误"));
            }
        }
        // 订单上次处理异常 或者 新的订单
        $ret = $player->process_payment($payment_info);
        $lock->del($lk);
        if($ret['s'] == StatusCode::ok){
            $_PAYMENT_RESPONSE = array('ErrorCode'=>0,'ErrorDesc'=>"订单{$transaction_id}处理成功");
        }else{
            $_PAYMENT_RESPONSE = array('ErrorCode'=>1005,'ErrorDesc'=>"订单{$transaction_id}处理失败,".$ret['msg']);
        }
        return ;
    }
    public function actionPaycallback_kunlun(){
        if(get_magic_quotes_gpc()){
            $_REQUEST['ext'] = stripslashes($_REQUEST['ext']);
        }
        $ext = json_decode($_REQUEST['ext'], true);
        $pid = strtolower($ext['uname']);
        $um = model_LoginUser::searchUniq('pid',$pid);

        if(is_null($um) || !$um || $um->id() < 1){
            return $this->response(array('retcode'=>2,'retmsg'=>"用户[{$pid}]不存在"));
        }
        $extInfo = explode(',', $ext['partnersorderid']);
        $kuid = $_REQUEST['uid'];//昆仑的用户id 
        $oid = $_REQUEST['oid'];
        $amount =  $_REQUEST['amount'];
        $coins = $_REQUEST['coins'];
        $dtime = $_REQUEST['dtime'];
        $key = '600fcc563ba6e8b84a4ee2527cdd3035';
        $sign = md5($oid . $kuid . $amount . $coins . $dtime . $key);

        if($um->id() != $extInfo[0]){
            $mon = getApp()->getPaymentMongoConnection();
            $payment_info = $mon->findOne(array('_u'=>$um->id(),'transaction_id'=>$oid));
            if($payment_info){
                $extInfo[0] = $um->id();
            }
            //return $this->response(array('retcode'=>2,'retmsg'=>"用户[{$pid}]不存在"));
        }

        if($sign !== $_REQUEST['sign']){
            return $this->response(array('retcode'=>1,'retmsg'=>"签名验证失败！"));
        }
        PL_Session::$usecookie = false;
        $_REQUEST['cid'] = PL_Session::gencid($extInfo[0],$extInfo[1]); 
        //cid里面包含了uid，分区，如果没有这个，调用游戏里面的方法可能会因为取不到uid，分区而失败
        $player = getApp()->getPlayer();
        $lock = DbConfig::getRedis('rank');
        $lk = "pay_lock_{$player->uid}_{$player->section_id}";
        $now = time();
        $res = $lock->SETNX($lk, $now);
        if($res){
            $lock->SETEX($lk, 20, $now);
        }else{
            return $this->response(array('ErrorCode'=>0,'ErrorDesc'=>"订单处理锁定！"));
        }
        $mon = getApp()->getPaymentMongoConnection();
        $payment_info = $mon->findOne(array('_u'=>$player->uid,'transaction_id'=>$oid));

        if(empty($payment_info)){

            $payment_info = $player->createPaymentInfo('custom');
            $payment_info['transaction_id'] = $oid;
            $payment_info['create_t'] = getApp()->now;
            $payment_info['cash'] = $amount;
            $payment_info['sumGem'] = $coins;
            $payment_info['source'] = 'kunlun';
            $mon->save($payment_info);
        }else{

            if($payment_info['status'] == StatusCode::payment_finished){
                return $this->response(array('retcode'=>4,'retmsg'=>"重复订单{$oid}已经处理"));
            } else if ($payment_info['status'] == StatusCode::payment_failed){
                return $this->response(array('retcode'=>4,'retmsg'=>"重复订单{$oid}已经处理"));
            }

        }

        $ret = $player->process_payment($payment_info);
        $lock->del($lk);
        if($ret['s'] == StatusCode::ok){
            return $this->response(array('retcode'=>0,'retmsg'=>"订单{$oid}已经处理"));
        }else{
            return $this->response(array('retcode'=>3,'retmsg'=>"订单{$oid}处理失败"));
        }

    }

    /**
     * 蜂巢支付回调地址有个ip列表限制
     */
    private function checkServerIP()
    {
       $white_list = 
          array(
             '42.121.1.108', '42.121.1.112', '112.124.122.122', '112.124.122.131', '124.65.196.2', '114.215.207.108',
             '115.29.234.79', '115.29.235.212', '112.124.116.129', '112.124.108.171', '42.121.116.233', '42.121.253.76',
          );
       $server_ip  = $_SERVER['HTTP_X_FORWARDED_FOR'];
       if (!in_array($server_ip, $white_list)) {
          glog::info($_SERVER, "comb_ip_abnormal");
          error_log("charge_ip $server_ip abnormal");
          return false;
       }
       return true;
    }

    function actionPaycallback_comb(){
        $app = getApp();
        $ret = $this->checkServerIP();
        if (!$ret) {
           //todo 先记录日志，然后看是否有问题;
        }
        $app->stat_direct_write = true;

        /*
            array(6) {
              ["userId"]=>
              string(8) "cacawang"
              ["gameId"]=>
              string(1) "1"
              ["reqtime"]=>
              int(1346742112296)
              ["state"]=>
              string(1) "1"
              ["consumeValue"]=>
              string(6) "200.00"
              ["extraData"]=>
              string(12) "wasjdaosiasd"
            }
         */
        //$data = 'j1RLRyw2/gQWh5xjuU2DOk9+CfUx13ePlqMoeLNZRBUk0M1jHHVtKDN/XFkvT2N3izI3Astm3sLX0urF/Kmqh/cv3YXy8PosPngxMTmeQtzIxP2yZjzpuDGhY9eXHn0ihhfMJ9w4XkV87STG2p2YJAIe81tqTErknvAcveAvPIA=';
        $key = '3dkXb5TCOkmXBrBQ';
        $input = file_get_contents('php://input');
        $data2 = base64_decode($input);
        $decrypted = pcdecrypt_ecb($data2,$key);
        $param = json_decode($decrypted,true);
        $pid = $param['userId'];
        $um = model_LoginUser::searchUniq('pid',$pid);
        if(is_null($um)){
            return $this->response(array('state'=>array('code'=>2,'msg'=>"用户[{$pid}]不存在")));
        }
        if(!$um){
            return $this->response(array('state'=>array('code'=>2,'msg'=>"用户[{$pid}]不存在")));
        }
        $uid   = $um->id();
        if($uid<1){
            return $this->response(array('state'=>array('code'=>2,'msg'=>"用户[{$pid}]不存在")));
        }

        $extraData = $param['extraData'];
        if($extraData=='PayFromPage'){
            if(P_PLATFORM!='gamecomb'){
                error_log("在其他平台调用蜂巢的支付接口?");
                return $this->response(array('state'=>array('code'=>2,'msg'=>"平台不对")));
            }
            $section_id = $param['gameServerZone'];
            $payment_config = getApp()->getPaymentConifg();
            $product_id = null;
            foreach($payment_config as $c){
                if($c['cash'] == intval($param['consumeValue'])){
                    $product_id = $c['product_id'];
                    break;
                }
            }
            if(is_null($product_id)){
                return $this->response(array('state'=>array('code'=>2,'msg'=>"支付额度[{$param['consumeValue']}]不存在")));
            }

            $transaction_id = $param['gameServerZone'].'_'.$param['userId'] .'_'.$param['reqtime'];
        }else{
            //合区之后在extData中增加uid by zhangjun 
            //sms的代码没有找到不明确什么时候在extData中添加了sms字符串
            list($section_id,$product_id,$transaction_id,$uid,$sms) = explode(",",$extraData);
            if(empty($product_id)){
                list($section_id,$product_id,$transaction_id,$uid,$sms) = explode("_",$extraData);
            }
            //切版本的时候做兼容 稳定之后可以删除 by zhangjun
            if(empty($uid)){
                $uid   = $um->id();
            }
            $payment_config = getApp()->getPaymentConifg();
            if($payment_config[$product_id]['cash'] != intval($param['consumeValue'])){
                // TODO  检查是不是小米用户?
                glog::info("ERROR\t$pid\t$product_id\t{$payment_config[$product_id]['cash']}\t{$param['consumeValue']}","test");
                $product_id = 'custom';
                $transaction_id = $param['gameServerZone'].'_'.$param['userId'] .'_'.$param['reqtime'];
                $cash = intval($param['consumeValue']);
            }else{
                // 正常的充值
                glog::info("OK\t$pid\t$product_id\t{$payment_config[$product_id]['cash']}\t{$param['consumeValue']}","test");
            }
        }

        PL_Session::$usecookie = false;
        $_REQUEST['cid'] = PL_Session::gencid($uid,$section_id);

        $player = getApp()->getPlayer();
        $lock = DbConfig::getRedis('rank');
        $lk = "pay_lock_{$player->uid}_{$player->section_id}";
        $now = time();
        $res = $lock->SETNX($lk, $now);
        if($res){
            $lock->SETEX($lk, 20, $now);
        }else{
            return $this->response(array('ErrorCode'=>0,'ErrorDesc'=>"订单处理锁定！"));
        }

        $mon = getApp()->getPaymentMongoConnection();
        $payment_info = $mon->findOne(array('_u'=>$player->uid,'transaction_id'=>$transaction_id));

        if(empty($payment_info)){

            if($extraData=='PayFromPage'){
                $payment_info = $player->createPaymentInfo($product_id);
                $payment_info['transaction_id'] = $transaction_id;
                $payment_info['create_t'] = getApp()->now;
                $payment_info['source'] = 'gamecomb_page';
            }else if($product_id=='custom'){
                $payment_info = $player->createPaymentInfo($product_id);
                $payment_info['transaction_id'] = $transaction_id;
                $payment_info['create_t'] = getApp()->now;
                $payment_info['source'] = 'xiaomi';
                $payment_info['cash'] = $cash;
            }else{
                return $this->response(array('state'=>array('code'=>2,'msg'=>"账单[{$transaction_id}]不存在")));
            }

        }else{
            if($payment_info['status'] == StatusCode::payment_finished || $payment_info['status'] == StatusCode::payment_failed ){
                return $this->response(array('state'=>array('code'=>1,'msg'=>"重复订单{$transaction_id}已经处理")));
            }
            //从productid中判断是否是飞钱充值 2014-1-9 by zhangjun 
            if((!empty($product_id)) && strstr($product_id,'sms')){
                $payment_info['is_sms'] = true;
            }
        }

        if($param['state']=="1"){
            // 订单上次处理异常 或者 新的订单
            $ret = $player->process_payment($payment_info);
            $lock->del($lk);
            if($ret['s'] == StatusCode::ok){
                return $this->response(array('state'=>array('code'=>1,'msg'=>"订单{$transaction_id}已经处理")));
            }else{
                return $this->response(array('state'=>array('code'=>2,'msg'=>"订单{$transaction_id}处理失败,".$ret['msg'])));
            }
        }else{
            $payment_info['status'] == StatusCode::payment_failed;
            $mon->save($payment_info);
            return $this->response(array('state'=>array('code'=>1,'msg'=>"订单{$transaction_id}已经处理")));
        }
    }

    function actionTapjoycallback_appstore(){
        $id = $_REQUEST['id'];
        $snuid = $_REQUEST['snuid'];
        $mac = $_REQUEST['mac_address'];
        $currency = $_REQUEST['currency'];
        $sign = $_REQUEST['verifier'];
        $secret_key = 'i6LIXRDsJN7cZJIvrqyA';
        $params = explode(',',$snuid);//pid,uid,sectionid
        $um = model_LoginUser::searchUniq('pid',$params[0]);
        if(is_null($um) || !$um || $um->id() < 1){
            $_PAYMENT_RESPONSE = array('retcode'=>1001,'retmsg'=>"用户[{$params[0]}]不存在");
            header("HTTP/1.0 403 Forbidden");
            return; 
        }
        if($um->id() != $params[1]){
            $_PAYMENT_RESPONSE = array('retcode'=>1002,'retmsg'=>"用户[{$params[0]}]不存在");
            header("HTTP/1.0 403 Forbidden");
            return; 
        }
        if(md5("$id:$snuid:$currency:$secret_key") == $sign){//验签成功
            // 参数：uid,sec,mac,tapjoy_id,gem
            // 返回：1 参数空，2 订单已存在，0 成功
            $ret = model_Tapjoy::add($params[1],$params[2],$mac,$id,$currency);
            if($ret == 0 || $ret == 2){
                header("HTTP/1.0 200 OK");
                $_PAYMENT_RESPONSE=array('Ret'=>$ret,'Desc'=>"处理成功 0-成功 2-重复订单");
            }else {
                header("HTTP/1.0 403 Forbidden");
                $_PAYMENT_RESPONSE=array('ErrorCode'=>1003,'ErrorDesc'=>"参数有误");
            } 
        }else{
            header("HTTP/1.0 403 Forbidden");
            $_PAYMENT_RESPONSE=array('ErrorCode'=>1000,'ErrorDesc'=>"验签失败");
        }
        return; 
    }

    function actionPlayerInfo(){
        $pid = self::getParam('pid');
        $type = self::getParam('type','pid');
        if(empty($pid)){
            return $this->response(array('s'=>'ERROR','msg'=>"need pid param"));
        }
        $um = model_LoginUser::searchUniq($type,$pid);
        if(is_null($um) || !$um){
            return $this->response(array('s'=>'ERROR','msg'=>"pid [$pid] not exists"));
        }
        $uid   = $um->id();
        $create_t = $um['_ct'];
        $istest = $um['istest'];
        $pid = $um['pid'];
        $section_ids = $um['secs'];
        if(empty($section_ids)){
            return $this->response(array('s'=>'ERROR','msg'=>"pid [$pid] do not have section information"));
        }
        $section_config = getApp()->getSectionConfig();
        $sections = array();
        $last_login_time = 0;
        $last_login_section_id = '';
        foreach($section_ids as $section_id=>$t){
            if( $t > $last_login_time){
                $last_login_time = $t;
                $last_login_section_id = $section_id;
            }
            $section_name = $section_config[$section_id]['index'] .'区 '. $section_config[$section_id]['name'];
            $section_data = array('section_id'=>$section_id,'section_name'=>$section_name);
            $player = new model_Player($uid,$section_id);
            $data = $player->getFields(array('name','level','vip.lvl'));
            if(!empty($data['name']) && !empty($data['level'])){
                $section_data['name'] = $data['name'];
                $section_data['level'] = $data['level'];
                $section_data['vip'] = $data['vip']['lvl'];
                $sections[$section_id] = $section_data;
            }
        }
        if(!empty($last_login_section_id)){
            $sections[$last_login_section_id]['default'] = true;
        }
        /*
        $info = array(
            'uid'=>$uid,
            'create_t'=>$create_t,
            'pid'=>$pid,
            'istest'=>$istest,
            'sections'=>$sections,
        );
        $payment_config = getApp()->getPaymentConifg();
        foreach($payment_config as &$config){
            if(empty($config['gemaward'])){
                $config['gemaward'] = 0;
            }
            $gem = $config['gem'] + $config['gemaward'];
            $config['desc'] = "{$config['cash']}元兑换{$gem}元宝";
        }
        return $this->response(array('s'=>'OK','info'=>$info,'payment_config'=>$payment_config));
         */
        return $this->response(array(
            's'=>'OK',
            'info'=>array(
                'pid'=>$pid,
                'sections'=>$sections,
            ),
        ));
    }
	
		/**
		 * 测试分区
		 */
		function actionTestSec(){

			$is_test = self::getParam('is_test');
			$section_list = array_keys(getApp()->getSectionConfig());
			$status = "ok";
			$msg = '';
			$result = array();
			try{
				foreach($section_list as $value){
					
					$mc = new PL_Db_Mongo(DbConfig::getSecMongodb("users",$value));
					$mc->switchColl($value."_users");
					$fields = array("_id","level","exp","_it","init_time");
					$cond = array();
					$user = $mc->findOne($cond,$fields);
					$result[$value] = $user;	
					if(empty($user)){
						$status = 1001;
						$msg .= "$value区未查找到用户信息<br />";
						//$status = "error";
					}
					
				}
				echo $status;
				if($is_test){
				
					echo "<pre>";
					echo $msg;
					echo "user data:<br/>";
					var_dump($result);

				}
			}catch(Exception $ex){
				$status = "error";
				echo $status;		
				echo "<pre>";
				echo $msg;
				echo "user data:<br/>";
				var_dump($result);
				var_dump($ex);
			}

		}
		
    function actionGetUserInfoForHelp(){
        $uid = self::getParam('uid');
        if($uid){
            $um = new model_LoginUser($uid);
            $um->get(array('pid'=>1,'secs'=>1,'_ct'=>1,'istest'=>1));
        }else{
            $pid = self::getParam('pid');
            $type = self::getParam('type','pid');
            if(empty($pid) || empty($type)){
                return $this->response(array('s'=>'ERROR','msg'=>"need param"));
            }
            $pid = trim($pid);
            if(empty($pid) || empty($type)){
                return $this->response(array('s'=>'ERROR','msg'=>"need param"));
            }
            $um = model_LoginUser::searchUniq($type,$pid);
        }
        if(is_null($um) || !$um){
            if(P_PLATFORM=='appstore' && $type == 'pid'){
                $type = 'email';
            }else if(P_PLATFORM=='appstore' && $type=='email'){
                $type = 'pid';
            }else{
                return $this->response(array('s'=>'ERROR','msg'=>"pid [$pid] not exists"));
            }
            $um = model_LoginUser::searchUniq($type,$pid);
            if(is_null($um) || !$um){
                return $this->response(array('s'=>'ERROR','msg'=>"pid & email [$pid] not exists"));
            }
        }
        $uid   = $um->id();
        $create_t = $um['_ct'];
        $istest = $um['istest'];
        $pid = $um['pid'];
        $section_ids = $um['secs'];
        if(empty($section_ids)){
            return $this->response(array('s'=>'ERROR','msg'=>"pid [$pid] do not have section information"));
        }
        $section_config = getApp()->getSectionConfig();
        $sections = array();
        $last_login_time = 0;
        $last_login_section_id = '';
        foreach($section_ids as $section_id=>$t){
            try{
                if( $t > $last_login_time){
                    $last_login_time = $t;
                    $last_login_section_id = $section_id;
                }
                $section_name = $section_config[$section_id]['index'] .'区 '. $section_config[$section_id]['name'];
                $section_data = array('section_id'=>$section_id,'section_name'=>$section_name);
                $player = new model_Player(getApp()->getRealUid($uid,$section_id), getApp()->getRealSec($section_id));
                $data = $player->getFields(array('name','level','vip.lvl','gem','total_gem_used','total_gem_added','total_gem_rewarded','total_gem_rewarded2'));
                if(!empty($data['name']) && !empty($data['level'])){
                    $section_data['name'] = $data['name'];
                    $section_data['level'] = $data['level'];
                    $section_data['vip'] = $data['vip']['lvl'];
                    $section_data['gem'] = $data['gem'];
                    $section_data['total_gem_used'] = $data['total_gem_used'];
                    $section_data['total_gem_added'] = $data['total_gem_added'];
                    $section_data['total_gem_rewarded'] = $data['total_gem_rewarded'];
                    $section_data['total_gem_rewarded2'] = $data['total_gem_rewarded2'];
                    $sections[$section_id] = $section_data;
                }
            }catch(Exception $ex){
                // 有些情况下， $section_id 对应的数据库配置被删除之后，
                // 再去读这个分区的数据出异常，这种情况下，忽略这个分区
            }
        }
        if(!empty($last_login_section_id)){
            $sections[$last_login_section_id]['default'] = true;
        }
        $info = array(
            'uid'=>$uid,
            'create_t'=>$create_t,
            'pid'=>$pid,
            'istest'=>$istest,
            'sections'=>$sections,
        );
        $payment_config = getApp()->getPaymentConifg();
        foreach($payment_config as $ck=>&$config){
            if($config['hide']){
                unset($payment_config[$ck]);
            }
            if(empty($config['gemaward'])){
                $config['gemaward'] = 0;
            }
            $gem = $config['gem'] + $config['gemaward'];
            $config['desc'] = "{$config['cash']}元兑换{$gem}元宝";
        }
        return $this->response(array('s'=>'OK','info'=>$info,'payment_config'=>$payment_config));
    }

    /**
     * 获取给定UIDs的信息
     */
    function actionGetUidsInfo(){
        $uids = self::getParam('uids');
        //如果这个用户UID已经因为合区改变了，但是没有传递改变后的uid 处理..
        $seclist = getApp()->getSectionConfig();
        foreach((array)$uids as $uid=>$info){
            $n_uid = $info['uid'];
            $n_sec = $info['sec'];
            if($seclist[$info['sec']]['merge'] && !strpos($info['uid'],'_')){
                $n_uid = $info['sec'].'_'.$info['uid'];
                $n_sec = $seclist[$info['sec']]['merge'];
            }
            $player = new model_Player($n_uid,$n_sec);
            $data = $player->getFields(array('name','level','vip.lvl','gem','total_gem_used'));
            $data = array_merge($data,$info);
            $result[$uid] = $data;
        }
        return $this->response(array('s'=>'OK','info'=>$result));
    }

    function actionGetUserInfo(){
        $uid = self::getParam('uid');
        if($uid){
            $um = new model_LoginUser($uid);
            $um->get(array('pid'=>1,'secs'=>1,'_ct'=>1,'istest'=>1));
        }else{
            $pid = self::getParam('pid');
            $type = self::getParam('type','pid');
            if(empty($pid) || empty($type)){
                return $this->response(array('s'=>'ERROR','msg'=>"need param"));
            }
            $pid = trim($pid);
            if(empty($pid) || empty($type)){
                return $this->response(array('s'=>'ERROR','msg'=>"need param"));
            }
            $um = model_LoginUser::searchUniq($type,$pid);
        }
        if(is_null($um) || !$um){
            if(P_PLATFORM=='appstore' && $type == 'pid'){
                $type = 'email';
            }else if(P_PLATFORM=='appstore' && $type=='email'){
                $type = 'pid';
            }else{
                return $this->response(array('s'=>'ERROR','msg'=>"pid [$pid] not exists"));
            }
            $um = model_LoginUser::searchUniq($type,$pid);
            if(is_null($um) || !$um){
                return $this->response(array('s'=>'ERROR','msg'=>"pid & email [$pid] not exists"));
            }
        }
        $uid   = $um->id();
        $create_t = $um['_ct'];
        $istest = $um['istest'];
        $pid = $um['pid'];
        $section_ids = $um['secs'];
        if(empty($section_ids)){
            return $this->response(array('s'=>'ERROR','msg'=>"pid [$pid] do not have section information"));
        }
        $section_config = getApp()->getSectionConfig();
        $sections = array();
        $last_login_time = 0;
        $last_login_section_id = '';
        foreach($section_ids as $section_id=>$t){
            try{
                if( $t > $last_login_time){
                    $last_login_time = $t;
                    $last_login_section_id = $section_id;
                }
                $section_name = $section_config[$section_id]['index'] .'区 '. $section_config[$section_id]['name'];
                $section_data = array('section_id'=>$section_id,'section_name'=>$section_name);
                $player = new model_Player(getApp()->getRealUid($uid,$section_id), getApp()->getRealSec($section_id));
                $data = $player->getFields(array('name','level','vip.lvl','gem','total_gem_used','total_gem_added','total_gem_rewarded','total_gem_rewarded2'));
                if(!empty($data['name']) && !empty($data['level'])){
                    $section_data['name'] = $data['name'];
                    $section_data['level'] = $data['level'];
                    $section_data['vip'] = $data['vip']['lvl'];
                    $section_data['gem'] = $data['gem'];
                    $section_data['total_gem_used'] = $data['total_gem_used'];
                    $section_data['total_gem_added'] = $data['total_gem_added'];
                    $section_data['total_gem_rewarded'] = $data['total_gem_rewarded'];
                    $section_data['total_gem_rewarded2'] = $data['total_gem_rewarded2'];
                    $sections[$section_id] = $section_data;
                }
            }catch(Exception $ex){
                // 有些情况下， $section_id 对应的数据库配置被删除之后，
                // 再去读这个分区的数据出异常，这种情况下，忽略这个分区
            }
        }
        if(!empty($last_login_section_id)){
            $sections[$last_login_section_id]['default'] = true;
        }
        $info = array(
            'uid'=>$uid,
            'create_t'=>$create_t,
            'pid'=>$pid,
            'istest'=>$istest,
            'sections'=>$sections,
        );
        $payment_config = getApp()->getPaymentConifg();
        foreach($payment_config as $ck=>&$config){
            // todo 
            if($config['hide']){
                if ($config['forweb']){
                    foreach($payment_config as $fk=>$fv){
                        if ($fv['cash'] == $config['cash'] && !$fv['hide']){
                            unset($payment_config[$fk]);
                        }
                    }
                }else{
                    unset($payment_config[$ck]);
                }
            }
            if(empty($config['gemaward'])){
                $config['gemaward'] = 0;
            }
            $gem = $config['gem'] + $config['gemaward'];
            $config['desc'] = "{$config['cash']}元兑换{$gem}元宝";
        }
        return $this->response(array('s'=>'OK','info'=>$info,'payment_config'=>$payment_config));
    }

    function actionTestGetUserInfo(){
        $uid = self::getParam('uid');
        if($uid){
            $um = new model_LoginUser($uid);
            $um->get(array('pid'=>1,'secs'=>1,'_ct'=>1,'istest'=>1));
        }else{
            $pid = self::getParam('pid');
            $type = self::getParam('type','pid');
            if(empty($pid) || empty($type)){
                return $this->response(array('s'=>'ERROR','msg'=>"need param"));
            }
            $pid = trim($pid);
            if(empty($pid) || empty($type)){
                return $this->response(array('s'=>'ERROR','msg'=>"need param"));
            }
            $um = model_LoginUser::searchUniq($type,$pid);
        }
        if(is_null($um) || !$um){
            if(P_PLATFORM=='appstore' && $type == 'pid'){
                $type = 'email';
            }else if(P_PLATFORM=='appstore' && $type=='email'){
                $type = 'pid';
            }else{
                return $this->response(array('s'=>'ERROR','msg'=>"pid [$pid] not exists"));
            }
            $um = model_LoginUser::searchUniq($type,$pid);
            if(is_null($um) || !$um){
                return $this->response(array('s'=>'ERROR','msg'=>"pid & email [$pid] not exists"));
            }
        }

        $data = model_Brush::getUserInfoForJSAPI($um);
        
        return $this->response($data);
        
    }

    
    public function create_signature($params,$secret_key){
        if(isset($params['sig'])){
            throw new Exception("params 中不能包含 sig字段");
        }
        $keys = array_keys($params);
        sort($keys);
        $str = "";
        foreach($keys as $key){
            $value = urlencode($params[$key]);
            $str .= "&{$key}={$value}";
        }
        $str = substr($str,1);
        //$sig = base64_encode(hash_hmac('md5',$str,$secret_key,true));
        $sig = md5($str.$secret_key);
        return $sig;
    }
    public function verify_signature($params,$secret_key){
        if(!isset($params['sig'])){
            glog::info("no sig params","addgem");
            return false;
        }
        $sig = $params['sig'];
        unset($params['sig']);
        unset($params['gem']);
        $keys = array_keys($params);
        sort($keys);
        $str = "";
        foreach($keys as $key){
            $value = urlencode($params[$key]);
            $str .= "&{$key}={$value}";
        }
        $str = substr($str,1);
        //$sig2 = base64_encode(hash_hmac('md5',$str,$secret_key,true));
        $sig2 = md5($str.$secret_key);
        glog::info("$str\nsig1=$sig\nsig2=$sig2","addgem");
        return $sig === $sig2;
    }

    public function actionAddGem(){
        glog::info(json_encode($_REQUEST),'addgem');
        $keys = array('buyer_email', 'uid', 'section_id', 'product_id', 'transaction_id', 'create_t', 'timestamp', 'sig', 'cash');
        $params = array();
        foreach($keys as $key){
            $$key = self::getParam($key);
            if(empty($$key)){
                $msg = "need param [$key]";
                glog::info($msg,'addgem');
                return $this->response(array('s'=>'ERROR','msg'=>$msg));
            }
            $params[$key] = $$key;
        }
        $payment_config = getApp()->getPaymentConifg();
        if(!isset($payment_config[$product_id])){
            if($product_id=='custom'){
                $params['gem'] = self::getParam('gem');
                /*
                if($cash < 600 ){
                    $msg = "自定义额度[$cash]小于600";
                    glog::info($msg,'addgem');
                    return $this->response(array('s'=>'ERROR','msg'=>$msg));
                }
                 */
            }else{
                $msg = "product_id[$product_id] not exists";
                glog::info($msg,'addgem');
                return $this->response(array('s'=>'ERROR','msg'=>$msg));
            }
        }

        // 验证参数
        if(!isset($_REQUEST['__no_sig__']) && !$this->verify_signature($params,'qxs%Mt6v@nVdUb9d')){
            return $this->response(array('s'=>'ERROR','msg'=>"signature verification failed"));
        }

        /*
        $um = model_LoginUser::searchUniq('pid',$pid);
        if(is_null($um) || !$um){
            return $this->response(array('s'=>'ERROR','msg'=>"pid[$pid] not exists"));
        }
        $uid   = $um->id();
        */

        PL_Session::$usecookie = false;
        $_REQUEST['cid'] = PL_Session::gencid($uid,$section_id);

        $player = getApp()->getPlayer();

        $mon = getApp()->getPaymentMongoConnection();
        $payment_info = $mon->findOne(array('_u'=>$player->uid,'transaction_id'=>$transaction_id));

        if(empty($payment_info)){
            // 生成新的订单信息

            $payment_info = $player->createPaymentInfo($product_id);
            $payment_info['transaction_id'] = $transaction_id;
            $payment_info['create_t'] = $create_t;
            $payment_info['source'] = 'internal';
            if($product_id=='custom'){
                $payment_info['cash'] = $cash;
            }
        }else{
            if($payment_info['status'] == StatusCode::payment_finished || $payment_info['status'] == StatusCode::payment_failed ){
                $msg = "duplicate transaction[{$transaction_id}] processed";
                glog::info($msg,'addgem');
                return $this->response(array('s'=>'OK','msg'=>$msg));
            }
            if($payment_info['_u']!=$uid || $payment_info['_sec']!=$section_id){
                $msg = "transaction info mismatch [$transaction_id][$uid][$section_id]";
                glog::info($msg,'addgem');
                return $this->response(array('s'=>"ERROR",'msg'=>$msg));
            }
        }

        // 订单上次处理异常 或者 新的订单
        $ret = $player->process_payment($payment_info);
        if($ret['s'] == StatusCode::ok){
            $msg = "transaction [{$transaction_id}] processed";
            glog::info($msg,'addgem');
            return $this->response(array('s'=>'OK','msg'=>$msg));
        }else{
            $msg = "transaction[{$transaction_id}] failed,".$ret['msg'];
            glog::info($msg,'addgem');
            return $this->response(array('s'=>'ERROR','msg'=>$msg));
        }
    }

    public function actionPaycallback_coolplus(){
        // todo 先验证签名

        $rawp = file_get_contents('php://input');
        if(empty($rawp)){
            echo "FAIL, empty post";return;
        }
        $params = json_decode($rawp,true);
        if(empty($params)){
            echo "FAIL, invalid post";return;
        }
        //[2012-11-05 21:17:36] raw post: {"exorderno":"s1,airmud.ares.g60,5097bc2cd1b32f1a4100031e","transid":"02112110521111012561","waresid":"10003300000005100033","chargepoint":1,"feetype":2,"money":10,"result":0,"transtype":0,"transtime":"2012-11-05 21:11:36","count":1,"sign":"772b70d08287cede4e45b4179ab56487"}
        $extra_order_info = $params['exorderno'];
        list($section_id,$product_id,$transaction_id) = explode(',',$extra_order_info);
        if(empty($transaction_id)){
            echo "FAIL, no transaction_id";return;
        }

        $mon = getApp()->getPaymentMongoConnection();
        $payment_info = $mon->findOne(array('transaction_id'=>$transaction_id));
        if(empty($payment_info)){
            echo "FAIL, transaction [$transaction_id] not exists";
            return;
        }else{
            if($payment_info['status'] == StatusCode::payment_finished || $payment_info['status'] == StatusCode::payment_failed ){
                echo "SUCCESS";
                return;
            }
        }
        $uid = $payment_info['_u'];

        PL_Session::$usecookie = false;
        $_REQUEST['cid'] = PL_Session::gencid($uid,$section_id);

        $player = getApp()->getPlayer();
        $lock = DbConfig::getRedis('rank');
        $lk = "pay_lock_{$player->uid}_{$player->section_id}";
        $now = time();
        $res = $lock->SETNX($lk, $now);
        if($res){
            $lock->SETEX($lk, 20, $now);
        }else{
            return $this->response(array('ErrorCode'=>0,'ErrorDesc'=>"订单处理锁定！"));
        }

        // 订单上次处理异常 或者 新的订单
        $ret = $player->process_payment($payment_info);
        $lock->del($lk);
        if($ret['s'] == StatusCode::ok){
            echo "SUCCESS";
            return;
        }else{
            echo "FAIL,[{$transaction_id}]处理失败,".$ret['msg'];
            return;
        }
    }
    public function actionGetAllSectionIDS(){
        $section_config = getApp()->getSectionConfig();
        $section_ids = array_keys($section_config);
        $ret = array('s'=>StatusCode::ok,'section_ids'=>$section_ids,'ids'=>$section_ids);
        die(htmlentities($_GET['callback']).'('.json_encode($ret).')');
    }

    public function actionGetAllSourceIDS(){
        $source_config = getApp()->getSourceConfig();
        $source_ids = array_keys($source_config);
        $ret = array('s'=>StatusCode::ok,'ids'=>$source_ids, 'data'=>$source_config);
        die(htmlentities($_GET['callback']).'('.json_encode($ret).')');
    }
    public function actionSetVIP(){
        $params = array();
        $params['uid'] = self::getParam('uid');
        $params['section_id'] = self::getParam('section_id');
        $params['platform'] = self::getParam('platform');
        $params['vip'] = self::getParam('vip'); 
        $params['operator'] = self::getParam('operator');

        if($params['platform'] != P_PLATFORM){
            return $this->response(array('s'=>StatusCode::error,'msg'=>"平台信息不匹配"));
        }

        $vip_configs = getApp()->getVipConfig();
        if(!isset($vip_configs[$params['vip']])){
            return $this->response(array('s'=>StatusCode::error,'msg'=>"VIP等级不合法"));
        }

        PL_Session::$usecookie = false;
        $_REQUEST['cid'] = PL_Session::gencid($params['uid'],$params['section_id']);


        $player = getApp()->getPlayer();
        $data = $player->getFields(array(
            'gem','total_gem_used','total_gem_rewarded','total_gem_added','total_gem_rewarded2','vip'
        ));
        $current_gem_total = $data['total_gem_added'] + $data['total_gem_rewarded2'];
        $old_vip = $data['vip']['lvl'];

        $vip_config = $vip_configs[$params['vip']];
        if($vip_config['gem_total'] < $current_gem_total){
            return $this->response(array('s'=>StatusCode::error,'msg'=>"已支付额度超过待设置的VIP等级"));
        }

        $login_model = new model_LoginUser($params['uid']);
        $login_model->get(array('istest'=>1));
        if(!$login_model['istest']){
            return $this->response(array('s'=>StatusCode::error,'msg'=>"只能给测试用户设置VIP等级"));
        }

        $gem_need_add = $vip_config['gem_total'] - $current_gem_total;
        // 不补偿元宝
        // TODO 日志
        $player->numberIncr('base','total_gem_rewarded2',$gem_need_add);
        $player->checkVipUpgrade();
        $ret = $player->commit();

        // 发邮件
        $uid = $player->uid;
        $section_id = $player->section_id;
        $login_model = new model_LoginUser($uid);
        $login_model->get(array('pid'=>1,'email'=>1));
        $pid = $login_model['pid'];
        $email = $login_model['email'];

        $new_vip = $player->numberGet('vip','lvl');
        $platform = P_PLATFORM;
        $name = $player->stringGet('base','name');
        $operator = $params['operator'];
        $subject = "[$operator]SetVIP[$platform][$section_id][$uid][vip:$new_vip]";

        $time = date("Y-m-d H:i:s");
        $content = "操作时间: $time \n";
        $content .= "操作人:$operator\n平台: $platform\nPID:$pid\nemail:$email\nUID:$uid\n分区:$section_id\n门派名:$name\n";
        $content .= "设置前vip:$old_vip\n";
        $content .= "设置后vip:$new_vip\n";

        model_Util::sendemail('op@playcrab.com',$subject,$content);

        return $this->response($ret);
    }

    public function actionBuchangGem(){
        glog::info(json_encode($_REQUEST),'addgem');
        glog::info(json_encode($_SERVER),'addgem');
        $hour = date("H");
        if($hour < 10 || $hour > 21 ){ 
            // 10:00 ~ 21:59 才能使用这个接口，防止阿里云的云盾自动发起回调...
            // 有没有更好的处理方法...
            return $this->response(array('s'=>'ERROR','msg'=>'操作时间不合法'));
        }
        $keys = array('section_id','product_id','platform','operator','optime');
        $params = array();
        foreach($keys as $key){
            $$key = trim(self::getParam($key));
            if(empty($$key)){
                $msg = "need param [$key]";
                glog::info($msg,'addgem');
                return $this->response(array('s'=>'ERROR','msg'=>$msg));
            }
            $params[$key] = $$key;
        }
        $now = time();
        if($now - $params['optime'] > 60 || $params['optime'] - $now > 60){
            return $this->response(array('s'=>'ERROR','msg'=>'操作已过期'));
        }

        $pid = self::getParam('pid');
        if($pid){
            $um = model_LoginUser::searchUniq('pid',$pid);
            if(!$um){
                return $this->response(array('ErrorCode'=>0,'ErrorDesc'=>"用户[{$pid}]不存在"));
            }
            $uid   = $um->id();
        }else{
            $uid = self::getParam('uid');
        }
        if(empty($uid)){
            return $this->response(array('s'=>'ERROR','msg'=>"需要UID参数"));
        }

        if($params['platform'] != P_PLATFORM){
            return $this->response(array('s'=>StatusCode::error,'msg'=>"平台信息不匹配"));
        }
        if($product_id == 'custom'){
            if(!in_array(P_PLATFORM,array('qqandroid','kunlun'))){
                return $this->response(array('s'=>StatusCode::error,'msg'=>P_PLATFORM."平台不支持自定义额度支付"));
            }
            $params['cash'] = trim(self::getParam('cash'));
            if( !is_numeric($params['cash']) || $params['cash'] <= 0 ){
                return $this->response(array('s'=>StatusCode::error,'msg'=>"自定义额度{$params['cash']}不合法"));
            }
        }else{
            $payment_config = getApp()->getPaymentConifg();
            if(!isset($payment_config[$product_id])){
                $msg = "product_id[$product_id] not exists";
                glog::info($msg,'addgem');
                return $this->response(array('s'=>'ERROR','msg'=>$msg));
            }
        }

        // 验证参数
        if(0 && !isset($_REQUEST['__no_sig__']) && !$this->verify_signature($params,'qxs%Mt6v@nVdUb9d')){
            return $this->response(array('s'=>'ERROR','msg'=>"signature verification failed"));
        }

        PL_Session::$usecookie = false;
        $_REQUEST['cid'] = PL_Session::gencid($uid,$section_id);

        $player = getApp()->getPlayer();

        $mon = getApp()->getPaymentMongoConnection();

        $mongoid = new MongoID();
        $transaction_id = $mongoid->{'$id'};


        // 生成新的订单信息
        $payment_info = $player->createPaymentInfo($product_id);
        if($product_id=='custom'){
            $payment_info['cash'] = $params['cash'];
            if(P_PLATFORM == 'kunlun'){
                $payment_info['sumGem'] = $payment_info['cash'] * 15;
            }
        }
        $payment_info['transaction_id'] = $transaction_id;
        $payment_info['create_t'] = getApp()->now;
        $payment_info['source'] = 'buchang';
        $payment_info['msg'] = $params['operator']."给玩家补偿未到账的支付,";
        $old_vip = $payment_info['_vip'];
        $old_gem = $payment_info['ogem'];

        // 订单上次处理异常 或者 新的订单
        $ret = $player->process_payment($payment_info);
        if($ret['s'] == StatusCode::ok){
            $msg = "transaction [{$transaction_id}] processed";
            glog::info($msg,'addgem');
            if(1){
                $gem_add_and_reward = $ret['info']['gem_add_and_reward'];
                // 补发传书
                $msg = array(
                    'type'=>'system',
                    'key'=>uniqid(),
                    'time'=>getApp()->now,
                    'content'=>"您未到账的{$gem_add_and_reward}元宝已经补发到账，祝您游戏愉快~",
                );
                model_Chat::sendMsg($msg,$uid,'origin',$section_id);
            }else{
                $gem_add_and_reward = $ret['info']['gem_add_and_reward'];
                $reward_gem = ceil($gem_add_and_reward * 0.1);
                // 补发传书
                $msg = array(
                    'type'=>'system',
                    'key'=>uniqid(),
                    'time'=>getApp()->now,
                    'content'=>"您未到账的{$gem_add_and_reward}元宝已经补发到账，现在额外补偿您{$reward_gem}元宝，祝您游戏愉快~",
                );
                $awards = array(
                    array('tag'=>'gem','num'=>$reward_gem),
                );
                $msg['status'] = 'award';
                $cdkey = model_Cdkey::gen($awards,"{$reward_gem}元宝");
                $msg['cdkey'] = $cdkey;
                model_Chat::sendMsg($msg,$uid,'origin',$section_id);
            }

            // 发邮件
            $login_model = new model_LoginUser($uid);
            $login_model->get(array('pid'=>1,'email'=>1));
            $pid = $login_model['pid'];
            $email = $login_model['email'];
            $login_model->opOne('buchange_gem_t',getApp()->now);
            $login_model->save();

            $new_gem = $player->numberGet('base','gem');
            $new_vip = $player->numberGet('vip','lvl');
            $platform = P_PLATFORM;
            $name = $player->stringGet('base','name');
            $cash = $payment_config[$product_id]['cash'];
            $operator = $params['operator'];
            $subject = "[$operator]BuChangChongZhi[$platform][$section_id][$uid][$cash yuan]";
            $time = date("Y-m-d H:i:s");
            $content = "操作时间: $time \n";
            $content .= "操作人:$operator\n平台: $platform\nPID:$pid\nemail:$email\nUID:$uid\n分区:$section_id\n门派名:$name\n";
            $content .= "充值前:\nvip:$old_vip\ngem:$old_gem\n";
            $content .= "充值后:\nvip:$new_vip\ngem:$new_gem\n";

            model_Util::sendemail('op@playcrab.com',$subject,$content);

            return $this->response(array('s'=>'OK','msg'=>$msg,'ret'=>$ret));
        }else{
            $msg = "transaction[{$transaction_id}] failed,".$ret['msg'];
            glog::info($msg,'addgem');
            return $this->response(array('s'=>'ERROR','msg'=>$msg,'ret'=>$ret));
        }
    }


	//处理微信
	//
	function actionWeixin(){
		$m = new model_Weixin();
		$m->process_dzm();
	}

	//处理微信
	//
	function actionPromotion_limei(){
		$ret = model_Promotion::record('limei');
		$out['message'] = 'OK';
		$out['successs'] = $ret;
		echo json_encode($out);

	}
	function actionPromotion_itool(){
		$ret = model_Promotion::record('itools');
		$out['code'] = 0;
		echo json_encode($out);
		exit();
	}
	function actionPromotion_midi(){
		$ret = model_Promotion::record('midi');
		$out['message'] = 'OK';
		$out['successs'] = 'true';
		echo json_encode($out);
		exit();
	}
	function actionPromotion_jzmob(){
		$ret = model_Promotion::record('jzmob');
		echo 1;
		exit();
	}
	function actionPromotion_wanpu(){
		$ret = model_Promotion::record('wanpu');
		$out['success'] = 'true';
		$out['message'] = '成功';
		echo json_encode($out);
		exit();
	}
	function actionPromotion_appdriver(){
		$ret = model_Promotion::record('appdriver');
		$out['success'] = 1;
		$out['msg'] = '成功';
		echo json_encode($out);
		exit();
	}
	function actionPromotion_appdriver_new(){
		$ret = model_Promotion::record('appdriver_new');
		$out['success'] = 1;
		$out['msg'] = '成功';
		echo json_encode($out);
		exit();
	}
	function actionPromotion_adwo(){
		$ret = model_Promotion::record('adwo');
		$out['success'] = 1;
		$out['msg'] = '成功';
		echo json_encode($out);
		exit();
	}
	function actionPromotion_domob(){
		$n = $_REQUEST['__s'];
		if(!$n)
			$n = 'domob';

		$ret = model_Promotion::record($n);
        header("location:https://itunes.apple.com/cn/app/da-zhang-men/id538640684");
	}
	function actionPromotion_guomeng(){
		$n = $_REQUEST['__s'];
		if(!$n)
			$n = 'guomeng';

		$ret = model_Promotion::record($n);
        header("location:https://itunes.apple.com/cn/app/da-zhang-men/id538640684");
	}
    function actionPromotion_domob_banner(){
		$n = $_REQUEST['__s'];
		if(!$n)
			$n = 'domob_banner';

        header("location:https://itunes.apple.com/cn/app/da-zhang-men/id538640684");
        exit();
		$ret = model_Promotion::record($n);
        header("location:https://itunes.apple.com/cn/app/da-zhang-men/id538640684");
    }

    function actionPromotion_dianru(){
        $n = $_REQUEST['__s'];
        if(!$n){
            $n = 'dianru';
        }

        if(empty($_REQUEST['drkey'])){
            echo "error";
            exit();
        }
        $ret = model_Promotion::record($n);
        echo "ok";
    }

	/**
	 * 生成cdk
	 * todo:更好的安全性
	 */
	function actionWeixincdkey(){
		if($_GET['ff']=='_playsss')
			echo model_Weixin::realGen($_GET['t']);
	}

    function response_qq($status){
        $output = http_build_query(array(
            'uid'=>$_REQUEST['uid'],
            'linkId'=>$_REQUEST['linkId'],
            'buyId'=>$_REQUEST['buyId'],
            'goodsId'=>$_REQUEST['goodsId'],
            'goodsCount'=>$_REQUEST['goodsCount'],
            'status'=>$status,
        ));
        global $_PAYMENT_RESPONSE;
        $_PAYMENT_RESPONSE = $output;

        $padding_length = strlen($output) % 8;
        if($padding_length){
            $output .= str_repeat(' ', $padding_length);
        }

        $des_key = '234714452425147581509045';
        //echo $output;
        echo mcrypt_encrypt("tripledes", $des_key, $output, "ecb");
    }
    function actionPaycallback_qq(){
        $source = $_REQUEST['type'];
        $pid = $_REQUEST['uid'];
        $linkId = $_REQUEST['linkId'];
        list($uid,$section_id) = explode(',',$linkId);
        $transaction_id = $_REQUEST['buyId'];
        $cash = $_REQUEST['goodsCount'] / 10;
        $create_t = $_REQUEST['buytime'];
        $product_id = 'custom';

        PL_Session::$usecookie = false;
        //更新2.9sdk调用支付接口Tencent.getInstance().showPayproxyActivity2支付回调中linkid=0 导致无法定位用户给其发放元宝，在12：00 临时处理如下
        if(empty($uid) || empty($section_id)){
            $um = model_LoginUser::searchUniq('pid',$pid);
            if(is_null($um) || !$um || $um->id() < 1){
                throw new Exception("用户[{$pid}]不存在");
                return ;
            }  
            $uid = $um->id();
            $section_id = $um['ls'];
        }
        //临时处理--end
        $_REQUEST['cid'] = PL_Session::gencid($uid,$section_id);

        $player = getApp()->getPlayer();
        $lock = DbConfig::getRedis('rank');
        $lk = "pay_lock_{$player->uid}_{$player->section_id}";
        $now = time();
        $res = $lock->SETNX($lk, $now);
        if($res){
            $lock->SETEX($lk, 20, $now);
        }else{
            return $this->response(array('ErrorCode'=>0,'ErrorDesc'=>"订单处理锁定！"));
        }

        $mon = getApp()->getPaymentMongoConnection();
        $payment_info = $mon->findOne(array('_u'=>$player->uid,'transaction_id'=>$transaction_id));

        if(empty($payment_info)){
            // 生成新的订单信息
            $payment_info = $player->createPaymentInfo($product_id);
            $payment_info['transaction_id'] = $transaction_id;
            $payment_info['create_t'] = $create_t;
            $payment_info['source'] = $_SESSION['source'];
            $payment_info['pay_method'] = $source;
            $payment_info['cash'] = $cash;
        }else{
            if($payment_info['status'] == StatusCode::payment_finished || $payment_info['status'] == StatusCode::payment_failed ){
                $msg = "duplicate transaction[{$transaction_id}] processed";
                return $this->response_qq(0);
            }
            if($payment_info['_u']!=$uid || $payment_info['_sec']!=$section_id){
                $msg = "transaction info mismatch [$transaction_id][$uid][$section_id]";
                return $this->response_qq(2);
            }
        }

        // 订单上次处理异常 或者 新的订单
        $ret = $player->process_payment($payment_info);
        $lock->del($lk);
        if($ret['s'] == StatusCode::ok){
            $msg = "transaction [{$transaction_id}] processed";
            return $this->response_qq(0);
        }else{
            $msg = "transaction[{$transaction_id}] failed,".$ret['msg'];
            return $this->response_qq(1);
        }
    }

	public function actionLunjian(){
        $section = $_REQUEST['sec'];
        if(empty($section)){
            die("需要分区参数sec");
        }
		return model_PVPUtil::createLunjian($section);
    }

    public function actionUpdateAccount(){
        $uid = $_GET['uid'];    
        $source = $_GET['source'];

        if(empty($uid) || empty($source)) return false;

        $um = getApp()->getlum($uid);
        $data = $um->get();

        if(empty($data)) return false;

        if(empty($data['source']) || $data['source'] == 'nosrc1' || $data['source'] == 'nosrc'){
            $um->opOne('source',$source);     
            $um->save();
        }

        return true;
    }

	/**
	 * 互动平台
	 */
	public function actionAdcallback_hudong(){
		if(empty($_REQUEST['udid']) || empty($_REQUEST['app'])){
			echo 'error';
			return;
		}

		model_Promotion::record('hudong');
	}

    /**
     * 有米广告平台接入，接口
     *
     */
    public function actionAdcallback_youmi(){
        $mac = $_REQUEST['_ym_uid2'];
        $url = $_REQUEST['_ym_url'];

        if(empty($mac) || empty($url)){
            echo 'error';return;
        } 

        model_Promotion::record('youmi');

        echo "ok";
    }

    public function actionAdcallback_qiubai(){
        $mac = $_REQUEST['mac'];

        if(empty($mac)){
            echo "error";
            exit();
        }
        model_Promotion::record('qiubai');

        echo "ok";
    }

    public function actionAdcallback_tapjoy(){
        $mac = $_REQUEST['mac'];
        $idfa = $_REQUEST['idfa'];

        if(empty($mac)){
            echo "error";
            exit();
        }
        model_Promotion::record('tapjoy');

        header("location:https://itunes.apple.com/app/da-zhang-men/id624500855");
    }

    public function actionAdcallback_tapjoy_dalu(){
        $mac = $_REQUEST['mac'];
        $idfa = $_REQUEST['idfa'];

        if(empty($mac)){
            echo "error";
            exit();
        }
        model_Promotion::record('tapjoy_dalu');

        header("location:https://itunes.apple.com/cn/app/da-zhang-men/id538640684");
    }

    /**
     * 有米广告平台接入，接口
     *
     */
    public function actionAdcallback_yijifen(){
        $appid = $_REQUEST['appid'];
        $deviceid = $_REQUEST['deviceid'];

        if($appid != 'playcrab_dzm' || empty($deviceid)){
            echo json_encode(array('success'=>false,'message'=>'param wrong'));return;
        } 

        model_Promotion::record('yijifen');

        echo json_encode(array('success'=>true,'message'=>'ok'));
    }

    /**
     * 唱吧调用接口
     */
    public function actionCallback_changba(){
        $mac = $_REQUEST['mac'];

        if(empty($mac)){
            echo json_encode(array('errorcode'=>'MAC_ADDRESS_NOT_EXIST'));
            exit();
        } 

        model_Promotion::record('changba');

        echo json_encode(array('errorcode'=>'SUCCEED'));
    }

    public function actionGetUserId(){
        $text = $_REQUEST['pid'];
        $sec = $_REQUEST['sec'];
        if($_REQUEST['passwd'] != 'playxxxcrab'){
            echo json_encode(array('error'=>3003,'msg'=>'wrong param'));
            exit();
        }

        if(empty($text)){
            echo json_encode(array('error'=>3001,'msg'=>'pid can not be null'));
            exit();
        }

        $uid = 0;
        $um = model_LoginUser::searchUniq('pid',$text);
        if($um)
            $uid = $um->id();
        if($uid < 1){
            $um = model_LoginUser::searchUniq('email',$text);
            if($um)
                $uid = $um->id();
        }
        if($uid < 1){
            $um = model_LoginUser::searchUniq('_ld.dangle_username','dcn_'.$text);
            if($um)
                $uid = $um->id();
        }
        if($uid < 1 && !empty($sec)){
            $um = new model_User(0,$sec);
            $users = $um->searchByPre('name',$text,array('name'=>1),10);
            foreach((array) $users as $k=>$v){
                $uid = $v['_id'];
                break;
            }
        }

        if($uid < 1){
            echo json_encode(array('error'=>3002,'msg'=>'uid not found'));
        }else{
            echo json_encode(array('error'=>0,'data'=>$uid,'success'=>'ok'));
        }
    }

    public function actionGetActionNames(){
        if($_REQUEST['passwd'] != 'playxxxcrab'){
            echo json_encode(array('error'=>3003,'msg'=>'wrong param'));
            exit();
        }
        $actions = require(ROOT."/admin/config/showlogconf.php");

        $result = array();
        foreach($actions as $value){
            $result[$value['c'].".".$value['m']] = !empty($value['title']) ? $value['title'] : '';
        }

        echo json_encode(array('error'=>0,'data'=>$result,'msg'=>'success'));
    }

    public function actionGetItemNames(){
        if($_REQUEST['passwd'] != 'playxxxcrab'){
            echo json_encode(array('error'=>3003,'msg'=>'wrong param'));
            exit();
        }
        $items = PL_Config_Numeric::get('item');
        $translate = PL_Config_Numeric::get('translate');
        $result = array();
        foreach($items as $key=>$value){
            $result[$key] = $translate[$value['name']]['zh_CN'];
        }
        echo json_encode(array('error'=>0,'data'=>$result,'msg'=>'success'));
    }


    public function actionGetSec(){
        if($_REQUEST['passwd'] != 'playxxxcrab'){
            echo json_encode(array('error'=>3003,'msg'=>'wrong param'));
            exit();
        }

        $data =getApp()->getSectionConfig();

        echo json_encode(array('error'=>0,'data'=>$data));
    }

    public function actionCheckcron(){
        echo "platform = ".P_PLATFORM;
        $cmd = new CheckcronCommand();
        $cmd->execute();
    }
    
    
    /**
     * 纵乐得到pid
     */
    function actionZLgetPid(){
        
       try{
        $email = model_System::myCrypt(trim(self::getParam('email')),"decode");
        $pass = model_System::myCrypt(trim(self::getParam('passwd')),"decode");

		//$email = trim(self::getParam('email'));
        //$pass = trim(self::getParam('passwd'));

        $result = model_System::getPid($email, $pass);
       }catch(Exception $e){
       
        echo  json_encode(array("s"=>608,"msg"=>"call error","exception"=>$e));
        exit;
       }

        echo  json_encode($result);
        
    }
    
    /**
     * 纵乐绑定
     */
    function actionZLBing(){
        

        $email = model_System::myCrypt(trim(self::getParam('email')),"decode");
        $pass = model_System::myCrypt(trim(self::getParam('passwd')),"decode");

        //$email = trim(self::getParam('email'));
        //$pass = trim(self::getParam('passwd'));

		//echo $email;
		//exit;
        $result = model_System::bingZL($email, $pass);

        echo json_encode($result);
        
    }
    
    function actionZLBingByPid(){
        
        $pid = model_System::myCrypt(trim(self::getParam('pid')),"decode");
        $pid = rtrim($pid,"\0");

        $result = model_System::bingZLByPid($pid);
        
        echo json_encode($result);

	}
        
    /**
     * 得到竞技场排行
     */
    function actionGetJjcRank(){
        
        $sec =  trim($_REQUEST['sec']);
        $platform = trim($_REQUEST['platform']);
        
        $zhu = model_Jjc::getZhuNoBySec($sec);
        
        $ji = model_Jjc::getXJiNo();
        
        $rank = model_Jjc::getAllServerRankForGM($ji,$zhu);
        
        $ret['s'] = StatusCode::ok;
        
        $ret['d']['rank'] = $rank;
        
        echo json_encode($ret);
        
    }
    /**
     * 根据平台得到分区列表
     */
    function actionGetSeclistByPlatform(){
        
        $platform = trim($_REQUEST['platform']);

        $config	= require COMM_ROOT.'/../platforms/'.$platform.'/config/config.php';
        $secs	= $config['secs'];
        
        $platform_dis = array(
            "gamecomb" => 2,
            "qqandroid" => 1,
            "kunlun" => 517000,
            "ios91" => 90
        );
        $sec_info = array();
        
        foreach($secs as $sec => $v){
            
            if($sec == "stest1"){
                continue;
            }
            
            if(!isset($platform_dis[$platform])){
                $showsec = substr($sec, 1);
            }else{
                $showsec = substr($sec, 1) -  $platform_dis[$platform];
            }
            
            $sec_info[] = array("sec"=>$showsec,"name"=>$v['name'],"real_sec"=>$sec);
            
        }
        
        $ret['s'] = StatusCode::ok;
        $ret['msg'] = "";
        $ret['d']['sec_info'] = $sec_info;
        
        
        echo json_encode($ret);
        
    }
    //设置服务器时间
    function actionSetTime(){
        if(P_PLATFORM != 'dev') die('not dev!');
        $time = $_REQUEST['time'];
    }

    /**  
     * actionPayment
     * @author 符璨
     * @param
     *          pid
     *          uid
     *          appid
     *          sec
     *          transaction_id:订单id
     *          cashier_id:支付单id
     *          cash:支付金额
     *          status:
     *          time:支付单生成时间戳
     *          product_id:购买产品编号
     *          product_cnt
     *          sig:签名
     * @return
     *      s 状态码
     *          100：玩家数据不存在
     *          108：签名验证错误
     *          11：product_id或其他原因引起的加载支付配置错误引发的异常
     *          ok：支付成功
     * @desc
     *      提供给cashier服务器调用的支付接口
     */
   public function actionPayment(){
        $now = getApp()->now;
        //取出所有参数
        $pid            = $_POST['pid'];
        $uid            = $_POST['uid'];
        $appid          = $_POST['appid'];
        $transaction_id = $_POST['transaction_id'];
        $cashier        = $_POST['cashier'];
        $cash           = $_POST['cash'];
        $status         = $_POST['status'];
        $create_t       = $_POST['create_t'];
        $product_id     = $_POST['product_id'];
        $product_cnt    = $_POST['product_cnt'];
        $channel        = $_POST['channel'];
        $channel_id     = $_POST['channel_id'];
        $sec            = $_POST['sec'];
        $isrepay        = $_POST['isrepay'];

        //验证签名
        $data = array(
            'pid'            => $pid,
            'uid'            => $uid,
            'appid'          => $appid,
            'channel'        => $channel,
            'channel_id'     => $channel_id,
            'sec'            => $sec,
            'transaction_id' => $transaction_id,
            'cashier'        => $cashier,
            'cash'           => $cash,
            'status'         => $status,
            'create_t'       => $create_t,
            'product_id'     => $product_id,
            'product_cnt'    => $product_cnt,
            'isrepay'        => $isrepay,
        );
        ksort($data); 

        $sig = md5(http_build_query($data) . '171ca1475ffcd016fca228cd716f14b7');
        if($sig != $_POST['sig']){
            echo json_encode(array('s'=>StatusCode::invalid_siginature));
            return;
        }

        //加锁避免重复处理
        $redis = DbConfig::getRedis('rank');
        $lock_key = "payment_{$transaction_id}";
        $lock_res = $redis->SETNX($lock_key, $now);
        if($lock_res){
            //60秒过期
            $redis->SETEX($lock_key, 60, $now);
        }
        else{
            echo json_encode(array('s'=>StatusCode::can_not_do));
            return;
        }

        //判断账单是否处理避免重复处理
        $mon = getApp()->getPaymentMongoConnection();
        $order = $mon->findOne(array('transaction_id'=>$transaction_id));
        if($order){
            echo json_encode(array('s'=>StatusCode::ok));
            return;
        }

        unset($data['time']);
        unset($data['uid']);
        //$data['cashier_t'] = $cashier_t;
        $data['process_t']   = $now;
        $data['_u']          = is_numeric($uid)? intval($uid) : $uid;
        $data['action']      = 'recharge_gem';
        $data['_sec']        = $data['sec'];
        $data['_tm']         = $data['create_t'];
        $player              = new model_Player($uid,$sec);
        $user_data           = $player->getFields(array('level','vip.lvl','gem'));
        $data['_lvl']        = $user_data['level'];
        $data['_vip']        = $user_data['vip']['lvl'];
        $data['ogem']        = $user_data['gem'];
        $data['order_id']    = $transaction_id;
        if ($data['channel'] == "zongle"){//需求使用纵乐sdk发布cps包 by zhangjun
            $data['source']  = $data['channel'].$data['channel_id'];
        }else{
            $data['source'] = $data['channel'];
        }

        //根据pid获取uid并生成用户session
        PL_Session::$usecookie = false;
        $_REQUEST['cid'] = PL_Session::gencid($uid, $sec);

        //uid非法
        if(!$uid || $uid < 0){
            //玩家不存在
            glog::info("异常的支付数据[uid:$uid][section_id:$sec][transaction_id:$transaction_id][product_id:$product_id]", 'payment');
            echo json_encode(array('s'=>StatusCode::exception));
            return;
        }

        try{
            $player = getApp()->getPlayer();
        }
        catch(Exception $e){
            //玩家不存在
            glog::info("异常的支付数据[uid:$uid][section_id:$sec][transaction_id:$transaction_id][product_id:$product_id]", 'payment');
            echo json_encode(array('s'=>StatusCode::exception,'msg'=>'error1'));
            return;
        }

        $data['cash'] = $data['cash'] / 100; //cash通知单位是分
        if($channel == 'wanpay_web'){
            $this->processWanpay($data);
        }
        $ret = $player->process_payment($data,true,$data['cash']);
        $redis->DEL($lock_key);
        echo json_encode($ret);
   }

    /**
     * 官网支付
     */
    private function processWanpay(&$data){
        $data['source'] = "internal";
        if($data['product_id'] == 'custom' && $data['cash'] < 1000){//自定义小于1000最低限额
            //glog::info("异常的支付数据，自定义金额小于1000[uid:$uid][section_id:$sec][transaction_id:$transaction_id][product_id:$product_id]", 'payment');
            //echo json_encode(array('s'=>StatusCode::exception,'msg'=>'error2'));
            //die;
        }
        if(intval($data['cash']) < 6){
            glog::info("异常的支付数据,转换到元小于6！[uid:$uid][section_id:$sec][transaction_id:$transaction_id][product_id:$product_id]", 'payment');
            echo json_encode(array('s'=>StatusCode::exception,'msg'=>'error3'));
            die;
        }
    }



	/**
	 * actionCashierGetInfo
	 * @author cq
	 * @date 2014/07/01
	 * @param
	 *      id:uid或者pid
	 * @return
	 *      s
	 *      info
	 *          uid
	 *          create_t
	 *          pid
	 *          istest
	 *          sections
	 *              s1
	 *                  name
	 *                  level
	 *                  vip
	 *                  gem
	 *                  defaul
	 *              s2
	 *                  ...
	 *      payment_config
	 *          系统的payment_config
	 */
	public function actionCashierGetInfo(){
		$now = getApp()->now;
		//$ip = getApp()->getClientIP();
	
		//验证消息来源是否合法
		$valid_ip_list = array(
				'115.29.193.89',
				'115.29.225.243',
				'115.29.229.132',
				'115.29.229.113',
				'117.121.10.35',
		);
		/*
		if(!in_array($ip, $valid_ip_list)){
		return array('s'=>StatusCode::invalid_ip);
		}*/
	
	
	
		$id = $_POST['id'];
        if(empty($id)){
            echo json_encode(array('s'=>StatusCode::invalid_param));
            return;
        }
		$mon = new PL_Db_Mongo(DbConfig::getMongodb('userlogin'));
	
		$um = $mon->findOne(array('email'=>$id));
		
		if(!$um){
			$um = $mon->findOne(array('pid'=>$id));
		}
		if(!$um){
			echo json_encode(array('s'=>StatusCode::invalid_param));
			return;
		}
	
		$ret = array();
		$ret['s'] = StatusCode::ok;
	
		//获取用户登录信息
		$ret['info'] = array();
		$ret['info']['uid'] = $um['_id'];
		//接口中需要添加pid by zhangjun
		$ret['info']['pid'] = $um['pid'];
		$ret['info']['create_t'] = $um['_ct'];
		$ret['info']['istest'] = 0;
		$ret['info']['istest'] = ($um['istest']) ? 'istest' : 0;
		$ret['info']['istest'] = ($um['isdev']) ? 'isdev' : $ret['info']['istest'];
	
	
		//获取用户各个分区信息
		$section_config = getApp()->getSectionConfig();
		$ret['info']['sections'] = array();
		if(isset($um['secs'])){
			foreach($um['secs'] as $sec => $last_t){
				$um_mc = new model_Player($um['_id'], $sec);
				$um_data = $um_mc->getFields(array('name', 'level', 'vip', 'gem', '_it'));
				$um_data['vip'] = $um_data['vip']['lvl'];
				$um_data['default'] = ($sec == $um['_ld']['sec']) ? 1 : 0;
				$um_data['section_name'] = $section_config[$sec]['name'];
				$ret['info']['sections'][$sec] = $um_data;
			}
		}
	
		//获取支付信息
		$ret['payment_config'] = getApp()->getPaymentConifg();//NULL, $um['source']);
		$ret['payment_unit'] = '元宝';
		$list = $ret['payment_config'];
		//添加自定义支付项
		$max_product_config = array('cash'=>-1);
		foreach($ret['payment_config'] as $index => $p_config){
			//修改一下提示信息
			$trans_config = PL_Config_Numeric::get('translate', $p_config['desc']);
			$ret['payment_config'][$index]['desc'] = $trans_config['zh_CN'];
			if($p_config['cash'] > $max_product_config['cash']){
				$max_product_config = $p_config;
				$product_config_list [] = $p_config;
			}
		}
	
		$ret['payment_config'] = array();
		$max_product = 1000;
		if($max_product_config['cash'] > 0){
			$custom_config = array(
					'type'=> 'define',
					'gt'=> $max_product,
					'inputdesc'=>'首次充值翻倍额度以游戏内最高可充值额度为准',
					//'gemcalc'=>"Math.ceil(cash*10 + cash*{$max_product_config['gemaward']}/{$max_product_config['cash']})",
					'gemcalc'=>"Math.ceil(cash*10 + cash*1300/998)",
					'product_id'=>'custom',
			);
			$ret['payment_config'][] = $custom_config;
		}
	
		foreach ($list as $k => $v){
			//if($v['gemaward'] > 0 && !isset($v['hide'])){
			if(isset($v['forweb'])){
				$ret['payment_config'][] = $v;
			}
        }
		echo json_encode($ret);
	}

    /**
     * 导出CDKey活动csv
     */
    function actionExportCdkeyActCsv(){
        
         //取出所有参数
        $ymd = $_REQUEST['ymd'];
        
        if(empty($ymd)){
            
            $ymd = date("Ymd");
        }

        model_Award::exportCSVByCdKeyAct(strtotime($ymd));
        
    }

    function actionExportPhoneCsv(){
        
        model_Brush::exportPhoneCSV();
        
    }
    
    function actionExportCdkeyLibaoCsv(){
        
        $result_list = $_REQUEST['result_list'];

        model_Award::exportCdkeyLibaoCSV($result_list);
        
    }
    
    function actionSendMSGForCurl(){
        
        $ymd = trim($_REQUEST['ymd']);
        
        if(empty($ymd)){
            $ymd = date("Ymd");
        }
        $chuangshu = trim($_REQUEST['chuangshu']);
        
        $ret = model_Award::sendCdkeyActMsg(strtotime($ymd),$chuangshu);
        
        if($ret){
            echo P_PLATFORM.":ok!";
        }else{
            
            echo P_PLATFORM.":fail!";
        }
        
    }
    
    function actionsendallCdkeyActMsg(){
        
        $ymd = trim($_REQUEST['ymd']);
        $chuangshu = trim($_REQUEST['chuangshu']);

        
        $urlconfigs = dzm_base::load_config('system','platform_admin_url');
        
        foreach($urlconfigs as $plat => $url){
            
            if($plat == "dev"){
                
                $url .= "?mod=jsapi&action=SendMSGForCurl&ver=wyc_act&platform=dev&config=dev&ymd=".$ymd."&chuangshu=".$chuangshu;
                
            }else{
                
                $url .= "?mod=jsapi&action=SendMSGForCurl&ymd=".$ymd."&chuangshu=".$chuangshu;
                
            }
            //post 请求开始 
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url); //需要获取的URL地址
            //curl_setopt($ch, CURLOPT_POST, 1); //会发送一个常规的POST请求.
            //curl_setopt($ch, CURLOPT_POSTFIELDS, $json); //post参数
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //让curl_exec返回结果数据，而不是直接输出
            curl_setopt($ch, CURLOPT_HEADER, false); //不输出文件头
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120); //timeout on connect，单位秒,也可以设置毫秒，用CURLOPT_CONNECTTIMEOUT参数
            curl_setopt($ch, CURLOPT_TIMEOUT, 60); //timeout on response，单位秒，也可以设置毫秒，用CURLOPT_TIMEOUT_MS参数
            $result = curl_exec($ch);
            echo $result; //如果不出现timeout,服务器成功返回，则返回正常结果，格式是一个json串。
            curl_close($ch);//关闭会话，释放资源
            
        }
        
        
    }

    /**
     * 获取用户VIP
     */
    public function actionGetVIP(){
        $sign = self::getParam('sign');
        $email = self::getParam('username');
        $params = $_POST;
        unset($params['sign']);
        $c_sign = self::sign($params,'ares');
        if(empty($email) or empty($sign) or $sign != $c_sign){
            self::showMsg(array('s'=>StatusCode::invalid_siginature));
        }
        $um = model_LoginUser::searchUniq('email', $email);
        if(empty($um)){
            self::showMsg(array('s' => StatusCode::auth_failed, 'msg' => 'username not found!'));
        }
        //循环玩家所有分区取VIP 找到vip13就停止返回
        $uid = $um['_id'];
        $vip = 1;
        foreach((array)$um['last_login_secs'] as $sec_k=>$in_time){
            $player = new model_Player($uid,$sec_k);
            $udata = $player->getFields(array('vip.lvl'));
            //echo $sec_k."-vip:".$vip['vip']['lvl']."<br>";
            $v = $udata['vip']['lvl'];
            if($v >= 13){
                $vip = $v;
                break;
            }elseif($v > $vip){
                $vip = $v;
            }
        }
        $result = array('pid'=>$um['pid'],'vip'=>$vip);
        self::showMsg(array('s'=>'ok','data'=>$result));
    }

    /**
     * 纵乐验证appstore账号密码
     * http://admin.appstore.dzm.playcrab.com/vms/index.php?mod=jsapi&action=CheckUser&username=1&password=1&sign=0f9018eac682fe9422910d97349932f3
     */
    public function actionCheckUser(){
        $sign = self::getParam('sign');
        $email = self::getParam('username');
        $params = $_POST;
        //$params = array('username'=>$_GET['username'],'password'=>$_GET['password']);
        //print_r($params);
        unset($params['sign']);
        $c_sign = self::sign($params,'ares');
        if(empty($email) or empty($sign) or $sign != $c_sign){
            self::showMsg(array('s'=>StatusCode::invalid_siginature));
        }
        $um = model_LoginUser::searchUniq('email', $email);
        if (!empty($um)) {
            $pass = self::getParam('password');
            if (!$um->checkPass($pass)) {
                self::showMsg(array('s' => StatusCode::auth_failed, 'msg' => 'password error!'));
            }
        }else{
            self::showMsg(array('s' => StatusCode::auth_failed, 'msg' => 'password or username error!'));
        }
        //循环玩家所有分区取VIP 找到vip13就停止返回
        $uid = $um['_id'];
        $vip = 1;
        foreach((array)$um['last_login_secs'] as $sec_k=>$in_time){
            $player = new model_Player($uid,$sec_k);
            $udata = $player->getFields(array('vip.lvl'));
            //echo $sec_k."-vip:".$vip['vip']['lvl']."<br>";
            $v = $udata['vip']['lvl'];
            if($v >= 13){
                $vip = $v;
                break;
            }elseif($v > $vip){
                $vip = $v;
            }
        }
        $result = array('pid'=>$um['pid'],'vip'=>$vip);
        self::showMsg(array('s'=>'ok','data'=>$result));
    }

    static function showMsg($msg){
        die(json_encode($msg));
    }

    static function sign($param,  $salt = 'powerbybase'){   
        ksort($param);
        //echo "----".http_build_query($param) . $salt."---";
        $sign = md5(http_build_query($param) . $salt);
        return $sign;
    } 

}

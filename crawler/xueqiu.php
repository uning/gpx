<?php
/**
 * 获取雪球k线数据
 *
 * todo: 获取更多数据
 *
 */

class Crawler_Xueqiu{

    static $cookies;
    static function  initCookies(){
        if(self::$cookies)
            return;
        $cfile = __DIR__.'/xueqiu.cookies';
        self::$cookies = file_get_contents($cfile);




    }


    static public function curlget($url){

        $headers = array(//'Connection: keep-alive',
            'Cache-Control: max-age=0',
            'Upgrade-Insecure-Requests: 0',
            'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language: zh-CN,zh;q=0.8,en;q=0.6,zh-TW;q=0.4',
            self::$cookies,
            //'Cookie: Hm_lvt_17fe7dbfb7c6403f008d815a35234de4=1463109988; s=7c12uwuni3; xq_a_token=d9095b17f7088dbc564055fcae89165b97ba4b46; xqat=d9095b17f7088dbc564055fcae89165b97ba4b46; xq_r_token=03b0332c53fe99c68f015640ebdaf8eadf21ddae; xq_is_login=1; u=7437463599; xq_token_expire=Mon%20Sep%2005%202016%2009%3A36%3A36%20GMT%2B0800%20(CST); bid=c12aeb0acdf4693cc89242dcd4b8a194_irpnm60y; snbim_minify=true; __utma=1.50337940.1470879396.1470879396.1470884275.2; __utmc=1; __utmz=1.1470879396.1.1.utmcsr=(direct)|utmccn=(direct)|utmcmd=(none); Hm_lvt_1db88642e346389874251b5a1eded6e3=1468807162,1470275641,1470373131; Hm_lpvt_1db88642e346389874251b5a1eded6e3=14708'
        );

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书和hosts
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $data = curl_exec($ch);
        if (curl_errno($ch)) {
            echo "curl $url Error: " . curl_error($ch);
        }
        curl_close($ch);
        return $data;

    }
    /**
     *
     * 获取股票日K线信息，从雪球
     * 返回如下json php 数组,默认取一天的
     *
     * "volume":3.1658434E7,"open":26.03,"high":26.84,"close":26.36,"low":25.89,"chg":0.39,"percent":1.5,"turnrate":3.47,"ma5":25.96,"ma10":24.77,"ma20":24.2,"ma30":23.36,"dif":1.06,"dea":0.86,"macd":0.4,"time":"Mon Mar 23 00:00:00 +0800 2015"}
     *
     *
     */
    static function getDayK($symbol,$from_date,$to_date = '',$cacheout = -1){
        static::initCookies();
        //注意begin，end 是毫秒单位的
        //xueqiu.com/stock/forchartk/stocklist.json?symbol=SZ002465&period=1day&type=before&begin=1427018383612&end=1427098383612
        $ps = strtotime($from_date).'000';
        if($to_date < $from_date){
            $to_date = $from_date;
        }
        $pe = strtotime($to_date).'999';
        $url = "https://xueqiu.com/stock/forchartk/stocklist.json?symbol=$symbol&period=1day&type=before&begin=$ps&end=$pe";
        $cachefile = CACHE_DIR.md5($url);
        if($cacheout != 0 ){
            if(file_exists($cachefile)){
                $mtime =  filemtime($cachefile);
                $now = time();
                if($mtime > $now - $cacheout || $cacheout < 0 ){
                    $content =  file_get_contents($cachefile);
                }
            }
        }
        if(!$content){
            $content = self::curlget($url);
        }
        if($content){
            $arr = json_decode($content,true);
            if($arr['success'] == true ){
                file_put_contents($cachefile,$content);
                return $arr['chartlist'];
            }
        }

    }

    /**
 *
     *
     *
     */
    static function getGupiaoDay($zqdm,$date){
        $id = $zqdm."_$date";
        $mc = DbConfig::getMongodb('dayklineinfo');
        $cond['$or'][]=array('_id'=>$id);
        $cond['$or'][]=array('zqdm'=>$zqdm);
        $ret = $mc->findOne($cond);
        if($ret && $ret['day'] > $date)//更新日k线数据已经有了,不再重新抓取
            return $ret;
        $pre = App::zqdmPre($zqdm);
        if(!$pre){
            echo "notpre $zqdm no  marcket pre get\n";
            return $ret;
        }
        //
        $todate = date('Ymd');
        $kline = static::getDayK($pre.$zqdm,$date,$todate);
        $ret = array();
        //返回的是第一条存在的k线信息,有可能日期不准
        if($kline){
            foreach($kline as $info){
                $day = date('Ymd',strtotime($info['time']));
                $id = $zqdm."_$day";
                $info['day'] = $day;
                $info['zqdm'] = $zqdm;
                if(!$ret)
                    $ret = $info;
                $mc->findAndModify(array('_id'=>$id),array('$set'=>$info),array(),array('upsert'=>true));
            }
        }
        return $ret;
    }

    /**
     * 全部获取一遍后好计算
     */
    static public function getXueDayKSave(){
        $zqrs = DbConfig::getParam('zqrs',PSPACE);

        foreach($zqrs as $k=>$v){
            $info = null;
            $cDate = $v['cdate'];
            if($cDate && $v[8] != 0)//去除新股申购
                $info=Crawler_Xueqiu::getGupiaoDay($k,$cDate); //not coment after all callc
            if(!$info){
                echo "fail==getGupiaoDay $cDate $k ".$v[3]."\n";
            }else{
                echo "succ==getGupiaoDay $cDate $k ".$v[3]." close:{$info['close']}\n";
}

        }
    }


}

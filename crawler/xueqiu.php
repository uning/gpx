<?php
/**
 * 获取雪球k线数据
 *
 */

class Crawler_Xueqiu{

    static $cookies;
    static function  initCookies(){
        if($cookies)
            return;
        $cfile = __DIR__.'/xueqiu.cookies';
        self::$cookies = file_get_contents($cfile);

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
            $params = array(
                'http' => array
                (
                    'method' => 'GET',
                    'header'=>"Cache-Control: max-age=0\r\nAccept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8\r\nAccept-Language: zh-CN,zh;q=0.8,en;q=0.6,zh-TW;q=0.4"
                    ."\r\nCookie:".self::$cookies."\r\n",
                    'user_agent'=>'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/40.0.2214.94 Safari/537.36',
                )
            );
            $ctx = stream_context_create($params);
            $fp = fopen($url, 'r', false, $ctx);
            if($fp){
                $content = stream_get_contents($fp);
                //echo $content;
                fclose($fp);
            }else{
                echo "http get failed:".$url."\n";
            }
        }
        if($content){
            $arr = json_decode($content,true);
            if($arr['success'] == true ){
                file_put_contents($cachefile,$content);
                return $arr['chartlist'];
            }
        }

    }


    static function getGupiaoDay($zqdm,$date){
        $id = $zqdm."_$date";
        $mc = DbConfig::getMongodb('dayklineinfo');
        if($ret = $mc->findOne(array('_id'=>$id)))
            return $ret;
        $pre = App::zqdmPre($zqdm);
        if(!$pre){
            echo $error = "$zqdm no  marcket pre get\n";
            return $ret;
        }
        //
        $todate = date('Ymd');
        $kline = static::getDayK($pre.$zqdm,$date,$todate);
        $ret = array();
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
}

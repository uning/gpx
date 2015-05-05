<?php
/**
 * 获取雪球k线数据
 *
 */

class Crawler_Xueqiu{

    //获取登陆后的雪球cookie
    const COOKIES='Cookie: xq_a_token=ded657d12d5f42cbf06142b4b611dca7792f6d0f; xqat=ded657d12d5f42cbf06142b4b611dca7792f6d0f; xq_r_token=6b2413cac9c989c53db3788db47eeba3c63e7e28; xq_token_expire=Fri%20May%2029%202015%2009%3A12%3A33%20GMT%2B0800%20(CST); xq_is_login=1; bid=c12aeb0acdf4693cc89242dcd4b8a194_i99704rx; snbim_minify=false; __utma=1.1280967048.1430527005.1430702000.1430788969.6; __utmb=1.36.9.1430795490450; __utmc=1; __utmz=1.1430527005.1.1.utmcsr=(direct)|utmccn=(direct)|utmcmd=(none); Hm_lvt_1db88642e346389874251b5a1eded6e3=1428840796,1429577930,1430451578,1430701852; Hm_lpvt_1db88642e346389874251b5a1eded6e3=1430797478';

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
        //注意begin，end 是毫秒单位的
        //xueqiu.com/stock/forchartk/stocklist.json?symbol=SZ002465&period=1day&type=before&begin=1427018383612&end=1427098383612
        $ps = strtotime($from_date).'000';
        if($to_date < $from_date){
            $to_date = $from_date;
        }
        $pe = strtotime($to_date).'999';
        $url = "http://xueqiu.com/stock/forchartk/stocklist.json?symbol=$symbol&period=1day&type=before&begin=$ps&end=$pe"; 
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
                    ."\r\n".self::COOKIES."\r\n",
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
        $arr = json_decode($content,true);
        if($arr['success'] == true ){
            file_put_contents($cachefile,$content);
            return $arr['chartlist'];
        }
    }


    static function getGupiaoDay($zqdm,$date){
        $id = $zqdm."_$date";
        $mc = DbConfig::getMongodb('dayklineinfo');
        if($ret = $mc->findOne(array('_id'=>$id)))
            return $ret;
        $pre = App::zqdmPre($zqdm);
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




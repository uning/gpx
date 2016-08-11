<?php
/**
 *
 * wmcloud.com 数据api接口
 *
 * @author Me
 * @version $Id$
 * @copyright Me, 18 September, 2015
 * @package default
 */



class Crawler_WmCloud{

    CONST API_TOKEN='d92d8fc9bcb4d11862864510edad7273b0c4e26018f775a2aae1d0b34f94eda2';
    CONST API_POINT='https://api.wmcloud.com:443/data/v1';
    /**
     * 获取数据
     *
     *
     * @return curlcontent
     * @author
     */
    protected function curl($url){
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        $headers = array("Authorization: Bearer ".self::API_TOKEN);
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
     * @param api
     * @param params 字符串或数组
     *
     * @return void
     * @author Tingkun
     */
    public function api($api,$params=''){
        if(is_array($params))
            $url = self::API_POINT.$api.'?'.http_build_query($params);
        else
            $url = self::API_POINT.$api.$params;
        $data = $this->curl($url);
        if($data){
            if(strstr($url,'.csv')){
                $arr = explode("\n",$data);
                foreach($arr as $l){
                     $csv[] = str_getcsv($l);
                }
                return $csv;
            }elseif(strstr($url,'.json')){
                $json =  json_decode($data,true);
                return $json;
            }
        }
        return $data;
    }


} // END class



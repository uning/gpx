<?php
//简单抓取
function url_content($url,$cacheout = 86400){
    $cachefile = CACHE_DIR.md5($url);
    if($cacheout != 0 ){
        if(file_exists($cachefile)){
            $mtime =  filemtime($cachefile);
            $now = time();
            if($mtime > $now - $cacheout || $cacheout < 0 ){
                return file_get_contents($cachefile);
            }
        }
    }
    $content = file_get_contents($url);
    if($content){
        file_put_contents($cachefile,$content);
    }
    return $content;
}


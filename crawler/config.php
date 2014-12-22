<?php
define('CACHE_TIMEOUT',86400);
define('CACHE_DIR',__DIR__.'/cache/');
//简单抓取
function url_content($url,$usecache = true){
    $cachefile = CACHE_DIR.md5($url);
    if($usecache){
        if(file_exists($cachefile)){
            $mtime =  filemtime($cachefile);
            $now = time();
            if($mtime > $now - CACHE_TIMEOUT){
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


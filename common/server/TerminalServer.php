<?php
/**
 * Developed by W.叶
 * Tue 23 Apr 2013 03:51:18 PM CST - created
 */

require_once __DIR__."/../model/QueueService.php";
require_once __DIR__."/../command/NoticePushCommand.php";

ini_set('memory_limit','2G');

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

class TerminalServer{
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
            echo "action[$action] not defined in TerminalServer\n";
            exit(1);
        }
    }
    public function action_comb0423_buchang(){
        echo "buchang";
    }
}


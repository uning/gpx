<?php

require_once __DIR__.'/base.php';
require_once __DIR__.'/App.php';

$mod = 'index';
if(isset($_REQUEST['mod'])){
	$mod = $_REQUEST['mod'];
}
App::run($mod);
<?php
/**
 * common/server/DumpServer.php
 *
 * Developed by Tingkun <tingkun@playcrab.com>
 * Copyright (c) 2012 Playcrab Corp.
 *
 * Changelog:
 * 2012-06-06 - created
 *
 */


class DumpServer extends PL_Server_Page{
	public function __construct(){
	}	
	
	function run(){

		echo "<pre>";
		echo "ROOT=".ROOT."\n";
		echo "NUMERIC_CONFIG=".NUMERIC_CONFIG."\n";
		echo "app_config(app_config_path)=".app_config('app_config_path')."\n";
        echo 'P_VERSION='.app_config('version')."\n";

		outdebug();
	}

}

<?php
/**
 * common/server/FileServer.php
 *
 * Developed by Tingkun <tingkun@playcrab.com>
 * Copyright (c) 2012 Playcrab Corp.
 *
 * Changelog:
 * 2012-07-14 - created
 *
 */

class FileServer extends PL_Server {
	public function run(){

		$file = $this->getParam('file');
		$rfile = ROOT.$file;
		if(file_exists($rfile))
			include $rfile;
		else{
			die("not find $rfile");
		}
	}
}

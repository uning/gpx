<?php
/**
 * IndexServer.php
 *
 * Developed by Tingkun <tingkun@playcrab.com>
 * Copyright (c) 2011 Playcrab Corp.
 * Licensed under terms of GNU General Public License.
 * All rights reserved.
 *
 * Changelog:
 * 2011-06-11 - created
 *
 */

class IndexServer extends PL_Server_Page{
	public function __construct(){
	 $this->viewRoot =  COMM_ROOT.'/view/';
	}	
	
	function actionIndex(){
		$this->bodyView = 'index.body.php';
		$this->tailerView = 'index.tailer.php';
		parent::actionIndex();
	}

	function actionFpass(){
		if($s = PL_Session::canStart()){
			die('auth');
		}
		$now = $_SERVER['REQUEST_TIME'];
		if($gtime = $s->getGtime() < $now - 5*36000 ){
			die('链接已经过期,重新找回密码');
		}
		$lum = new model_LoginUser($s->getid());
		$d = $lum->get();
		if($_POST['npass']){
			
		}

		include  $this->viewRoot.'findpasspage.php';
		
	}

}

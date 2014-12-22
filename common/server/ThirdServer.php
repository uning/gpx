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

class ThirdServer extends PL_Server_Page{
	public function __construct(){
	 $this->viewRoot = ROOT.'/platforms/'.P_PLATFORM.'/view/';
	}	
	
	function actionIndex(){
		$pid = $_REQUEST['pid'];
		if(!$pid){
			$pid = $_COOKIE['pid'];
		}
		if(!$pid){
			$pid = 'wplayers'.mt_rand(1,100);
		}
		setcookie('pid',$pid);
		$um = model_LoginUser::genbypid($pid,$isnew);
		$u = $um->_id;
		$sess = PL_Session::start($u,'s1');
		$_SESSION['isNew'] = 1;
		$cid = $sess->getCid($u);
		$this->bodyView = $this->viewRoot.'index.body.php';
		$this->tailerView =  $this->viewRoot.'index.tailer.php';
		include  $this->viewRoot.'layout.php';
	}
	function actionSl(){
		$pid = $_REQUEST['pid'];
		if(!$pid){
			$pid = $_COOKIE['pid'];
		}
		if(!$pid){
			$pid = 'wplayers'.mt_rand(1,100);
		}
		setcookie('pid',$pid);
		$um = model_LoginUser::genbypid($pid,$isnew);
		$this->tailerView =  $this->viewRoot.'list.php';
		include  $this->viewRoot.'layout.php';


	}

	/*
	function run(){
		$view = $this->viewRoot.'index.php';
		include($view);
	}
	*/

}

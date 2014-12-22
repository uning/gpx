<?php
class IndexServer  extends PL_Server_Page{
	
	public function __construct(){
		$this->viewRoot = ROOT.'/common/view';
        require(__DIR__.'/common/view/table.php');
        //PL_Session::start();
    }

	function handle(&$req = NULL){
        $action = $this->getParam('action','list');
		$coll = $this->getParam('coll','jgd');
        $show_config = include(ROOT.'/data/dataconf.php');
        $this->bodyView = $this->viewRoot."/$action.php";
        if(file_exists($this->bodyView)){
            if($_REQUEST['__nl']){
                include $this->bodyView;
                return;
            }
        }
        require $this->viewRoot."/layout.php";
	}


}

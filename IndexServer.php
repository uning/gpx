<?php
class IndexServer  extends PL_Server_Page{

	public function __construct(){
		$this->viewRoot = ROOT.'/common/view';
        //require(__DIR__.'/common/view/table.php');
        //PL_Session::start();
    }

	function handle(&$req = NULL){
        $action = $this->getParam('action');
        if($action == ''){
            require $this->viewRoot."/frame.php";
            return;
        }
		$coll = $this->getParam('coll','jgd');
        $this->bodyView = $this->viewRoot."/$action.php";
        if(file_exists($this->bodyView)){
            if($_REQUEST['__nl']){
                include $this->bodyView;
                return;
            }
        }
        require $this->viewRoot."/layout.php";
	}


    /**
     * 处理jqGrid请求数据参数的函数
     * sidx -- 排序字段
     * psidx -- 预设排序字段
     * filters -- 查询条件
     * pfilters -- 查询条件
     *
     * 应用返回
     */
    static function processGridAjaxParams(&$sort,&$cond,&$limit,&$skip,&$filterstr = null,&$sidx = null){
        $condtpls = array(
            'jgdyz'=>array('$or'=>array( array(1=>'银行转证券'),array(1=>'证券转银行')))
        );

        $multiSort = static::getParam('multiSort',true);
        $psidx = static::getParam('psidx');//this param is overwrite by get data,but initGrid not suupport multiSort
        $sord = static::getParam('sord','desc');
        $sidx = static::getParam('sidx');
        if(!$sidx && $sidx !== '0'){
            if($psidx)
                $sidx = $psidx;
        }else{
            $sidx .=' '.$sord;
        }
        preg_match_all('/[\s]*([\w]+)[\s]+([\w]+)[\s]*/',$sidx,$mout);
        foreach((array)$mout[1] as $k=>$v){
            if($v == 'asc' || $v == 'desc')
                continue;
            if($mout[2][$k] == 'asc'){
                $sort[$v] = 1;
            }else
                $sort[$v] = -1;
        }


        $condtpl =static::getParam('condtpl');
        if($condtpls[$condtpl])
            $cond = $condtpls[$condtpl];
        else{


        //process query
        //todo procecess all ops
        $optomon = array('le'=>'$lte','eq'=>'$eq','lt'=>'$lt','gt'=>'$gt','ge'=>'$gte','ne'=>'$ne');
        $filterstr =static::getParam('filters');
        $pfilterstr =static::getParam('pfilters');
        if(!$filterstr){
            $filterstr = $pfilterstr;
        }
        if($filterstr){
            $filters = json_decode($filterstr,true);
            if($filters['groupOp'] == 'AND'){
                foreach($filters['rules'] as $ru){
                    $op = $ru['op'];
                    //if(in_array($op,array ('lte','eq','ne'))
                    $dbop = $optomon[$op];
                    if($dbop){
                        $cond[$ru['field']][$dbop] = $ru['data'];
                    }else if($op == 'bw'){
                        $cond[$ru['field']] = new MongoRegex("/^{$ru['data']}/");
                    }
                }
            }elseif($filters['groupOp'] == 'OR'){
                foreach($filters['rules'] as $ru){
                    $op = $ru['op'];
                    //if(in_array($op,array ('lte','eq','ne'))
                    $dbop = $optomon[$op];
                    if($dbop){
                        $cond['$or'][] = array($ru['field'][$dbop] => $ru['data']);
                    }else if($op == 'bw'){
                        $cond['$or'][] = array($ru['field'] => new MongoRegex("/^{$ru['data']}/"));
                    }
                }
            }
        }
        }
        $limit = static::getParam('rows',20);
        $page = static::getParam('page',0);
        $skip = $page < 1 ? 0 : ($page - 1)*$limit;

    }

}

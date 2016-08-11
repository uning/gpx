<?php
$leftmenu = array(
    array('id'=>'chich','cntext'=>'持仓','pid'=>'root','nurl'=>url(array('action'=>'view','coll'=>'calcc','lastest'=>'lastest'))),
    array('id'=>'chich_zuixin','cntext'=>'最新持仓','pid'=>'chich','url'=>url(array('action'=>'view','coll'=>'calcc','lastest'=>'lastest'))),
    array('id'=>'chich_zuixin0','cntext'=>'最新持仓(calcc含0)','pid'=>'chich','url'=>url(array('action'=>'view','include0'=>1,'coll'=>'calcc','lastest'=>'lastest'))),
   array('id'=>'chich_zuixin0','cntext'=>'最新持仓(jgdc含0)','pid'=>'chich','url'=>url(array('action'=>'view','include0'=>1,'coll'=>'jgdc'))),
    array('id'=>'chich_zuixin','cntext'=>'汇总多日','pid'=>'chich','url'=>url(array('action'=>'view','header'=>'theader','coll'=>'calcc'))),
    array('id'=>'chich_zuixin','cntext'=>'股票多日','pid'=>'chich','url'=>url(array('action'=>'view','coll'=>'calcc'))),
    array('id'=>'chich_jisuan','cntext'=>'计算','pid'=>'chich','url'=>url(array('action'=>'calcc','coll'=>'calcc','lastest'=>'lastest'))),
    array('id'=>'hg_huoqu','cntext'=>'获取股票价格','pid'=>'chich','url'=>url(array('action'=>'gethq','coll'=>'calcc','lastest'=>'lastest'))),

    /*太慢，先zhu'shi注释掉*/
    //array('id'=>'hg_huoqu','cntext'=>'获取股票价格xq','pid'=>'chich','url'=>url(array('action'=>'gethq_xq','coll'=>'calcc','lastest'=>'lastest'))),

    /*没更新数据了，也不准*/
    //array('id'=>'zjgf_chich','cntext'=>'券商合并持仓','pid'=>'chich','url'=>url(array('action'=>'view','coll'=>'zjgf','header'=>'theader','psidx'=>'date desc') )),
    //array('id'=>'zjgf_chichmx','cntext'=>'券商持仓明细','pid'=>'chich','url'=>url(array('action'=>'view','coll'=>'zjgf','header'=>'header') )),





    array('id'=>'jgd','cntext'=>'交割单','pid'=>'root','nurl'=>url(array('action'=>'view','coll'=>'jgd'))),
    array('id'=>'jgd_ll','cntext'=>'浏览','pid'=>'jgd','url'=>url(array('action'=>'view','coll'=>'jgd'))),
    array('id'=>'jgd_bz','cntext'=>'标注记录','pid'=>'jgd','url'=>url(array('action'=>'view','coll'=>'jgd','bz'=>1))),
    array('id'=>'jgd_chich','cntext'=>'s持仓','desc'=>'服务器计算的持仓','pid'=>'jgd','url'=>url(array('action'=>'view','coll'=>'jgd','chich'=>1,'unidf'=>2,'numf'=>6,'psidx'=>'0 desc'))),
    array('id'=>'jgd_cz','cntext'=>'s操作汇总','pid'=>'jgd','url'=>url(array('action'=>'view','coll'=>'jgd','chich'=>1,'unidf'=>1,'psidx'=>'0 desc,16 desc'))),
    array('id'=>'jgd_chich0','cntext'=>'s持仓(含0)','pid'=>'jgd','url'=>url(array('action'=>'view','coll'=>'jgd','chich'=>1,'unidf'=>2,'psidx'=>'0 desc,16 desc'))),
    array('id'=>'jgd_yz','cntext'=>'银证记录','pid'=>'jgd','url'=>url(array('action'=>'view','coll'=>'jgd','condtpl'=>'jgdyz'))),
    array('id'=>'jgd_xgrz','cntext'=>'新股入账','pid'=>'jgd','url'=>url(array('action'=>'view','coll'=>'jgd','condtpl'=>'jgdxgrz'))),
    array('id'=>'jgd_sgzq','cntext'=>'申购中签','pid'=>'jgd','url'=>url(array('action'=>'view','coll'=>'jgd','condtpl'=>'jgdsgzq'))),

    array('id'=>'jgd_gphz','cntext'=>'按股票汇总','pid'=>'jgd','url'=>url(array('action'=>'view','coll'=>'jgd','groups'=>'2'))),
    array('id'=>'jgd_gpmzhz','cntext'=>'按股票买卖汇总','pid'=>'jgd','url'=>url(array('action'=>'view','coll'=>'jgd','groups'=>'2,1'))),
    array('id'=>'jgd_rqhz','cntext'=>'按日期汇总','pid'=>'jgd','url'=>url(array('action'=>'view','coll'=>'jgd','groups'=>'0'))),


    array('id'=>'zjls','cntext'=>'资金流水','pid'=>'root','url'=>url(array('action'=>'view','coll'=>'zjls'))),
    array('id'=>'zjls_ll','cntext'=>'浏览','pid'=>'zjls','url'=>url(array('action'=>'view','coll'=>'zjls'))),
    array('id'=>'zjls_bz','cntext'=>'原因记录','pid'=>'zjls','url'=>url(array('action'=>'view','coll'=>'zjls','bz'=>1))),

    array('id'=>'lscj','cntext'=>'历史成交','pid'=>'root'),
    array('id'=>'lscj_bz','cntext'=>'原因记录','pid'=>'lscj','url'=>url(array('action'=>'view','coll'=>'lscj','bz'=>1))),
    array('id'=>'lscj_ll','cntext'=>'浏览','pid'=>'lscj','url'=>url(array('action'=>'view','coll'=>'lscj'))),


    //array('id'=>'import','cntext'=>'导入','pid'=>'root'),
    array('id'=>'import_jl','cntext'=>'导入','pid'=>'root','url'=>url(array('action'=>'import'))),
    //array('id'=>'import_t','cntext'=>'汇总zjgf','pid'=>'import','url'=>url(array('action'=>'importt'))),
);

?>
<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title> gupiaox </title>

<script type="text/javascript">
	var ctx = "";//url pre
	var console = console||{};
	console.info = console.info||function(){};
<?php
 echo 'var leftmenu = '.json_encode($leftmenu,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE).';';
?>

</script>
<script src="bower_components/jquery/dist/jquery.min.js"></script>
<script type="text/javascript" src="asset/js/lib/panListMenu/listMenu.js"></script>

<link rel="stylesheet" href="asset/js/lib/panListMenu/listMenu.css" />
<link rel="icon" type="image/x-icon" href="asset/img/fav.ico">
<link rel="apple-touch-icon" type="image/x-icon" href="asset/img/fav.ico">
<style>
    body{
        font-family: 'Microsoft Yahei';
    }
    .left-pane{
        position:absolute;
        left:0px;
        top:0px;
        bottom: 0px;
        width: 200px;
        border-right: 1px solid #85B5D9;
    }
    .right-pane{
        position: absolute;
        right: 0px;
        top: 0px;
        bottom: 0px;
        left:202px;
        border-left: 1px solid #85B5D9;
    }
    .left-pane-title{
        position: absolute;
        left:0px;
        right: 0px;
        top: 0px;
        height: 30px;
        text-align: center;
        line-height: 30px;
        background-color: #85B5D9;
    }
    .left-pane-list{
        position: absolute;
        left:0px;
        right: 0px;
        bottom: 0px;
        top: 32px;
    }
    .right-pane-title{
        position: absolute;
        left:0px;
        top: 0px;
        right: 30px;
        height: 30px;
        background-color: #85B5D9;
    }
    .right-pane-content{
        position: absolute;
        left:0px;
        right: 0px;
        bottom: 0px;
        top: 32px;
    }
    .right-pane-content iframe{
        width: 100%;
        height: 100%;
        position: relative;
    }
    .title-item{
        position: relative;
        padding: 0 12px;
        float: left;
        height: 30px;
        text-align: center;
        line-height: 30px;
        background-color: E7F3FD;
        font-size: 12px;
        color: #666;
        cursor: pointer;
    }
    .selected{
        color: orange;
        background-color: #fff;
        cursor: default;
    }
    .item-close{
        position: absolute;
        right: 0px;
        top: 0px;
        width: 30px;
        height: 30px;
        background-color: #85B5D9;
        cursor: pointer;
    }

</style>
    </head>
    <body>
        <div class="left-pane" style=" ">
            <div class="left-pane-title" style=" ">
                功能项
            </div>

            <div id="listMenu" class="left-pane-list" ></div>
        </div>
        <div class="right-pane" style=" ">
            <div class="right-pane-title" style=" ">
            </div>
            <div class="right-pane-content" style=" ">
            </div>
            <div class="item-close" style=" ">
                <img src="asset/img/close.png" alt="" style="width: 100%;height: 100%;" />
            </div>
        </div>

        <div class="" style="text-align: center;margin-top: 16px;font-size: 12px;line-height: 22px;">
            <hr />
        </div>
    </body>
    <script>
        <?php include 'index.js';?>
    </script>
</html>

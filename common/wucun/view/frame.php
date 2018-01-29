<?php
$gridconfig = DbConfig::getParam('gridconfig', 'grid');

$lf = array();
foreach ($gridconfig as $k => $v) {
    $lf[] = array('id'=>$k,'cntext'=>$v['name'],'pid'=>'root');
    $lf[] = array('id'=>$k.'_view','cntext'=>'查看','pid'=>$k,'url'=>url(array('action'=>'view','coll'=>$k)));
    $headers = $v['showheaders'];
    if ($headers) {
        foreach ($headers as $kk=>$vv) {
            $lf[] = array('id'=>$k.'_view_'.$kk,'cntext'=>'查看'.$vv,'pid'=>$k,'url'=>url(array('action'=>'view','header'=>$kk,'coll'=>$k)));
        }
    }

    $lf[] = array('id'=>$k.'_import','cntext'=>'导入','pid'=>$k,'url'=>url(array('action'=>'import','coll'=>$k)));
    $lf[] = array('id'=>$k.'_add','cntext'=>'添加行','pid'=>$k,'url'=>url(array('action'=>'add','coll'=>$k)));
    $lf[] = array('id'=>$k.'_del','cntext'=>'标记删除','pid'=>$k,'url'=>url(array('action'=>'del','coll'=>$k)));
}
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
 echo 'var leftmenu = '.json_encode($lf,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE).';';
 echo 'var aa = '.json_encode($headers,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE).';';
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

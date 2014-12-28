<?php
$leftmenu = array(
    array('id'=>'jgd','cntext'=>'交割单','pid'=>'root','nurl'=>url(array('action'=>'view','coll'=>'jgd'))),
    array('id'=>'jgd_bz','cntext'=>'原因记录','pid'=>'jgd','url'=>url(array('action'=>'view','coll'=>'jgd','bz'=>1))),
    array('id'=>'jgd_chich','cntext'=>'s持仓','desc'=>'服务器计算的持仓','pid'=>'jgd','url'=>url(array('action'=>'view','coll'=>'jgd','chich'=>1,'unidf'=>2,'numf'=>6,'psidx'=>'0 desc,16 desc'))),
    array('id'=>'jgd_cz','cntext'=>'s操作汇总','pid'=>'jgd','url'=>url(array('action'=>'view','coll'=>'jgd','chich'=>1,'unidf'=>1,'psidx'=>'0 desc,16 desc'))),
    array('id'=>'jgd_chich0','cntext'=>'s持仓(含0)','pid'=>'jgd','url'=>url(array('action'=>'view','coll'=>'jgd','chich'=>1,'unidf'=>2,'psidx'=>'0 desc,16 desc'))),
    //array('id'=>'jgd_chichc','cntext'=>'c持仓','pid'=>'jgd','url'=>url(array('action'=>'view','coll'=>'jgd','groups'=>'2'))),
    array('id'=>'jgd_gphz','cntext'=>'按股票汇总','pid'=>'jgd','url'=>url(array('action'=>'view','coll'=>'jgd','groups'=>'2'))),
    array('id'=>'jgd_gpmzhz','cntext'=>'按股票买卖汇总','pid'=>'jgd','url'=>url(array('action'=>'view','coll'=>'jgd','groups'=>'2,1'))),
    array('id'=>'jgd_rqhz','cntext'=>'按日期汇总','pid'=>'jgd','url'=>url(array('action'=>'view','coll'=>'jgd','groups'=>'0'))),


    array('id'=>'zjls','cntext'=>'资金流水','pid'=>'root'),
    array('id'=>'zjls_bz','cntext'=>'原因记录','pid'=>'zjls','url'=>url(array('action'=>'view','coll'=>'zjls','bz'=>1))),

    array('id'=>'lscj','cntext'=>'历史成交','pid'=>'root'),
    array('id'=>'lscj_bz','cntext'=>'原因记录','pid'=>'lscj','url'=>url(array('action'=>'view','coll'=>'lscj','bz'=>1))),

    array('id'=>'zjgf','cntext'=>'资金股份','pid'=>'root'),
    array('id'=>'zjgf_chich','cntext'=>'持仓','pid'=>'zjgf','url'=>url(array('action'=>'view','coll'=>'zjgf','header'=>'theader') )),
    array('id'=>'zjgf_chich','cntext'=>'持仓明细','pid'=>'zjgf','url'=>url(array('action'=>'view','coll'=>'zjgf','header'=>'header','groups'=>'date') )),

    array('id'=>'import','cntext'=>'导入','pid'=>'root'),
    array('id'=>'import_jl','cntext'=>'交易记录()','pid'=>'import','url'=>url(array('action'=>'import'))),
    array('id'=>'import_t','cntext'=>'汇总zjgf','pid'=>'import','url'=>url(array('action'=>'importt'))),
);

?>
<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title> gupiaox </title>
		
<script type="text/javascript">
	var ctx = "";//url pre
	var console = console||new Object();
	console.info = console.info||function(){};
<?php
 echo 'var leftmenu = '.json_encode($leftmenu,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE).';';
?>

</script>
        <link rel="stylesheet" href="<?php $curlp = 'asset/common';echo $curlp;?>/jui/1112/jquery-ui.css">

        <script src="<?php echo $curlp;?>/jqGrid/js/jquery-1.11.0.min.js"></script>
        <script src="<?php echo $curlp;?>/jui/1112/jquery-ui.js"></script>

        <script src="<?php echo $curlp?>/jqGrid/js/i18n/grid.locale-en.js" type="text/javascript"></script>
        <script src="<?php echo $curlp?>/jqGrid/js/jquery.jqGrid.min.js" type="text/javascript"></script>
        <link rel="stylesheet" type="text/css" media="screen" href="<?php echo $curlp?>/css/ui.jqgrid.css" />


        <link rel="stylesheet" href="<?php echo $curlp?>/panListMenu/listMenu.css" />
        <script type="text/javascript" src="<?php echo $curlp?>/panListMenu/listMenu.js"></script>
		<link rel="stylesheet" href="asset/index.css" />
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
				<div freeze=true class="title-item selected" style=" " src="?action=view" >gpx</div>
			</div>
			<div class="right-pane-content" style=" ">
				<iframe src="?action=view" frameborder="0"></iframe>
			</div>
			<div class="item-close" style=" ">
				<img src="asset/close.png" alt="" style="width: 100%;height: 100%;" />
			</div>
		</div>
		
<div class="" style="text-align: center;margin-top: 16px;font-size: 12px;line-height: 22px;">
	<hr />
</div>
</body>
<script>
$(function(){

    var menudata = leftmenu || [];
    var id2menudata = {};
     menudata.forEach(function(v){
id2menudata[v.id] = v;
 
});
    
    function pageInit(){
        createList();
        addAction();
    }
    function addAction(){
        $(".item-close").click(function(){
            closeTab();
        });
        $(document).on("click",'.right-pane-title>div',function(){
            console.log('click ',$(this).attr("src"));
            selectTab($(this).attr("src"));
        })
    }
    function createList(){
        var showfield = "cntext";
        $("#listMenu").listMenu({
            parentField:"pid",
            idField:"id",
            captionField:"text",
            rootId:"root",
            multSelect: true,
            onCreateText:function(data){
                return data[showfield];
            },
            onClickItem:function(data){
                if(data.url){
                        var title = data[showfield];
                    if(data.pid != 'root'){
                        title = id2menudata[data.pid][showfield] + '>' +title;
                    }
                    addTab(title,ctx+data.url);
                }
            }
        });//初始化
        loadMenu();
    }
    function loadMenu(){

        $("#listMenu").listMenu("load",menudata);
    }

    var tabUrlList = new Object();
    var tabLength = 0;
    function addTab(title,url){
        if(tabLength>8){
            alert("请先尝试关闭一些页面，再打开新页面");
            return;
        }
        if(tabUrlList[url]){
            selectTab(url);
            return;
        }
        tabUrlList[url] = "1";
        tabLength++;
        var titleDom = $('<div class="title-item" style=" ">窗口1</div>');
        var contentDom = $('<iframe src="" frameborder="0"></iframe>');

        titleDom.attr({
            src:url,
            title:title
        }).html(title);
        contentDom.attr({
            src:url
        });
        $(".right-pane-title").append(titleDom);
        $(".right-pane-content").append(contentDom);
        selectTab(url);

    }
    function selectTab(url){
        var old = $(".right-pane-title .selected");//.removeClass("selected");
        if(old.attr('url') == url)
             return;
        old.removeClass("selected");
        $(".right-pane-title").find("[src='"+url+"']").addClass("selected");
        $(".right-pane-content iframe").hide();
        $(".right-pane-content").find("[src='"+url+"']").show();
    }
    function closeTab(){
        var selected = $(".right-pane-title .selected");
        if(selected.attr("freeze") === "true"){
            return;
        }
        var next = selected.next();
        var prev = selected.prev();
        if(next.length>0){
            tabUrlList[selected.attr("src")] = null;
            tabLength--;
            next.addClass("selected");
            $(".right-pane-content").find("[src='"+selected.attr("src")+"']").remove();
            selected.remove();
            selectTab(next.attr("src"));
        }else if(prev.length>0){
            tabUrlList[selected.attr("src")] = null;
            tabLength--;
            $(".right-pane-content").find("[src='"+selected.attr("src")+"']").remove();
            selected.remove();
            selectTab(prev.attr("src"));
        }
    }

    pageInit();
})
</script>


</html>

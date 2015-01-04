<!doctype html>
<html lang="zh-CN">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title> gupiaox </title>
        <?php if($this->headerView) require $this->headerView;$curlp = P_URLP;?>
        <script src="bower_components/requirejs/require.js" ndata-main="main.js"></script> 
        <script src="main.js"></script> 

<?php if(0){?>
        <link rel="stylesheet" href="<?php echo $curlp;?>/bootstrap/css/bootstrap.css"/>
        <link rel="stylesheet" href="<?php echo $curlp;?>/bootstrap/css/bootstrap-theme.css"/>
        <link rel="stylesheet" href="<?php echo $curlp;?>/jui/1112/jquery-ui.css"/>
        <link rel="stylesheet" href="<?php echo $curlp;?>/multiselect/css/ui.multiselect.css">

        <link rel="stylesheet" href="<?php echo $curlp?>/jqGrid/css/ui.jqgrid.css" />

        <script src="<?php echo $curlp;?>/jqGrid/js/jquery-1.11.0.min.js"></script>
        <script src="<?php echo $curlp;?>/jui/1112/jquery-ui.js"></script>
        <script src="<?php echo $curlp;?>/multiselect/js/ui.multiselect.js"></script>
        <script src="<?php echo $curlp?>/jqGrid/js/i18n/grid.locale-cn.js" type="text/javascript"></script>
        <script src="<?php echo $curlp?>/jqGrid/js/jquery.jqGrid.src.js" type="text/javascript"></script>
        <script src="<?php echo $curlp;?>/bootstrap/js/bootstrap.js"></script>
<?php }?>
        <style>
            .ui-pg-input {width: auto; padding: 0px; margin: 0px;   height: 18px !important;}
            /*select.ui-pg-selbox {width: auto; padding: 0px; margin: 0px; height: 18px}*/
        </style>

    </head>
    <body>
        <div class='container'>
            <div class="row">
                <div class="span2 bs-docs-sidebar">
                </div>

                <div class="span10">
                    <?php if($this->bodyView)include $this->bodyView;?>
                    <div id="gridarea">
                    </div>
                </div> <!-- span10 -->
            </div> <!-- row -->
        </div> <!-- container -->
    </body>
</html>
<?php if($this->tailerView) include $this->tailerView; ?>

<!doctype html>
<html lang="zh-CN">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title> gupiaox </title>
        <?php if($this->headerView) require $this->headerView;$curlp = P_URLP;?>
        <link rel="stylesheet" href="<?php echo $curlp;?>/jui/1112/jquery-ui.css">

        <script src="<?php echo $curlp;?>/jqGrid/js/jquery-1.11.0.min.js"></script>


         <script src="<?php echo $curlp;?>/jui/1112/jquery-ui.js"></script>
        
        <script src="<?php echo $curlp;?>/multiselect/js/ui.multiselect.js"></script>
        <link rel="stylesheet" href="<?php echo $curlp;?>/multiselect/css/ui.multiselect.css">

        <script src="<?php echo $curlp?>/jqGrid/js/i18n/grid.locale-en.js" type="text/javascript"></script>
        <script src="<?php echo $curlp?>/jqGrid/js/jquery.jqGrid.src.js" type="text/javascript"></script>
        <link rel="stylesheet" type="text/css" media="screen" href="<?php echo $curlp?>/css/ui.jqgrid.css" />

<script>
var DC = null;
    $(function() {
        $(document).tooltip();

/*
        $( "#tabs" ).tabs({
            active : <?php echo $active?>,
            beforeLoad: function( event, ui ) {
                ui.jqXHR.error(function() {
                    ui.panel.html(
                    "Couldn't load this tab. We'll try to fix this as soon as possible. " +
                    "If this wouldn't be a demo." );
                    });
                }
            });
*/
    });
<?php //include __DIR__.'/DC.js'?>
</script>

    </head>
    <body>
        <div class='container'>

            <div class="row">
                <div class="span2 bs-docs-sidebar">
                </div>

                <div class="span10">
                <?php if($this->bodyView)include $this->bodyView;?>
                </div> <!-- span10 -->
            </div> <!-- row -->
        </div> <!-- container -->

    </body>
</html>
<?php if($this->tailerView) include $this->tailerView; ?>

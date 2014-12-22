<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title> gupiaox </title>
        <?php if($this->headerView) require $this->headerView;$curlp = P_URLP;?>
        <link rel="stylesheet" href="<?php echo $curlp;?>/jui/1112/jquery-ui.css">

        <script src="<?php echo $curlp;?>/jqGrid/js/jquery-1.11.0.min.js"></script>

        <script src="<?php echo $curlp?>/jqGrid/js/i18n/grid.locale-en.js" type="text/javascript"></script>
        <script src="<?php echo $curlp?>/jqGrid/js/jquery.jqGrid.min.js" type="text/javascript"></script>
        <link rel="stylesheet" type="text/css" media="screen" href="<?php echo $curlp?>/css/ui.jqgrid.css" />

    </head>
    <body>

        <div class='container'>

            <div class="row">
                <div class="span2 bs-docs-sidebar">
                </div>

                <div id="tabs">
                    <ul>
                        <?php
                        foreach($show_config as $k=>$v){
                        $param = $v['param'];
                        $param['coll'] = $k;
                        $param['__nl'] = 1;
                        $css = '';
                        if($k == $coll){
                        $css = 'active';
                        }
                        echo     "<li class='$css'><a href='".url($param)."' >{$v['name']}</a></li> \n";
                        }
                        ?>
                    </ul>
                </div>
                <div class="span10">
                </div> <!-- span10 -->
            </div> <!-- row -->
        </div> <!-- container -->

    </body>
    <script src="<?php echo $curlp;?>/jui/1112/jquery-ui.js"></script>
</html>
<?php if($this->tailerView) include $this->tailerView; ?>
<script>
    /*
    $(document).ready(function(){
        //初始化日历
        var  p ={dateFormat:'yymmdd',yearRage:'2012:2013',firstDay:1};
        $( "#start" ).datepicker(p);
        $( "#end" ).datepicker(p);
        $( "#start" ).datepicker('setDate','<?php echo $dstart?>');
        $( "#end" ).datepicker('setDate','<?php echo $dstop?>');

        $('.del-alert').click(function(){
            $(this).html();
            //confirm
        });
        DT.init();
        //$('#secsel').change(function(){var loc = $(this).val();window.location = loc;})

    });
    */
    $(function() {
        $( "#tabs" ).tabs({
            beforeLoad: function( event, ui ) {
                ui.jqXHR.error(function() {
                    ui.panel.html(
                    "Couldn't load this tab. We'll try to fix this as soon as possible. " +
                    "If this wouldn't be a demo." );
                    });
                }
                });
                });

<?php include __DIR__.'/DC.js'?>
</script>


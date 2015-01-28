<!doctype html>
<html lang="zh-CN">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="icon" type="image/x-icon" href="asset/img/fav.ico">
        <title> gupiaox </title>
        <?php if($this->headerView) require $this->headerView;$curlp = P_URLP;?>
        <script src="bower_components/requirejs/require.js" ndata-main="main.js"></script> 
        <script src="main.js"></script> 

        <style>
            .ui-pg-input {width: auto; padding: 0px; margin: 0px;   height: 18px !important;}
            .cellred{ color: red;}
            .cellgreen{ color: green;}
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

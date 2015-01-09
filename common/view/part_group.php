

<div class='alert alert-info'>
<style>
    #selectable .ui-selecting { background: #FECA40; }
    #selectable .ui-selected { background: #F39814; color: white; }
    #selectable { list-style-type: none; margin: 0; padding: 0; width: 90%; }
    #selectable li { margin: 1px; padding: 1px; float:left; text-align: center; }
</style>
   <ol id="selectable">
        <?php 
        $groups = $dconf['groups'];
        $group = -1;
        foreach($groups as $k=>$v){
        $sel = 'ui-state-default';
        foreach((array)$gps as $kk=>$vv){
        //$bbb = $vv == $k;//bug php '0' == 'date' is true
        //echo " {$bbb} [$k] [$vv]\n";#print_r($gps);
        if($vv === $k){
        $sel = 'ui-selected';
        }
        }
        echo "<li ddvalue='$k' class='$sel'>$v</li>\n";
        }
        ?>
        <li ddvalue='gempty' class='ui-state-default'>清除Group</li>
        <li><button title='按住ctrl 选择多个,聚合是在浏览器做的，按回车搜索'   id="chngroup">Group By:</button></li>
    </ol>
<br/>
</div>
<hr/>


    <div>
      <h4  title='聚合是在浏览器做的，按回车搜索' >Group by: <select id="chngroup_<?php echo $coll?>">

    <option value="clear" >无Group</option>    
<?php 
    $dconf = &$show_config[$coll];
    $groups = $dconf['groups'];
    $group = -1;
    foreach($groups as $k=>$v){
        $sel = '';
        if($group == -1){
            $group = $k;
           // $sel = 'selected';
        }
      echo "<option value='$k' $sel>按$v</a></option>\n";
    }
?>
    </select></h4>


          <table id='grid_<?php echo $coll?>'></table>
          <div id='pager_<?php echo $coll?>'></div>


          </div>
<script>
<?php include __DIR__.'/view.js'

</script>



          <div>
          <table id='grid_<?php echo $coll?>'></table>
          <div id='pager_<?php echo $coll?>'></div>
          </div>
<?php
$dconf = &$show_config[$coll];
/*
$colModel = $dconf['colModel'];
foreach($colModel as &$v){
    if(!isset($v['index']))//default index
        $v['index'] = $v['name'];
    if(!isset($v['stype'] ))//off search
        $v['search'] = false;
    if(!isset($v['sorttype'] ))//off sort
        $v['sortable'] = false;
}
 */
?>
<script>
<?php include __DIR__.'/view.js'

</script>


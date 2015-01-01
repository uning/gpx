          <table class='jqgrid' id='grid_<?php echo $coll?>'></table>
          <div id='pager_<?php echo $coll?>'></div>
<script>

<?php 
echo "var COLL = '$coll';\n";
echo 'var cjqconf = '.json_encode((object)$jqconf,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE).';'."\n";
echo 'var csubConf= '.json_encode((object)$subConf,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE).';'."\n";
include __DIR__.'/view.js';
?>
</script>


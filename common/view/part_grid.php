<script>
<?php 
echo "var COLL = '$coll';\n";
echo 'var cjqconf = '.json_encode((object)$jqconf,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE).';'."\n";
echo 'var csubConf= '.json_encode((object)$subConf,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE).';'."\n";
//include __DIR__.'/view.js';
?>
var jqconf  = jqconf || {}; //for debug
var dbe = null;
var grido = null;
require(['view'],function(v){
    v.show(cjqconf,csubConf);
})
</script>


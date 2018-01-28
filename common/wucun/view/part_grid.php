<form   method='post'>
                      开始日期:<input id='startdate' name='startdate' value='<?php echo $startdate; ?>' type='text'/>
                      结束日期:<input id='enddate' name='enddate'   value='<?php echo $enddate; ?>' type='text'/>
                      <button>提交</button>
  </form>
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
var View = null;
require(['view'],function(v){
    v.show(cjqconf,csubConf);
    View = v;
})
</script>

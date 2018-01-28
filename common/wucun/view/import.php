<?php

//echo clipbordExcelImport($coll,$collconf,$content);

$content = $this->getParam('content');
$coll = $this->getParam('coll');
if ($content) {
    //$error = Crawler_Tool::postImportHx($content,$coll,$myerrorno);
     $lines = explode("\n", $content);
     $error = Crawler_Tool::ceImportShimoExcel($lines,$coll,$myerrorno);
     $datestr = @date('YmdHis');
    file_put_contents(ROOT."/data/raw/httppost/{$coll}_{$datestr}.txt", $content);
}
?>
    <div>石墨excel copy导入，参数startdate,enddate可指定条件</div>
    <hr/>
<pre>
<?php echo "[$myerrorno]\n$error";?>
</pre>
    <form method='POST'>
    <input type=hidden  name='coll' value="<?php echo $coll?>"/>
    <input type="submit" value="提交"/>
    <br/>
    <textarea name='content' cols='100' rows='30'><?php //echo $content;?></textarea>
</form>

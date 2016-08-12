<?php

//echo clipbordExcelImport($coll,$collconf,$content);

$content = $this->getParam('content');
$coll = $this->getParam('coll');
if($content){
    $error = Crawler_Tool::postImportHx($content,$coll,$myerrorno);
     $datestr = @date('YmdHis');
    file_put_contents(ROOT."/data/raw/httppost/{$coll}_{$datestr}.txt",$content);
}
?>
    <div>支持资金股份表格 查询里各个表格导入,从excel copy后粘贴到文本框,可在url后面加参数coll指定数据表</div>
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

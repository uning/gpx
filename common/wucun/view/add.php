<?php

$collconf = DbConfig::getParam("gridconfig.$coll",'grid');
$collheader = $collconf['header'];
$datepos = $collconf['datepos'];
$row = $this->getParam('row',array());
if ($row) {
    $date = $row[0];
    if($row[1]&&$row[2]){
        $mkey =$row[0].$row[1]; 
        $cond['_id'] = md5($mkey); 
        $mc = DbConfig::getMongodb($coll);
        $row['_byone'] = 'add';
        $mc->findAndModify($cond,array('$set'=>$row),array(),array('upsert'=>true));
        $error = $mkey." 提交成功";
    }else{
        $error = "项目和涉及金额不能为空";
    }
} else {
    $row[$datepos] = date('Y.m.d');
    $row[4] = '李春龙';
    $row[5] = 1;

}



?>
    <h1><?php echo $collconf['name']; ?> 记录数据</h1>
    <hr/>
<pre>
<?php echo "$error"; ?>
</pre>
    <form class='ui.form' method='POST'>
    <input type=hidden  name='coll' value="<?php echo $coll?>"/>
<?php 
foreach ($collheader as $k=>$v){
    echo "
    <label for='id_row$k' style='display:inline-block;width:100px;align:right;' >$v:</label>
    <input id='id_row$k' name='row[$k]' value='{$row[$k]}' />
    <br/>
    ";
}
?>
    <input  style="width:150px" align='middle' type="submit" value="提交"/>
</form>
<script>
 
 var autoCompleteData={
 id_row1:[
 "牛肉"
,"蒜苗"
,"生菜"
,"香菜"
,"青菜"
,"青椒"
,"葱"
,"大豆油"
,"面条"
,"店铺电费"
,"营业收入"
,"猪肉"
,"宿舍租金"
,"腊肉香肠"
,"米粉"
,"打包碗"
,"大米"
,"煤气"
,"库房租金"
,"生豆米"
,"猪油"
,"碎牛肉"
,"盐蛋"
,"带皮牛肉"
,"工作服"
,"蒸炉(电)"
,"酸菜"
,"菜籽油"
,"豪吉鸡精"
,"花椒"
,"美的店压力锅"
,"手工米皮"
,"餐纸(wucun)"
,"湿米皮"
,"宿舍电费"
,"菜籽油"
,"店铺电费"
,"鸡精"
,"碎牛肉"
,"户外灯箱"
,"带皮牛肉"
,"味精"
,"白糖"
,"打包汤盒"
,"腊肉"
,"糊辣椒面"
,"煤气(小罐)"
,"豆米"
,"打包盒"
,"黄豆"
,"一次性筷子"
,"荧光屏黑板"
,"店铺水费"
,"醋"
,"墨鱼"
,"甜面酱"
,"土碗"
,"食品包装袋(大)"
,"小米辣"
,"酱油"
,"牛杂骨"
,"生辣椒面"
,"打车"
,"食品包装袋(中)"
,"热敏打印纸"
,"泡椒"
,"地毯"
,"阜丰味精"
,"阜风味精"
,"牛筒骨"
,"运费"
,"红薯"
,"墨鱼两包"
,"汤包装盒"
,"白糖"
,"桶装水"
,"糟辣椒"
,"牛骨头"
,"糍粑辣"
,"外卖保温箱"
,"库房水费"
,"墨鱼仔"
,"牛大骨"
,"西红柿"
,"葱"
,"干黄豆"
,"花椒油"
,"洗洁精"
,"一次性杯子"
,"面"
]
,id_row7:[
    "斤"
    ,"桶"
    ,"把"
    ,"罐"
    ,"盒"
    ,"瓶"
    ,"瓶"
    ,"度"
    ,"卷"
    ,"包"
    ,""
    

        ]
 };

var getSourceFunc = function(array){
        return function(request, response){
               console.log('autocomplete', request);
                var matcher = new RegExp( $.ui.autocomplete.escapeRegex( request.term ) );
                for(var i=0;i<array.length;i++) {
                    for(var j=i+1;j<array.length;j++) {
                        //注意 ===
                        if(array[i]===array[j]) {
                            array.splice(j,1);
                            j--;
                        }
                    }
                }
                //console.log(array);
                //response(array);
                var matcharr = $.grep( array, function( value ) {
                    return matcher.test( value ) ;
                });
                if(matcharr.length > 0){
                    response(matcharr);
                }else
                    response(array);
            }
 }
require(['jquery-ui'],function(v){
    for (i in autoCompleteData){
        console.log('init autocomplete',i,autoCompleteData[i]);
        $('#'+i).autocomplete({
                  minLength: 0,
                  source:getSourceFunc(autoCompleteData[i])
        });
    }
});
</script>

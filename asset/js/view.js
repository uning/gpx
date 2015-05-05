define(
       [
           'jquery.jqGrid',
       ],
       function(){
           var roundf = function(num,len){
               if(typeof(num)!= 'number')
                   return num;
               len = len || 3;
               var ra = 1 ;
               for(var i = 0;i < len ; ++i){
                   ra *= 10;
               }
               return Math.round(num*ra)/ra;
           };
           var setCell=function(row,col,v,color){
               var css = '';
               color = color || 0;
               if(color>0)
                   css = 'cellred';
               else if(color < 0)
                   css = 'cellgreen';
               v = roundf(v);
               grido.setCell(row,col,v,css);
               $('#grid_sshq').setCell(row,col,v,css);
           };
           //private
       var _zqdm2sc={
           '112155':'sz',
           '113001':'sh',
           '150182':'sz',
           '122083':'sh',
       };
       var getSZSH = function(v){//获取是那个市场的代码
           var fn = v[0];
           if(fn == '6' || fn == '5')
               return 'sh';
           if(fn == '0' || fn == '3' )
               return 'sz';
           if(_zqdm2sc[v])
               return _zqdm2sc[v];
           console.log(v,'不能识别是sh.sz def sh');
           return 'sh';
       };
       var getXueqiuLink=function(v){
           return 'http://xueqiu.com/S/'+getSZSH(v)+v;
       };

       var _zqdm2ids = {};


   return {
       zqLastRefreshTimes:{},
       refreshHq:function(){
           var now = new Date().getTime();
           var strZqs = this.getHqIdsStr();
           var url = 'hqjsProxy.php?list=' + strZqs;
           var zqdm,rowid,zqsl,qse;
           var idxzqsl  =  this.getCollProp('证券数量','label');
           var idxzxsz  =  this.getCollProp('最新市值','label');
           var idxqse  =  this.getCollProp('清算额','label');
           var  zqLastRefreshTimes = this.zqLastRefreshTimes;
           $.getJSON(url,function(data,status){
               //console.log(status,url);
               if(status == 'success'){
                   var sshgrid = $('#grid_sshq');
                   if(sshgrid.attr('role') != 'presentation'){
                       sshgrid.jqGrid({
                           caption:'实时行情',
                           datatype:'local',
                           height:'auto',
                           colModel:cjqconf.sshqColModel,
                           ondblClickRow:function(rowid, iRow, iCol, e){
                               var zqdm = rowid.substr(0,6);
                               var url = getXueqiuLink(zqdm);
                               window.open(url,zqdm);
                               console.log('sshq ondblClickRow: on ',rowid,iRow, iCol,url);//, colconf);
                           },
                           
                       });
                   }
                   //sshgrid.remove();


                   $.each(data,function(k,v){
                       
                       zqdm = v['zqdm'];
                       rowid = _zqdm2ids[zqdm];
                       if(rowid == undefined){
                           console.log(zqdm,'notfind');
                           return;
                       }
                       if(zqLastRefreshTimes[zqdm] == v['31']){
                           //console.log(zqdm,zqLastRefreshTimes[zqdm],'refresh time not mod');
                           return;
                       }
                       zqLastRefreshTimes[zqdm] =  v['31'];
                       
                       qse = parseFloat(grido.getCell(rowid,idxqse));
                       zqsl = parseFloat(grido.getCell(rowid,idxzqsl));
                       var czxsz = parseFloat(grido.getCell(rowid,idxzxsz));

                       var jrzf = parseFloat(v['1']);//开盘
                       var zrsp = parseFloat(v['2']);//昨日收盘
                       var dqjg = parseFloat(v['3']);//实时价格
                       var sszf = (dqjg -zrsp)*100/zrsp;
                       var zxsz = zqsl* dqjg;
                       var ssyk = qse + zxsz;

                       v._id = rowid;
                       v.zqsl = zqsl;
                       v.qse  = qse;
                       var ov  = sshgrid.getRowData(rowid);
                       //console.log(rowid,ov._id,ov.zqdm);
                       if(ov.zqdm == zqdm){
                           sshgrid.setRowData(rowid,v);
                       }else{
                           sshgrid.addRowData(rowid,v);
                       }
                       var jryk = (dqjg - zrsp)*zqsl;
                       //*
                       setCell(rowid,'ssjg',dqjg,dqjg - zrsp);//实时价格
                       setCell(rowid,'sszf',sszf,sszf);//涨幅
                       setCell(rowid,'ssyk',ssyk,ssyk);
                       setCell(rowid,'jryk',jryk,jryk);//今日盈亏
                       setCell(rowid,'zxsz',zxsz);
                      /* 
                       v.ssjg = roundf(dqjg);
                       v.sszf = roundf(sszf);
                       v.ssyk = roundf(ssyk);
                       v.zqsl = roundf(zqsl);
                       v.jryk = roundf(jryk);
                       */

                       //console.log(idxzxsz,zxsz,czxsz,rowid,zqdm,qse,zqsl,v);
                   });
               }
           });
       },
       //获取第几行
       getCollProp:function(val,fkey,key){
           var ret = null;
           fkey = fkey || 'label';
           key = key || 'collidx';
           grido.getGridParam('colModel').forEach(function(v){
               if(v[fkey] == val){
                   ret = v[key];
               }
           });
           return ret;
       },
       //获取行情ids
       getHqIdsStr:function(){
           var collidx = this.getCollProp('证券代码','label');
           var str = '';
           if(collidx){
               var dids = grido.getCol(collidx);
               var rowids = grido.getCol('_id');
               var appeared = {};
               dids.forEach(function(v,k){
                   if(appeared[v])
                       return;
                   appeared[v] = true;
                   str += getSZSH(v) + v + ',';
                   _zqdm2ids[v] = rowids[k];
               });
           }
           return str;
       },

       show:function(cjqconf,csubConf){
           // searchopt de
           var _opopt = ['bw','eq','ge','le'];
           var _searchopt = {sopt:['eq','ge','le']};
           var _txtsearchopt = {sopt:['bw']};
          
           //search util funcs
           var _dataInitColldata = function(grid,collInd){
               return function(element){
                   $(element).autocomplete({
                       minLength: 0,
                       source: function(request, response){
                           //console.log('dataInit',grid,collInd,request);
                           var array = $(grid).jqGrid('getCol',collInd);
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
                       },
                       //id: 'AutoComplete',
                   });
               };
           };

           //for view bz
           var formatterBz = function(cellValue, options, rowObject){
               var ret  = ' ' ;
               var type = jQuery.type(cellValue);
               if(type == 'object'){
                   for(var i in cellValue){
                       var v = cellValue[i];
                       if(v.content)
                           ret +=  v.content + ' ';
                   }
               }else if(type == 'string' || type == 'number'){
                   ret += cellValue;
               }
               return ret;
           };

           var renderTo = 'gridarea';
           var grid_id  = 'grid_' + COLL;
           var pager_id = 'pager_' + COLL;
           var grid_id_jq  = '#' + grid_id;
           var pager_id_jq = '#' + pager_id;
           var qurl = '?action=q&__nl=1&coll=' + COLL;
           var subConf ={
               urlp:"?action=bz&__nl=1&coll="+COLL+"&prid=",
               editurlp:"?action=bz&__nl=1&coll="+COLL+"&prid=",
               datatype: "json",
               me_edit:true,
               colModel: [
                   {name:"id",label:'times',index:"time",key:true,hidden:true},
                   {name:"time",label:'时间',index:"time",width:150},
                   //{name:"content",label:'内容',index:"item",editable:true,edittype:"textarea", editoptions:{rows:"3",cols:"20"},width:800,sortable:false},
                   {name:"content",label:'内容',index:"item",editable:true,width:500,sortable:false},
               ],
               rowNum:10000,
               pgbuttons : false,
               height: '100%',
           };


           var subgridExpander =function(opts){
               return function(subgrid_id, row_id) {
                   var subgrid_table_id, pager_id;
                   subgrid_table_id = subgrid_id+"_t";
                   pager_id = "p_"+subgrid_table_id;
                   $("#"+subgrid_id).html("<table id='"+subgrid_table_id+"' class='scroll'></table><div id='"+pager_id+"' class='scroll'></div>");
                   //var opts = subConf;
                   opts.pager = pager_id;
                   var rowdata = grido.getRowData(row_id);
                   opts.url = opts.urlp + row_id + '&rowdata='+JSON.stringify(rowdata);
                   //opts.editurl = opts.urlp + row_id;
                   opts.editurl = opts.url;
                   var bzs,i,bzo;
                   var sgo = jQuery("#"+subgrid_table_id);

                   //console.log(opts);
                   opts.colModel.forEach(function(v,i){
                       if(v.name == 'bz')
                           v.formatter = formatterBz;
                   });

                   sgo.jqGrid(opts);
                   sgo.jqGrid('navGrid',"#"+pager_id,{edit:false,add:false,del:false});
                   if(opts.me_edit)
                       sgo.jqGrid('inlineNav', "#"+pager_id);
                   //console.log('subGridExpand',row_id,subgrid_id,opts.url);
                   if(opts.datatype == 'local'){
                       rowdata = grido.getLocalRow(row_id);
                       bzs =  rowdata?rowdata.subg:null;
                       //console.log('subGridExpand',row_id,subgrid_id,bzs);
                       if('object' == jQuery.type(bzs)){
                           for(i in bzs){
                               bzo = bzs[i];
                               sgo.jqGrid('addRowData',i,bzo);
                               if(bzo && bzo.content){
                               }
                           }
                       }
                   }
               };
           };


           //查询交割单可以评论
           if(csubConf && csubConf.subGrid){
               //console.log('before sub extend',subConf);
               var newaa = $.extend(true,{},subConf);   
               newaa.urlp = '?action=bz&__nl=1&coll=jgd&prid=';
               newaa.editurlp = '?action=bz&__nl=1&coll=jgd&prid=';
               subConf.subGridRowExpanded = subgridExpander(newaa);
           }

           $.extend(true,subConf,csubConf || {});

           console.log('subConf',subConf);
           console.log('csubConf',csubConf);

           jqconf ={
               url:qurl,
               jsonReader:{repeatitems:false},

               //search in local
               //loadonce: true,rowNum:50000,
               rowNum: 50,


               rowList: [50,80,50000],
               toppager:true,//call after custom button add ? element id = grid_id + '_toppager'
               //toolbar:[true,'both'],//

               pager: pager_id_jq,
               shrinkToFit: false,
               scroll:true,
               width: '1200',
               height: 'auto',
               datatype: 'json',
               multiSort: true,
               viewrecords: true,
               //sortorder: 'desc',
               //sortname: '0',
               //
               altRows: true,

               //subGrid: true,
               grouping: true,
               groupingView: {
                   // groupField: ['2'],
                   //groupColumnShow: [true],
                   //groupColumnShow: [false],
                   groupText: ["<b>{0}</b>"],
                   showSummaryOnHide:true,
                   // groupSummaryPos: ['footer'],//没有对齐显示 header footer
                   groupOrder: ["asc"],
                   groupSummary: [true],
                   //hideFirstGroupCol:true,
                   groupCollapse: true,
               },
               onSelectRow: function(rowid, selected){
                   //console.log('onSelectRow',rowid,selected);
               },
               //处理添加评论
               ondblClickRow:function(rowid, iRow, iCol, e){
                   var gid = e.currentTarget.id;
                   var go = $('#'+gid);
                   go.toggleSubGridRow(rowid);
                   //console.log('ondblClickRow: on bz',rowid,iRow, iCol);//, colconf);
               },

               //footerrow : true,
               userDataOnFooter: true,// use the userData parameter of the JSON response to display data on footer

               // colModel:colModel,

               subGridOptions: {
                   plusicon: "ui-icon-triangle-1-e",
                   minusicon: "ui-icon-triangle-1-s",
                   openicon: "ui-icon-arrowreturn-1-e",
                   // load the subgrid data only once
                   // and the just show/hide
                   reloadOnExpand: true,
                   // select the row when the expand column is clicked
                   selectOnExpand : true
               },
               subGridRowExpanded:subgridExpander(subConf), 
           };
           $.extend(true,jqconf,cjqconf || {});
           //默认设置
           jqconf.colModel.forEach(function(v,i){
               var collidx = i;//列编号
               if(jqconf.subGrid)
                   collidx += 1; //
               v.collidx = collidx;
               if(!v.hasOwnProperty('index')){
                   v.index = v.name;
               }

               if(jqconf.loadonce){
                   v.searchoptions =  v.searchoptions ||{
                       sopt:_opopt,
                       dataInit:_dataInitColldata(grid_id_jq,collidx)//
                   };
               }else{
                   if(!v.hasOwnProperty('stype')){
                       v.search = false;
                   }else{//默认tip，从数据中选
                       v.searchoptions =  v.searchoptions ||{
                           sopt:_opopt,
                           dataInit:_dataInitColldata(grid_id_jq,collidx)//
                       };
                   }
               }
               if(!v.hasOwnProperty('sorttype')){
                   v.sortable = false;
               }

               if(v.name == 'bz'){
                   v.formatter = formatterBz;
               }
               if(v.name == 'content'){
                   v.edittype = 'textarea';
               }
               v.editable = true;

               // jqconf.colModel[i] = v;
           });

           if(cjqconf.chich){
               if(cjqconf.bz != 1)
               subConf.colModel = jqconf.colModel;
           }


           $('#' + renderTo).prepend('<table class="jqgrid" id="' + grid_id + '"></table><div id="' + pager_id + '"></div>');
           grido = jQuery(grid_id_jq);
           grido.jqGrid(jqconf);



           // activate the toolbar searching
           grido.jqGrid('filterToolbar',{
               // JSON stringify all data from search, including search toolbar operators
               //stringResult: true,
               // instuct the grid toolbar to show the search options
               searchOperators: true,
               searchOnEnter:true,
               //autosearch : false
           });

           //this not work with subGrid
           grido.jqGrid("setFrozenColumns");

           var navopts = {
               edit:false,
               add:false,
               del:false,
               view:true,
               csv:true,//not support
               //search: false, // show search button on the toolbar
               position: "left", 
               cloneToTop:true
           };
           var addopts = 
               {
               closeAfterAdd: true,
               recreateForm: true,
               errorTextFormat: function (data) {
                   return 'Error: ' + data.responseText;
               }
           };
           var searchopts = {
               multipleSearch:true,
               multipleGroup:true,
               showQuery: true,
               searchtext:'查找',
               // set the names of the template
               //"tmplNames" : ["Template One", "Template Two"],
               // set the template contents
               // "tmplFilters": [template1, template2]
           };
           var editopts = {},delopts={};
           //toolbar
           grido.jqGrid('navGrid',pager_id_jq,navopts,editopts,addopts,delopts,searchopts);

           //可编辑
           if(cjqconf.edit){
               grido.jqGrid('inlineNav', pager_id_jq);
           }



           var groupsText = '';
           //selgroup
           function getSelStr(){
               var ret = '',em = false,val = '';
               groupsText = '';
               $(".ui-selected", $('#selectable')).each(function(i,item){
                   if(ret !== ''){
                       ret += ',';
                       groupsText += ',';
                   }
                   val = $(item).attr('ddvalue');
                   ret += val;
                   groupsText += $(item).html();
                   //console.log($(item).html());
                   if(val == 'gempty')em =true;
               });
               if(em){ ret = '';
                   groupsText = '';
               }
               return ret;
           }

           $( "#selectable" ).selectable({
               droppable:'enanble',
               start:function(e){
               },
               stop: function(e) {
                   var gstr = getSelStr();
                   $('#chgrpbtn>div,#chngroup').html('Group by:'+groupsText);
                   //$("#select-result" ).html(gstr);
                   //$("#chngroup").html('Group by:' + groupsText);
               }
           });

           var changeGroup =function() {
               var gstr = getSelStr();
               $('#chgrpbtn>div,#chngroup').html('Group by:'+groupsText);
               //console.log('Group click:',gstr);
               if(gstr === '' || gstr === 'gempty'){
                   //alert('choose fields is empty!,clear group');
                   grido.jqGrid('groupingRemove',true);
                   return false;
               }
               var  gps= [],gvSum=[],gvPos=[];
               $(".ui-selected", $('#selectable')).each(function(i,item) {
                   gps.push(item.getAttribute('ddvalue'));
                   gvSum.push(true);
                   gvPos.push('footer');
               });
               //grido.jqGrid('subGrid'
               grido.jqGrid('groupingGroupBy',gps,{groupSummary:gvSum,groupSummaryPos:gvPos});
           };

           // on chang select value change grouping
           $("#chngroup").click( changeGroup);
           // add first custom button on top
           var buttonopts = {
               buttonicon: "ui-icon-calculator",
               title: "选择显示列",
               caption: "",
               position: "last",
               onClickButton: function() {
                   // call the column chooser method
                   grido.jqGrid('columnChooser');
               }
           };
           grido.navButtonAdd(grid_id_jq + '_toppager', buttonopts);



           // add group button on top
           buttonopts = {
               //buttonicon: "ui-icon-calculator",
               title: "按选择字段Group",
               id:'chgrpbtn',
               caption: "Group",
               position: "last",
               onClickButton:changeGroup,
           };
           if($('#selectable').length >0)
               grido.navButtonAdd(grid_id_jq + '_toppager', buttonopts);
           buttonopts = {
               buttonicon: "",
               title: "展开折叠所有行",
               id:'togglesubgrid',
               caption: "ToggleSubGrid",
               position: "last",
               onClickButton:function(){
                   $.each(grido.getDataIDs(),function(a,did){
                       grido.toggleSubGridRow(did);
                   });
               },
           };
           //grido.navButtonAdd(grid_id_jq + '_toppager', buttonopts);

           var gstr = getSelStr();
           $('#chgrpbtn>div,#chngroup').html('Group by:'+groupsText);

           this.initPageRefresh();
       },
       initPageRefresh:function(){
           var timer = null;
           var chau = $('#check-autorefresh'); //.is(':checked');
           var au = $('#autorefresh'); //.is(':checked');
           var v = this;
           var refresh = function(){
               v.refreshHq();
               var now = new Date();
               $('#autorefresh-lasttime').html(now.toString());
           };

           var change = function(){
               var checked = chau.is(':checked');;
               var gap = au.val();
               if(checked){
                   clearInterval(timer);
                   timer = setInterval(refresh,gap * 1000);
                   refresh();
                   console.log('refreshHq on',gap);
               }else{
                   clearInterval(timer);
                   timer =null;
                   console.log('refreshHq off',gap);
               }
           };
           au.change(change);
           chau.change(change);
           //change();
       }
   };
});




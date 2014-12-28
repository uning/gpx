var jqconf  = jqconf || {}; //for debug
var dbe = null;
var grido = null;


jQuery(document).ready(function(){

// searchopt de
var _opopt = ['bw','eq','ge','le'];
var _searchopt = {sopt:['eq','ge','le']};
var _txtsearchopt = {sopt:['bw']};
//wrap param grid,collInd( for name is number,confused the api) to Datainit
var _dataInitColldata = function(grid,collInd){
    return function(element){
        $(element).autocomplete({
            minLength: 0,
            source: function(request, response){
                console.log('dataInit',grid,collInd,request);
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


    var grid_id_jq  = '#grid_' + COLL;
    var pager_id_jq = '#pager_' + COLL;
    

    var qurl = '?action=q&__nl=1&coll=' + COLL;

    var bzOpts ={
            url:"?action=bz&__nl=1&coll="+COLL+"&prid=",
            editurl:"?action=bz&__nl=1&coll="+COLL+"&prid=",
            datatype: "json",
            colModel: [
                {name:"id",label:'times',index:"time",key:true,hidden:true},
                {name:"time",label:'时间',index:"time",width:80},
                //{name:"content",label:'内容',index:"item",editable:true,edittype:"textarea", editoptions:{rows:"3",cols:"20"},width:800,sortable:false},
                {name:"content",label:'内容',index:"item",editable:true,width:500,sortable:false},
            ],
            rowNum:20,
            pgbuttons : false,
            height: '100%',
        },subOpts={
            datatype: "local",
            colModel:  colModel,
            rowNum:20,
            pgbuttons : false,
            height: '100%',
        };
    var subgridExpand = function(subgrid_id, row_id) {
        var subgrid_table_id, pager_id;
        subgrid_table_id = subgrid_id+"_t";
        pager_id = "p_"+subgrid_table_id;
        $("#"+subgrid_id).html("<table id='"+subgrid_table_id+"' class='scroll'></table><div id='"+pager_id+"' class='scroll'></div>");
        var opts = bzOpts;
        if(cjqconf.chich == 1){
            opts = subOpts;
           // opts.subGrid = true;
        }
        
        
        opts.pager = pager_id;
        opts.url += row_id;
        opts.editurl += row_id;


        var sgo = jQuery("#"+subgrid_table_id);
        var rowdata = grido.getLocalRow(row_id);
        var bzs =  rowdata?rowdata.bz:null;
        sgo.jqGrid(opts);
        sgo.jqGrid('navGrid',"#"+pager_id,{edit:false,add:false,del:false});
        if(cjqconf.chich == 1){
            bzs = rowdata.subg;
        }else{
            sgo.jqGrid('inlineNav', "#"+pager_id);
        }
        //console.log('subGridExpand',row_id,rowdata,bzs);
        if(cjqconf.chich == 1)
            if('object' == jQuery.type(bzs)){
                for(var i in bzs){
                    var bzo = bzs[i];
                    //console.log(bzo);
                    sgo.jqGrid('addRowData',i,bzo);
                    if(bzo && bzo.content){
                    }
                }
            }
    };


    //加隐藏列
    if(cjqconf.chich){
        colModel.push({
            name: "subg",
            hidden: true,
        });
    }
    jqconf ={
        url:qurl,
        jsonReader:{repeatitems:false},

        //search in local
        //loadonce: true,rowNum:50000,
        rowNum: 50,


        rowList: [50,80,50000],
        toppager:true,//call after custom button add ? element id = grid_id + '_toppager'
        //toolbar:[true,'both'],//

        //caption: '<?php echo $dconf["name"]?>',
        pager: pager_id_jq,
        shrinkToFit: false,
        scroll:true,
        width: '1200',
        height: '100%',
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
            // groupField: ["<?php echo $group?>"],
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

        userDataOnFooter: true,// use the userData parameter of the JSON response to display data on footer

        colModel:colModel,

        subGridOptions: {
            plusicon: "ui-icon-triangle-1-e",
            minusicon: "ui-icon-triangle-1-s",
            openicon: "ui-icon-arrowreturn-1-e",
            // load the subgrid data only once
            // and the just show/hide
            reloadOnExpand: false,
            // select the row when the expand column is clicked
            selectOnExpand : true
        },
        subGridRowExpanded:subgridExpand, 
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
        if(!v.hasOwnProperty('stype')){
            v.search = false;
        }else{//默认tip，从数据中选
            v.searchoptions =  v.searchoptions ||{
                sopt:_opopt,
                dataInit:_dataInitColldata(grid_id_jq,collidx)//
            };
        }
        if(!v.hasOwnProperty('sorttype')){
            v.sortable = false;
        }
        if(v.name == 'bz'){
            v.formatter = formatterBz;
        }

        // jqconf.colModel[i] = v;
    });



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
        //$('#chgrpbtn>div').html('Group by:'+groupsText);
        $('#chgrpbtn>div,#chngroup').html('Group by:'+groupsText);
        console.log('Group click:',gstr);
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



    // add first custom button on top
    buttonopts = {
        //buttonicon: "ui-icon-calculator",
        title: "按选择字段Group",
        id:'chgrpbtn',
        caption: "Group",
        position: "last",
        onClickButton:changeGroup,
    };
    grido.navButtonAdd(grid_id_jq + '_toppager', buttonopts);

    var gstr = getSelStr();
    $('#chgrpbtn>div,#chngroup').html('Group by:'+groupsText);
});




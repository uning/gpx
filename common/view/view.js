
var jqconf  = {};

jQuery(document).ready(function(){
    // Here we set the altRows option globally
    jQuery.extend(jQuery.jgrid.defaults,{
        recordtext: "View {0} - {1} of {2}",
        emptyrecords: "No records to view",
        loadtext: "Loading...",
        pgtext : "Page {0} of {1}"
    });
    var COLL = '<?php echo $coll?>';
    var grid_id_jq  = '#grid_' + COLL;
    var pager_id_jq = '#pager_' + COLL;
    var colModel = DC[COLL].colModel;
    colModel.forEach(function(v,i){
        if(!v.hasOwnProperty('index')){
            v.index = v.name;
        }
        if(!v.hasOwnProperty('stype')){
            v.search = false;
        }
        if(!v.hasOwnProperty('sorttype')){
            v.sortable = false;
        }
        colModel[i] = v;
    });

    jqconf = {

        url:'?action=q&__nl=1&coll='+COLL,
        jsonReader:{repeatitems:false},
        rowNum: 20,
        rowList: [20,80,500],
        height: '10%',
        caption: '<?php echo $dconf["name"]?>',
        pager: pager_id_jq,
        datatype: 'json',
        multiSort: true,
        viewrecords: true,
        sortorder: 'desc',
        sortname: '0',

        userDataOnFooter: true,// use the userData parameter of the JSON response to display data on footer

        colModel:colModel<?php //echo json_encode($colModel);?>,

        subGrid: true,
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
        subGridRowExpanded: function(subgrid_id, row_id) {
            var subgrid_table_id, pager_id;
            subgrid_table_id = subgrid_id+"_t";
            pager_id = "p_"+subgrid_table_id;
            $("#"+subgrid_id).html("<table id='"+subgrid_table_id+"' class='scroll'></table><div id='"+pager_id+"' class='scroll'></div>");
            jQuery("#"+subgrid_table_id).jqGrid({
                url:"?action=bz&__nl=1&coll=<?php echo $coll?>&prid="+row_id,
                editurl:"?action=bz&__nl=1&coll=<?php echo $coll?>&prid="+row_id,
                datatype: "json",
                colModel: [
                    {name:"id",label:'times',index:"time",key:true,hidden:true},
                    {name:"time",label:'时间',index:"time",width:80},
                    {name:"content",label:'内容',index:"item",editable:true,edittype:"textarea", editoptions:{rows:"3",cols:"20"},width:800,sortable:false},
                ],
                rowNum:20,
                pager: pager_id,
                pgbuttons : false,
                height: '100%'
            });
            jQuery("#"+subgrid_table_id).jqGrid('navGrid',"#"+pager_id,{edit:true,add:true,del:true})
        }
    };

    var grido = jQuery(grid_id_jq)
    grido.jqGrid(jqconf);


    //toolbar
    grido.jqGrid('navGrid',
                 pager_id_jq,
                 {
                     edit:false,
                     add:false,
                     del:false,
                     view:true,
                     cloneToTop:true
                 },
                 // options for the Edit Dialog
                 {},
                 // options for the Add Dialog
                 {
                     closeAfterAdd: true,
                     recreateForm: true,
                     errorTextFormat: function (data) {
                         return 'Error: ' + data.responseText
                     }
                 },
                 //delete dialog
                 {
                 },
                 //search
                 {
                     multipleSearch:true,
                     multipleGroup:true,
                     showQuery: true,
                     // set the names of the template
                     //"tmplNames" : ["Template One", "Template Two"],
                     // set the template contents
                     // "tmplFilters": [template1, template2]
                 }
                );
});

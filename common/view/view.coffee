jqconf = jqconf or {} #for debug
dbe = null
grido = null
jQuery(document).ready ->
  
  # searchopt de
  
  #wrap param grid,collInd( for name is number,confused the api) to Datainit
  
  #console.log('dataInit',grid,collInd,request);
  
  #注意 ===
  
  #console.log(array);
  #response(array);
  
  #id: 'AutoComplete',
  
  #for view bz
  
  #{name:"content",label:'内容',index:"item",editable:true,edittype:"textarea", editoptions:{rows:"3",cols:"20"},width:800,sortable:false},
  
  #console.log('subGridExpand',row_id,subgrid_id,opts.url);
  
  #console.log('subGridExpand',row_id,subgrid_id,bzs);
  
  #search in local
  #loadonce: true,rowNum:50000,
  #call after custom button add ? element id = grid_id + '_toppager'
  #toolbar:[true,'both'],//
  
  #sortorder: 'desc',
  #sortname: '0',
  #
  
  #subGrid: true,
  
  # groupField: ['2'],
  #groupColumnShow: [true],
  #groupColumnShow: [false],
  
  # groupSummaryPos: ['footer'],//没有对齐显示 header footer
  
  #hideFirstGroupCol:true,
  
  #console.log('onSelectRow',rowid,selected);
  
  #处理添加评论
  
  #console.log('ondblClickRow: on bz',rowid,iRow, iCol);//, colconf);
  # use the userData parameter of the JSON response to display data on footer
  
  # colModel:colModel,
  
  # load the subgrid data only once
  # and the just show/hide
  
  # select the row when the expand column is clicked
  
  #默认设置
  #列编号
  #
  #默认tip，从数据中选
  #
  
  # jqconf.colModel[i] = v;
  
  # activate the toolbar searching
  
  # JSON stringify all data from search, including search toolbar operators
  #stringResult: true,
  # instuct the grid toolbar to show the search options
  
  #autosearch : false
  
  #this not work with subGrid
  #not support
  #search: false, // show search button on the toolbar
  
  # set the names of the template
  #"tmplNames" : ["Template One", "Template Two"],
  # set the template contents
  # "tmplFilters": [template1, template2]
  
  #toolbar
  
  #selgroup
  getSelStr = ->
    ret = ""
    em = false
    val = ""
    groupsText = ""
    $(".ui-selected", $("#selectable")).each (i, item) ->
      if ret isnt ""
        ret += ","
        groupsText += ","
      val = $(item).attr("ddvalue")
      ret += val
      groupsText += $(item).html()
      
      #console.log($(item).html());
      em = true  if val is "gempty"
      return

    if em
      ret = ""
      groupsText = ""
    ret
  _opopt = [
    "bw"
    "eq"
    "ge"
    "le"
  ]
  _searchopt = sopt: [
    "eq"
    "ge"
    "le"
  ]
  _txtsearchopt = sopt: ["bw"]
  _dataInitColldata = (grid, collInd) ->
    (element) ->
      $(element).autocomplete
        minLength: 0
        source: (request, response) ->
          array = $(grid).jqGrid("getCol", collInd)
          matcher = new RegExp($.ui.autocomplete.escapeRegex(request.term))
          i = 0

          while i < array.length
            j = i + 1

            while j < array.length
              if array[i] is array[j]
                array.splice j, 1
                j--
              j++
            i++
          matcharr = $.grep(array, (value) ->
            matcher.test value
          )
          if matcharr.length > 0
            response matcharr
          else
            response array
          return

      return

  formatterBz = (cellValue, options, rowObject) ->
    ret = " "
    type = jQuery.type(cellValue)
    if type is "object"
      for i of cellValue
        v = cellValue[i]
        ret += v.content + " "  if v.content
    else ret += cellValue  if type is "string" or type is "number"
    ret

  grid_id_jq = "#grid_" + COLL
  pager_id_jq = "#pager_" + COLL
  qurl = "?action=q&__nl=1&coll=" + COLL
  subConf =
    urlp: "?action=bz&__nl=1&coll=" + COLL + "&prid="
    editurlp: "?action=bz&__nl=1&coll=" + COLL + "&prid="
    datatype: "json"
    me_edit: true
    colModel: [
      {
        name: "id"
        label: "times"
        index: "time"
        key: true
        hidden: true
      }
      {
        name: "time"
        label: "时间"
        index: "time"
        width: 80
      }
      {
        name: "content"
        label: "内容"
        index: "item"
        editable: true
        width: 500
        sortable: false
      }
    ]
    rowNum: 20
    pgbuttons: false
    height: "100%"

  $.extend true, subConf, csubConf or {}
  subgridExpand = (subgrid_id, row_id) ->
    subgrid_table_id = undefined
    pager_id = undefined
    subgrid_table_id = subgrid_id + "_t"
    pager_id = "p_" + subgrid_table_id
    $("#" + subgrid_id).html "<table id='" + subgrid_table_id + "' class='scroll'></table><div id='" + pager_id + "' class='scroll'></div>"
    opts = subConf
    opts.pager = pager_id
    rowdata = grido.getRowData(row_id)
    opts.url = opts.urlp + row_id + "&rowdata=" + JSON.stringify(rowdata)
    opts.editurl = opts.urlp + row_id
    bzs = undefined
    i = undefined
    bzo = undefined
    sgo = jQuery("#" + subgrid_table_id)
    sgo.jqGrid opts
    sgo.jqGrid "navGrid", "#" + pager_id,
      edit: false
      add: false
      del: false

    sgo.jqGrid "inlineNav", "#" + pager_id  if opts.me_edit
    if opts.datatype is "local"
      rowdata = grido.getLocalRow(row_id)
      bzs = (if rowdata then rowdata.subg else null)
      if "object" is jQuery.type(bzs)
        for i of bzs
          bzo = bzs[i]
          sgo.jqGrid "addRowData", i, bzo  if bzo and bzo.content
    return

  jqconf =
    url: qurl
    jsonReader:
      repeatitems: false

    rowNum: 50
    rowList: [
      50
      80
      50000
    ]
    toppager: true
    pager: pager_id_jq
    shrinkToFit: false
    scroll: true
    width: "1200"
    height: "100%"
    datatype: "json"
    multiSort: true
    viewrecords: true
    altRows: true
    grouping: true
    groupingView:
      groupText: ["<b>{0}</b>"]
      showSummaryOnHide: true
      groupOrder: ["asc"]
      groupSummary: [true]
      groupCollapse: true

    onSelectRow: (rowid, selected) ->

    ondblClickRow: (rowid, iRow, iCol, e) ->
      gid = e.currentTarget.id
      go = $("#" + gid)
      go.toggleSubGridRow rowid
      return

    userDataOnFooter: true
    subGridOptions:
      plusicon: "ui-icon-triangle-1-e"
      minusicon: "ui-icon-triangle-1-s"
      openicon: "ui-icon-arrowreturn-1-e"
      reloadOnExpand: false
      selectOnExpand: true

    subGridRowExpanded: subgridExpand

  $.extend true, jqconf, cjqconf or {}
  jqconf.colModel.forEach (v, i) ->
    collidx = i
    collidx += 1  if jqconf.subGrid
    v.collidx = collidx
    v.index = v.name  unless v.hasOwnProperty("index")
    unless v.hasOwnProperty("stype")
      v.search = false
    else
      v.searchoptions = v.searchoptions or
        sopt: _opopt
        dataInit: _dataInitColldata(grid_id_jq, collidx)
    v.sortable = false  unless v.hasOwnProperty("sorttype")
    v.formatter = formatterBz  if v.name is "bz"
    return

  subConf.colModel = jqconf.colModel  if cjqconf.chich
  grido = jQuery(grid_id_jq)
  grido.jqGrid jqconf
  grido.jqGrid "filterToolbar",
    searchOperators: true
    searchOnEnter: true

  grido.jqGrid "setFrozenColumns"
  navopts =
    edit: false
    add: false
    del: false
    view: true
    csv: true
    position: "left"
    cloneToTop: true

  addopts =
    closeAfterAdd: true
    recreateForm: true
    errorTextFormat: (data) ->
      "Error: " + data.responseText

  searchopts =
    multipleSearch: true
    multipleGroup: true
    showQuery: true
    searchtext: "查找"

  editopts = {}
  delopts = {}
  grido.jqGrid "navGrid", pager_id_jq, navopts, editopts, addopts, delopts, searchopts
  groupsText = ""
  $("#selectable").selectable
    droppable: "enanble"
    start: (e) ->

    stop: (e) ->
      gstr = getSelStr()
      $("#chgrpbtn>div,#chngroup").html "Group by:" + groupsText
      return

  
  #$("#select-result" ).html(gstr);
  #$("#chngroup").html('Group by:' + groupsText);
  changeGroup = ->
    gstr = getSelStr()
    $("#chgrpbtn>div,#chngroup").html "Group by:" + groupsText
    
    #console.log('Group click:',gstr);
    if gstr is "" or gstr is "gempty"
      
      #alert('choose fields is empty!,clear group');
      grido.jqGrid "groupingRemove", true
      return false
    gps = []
    gvSum = []
    gvPos = []
    $(".ui-selected", $("#selectable")).each (i, item) ->
      gps.push item.getAttribute("ddvalue")
      gvSum.push true
      gvPos.push "footer"
      return

    
    #grido.jqGrid('subGrid'
    grido.jqGrid "groupingGroupBy", gps,
      groupSummary: gvSum
      groupSummaryPos: gvPos

    return

  
  # on chang select value change grouping
  $("#chngroup").click changeGroup
  
  # add first custom button on top
  buttonopts =
    buttonicon: "ui-icon-calculator"
    title: "选择显示列"
    caption: ""
    position: "last"
    onClickButton: ->
      
      # call the column chooser method
      grido.jqGrid "columnChooser"
      return

  grido.navButtonAdd grid_id_jq + "_toppager", buttonopts
  
  # add group button on top
  buttonopts =
    
    #buttonicon: "ui-icon-calculator",
    title: "按选择字段Group"
    id: "chgrpbtn"
    caption: "Group"
    position: "last"
    onClickButton: changeGroup

  grido.navButtonAdd grid_id_jq + "_toppager", buttonopts
  buttonopts =
    buttonicon: ""
    title: "展开折叠所有行"
    id: "togglesubgrid"
    caption: "ToggleSubGrid"
    position: "last"
    onClickButton: ->
      $.each grido.getDataIDs(), (a, did) ->
        grido.toggleSubGridRow did
        return

      return

  
  #grido.navButtonAdd(grid_id_jq + '_toppager', buttonopts);
  gstr = getSelStr()
  $("#chgrpbtn>div,#chngroup").html "Group by:" + groupsText
  return


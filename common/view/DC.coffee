DC = null

# searchopt de
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

#wrap param grid,collInd( for name is number,confused the api) to Datainit
_dataInitColldata = (grid, collInd) ->
  (element) ->
    $(element).autocomplete
      minLength: 0
      source: (request, response) ->
        console.log "dataInit", grid, collInd, request
        array = $(grid).jqGrid("getCol", collInd)
        matcher = new RegExp($.ui.autocomplete.escapeRegex(request.term))
        i = 0

        while i < array.length
          j = i + 1

          while j < array.length
            
            #注意 ===
            if array[i] is array[j]
              array.splice j, 1
              j--
            j++
          i++
        
        #console.log(array);
        #response(array);
        matcharr = $.grep(array, (value) ->
          matcher.test value
        )
        if matcharr.length > 0
          response matcharr
        else
          response array
        return

    return


#id: 'AutoComplete',

#for view bz
formatterBz = (cellValue, options, rowObject) ->
  ret = " "
  type = jQuery.type(cellValue)
  if type is "object"
    for i of cellValue
      v = cellValue[i]
      ret += v.content + " "  if v.content
  else ret += cellValue  if type is "string" or type is "number"
  ret


#export DC
DC =
  jgd:
    name: "交割单"
    filename: "jgd.csv"
    colModel: [
      {
        name: "_id"
        index: "_id"
        hidden: true
        frozen: true
        key: true
      }
      {
        name: "0"
        index: "0"
        label: "交割日期"
        width: 90
        stype: "text"
        frozen: true
        summaryTpl: "total:{0}" # set the summary template to show the group summary
        summaryType: "count" # set the formula to calculate the summary typ
        sorttype: "date"
      }
      {
        name: "1"
        index: "1"
        label: "业务名称"
        stype: "text"
        width: 75
        frozen: true
        sorttype: "text"
      }
      {
        name: "2"
        index: "2"
        label: "证券代码"
        width: 75
        
        #searchoptions: _txtsearchopt,
        stype: "text"
        frozen: true
        sorttype: "text"
      }
      {
        name: "3"
        index: "3"
        label: "证券名称"
        width: 75
        stype: "text"
        
        #searchoptions: _searchopt,
        frozen: true
        sorttype: "text"
      }
      {
        name: "4"
        index: "4"
        label: "成交价格"
        frozen: true
        width: 75
        sorttype: "number"
      }
      {
        name: "5"
        index: "5"
        label: "成交数量"
        width: 75
        summaryTpl: "{0}" # set the summary template to show the group summary
        summaryType: "sum" # set the formula to calculate the summary typ
        sorttype: "number"
      }
      {
        name: "6"
        index: "6"
        label: "剩余数量"
        width: 75
        sorttype: "number"
      }
      {
        name: "7"
        index: "7"
        label: "成交金额"
        width: 75
        summaryTpl: "{0}" # set the summary template to show the group summary
        summaryType: "sum" # set the formula to calculate the summary typ
        sorttype: "number"
      }
      {
        name: "8"
        index: "8"
        label: "清算金额"
        width: 75
        summaryTpl: "{0}" # set the summary template to show the group summary
        summaryType: "sum" # set the formula to calculate the summary typ
        sorttype: "number"
      }
      {
        name: "9"
        index: "9"
        label: "剩余金额"
        width: 75
        sorttype: "number"
      }
      {
        name: "10"
        index: "10"
        label: "佣金"
        width: 50
        summaryTpl: "{0}" # set the summary template to show the group summary
        summaryType: "sum" # set the formula to calculate the summary typ
        sorttype: "number"
      }
      {
        name: "11"
        index: "11"
        label: "印花税"
        width: 50
        summaryTpl: "{0}"
        summaryType: "sum"
        sorttype: "number"
      }
      {
        name: "12"
        index: "12"
        label: "过户费"
        width: 50
        summaryTpl: "{0}"
        summaryType: "sum"
        sorttype: "number"
      }
      {
        name: "13"
        index: "13"
        label: "结算费"
        width: 50
        summaryTpl: "{0}"
        summaryType: "sum"
        sorttype: "number"
      }
      {
        name: "14"
        index: "14"
        label: "附加费"
        width: 50
        summaryTpl: "{0}"
        summaryType: "sum"
        sorttype: "number"
      }
      {
        name: "bz"
        label: "备注"
        width: 75
        formatter: formatterBz
        editable: "true"
        edittype: "textarea"
      }
      {
        name: "15"
        index: "15"
        label: "币种"
        width: 50
      }
      {
        name: "16"
        index: "16"
        label: "成交编号"
        width: 60
        sorttype: "text"
      }
      {
        name: "17"
        index: "17"
        label: "股东代码"
        width: 60
      }
      {
        name: "18"
        index: "18"
        label: "资金帐号"
        width: 60
      }
    ]

  lscj:
    name: "历史成交"
    colModel: [
      {
        name: "_id"
        index: "_id"
        hidden: true
        key: true
      }
      {
        name: "0"
        index: "0"
        label: "成交日期"
        width: 75
        stype: "text"
        
        #searchoptions: _datesearchopt,
        sorttype: "date"
      }
      {
        name: "1"
        index: "1"
        label: "成交时间"
        stype: "text"
        width: 75
        sorttype: "text"
      }
      {
        name: "2"
        index: "2"
        label: "证券代码"
        width: 75
        stype: "text"
        sorttype: "text"
      }
      {
        name: "3"
        index: "3"
        label: "证券名称"
        width: 75
        stype: "text"
        sorttype: "text"
      }
      {
        name: "4"
        index: "4"
        label: "买卖标志"
        width: 75
        sorttype: "number"
      }
      {
        name: "5"
        index: "5"
        label: "委托价格"
        width: 75
        sorttype: "number"
      }
      {
        name: "6"
        index: "6"
        label: "委托数量"
        width: 75
        summaryTpl: "{0}"
        summaryType: "sum"
        sorttype: "number"
      }
      {
        name: "7"
        index: "7"
        label: "委托编号"
        width: 75
        sorttype: "number"
      }
      {
        name: "8"
        index: "8"
        label: "成交价格"
        width: 75
        sorttype: "number"
      }
      {
        name: "9"
        index: "9"
        label: "成交数量"
        width: 75
        summaryTpl: "{0}"
        summaryType: "sum"
        sorttype: "number"
      }
      {
        name: "10"
        index: "10"
        label: "成交金额"
        width: 50
        summaryTpl: "{0}"
        summaryType: "sum"
        sorttype: "number"
      }
      {
        name: "11"
        index: "11"
        label: "成交编号"
        width: 50
        sorttype: "number"
      }
      {
        name: "12"
        index: "12"
        label: "股东编码"
        width: 50
        sorttype: "number"
      }
      {
        name: "13"
        index: "13"
        label: "交易所"
        width: 50
        sorttype: "text"
      }
      {
        name: "14"
        index: "14"
        label: "原备注"
        width: 50
        sorttype: "text"
      }
      {
        name: "bz"
        label: "备注"
        width: 75
        formatter: formatterBz
        editable: "true"
        edittype: "textarea"
      }
      {
        name: "15"
        index: "15"
        label: "剩余数量"
        width: 50
      }
    ]

  zjls:
    name: "资金流水"
    colModel: [
      {
        name: "_id"
        index: "_id"
        hidden: true
        key: true
      }
      {
        name: "0"
        index: "0"
        label: "成交日期"
        width: 75
        stype: "text"
        sorttype: "date"
      }
      {
        name: "1"
        index: "1"
        label: "业务名称"
        stype: "text"
        width: 75
        sorttype: "text"
      }
      {
        name: "2"
        index: "2"
        label: "发生金额"
        summaryTpl: "{0}"
        summaryType: "sum"
        width: 75
        stype: "text"
        sorttype: "number"
      }
      {
        name: "3"
        index: "3"
        label: "剩余金额"
        width: 75
        stype: "number"
        sorttype: "number"
      }
      {
        name: "4"
        index: "4"
        label: "证券名称"
        stype: "text"
        width: 75
        sorttype: "text"
      }
      {
        name: "5"
        index: "5"
        label: "成交价格"
        width: 75
        sorttype: "number"
      }
      {
        name: "6"
        index: "6"
        label: "成交数量"
        width: 75
        summaryTpl: "{0}"
        summaryType: "sum"
        sorttype: "number"
      }
      {
        name: "7"
        index: "7"
        label: "剩余数量"
        width: 75
        sorttype: "number"
      }
      {
        name: "8"
        index: "8"
        label: "币种"
        width: 50
        sorttype: "text"
      }
      {
        name: "9"
        index: "9"
        label: "证券代码"
        stype: "text"
        width: 75
        sorttype: "text"
      }
      {
        name: "10"
        index: "10"
        label: "股东代码"
        stype: "text"
        width: 50
        sorttype: "text"
      }
      {
        name: "11"
        index: "11"
        label: "资金账号"
        width: 50
        sorttype: "text"
      }
      {
        name: "bz"
        label: "备注"
        width: 75
        formatter: formatterBz
        editable: "true"
        edittype: "textarea"
      }
    ]

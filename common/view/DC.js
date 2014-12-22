
//for view bz
function formatterBz(cellValue, options, rowObject){
    var ret  = ' ' ;
    var type = jQuery.type(cellValue);
    if(type == 'object'){
        for(var i in cellValue){
            var v = cellValue[i];
            ret +=  v.content + ' ';

        }
    }else if(type == 'string' || type == 'number'){
        ret += cellValue;
    }
    return ret;
}
var  DC = DC || {
    jgd: {
        name: "交割单",
        filename: "jgd.csv",
        colModel: [
            {
            name: "_id",
            index: "_id",
            hidden: true,
            key: true
        },
        {
            name: "0",
            index: "0",
            label: "交割日期",
            width: 75,
            stype: "text",
            searchoptions: {
                dataInit: 'datePick',
                attr: {
                    title: "Select Date"
                },
                sopt: [
                    'eq',
                    'ge',
                    'le'
                ]
            },
            sorttype: "date"
        },
        {
            name: "1",
            index: "1",
            label: "业务名称",
            stype: "text",
            width: 75,
            sorttype: "text"
        },
        {
            name: "2",
            index: "2",
            label: "证券代码",
            width: 75,
            stype: "text",
            searchoptions: {
                sopt: [
                    'eq',
                    'ge',
                    'le'
                ]
            },
            sorttype: "text"
        },
        {
            name: "3",
            index: "3",
            label: "证券名称",
            width: 75,
            stype: "text",
            searchoptions: {
                sopt: [
                    'eq',
                    'ge',
                    'le'
                ]
            },
            sorttype: "text"
        },
        {
            name: "4",
            index: "4",
            label: "成交价格",
            width: 75,
            sorttype: "number"
        },
        {
            name: "5",
            index: "5",
            label: "成交数量",
            width: 75,
            sorttype: "number"
        },
        {
            name: "6",
            index: "6",
            label: "剩余数量",
            width: 75,
            sorttype: "number"
        },
        {
            name: "7",
            index: "7",
            label: "成交金额",
            width: 75,
            sorttype: "number"
        },
        {
            name: "8",
            index: "8",
            label: "清算金额",
            width: 75,
            sorttype: "number"
        },
        {
            name: "9",
            index: "9",
            label: "剩余金额",
            width: 75,
            sorttype: "number"
        },
        {
            name: "10",
            index: "10",
            label: "佣金",
            width: 50,
            sorttype: "number"
        },
        {
            name: "11",
            index: "11",
            label: "印花税",
            width: 50,
            sorttype: "number"
        },
        {
            name: "12",
            index: "12",
            label: "过户费",
            width: 50,
            sorttype: "number"
        },
        {
            name: "13",
            index: "13",
            label: "结算费",
            width: 50,
            sorttype: "number"
        },
        {
            name: "14",
            index: "14",
            label: "附加费",
            width: 50,
            sorttype: "number"
        },
        {
            name: "bz",
            label: "备注",
            width: 75,
            formatter: formatterBz,
            editable: "true",
            edittype: "textarea"
        },
        {
            name: "15",
            index: "15",
            label: "币种",
            width: 50
        },
        {
            name: "16",
            index: "16",
            label: "成交编号",
            width: 60,
            sorttype: "text"
        },
        {
            name: "17",
            index: "17",
            label: "股东代码",
            width: 60
        },
        {
            name: "18",
            index: "18",
            label: "资金帐号",
            width: 60
        }
        ]
    },
    cjjl: {
        name: "成交记录",
        colModel: [
            {
            name: "_id",
            index: "_id",
            hidden: true,
            key: true
        },
        {
            name: "0",
            index: "0",
            label: "成交日期",
            width: 75,
            stype: "text",
            searchoptions: {
                dataInit: 'datePick',
                attr: {
                    title: "Select Date"
                },
                sopt: [
                    'eq',
                    'ge',
                    'le'
                ]
            },
            sorttype: "date"
        },
        {
            name: "1",
            index: "1",
            label: "成交时间",
            stype: "text",
            width: 75,
            sorttype: "text"
        },
        {
            name: "2",
            index: "2",
            label: "证券代码",
            width: 75,
            stype: "text",
            searchoptions: {
                sopt: [
                    'eq',
                    'ge',
                    'le'
                ]
            },
            sorttype: "text"
        },
        {
            name: "3",
            index: "3",
            label: "证券名称",
            width: 75,
            stype: "text",
            searchoptions: {
                sopt: [
                    'eq',
                    'ge',
                    'le'
                ]
            },
            sorttype: "text"
        },
        {
            name: "4",
            index: "4",
            label: "买卖标志",
            width: 75,
            sorttype: "number"
        },
        {
            name: "5",
            index: "5",
            label: "委托价格",
            width: 75,
            sorttype: "number"
        },
        {
            name: "6",
            index: "6",
            label: "委托数量",
            width: 75,
            sorttype: "number"
        },
        {
            name: "7",
            index: "7",
            label: "委托编号",
            width: 75,
            sorttype: "number"
        },
        {
            name: "8",
            index: "8",
            label: "成交价格",
            width: 75,
            sorttype: "number"
        },
        {
            name: "9",
            index: "9",
            label: "成交数量",
            width: 75,
            sorttype: "number"
        },
        {
            name: "10",
            index: "10",
            label: "成交金额",
            width: 50,
            sorttype: "number"
        },
        {
            name: "11",
            index: "11",
            label: "成交编号",
            width: 50,
            sorttype: "number"
        },
        {
            name: "12",
            index: "12",
            label: "股东编码",
            width: 50,
            sorttype: "number"
        },
        {
            name: "13",
            index: "13",
            label: "交易所",
            width: 50,
            sorttype: "text"
        },
        {
            name: "14",
            index: "14",
            label: "原备注",
            width: 50,
            sorttype: "text"
        },
        {
            name: "bz",
            label: "备注",
            width: 75,
            formatter: formatterBz,
            editable: "true",
            edittype: "textarea"
        },
        {
            name: "15",
            index: "15",
            label: "剩余数量",
            width: 50
        }
        ]
    },
    zjls: {
        name: "资金流水",
        colModel: [
            {
            name: "_id",
            index: "_id",
            hidden: true,
            key: true
        },
        {
            name: "0",
            index: "0",
            label: "成交日期",
            width: 75,
            stype: "text",
            searchoptions: {
                dataInit: 'datePick',
                attr: {
                    title: "Select Date"
                },
                sopt: [
                    'eq',
                    'ge',
                    'le'
                ]
            },
            sorttype: "date"
        },
        {
            name: "1",
            index: "1",
            label: "业务名称",
            stype: "text",
            width: 75,
            sorttype: "text"
        },
        {
            name: "2",
            index: "2",
            label: "发生金额",
            width: 75,
            stype: "text",
            searchoptions: {
                sopt: [
                    'eq',
                    'ge',
                    'le'
                ]
            },
            sorttype: "number"
        },
        {
            name: "3",
            index: "3",
            label: "剩余金额",
            width: 75,
            stype: "number",
            searchoptions: {
                sopt: [
                    'eq',
                    'ge',
                    'le'
                ]
            },
            sorttype: "number"
        },
        {
            name: "4",
            index: "4",
            label: "证券名称",
            width: 75,
            sorttype: "text"
        },
        {
            name: "5",
            index: "5",
            label: "成交价格",
            width: 75,
            sorttype: "number"
        },
        {
            name: "6",
            index: "6",
            label: "成交数量",
            width: 75,
            sorttype: "number"
        },
        {
            name: "7",
            index: "7",
            label: "剩余数量",
            width: 75,
            sorttype: "number"
        },
        {
            name: "8",
            index: "8",
            label: "币种",
            width: 50,
            sorttype: "text"
        },
        {
            name: "9",
            index: "9",
            label: "证券代码",
            width: 75,
            sorttype: "text"
        },
        {
            name: "10",
            index: "10",
            label: "股东代码",
            width: 50,
            sorttype: "text"
        },
        {
            name: "11",
            index: "11",
            label: "资金账号",
            width: 50,
            sorttype: "text"
        },
        {
            name: "bz",
            label: "备注",
            width: 75,
            formatter: formatterBz,
            editable: "true",
            edittype: "textarea"
        }
        ]
    }
}


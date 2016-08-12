<?php
return array (
    'jgd' =>
    array (
        'name' => '交割单',
        'filename' => 'jgd.csv',
        'idfs' =>
        array (
            0 => 0,
            1 => 2,
            2 => 4,
            3 => 5,
            4 => 6,
            5 => 7,
            6 => 8,
            7 => 9,
            8 => 10,
            9 => 11,
            10 => 12,
            11 => 13,
            12 => 16,
        ),
        'groups' =>
        array (
            0 => '交割日期',
            17 => '股东代码',
            18 => '资金帐号',
            3 => '证券名称',
            2 => '证券代码',
            1 => '业务名称',
        ),
        'numfields'=>array(
            4 => '成交价格',
            5 => '成交数量',
            6 => '剩余数量',
            7 => '成交金额',
            8 => '清算金额',
            9 => '剩余金额',
            10 => '佣金',
            11 => '印花税',
            12 => '过户费',
            13 => '结算费',
            14 => '附加费',
        ),
        'header' =>
        array (
            0 => '交割日期',
            1 => '业务名称',
            2 => '证券代码',
            3 => '证券名称',
            4 => '成交价格',
            5 => '成交数量',
            6 => '剩余数量',
            7 => '成交金额',
            8 => '清算金额',
            9 => '剩余金额',
            10 => '佣金',
            11 => '印花税',
            12 => '过户费',
            13 => '结算费',
            14 => '附加费',
            15 => '币种',
            16 => '成交编号',
            17 => '股东代码',
            18 => '资金帐号',
        ),
        'colModel' =>
        array(
            //'_fnorder'=>array('name' => '_fnorder','label'=>'fn'),
            0 =>
            array (
                'name' => '0',
                'label' => '交割日期',
                'width' => 90,
                'stype' => 'text',
                'frozen' => true,
                'summaryTpl' => 'total:{0}',
                'summaryType' => 'count',
                'sorttype' => 'date',
            ),
            1 =>
            array (
                'name' => '1',
                'label' => '业务名称',
                'stype' => 'text',
                'width' => 75,
                'frozen' => true,
                'sorttype' => 'text',
            ),

            2 =>
            array (
                'name' => '2',
                'label' => '证券代码',
                'width' => 75,
                'stype' => 'text',
                'frozen' => true,
                'sorttype' => 'text',
            ),
            3 =>
            array (
                'name' => '3',
                'label' => '证券名称',
                'width' => 75,
                'stype' => 'text',
                'frozen' => true,
                'sorttype' => 'text',
            ),
            4 =>
            array (
                'name' => '4',
                'label' => '成交价格',
                'frozen' => true,
                'width' => 75,
                'sorttype' => 'number',
            ),
            5 =>
            array (
                'name' => '5',
                'label' => '成交数量',
                'width' => 75,
                'summaryTpl' => '{0}',
                'summaryType' => 'sum',
                'sorttype' => 'number',
            ),
            6 =>
            array (
                'name' => '6',
                'label' => '剩余数量',
                'width' => 75,
                'sorttype' => 'number',
            ),
            7 =>
            array (
                'name' => '7',
                'label' => '成交金额',
                'width' => 75,
                'summaryTpl' => '{0}',
                'summaryType' => 'sum',
                'sorttype' => 'number',
            ),
            8 =>
            array (
                'name' => '8',
                'label' => '清算金额',
                'width' => 75,
                'summaryTpl' => '{0}',
                'summaryType' => 'sum',
                'sorttype' => 'number',
            ),
            9 =>
            array (
                'name' => '9',
                'label' => '剩余金额',
                'width' => 75,
                'sorttype' => 'number',
            ),
            10 =>
            array (
                'name' => '10',
                'label' => '佣金',
                'width' => 50,
                'summaryTpl' => '{0}',
                'summaryType' => 'sum',
                'sorttype' => 'number',
            ),
            11 =>
            array (
                'name' => '11',
                'label' => '印花税',
                'width' => 50,
                'summaryTpl' => '{0}',
                'summaryType' => 'sum',
                'sorttype' => 'number',
            ),
            12 =>
            array (
                'name' => '12',
                'label' => '过户费',
                'width' => 50,
                'summaryTpl' => '{0}',
                'summaryType' => 'sum',
                'sorttype' => 'number',
            ),
            /*
            13 =>
            array (
                'name' => '13',
                'label' => '结算费',
                'width' => 50,
                'summaryTpl' => '{0}',
                'summaryType' => 'sum',
                'sorttype' => 'number',
            ),
            14 =>
            array (
                'name' => '14',
                'label' => '附加费',
                'width' => 50,
                'summaryTpl' => '{0}',
                'summaryType' => 'sum',
                'sorttype' => 'number',
            ),
            15 =>
            array (
                'name' => '15',
                'label' => '币种',
                'width' => 50,
                'sortable' => false,
            ),
            16 =>
            array (
                'name' => '16',
                'label' => '成交编号',
                'width' => 60,
                'sorttype' => 'text',
            ),
            //*/
            17 =>
            array (
                'name' => '17',
                'label' => '股东代码',
                'width' => 60,
                'sortable' => false,
            ),
            18 =>
            array (
                'name' => '18',
                'label' => '资金帐号',
                'width' => 60,
                'sortable' => false,
            ),
            'subg' =>
            array (
                'name' => 'subg',
                'hidden' => true,
                'sortable' => false,
            ),
        ),
    ),
    'lscj' =>
    array (
        'name' => '历史成交',
        'filename' => 'cjjl.csv',
        'idfs' =>
        array (
            0 => 0,
            1 => 1,
            2 => 2,
            3 => 5,
            4 => 6,
            5 => 7,
            6 => 8,
            7 => 9,
            8 => 12,
        ),
        'groups' =>
        array (
            0 => '成交日期',
            2 => '证券代码',
            3 => '证券名称',
            4 => '买卖标志',
            7 => '委托编号',
            11 => '成交编号',
            12 => '股东代码',
            13 => '交易所名称',
        ),
        'numfields'=>array(
            5 => '委托价格',
            6 => '委托数量',
            8 => '成交价格',
            9 => '成交数量',
            10 => '成交金额',
            15 => '剩余数量',
        ),
        'header' =>
        array (
            0 => '成交日期',
            1 => '成交时间',
            2 => '证券代码',
            3 => '证券名称',
            4 => '买卖标志',
            5 => '委托价格',
            6 => '委托数量',
            7 => '委托编号',
            8 => '成交价格',
            9 => '成交数量',
            10 => '成交金额',
            11 => '成交编号',
            12 => '股东代码',
            13 => '交易所名称',
            14 => '备注',
            15 => '剩余数量',
        ),
        'aheader' =>
        array (
            'comment' => '思路原因',
        ),
        'colModel' =>
        array (
            0 =>
            array (
                'name' => '0',
                'label' => '成交日期',
                'width' => 75,
                'stype' => 'text',
                'sorttype' => 'date',
            ),
            1 =>
            array (
                'name' => '1',
                'label' => '成交时间',
                'stype' => 'text',
                'width' => 75,
                'sorttype' => 'text',
            ),
            2 =>
            array (
                'name' => '2',
                'label' => '证券代码',
                'width' => 75,
                'stype' => 'text',
                'sorttype' => 'text',
            ),
            3 =>
            array (
                'name' => '3',
                'label' => '证券名称',
                'width' => 75,
                'stype' => 'text',
                'sorttype' => 'text',
            ),
            4 =>
            array (
                'name' => '4',
                'label' => '买卖标志',
                'width' => 75,
                'sorttype' => 'number',
            ),
            5 =>
            array (
                'name' => '5',
                'label' => '委托价格',
                'width' => 75,
                'sorttype' => 'number',
            ),
            6 =>
            array (
                'name' => '6',
                'label' => '委托数量',
                'width' => 75,
                'summaryTpl' => '{0}',
                'summaryType' => 'sum',
                'sorttype' => 'number',
            ),
            7 =>
            array (
                'name' => '7',
                'label' => '委托编号',
                'width' => 75,
                'sorttype' => 'number',
            ),
            8 =>
            array (
                'name' => '8',
                'label' => '成交价格',
                'width' => 75,
                'sorttype' => 'number',
            ),
            9 =>
            array (
                'name' => '9',
                'label' => '成交数量',
                'width' => 75,
                'summaryTpl' => '{0}',
                'summaryType' => 'sum',
                'sorttype' => 'number',
            ),
            10 =>
            array (
                'name' => '10',
                'label' => '成交金额',
                'width' => 50,
                'summaryTpl' => '{0}',
                'summaryType' => 'sum',
                'sorttype' => 'number',
            ),
            11 =>
            array (
                'name' => '11',
                'label' => '成交编号',
                'width' => 50,
                'sorttype' => 'number',
            ),
            12 =>
            array (
                'name' => '12',
                'label' => '股东编码',
                'width' => 50,
                'sorttype' => 'number',
            ),
            13 =>
            array (
                'name' => '13',
                'label' => '交易所',
                'width' => 50,
                'sorttype' => 'text',
            ),
            14 =>
            array (
                'name' => '14',
                'label' => '原备注',
                'width' => 50,
                'sorttype' => 'text',
            ),
            15 =>
            array (
                'name' => '15',
                'label' => '剩余数量',
                'width' => 50,
            ),
        ),
    ),
    'zjls' =>
    array (
        'name' => '资金流水',
        'filename' => 'zjls.csv',
        'idfs' =>
        array (
            0 => 0,
            1 => 2,
            2 => 3,
            3 => 5,
            4 => 6,
            5 => 9,
            6 => 10,
        ),
        'groups' =>
        array (
            9 => '证券代码',
            0 => '成交日期',
            1 => '业务名称',
            4 => '证券名称',
            10 => '股东代码',
            11 => '资金帐号',
        ),
        'numfields'=>array(
            2 => '发生金额',
            3 => '剩余金额',
            5 => '成交价格',
            6 => '成交数量',
            7 => '剩余数量',
        ),
        'header' =>
        array (
            0 => '成交日期',
            1 => '业务名称',
            2 => '发生金额',
            3 => '剩余金额',
            4 => '证券名称',
            5 => '成交价格',
            6 => '成交数量',
            7 => '剩余数量',
            8 => '币种',
            9 => '证券代码',
            10 => '股东代码',
            11 => '资金帐号',
        ),
        'colModel' =>
        array (
            0 =>
            array (
                'name' => '0',
                'label' => '成交日期',
                'width' => 75,
                'stype' => 'text',
                'sorttype' => 'date',
            ),
            1 =>
            array (
                'name' => '1',
                'label' => '业务名称',
                'stype' => 'text',
                'width' => 75,
                'sorttype' => 'text',
            ),
            2 =>
            array (
                'name' => '2',
                'label' => '发生金额',
                'summaryTpl' => '{0}',
                'summaryType' => 'sum',
                'width' => 75,
                'stype' => 'text',
                'sorttype' => 'number',
            ),
            3 =>
            array (
                'name' => '3',
                'label' => '剩余金额',
                'width' => 75,
                'stype' => 'number',
                'sorttype' => 'number',
            ),
            4 =>
            array (
                'name' => '4',
                'label' => '证券名称',
                'stype' => 'text',
                'width' => 75,
                'sorttype' => 'text',
            ),
            5 =>
            array (
                'name' => '5',
                'label' => '成交价格',
                'width' => 75,
                'sorttype' => 'number',
            ),
            6 =>
            array (
                'name' => '6',
                'label' => '成交数量',
                'width' => 75,
                'summaryTpl' => '{0}',
                'summaryType' => 'sum',
                'sorttype' => 'number',
            ),
            7 =>
            array (
                'name' => '7',
                'label' => '剩余数量',
                'width' => 75,
                'sorttype' => 'number',
            ),
            8 =>
            array (
                'name' => '8',
                'label' => '币种',
                'width' => 50,
                'sorttype' => 'text',
            ),
            9 =>
            array (
                'name' => '9',
                'label' => '证券代码',
                'stype' => 'text',
                'width' => 75,
                'sorttype' => 'text',
            ),
            10 =>
            array (
                'name' => '10',
                'label' => '股东代码',
                'stype' => 'text',
                'width' => 50,
                'sorttype' => 'text',
            ),
            11 =>
            array (
                'name' => '11',
                'label' => '资金账号',
                'width' => 50,
                'sorttype' => 'text',
            ),
        ),
    ),
    'zjgf'=>array(
        'name'=>'资金股份',
        'theader_txtfields'=>array(0),
        'theader'=>array(
            0 => '币种',
            1 => '余额',
            2 => '可用',
            3 => '参考市值',
            4 => '资产',
            5 => '盈亏',
        ),
        'txtfields'=>array(0,1,12,13,14,15),
        'groups'=>array(
            'date'=>'日期',
            '12'=>'证券代码',
            '0'=>'证券名称',
        ),
        'numfields'=>array(
            2 => '证券数量',
            3 => '库存数量',
            4 => '可卖数量',
            5 => '参考成本价',
            6 => '当前价',
            7 => '最新市值',
            8 => '浮动盈亏',
            9 => '盈亏比例(%)',
            10 => '今买数量',
            11 => '冻结数量',
        ),
        'sumops'=>array('8'=>'sum','7'=>'sum'),
//系统日期 证券名称 证券数量 库存数量 可卖数量 参考成本价 当前价 最新市值 浮动盈亏 盈亏比例(%) 今买数量 冻结数量 证券代码 股东代码 交易所名称 资金帐号

        'header'=>array(
            0 =>'系统日期',
            1 => '证券名称',
            2 => '证券数量',
            3 => '库存数量',
            4 => '可卖数量',
            5 => '参考成本价',
            6 => '当前价',
            7 => '最新市值',
            8 => '浮动盈亏',
            9 => '盈亏比例(%)',
            10 => '今买数量',
            11 => '冻结数量',
            12 => '证券代码',
            13 => '股东代码',
            14 => '交易所名称',
            15 => '资金帐号',
        ),
    ),
    'dzd'=>array(
        'header'=>array (
            0 => '成交日期',
            1 => '业务名称',
            2 => '证券代码',
            3 => '证券名称',
            4 => '成交价格',
            5 => '成交数量',
            6 => '成交金额',
            7 => '发生金额',
            8 => '剩余金额',
            9 => '币种',
            10 => '成交编号',
            11 => '股东代码',
            12 => '资金帐号',
        ),
        'sumops'=>array('7'=>'sum'),
        'idfs'=>array(0,1,2,5,7,8,10),
        'groups'=>array(
            0 => '成交日期',
            1 => '业务名称',
            2 => '证券代码',
            3 => '证券名称',
        ),
        'numfields'=>array(
            4 => '成交价格',
            5 => '成交数量',
            6 => '成交金额',
            7 => '发生金额',
            8 => '剩余金额',
        )
    ),
    'calcc'=>array(

        'sfields'=>array(2=>'text','3'=>'text','chtime'=>'text'),//搜索的字段
        'groups' =>
        array (
            'date' => '日期',
            '_forsum' => '求和',
        ),
        'sumops'=>array(
            2 => 'count',
            6 => 'sum',
            'zxsz' => 'sum',
            '8'=>'sum',
            'fdyk' => 'sum',
            'zcbl' => 'sum',
        ),
        'header'=>array(
            2 => '证券代码',
            3 => '证券名称',
            6 => '证券数量',
            4 => '收盘价',
            'pdate' => '价格日期',

            'zxsz' => '最新市值',
            '8'=>'清算额',
            'cdate' =>'开始建仓',
            'chtime'=>'持仓天数',

            'ljmr' => '累计买入',
            'fdyk' => '浮动盈亏',
            'ykbl' => '盈亏比例(%)',
            'zcbl' => '占仓比例(%)',
            'cbj' => '成本价', //  清算/剩余数量
            'ldate'=>'最后操作',
            10 => '佣金',
            11 => '印花税',
            12 => '过户费',
            //'date'=>'持仓日期',//关联日期，标示某一天的持仓
            //13 => '结算费',
            //14 => '附加费',
        ),
        'theader_sfields'=>array('date'=>'text'),//搜索的字段
        'theader'=>array(
             'date'=>'日期',
            'zxsz' => '最新市值',
            'zc' => '资产总额',
            'kyye' => '可用余额',
            8 => '结算余额',
            'jsyk' => '计算盈亏', //资产总额 - 银行流入
            'ljyk' => '累计盈亏', //∑股票盈亏
            'cw' => '仓位(%)',//市值/资产总额
            'ykbl' => '盈亏比例(%)',
            'yinhangtr'=>'银行投入',
            'rzye' => '融资余额',
            'rqye' => '融券余量',

            'yinhangzr'=>'银行转入',
            'yinhangzc'=>'银行转出',
            'rongzich'=>'融资偿还',
            'rongzijr'=>'融资借入',
            'rongzilx'=>'融资利息',
            'rongquanlx'=>'融券利息',
            10 => '佣金',
            11 => '印花税',
            12 => '过户费',
            //13 => '结算费',
            //14 => '附加费',
        ),

        'jgdheader'=>array(
            0 => '交割日期',
            1 => '业务名称',
            3 => '证券名称',
            6 => '剩余数量',
            4 => '成交价格',
            'chtime'=>'持仓天数',
            5 => '成交数量',

            7 => '成交金额',
            8 => '清算金额',

            10 => '佣金',
            11 => '印花税',
            12 => '过户费',
            //13 => '结算费',
            //14 => '附加费',
            //15 => '币种',
            //9 => '剩余金额',
            //2 => '证券代码',
            //16 => '成交编号',
            //17 => '股东代码',
            //18 => '资金帐号',
        ),
    ),
    'sshq'=>array(
        'header'=>array(
            '0' => '证券名称',
            'zqdm' => '证券代码',
            'zqsl' => '证券数量',
            'ssjg' => '实时价格',
            'sszf' =>'今日涨幅%',
            'ssyk' =>'实时总盈亏',
            'jryk' =>'今日盈亏',
            'qse' => '清算',
            'zxsz' => '最新市值',
            '1' => '今日开盘价',
            '2' => '昨日收盘价',
            '3' => '当前价格',
            '31' => '时间',
            '4' => '今日最高价',
            '5' => '今日最低价',
            '6' => '竞买价',
            '7' => '竞卖价',
            '8' => '成交的股票数',
            '9' => '成交金额',
            '10' => '买一gs',
            '11' => '买一bj',
            '12' => '买二gs',
            '13' => '买二bj',
            '14' => '买三gs',
            '15' => '买三bj',
            '16' => '买四gs',
            '17' => '买四bj',
            '18' => '买五gs',
            '19' => '买五bj',
            '20' => '卖一gs',
            '21' => '卖一bj',
            '22' => 'm2gs',
            '23' => 'm2bj',
            '24' => 'm3gs',
            '25' => 'm3bj',
            '26' => 'm4gs',
            '27' => 'm4bj',
            '28' => 'm5gs',
            '29' => 'm5bj',
            '30' => '日期',
        )
    ),
    'czbz'=>array(
        'sfields'=>array(2=>'text','3'=>'text','chtime'=>'text'),//搜索的字段
        'header'=>array(
            'zqdm'=> '证券代码',
            'zqmc' => '证券名称',
            'time' => '日期',
            'ts' => 'ts',
            'content' => '内容',
        )
    ),
    'jgdc'=>array(
        'sfields'=>array(2=>'text','3'=>'text','chtime'=>'text'),//搜索的字段
        'groups' =>
        array (
            'date' => '日期',
            '_forsum' => '求和',
        ),
        'sumops'=>array(
            2 => 'count',
            6 => 'sum',
            'zxsz' => 'sum',
            '8'=>'sum',
            'fdyk' => 'sum',
            'zcbl' => 'sum',
            10 => 'sum',
            11 => 'sum',
            12 => 'sum',
        ),
        'header'=>array(
            2 => '证券代码',
            3 => '证券名称',
            6 => '证券数量',
            '4' => '收盘价',
            'pdate' => '价格日期',
            'zxsz' => '市值',
            '8'=>'清算额',
            '_forsum'=>'求和',

            'ljmr' => '累计买入',
            'fdyk' => '浮动盈亏',
            'ykbl' => '盈亏比例(%)',
            /*
            'zcbl' => '占仓比例(%)',
            'cdate' =>'开始建仓',
            'chtime'=>'持仓天数',
             */
            'ldate'=>'最后操作',
            10 => '佣金',
            11 => '印花税',
            12 => '过户费',
        ),
    ),

);

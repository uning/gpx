require.config({
    paths: {
        gridSSHQ: 'asset/js/gridSSHQ.conf',
        bootstrap: 'node_modules/bootstrap/dist/js/bootstrap',
        'grid.locale-en': 'node_modules/jqGrid/js/i18n/grid.locale-en',
        'grid.locale-cn': 'node_modules/jqGrid/js/i18n/grid.locale-cn',
        'jquery.jqGrid': 'node_modules/jqGrid/',
        jquery: 'node_modules/jquery/dist/jquery',
        'jquery-ui': 'node_modules/jquery-ui-dist/jquery-ui',
        'ui.multiselect': 'node_modules/multiselect/js/jquery.multi-select',
        css: 'node_modules/require-css/css',
        'css-builder': 'node_modules/require-css/css-builder',
        normalize: 'node_modules/require-css/normalize',
        requirejs: 'node_modules/requirejs/require',
        view: 'asset/js/view',
        log: 'asset/js/lib/Log/log'
    },
    map: {
        '*': {
            css: 'node_modules/require-css/css'
        }
    },
    shim: {
        bootstrap: {
            deps: [
                'jquery',
                ,'css!node_modules/bootstrap/dist/css/bootstrap.css'
                //,'css!node_modules/bootstrap/dist/css/bootstrap-theme.css'
            ]
        },
        'jquery.jqGrid': {
            deps: [
                'jquery-ui',
                'ui.multiselect',
                'grid.locale-cn',
                'css!node_modules/jqGrid/css/ui.jqGrid.css'
            ]
        },
        'jquery-ui': {
            deps: [
                'jquery',
                'css!node_modules/jquery-ui-dist/jquery-ui.theme.css'
            ]
        },
        'ui.multiselect': {
            deps: [
                'jquery-ui',
                'css!node_modules/multiselect/css/multi-select.css'
            ]
        },
        'grid.locale-cn': {
            deps: [
                'jquery'
            ]
        },
        log: {
            deps: [
                'asset/js/lib/Log/delegator',
                'asset/js/lib/Log/jsDump-1.0.0'
            ]
        }
    },
    packages: [

    ]
});

requirejs(['jquery-ui',],function(){
    $(function(){
        $(document).tooltip();
    });
});


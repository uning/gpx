require.config({
    paths: {
        gridSSHQ: 'asset/js/gridSSHQ.conf',
        bootstrap: 'bower_components/bootstrap/dist/js/bootstrap',
        'grid.locale-en': 'bower_components/jqgrid/js/i18n/grid.locale-en',
        'jquery.jqGrid': 'bower_components/jqgrid/js/jquery.jqGrid',
        jquery: 'bower_components/jquery/dist/jquery',
        'jquery-ui': 'bower_components/jquery-ui/jquery-ui',
        'ui.multiselect': 'bower_components/multiselect/js/ui.multiselect',
        css: 'bower_components/require-css/css',
        'css-builder': 'bower_components/require-css/css-builder',
        normalize: 'bower_components/require-css/normalize',
        requirejs: 'bower_components/requirejs/require',
        'grid.locale-cn': 'bower_components/jqgrid/js/i18n/grid.locale-cn',
        view: 'asset/js/view',
        log: 'asset/js/lib/Log/log'
    },
    map: {
        '*': {
            css: 'bower_components/require-css/css'
        }
    },
    shim: {
        bootstrap: {
            deps: [
                'jquery',
                'css!bower_components/bootstrap/dist/css/bootstrap.css',
                'css!bower_components/bootstrap/dist/css/bootstrap-theme.css'
            ]
        },
        'jquery.jqGrid': {
            deps: [
                'jquery-ui',
                'ui.multiselect',
                'grid.locale-cn',
                'css!bower_components/jqgrid/css/ui.jqgrid.css'
            ]
        },
        'jquery-ui': {
            deps: [
                'jquery',
                'css!bower_components/jquery-ui/themes/redmond/jquery-ui.css'
            ]
        },
        'ui.multiselect': {
            deps: [
                'jquery-ui',
                'css!bower_components/multiselect/css/ui.multiselect.css'
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


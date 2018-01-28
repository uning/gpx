(function($,undefined){
    var menudata = leftmenu || [];
    var id2menudata = {};
    menudata.forEach(function(v){
        id2menudata[v.id] = v;

    });

    function pageInit(){
        createList();
        addAction();
    }
    function addAction(){
        $(".item-close").click(function(){
            closeTab();
        });
        var timer = null;
        $(document).on("click",'.right-pane-title>div',function(event){
            //console.log('click ',$(this).attr("src"),event.detail);
           //*
            clearTimeout(timer); // 这里加一句是为了兼容 Gecko 的浏览器 / 
            if (timer){ 
                // dblclick 事件的处理 
                clearTimeout(timer); 
                timer = null;
                closeTab();
                return ;
            }
            var url = $(this).attr("src");
            timer = setTimeout(function() { 
                clearTimeout(timer); 
                timer = null;
                // click 事件的处理 
                //selectTab($url);
            }, 300);
            selectTab(url);
        });
    }
    function createList(){
        console.log('createList');
        var showfield = "cntext";
        var timer = null;
        $("#listMenu").listMenu({
            parentField:"pid",
            idField:"id",
            captionField:"text",
            rootId:"root",
            multSelect: true,
            onCreateText:function(data){
                return data[showfield];
            },
            onClickItem:function(data){
                clearTimeout(timer); // 这里加一句是为了兼容 Gecko 的浏览器 / 
                if (timer){ 
                    // dblclick 事件的处理 
                    clearTimeout(timer); 
                    timer = null;
                    if(data.url){
                        //addTab(title,ctx+data.url,true);
                        window.open(data.url);
                    }
                    return ;
                }
                timer = setTimeout(function() { 
                    clearTimeout(timer); 
                    timer = null;
                    if(data.url){
                        var title = data[showfield];
                        if(data.pid != 'root'){
                            title = id2menudata[data.pid][showfield] + '>' +title;
                        }
                        addTab(title,ctx+data.url,true);
                    }
                    // click 事件的处理 
                }, 300);
            }
        });//初始化
        loadMenu();
    }
    function loadMenu(){

        $("#listMenu").listMenu("load",menudata);
    }

    var tabUrlList = {};
    var tabLength = 0;
    var defurl = '?action=view&coll=wucunLs',deftitle='五村流水>查看';
    addTab(deftitle,defurl);

    function addTab(title,url,fresh){
        if(tabLength>8){
            alert("请先尝试关闭一些页面，再打开新页面");
            return;
        }
        if(tabUrlList[url]){
            selectTab(url,true);
            return;
        }
        tabUrlList[url] = "1";
        tabLength++;
        var titleDom = $('<div class="title-item" style=" ">窗口1</div>');
        var contentDom = $('<iframe src="" frameborder="0"></iframe>');

        titleDom.attr({
            src:url,
            title:title
        }).html(title);
        contentDom.attr({
            src:url
        });
        $(".right-pane-title").append(titleDom);
        $(".right-pane-content").append(contentDom);
        selectTab(url);

    }
    function selectTab(url,refresh){
        var old = $(".right-pane-title .selected");
        old.removeClass("selected");
        $(".right-pane-title").find("[src='"+url+"']").addClass("selected");
        $(".right-pane-content iframe").hide();
        var page =$(".right-pane-content").find("[src='"+url+"']");
        if(refresh)
            page.attr('src',url);
        page.show();

    }
    function closeTab(){
        var selected = $(".right-pane-title .selected");
        if(selected.attr("freeze") === "true"){
            return;
        }
        var next = selected.next();
        var prev = selected.prev();
        if(next.length>0){
            tabUrlList[selected.attr("src")] = null;
            tabLength--;
            next.addClass("selected");
            $(".right-pane-content").find("[src='"+selected.attr("src")+"']").remove();
            selected.remove();
            selectTab(next.attr("src"));
        }else if(prev.length>0){
            tabUrlList[selected.attr("src")] = null;
            tabLength--;
            $(".right-pane-content").find("[src='"+selected.attr("src")+"']").remove();
            selected.remove();
            selectTab(prev.attr("src"));
        }
    }
    pageInit();
})(jQuery);

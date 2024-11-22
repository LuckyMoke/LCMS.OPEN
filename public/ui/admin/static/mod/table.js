var lcmsTableAjaxAct=function(tableId,Act,defAct){switch(Act){case"close":setTimeout((function(){var tables=$("table.lcms-table",parent.document);if(tables.length>0){tables.each((function(){if($(this).hasClass("lcms-table-tree")){parent.layui.treeTable.reloadData($(this).attr("id"))}else{parent.layui.table.reloadData($(this).attr("id"))}}));LCMS.util.iframe({do:"close"})}else{LCMS.util.iframe({do:"close",reload:true})}}),100);break;case"reload":setTimeout((function(){var exts=$("#"+tableId).attr("data-exts");if(exts){layui[exts].reloadData(tableId)}}),100);break;case"reload-page":setTimeout((function(){window.location.reload()}),100);break;case"reload-parent":setTimeout((function(){parent.window.location.reload()}),100);break;case"reload-top":setTimeout((function(){top.window.location.reload()}),100);break;case"goback":setTimeout((function(){window.location.href=document.referrer}),100);break;default:defAct&&setTimeout((function(){defAct()}),100);break}},lcmsTableAjax=function(tableId,data,arr){LCMS.util.ajax({type:"POST",url:data.url,data:{LC:arr},timeout:data.timeout,success:function(res){if(1==res.code){LCMS.util.notify({content:res.msg});lcmsTableAjaxAct(tableId,res.go)}else if(2==res.code){LCMS.util.notify({content:res.msg});setTimeout((function(){LCMS.util.getFun(res.go)(res.data)}),res.msg?100:1)}else{res.msg&&LCMS.util.notify({type:"error",content:res.msg})}}})};$(".lcms-table").each((function(){top.LCMSUIINDEX++;let tableId="LCMSTABLE"+top.LCMSUIINDEX,_this=$(this),data=JSON.parse(_this.attr("data")),layuiTable=layui.table,loaded=false;if(location.hash){data.page=location.hash.replace("#!lcmstablepage=","")}_this.attr({id:tableId,"lay-filter":tableId});if(data.defaultToolbar){let exbtn=data.defaultToolbar[data.defaultToolbar.length-1];if(exbtn&&"exports"==exbtn.name){const exfun=LCMS.util.getFun(exbtn.onClick);data.defaultToolbar[data.defaultToolbar.length-1].onClick=function(obj){exfun(obj)}}}if(_this.hasClass("lcms-table-tree")){let openClose=true;layuiTable=layui.treeTable;layuiTable.render({id:tableId,elem:"#"+tableId,url:data.url,defaultToolbar:data.defaultToolbar,totalRow:data.totalRow,toolbar:data.toolbar,page:false,limit:0,cols:[data.cols],tree:{customName:{id:"id",pid:data.pid||"pid",name:data.show||"title",rootId:0},view:{iconClose:`<img src="/public/static/images/icons/folder.svg" />`,iconOpen:`<img src="/public/static/images/icons/folder-open.svg" />`,iconLeaf:`<img src="/public/static/images/icons/file.svg" />`,dblClickExpand:false},data:{isSimpleData:true,cascade:"children"}},escape:false,autoSort:data.autoSort,cellExpandedMode:"tips",before:function(options){data.before&&LCMS.util.getFun(data.before)(options)},parseData:function(res){for(var i=0;i<res.data.length;i++){res.data[i]["id"]=parseInt(res.data[i].id);res.data[i][data.pid]=parseInt(res.data[i][data.pid])}if(res.data.length>50){loaded=true;openClose=false}return res},done:function(res){if(!loaded){loaded=true;layuiTable.expandAll(tableId,true);_this.siblings(".layui-table-view").find(".layui-table-box").css("min-height","auto");_this.parent().attr("data-loaded","true")}LCMS.util.lazyImg("lazyload");data.done&&LCMS.util.getFun(data.done)(this,res,curr,count)},error:function(e,msg){data.error&&LCMS.util.getFun(data.error)(e,msg)},complete:function(xhr,ts){data.complete&&LCMS.util.getFun(data.complete)(this,xhr,ts)}});var LCMSTREETABLE_EXPAND=function(obj){layuiTable.expandAll(obj.config.id,openClose?false:true);openClose=openClose?false:true}}else{layuiTable.render({id:tableId,elem:"#"+tableId,url:data.url,defaultToolbar:data.defaultToolbar,totalRow:data.totalRow,toolbar:data.toolbar,page:{theme:"#409eff",curr:parseInt(data.page),hash:"lcmstablepage"},limit:data.limit,limits:[20,50,100,300,500,700,1000],cols:[data.cols],escape:false,autoSort:data.autoSort,cellExpandedMode:"tips",before:function(options){data.before&&LCMS.util.getFun(data.before)(options)},done:function(res,curr,count){var limit=layuiTable.getOptions(tableId).limit,pageMax=Math.ceil(count/limit);if(pageMax>0&&pageMax<curr){layuiTable.reloadData(tableId,{where:{},page:{theme:"#409eff",curr:pageMax,hash:"lcmstablepage"}});window.location.hash="!lcmstablepage="+pageMax}if(!loaded){loaded=true;_this.siblings(".layui-table-view").find(".layui-table-box").css("min-height","auto");_this.siblings(".layui-table-view").find(".lcms-table-toolbar-search").css({opacity:1});_this.parent().attr("data-loaded","true")}LCMS.util.lazyImg("lazyload");data.done&&LCMS.util.getFun(data.done)(this,res,curr,count)},error:function(e,msg){data.error&&LCMS.util.getFun(data.error)(e,msg)},complete:function(xhr,ts){data.complete&&LCMS.util.getFun(data.complete)(this,xhr,ts)}})}var toolSelf=_this.siblings(".layui-table-view").find(".layui-table-tool-self"),toolForm=_this.siblings(".lcms-table-toolbar-search-tpl"),toolTpl=toolForm.html(),toolTpls=toolForm.attr("data-tpls"),toolTplsData={};if(toolTpls){toolTpls=toolTpls.split(",");for(let i=0;i<toolTpls.length;i++){const id=toolTpls[i],tpl=$(`${id}`);if(id){toolTplsData[id]=tpl.length>0?tpl.html():""}}}toolSelf.prepend(LCMS.util.tpl(toolTpl,toolTplsData));if(toolForm.hasClass("lcms-table-tool-self-fixed")){let thisForm=toolSelf.children("form");thisForm.css({opacity:0});toolSelf.addClass("lcms-table-tool-self-fixed");toolSelf.css({paddingTop:thisForm.height()})}layuiTable.on(`toolbar(${tableId})`,(function(obj){var data=$(this).data(),checkStatus=layuiTable.checkStatus(obj.config.id);switch(obj.event){case"LCMSTABLE_SEARCHOPEN":layer.open({title:"搜索",type:1,shade:0,area:"300px",resize:false,content:toolSelf.find(".lcms-table-toolbar-search-box")});break;case"LCMSTABLE_REFRESH":if(toolSelf.children("form")[0]){toolSelf.children("form")[0].reset()}let tdata=layuiTable.getOptions(tableId);let opts={where:{},lcms_search_where:{},page:{theme:"#409eff",curr:1,hash:"lcmstablepage"}};if(tdata.initSort){tdata.initSort.type=null;opts.initSort=tdata.initSort}layuiTable.reloadData(tableId,opts);break;case"LCMSTABLE_REFRESH_TREE":layuiTable.reloadData(tableId);break;case"LCMSTREETABLE_EXPAND":LCMSTREETABLE_EXPAND(obj);break;case"ajax":if(data.tips){$(this).hasClass("layui-btn-danger")&&LCMS.util.notify({type:"flash"});layer.alert(data.tips,{title:$(this).attr("data-title"),area:"120px"},(function(){lcmsTableAjax(tableId,data,checkStatus.data)}))}else{lcmsTableAjax(tableId,data,checkStatus.data)}break;case"iframe":let area=null;if(data.area){area=data.area.split(",");if(area.length<2){area=area[0]}}LCMS.util.iframe({title:$(this).attr("data-title"),url:data.url,area:area});break;case"href":if("_blank"==data.target){window.open(data.url)}else if("_router"==data.target){LCMS.plugin.router(data.url)}else{window.location.href=data.url}break;default:obj.data=checkStatus.data;LCMS.util.getFun(obj.event)(obj);break}}));layuiTable.on(`tool(${tableId})`,(function(obj){var data=$(this).data();if(data.url){data.url=LCMS.util.tpl(data.url,obj.data)}switch(obj.event){case"ajax":if(data.tips){$(this).hasClass("layui-btn-danger")&&LCMS.util.notify({type:"flash"});layer.alert(LCMS.util.tpl(data.tips,obj.data),{title:($(this).attr("data-title")?$(this).attr("data-title"):"")+(obj.data.id?" [ID:"+obj.data.id+"]":""),area:"120px"},(function(){lcmsTableAjax(tableId,data,obj.data)}))}else{lcmsTableAjax(tableId,data,obj.data)}break;case"iframe":let area=null;if(data.area){area=data.area.split(",");if(area.length<2){area=area[0]}}LCMS.util.iframe({title:$(this).attr("data-title")?$(this).attr("data-title"):"",url:data.url+"&id="+obj.data.id,area:area});break;case"href":data.url=`${data.url}&id=${obj.data.id}`;if("_blank"==data.target){window.open(data.url)}else if("_router"==data.target){LCMS.plugin.router(data.url)}else{window.location.href=data.url}break;default:LCMS.util.getFun(obj.event)(obj);break}}));layuiTable.on(`edit(${tableId})`,(function(obj){LCMS.util.ajax({type:"POST",url:data.url+"-save",data:{LC:{id:obj.data.id,name:obj.field,value:obj.value}},success:function(res){if(1==res.code){LCMS.util.notify({content:res.msg});lcmsTableAjaxAct(tableId,res.go)}else{LCMS.util.notify({type:"error",content:res.msg})}}})}));layuiTable.on(`sort(${tableId})`,(function(obj){if(data.autoSort){LCMS.util.lazyImg("lazyload")}else{let search=Object.assign({},obj.config.lcms_search_where||{});layuiTable.reloadData(tableId,{initSort:obj,where:Object.assign(search,{[obj.field]:obj.type})})}}));layui.form.on("submit(LCMSTABLE_SEARCH)",(function(obj){let tdata=layuiTable.getOptions(tableId);layuiTable.reloadData(tableId,{where:Object.assign(tdata.where||{},obj.field),lcms_search_where:obj.field,page:{theme:"#409eff",curr:1,hash:"lcmstablepage"}});return false}));layui.form.on("switch(LCMSTABLE_SWITCH)",(function(obj){var elem=$(obj.elem),data=elem.data();if(data.url.length>0){LCMS.util.ajax({type:"POST",url:data.url,data:{LC:{id:LCMS.util.getQuery("id",data.url),name:LCMS.util.getQuery("name",data.url),value:elem.is(":checked")?"1":"0"}},timeout:data.timeout,success:function(res){if(1==res.code){LCMS.util.notify({content:res.msg});lcmsTableAjaxAct(tableId,res.go,(function(){if(res.go){window.location.href=res.go}}))}else if(2==res.code){LCMS.util.notify({content:res.msg});elem.prop("checked",elem.is(":checked")?false:true);layui.form.render("checkbox");setTimeout((function(){LCMS.util.getFun(res.go)(res.data)}),res.msg?100:1)}else{res.msg&&LCMS.util.notify({type:"error",content:res.msg});elem.prop("checked",elem.is(":checked")?false:true);layui.form.render("checkbox")}}})}}))}));if($(".lcms-table-toolbar-date").length>0){$(".lcms-table-toolbar-date").each((function(){var input=$(this).children("input")[0],data=$(input).data();layui.laydate.render({elem:input,trigger:"click",type:data.type?data.type:"date",range:data.range?"1"==data.range?true:data.range:false,rangeLinked:data.range?true:false,min:data.min?data.min:"1900-1-1",max:data.max?data.max:"2099-12-31",calendar:true,theme:["#409eff","#edf6ff"]})}))}$(".lcms-table-box").on({mouseenter:function(){if($(this).children("i.layui-icon").length>0){let title=$(this).attr("data-title");title&&layer.tips(title,$(this),{tips:[1,"#303133"],time:0})}},mouseleave:function(){layer.closeAll("tips")}},"button");$(".lcms-table-box").on({mousedown:function(){const tableId=$(this).children("table").attr("id");LCMS.plugin=Object.assign(LCMS.plugin,{table:{focusid:tableId}})}});
$('.lcms-form-table').each(function(index){var id='LCMSTABLE'+index,data=JSON.parse($.base64.decode($(this).attr('data'))),search=$(this).prev('.lcms-form-table-toolbar-search');$(this).attr({'id':id,'lay-filter':id});var mytable=layui.table.render({id:id,elem:'#'+id,url:data.url,defaultToolbar:data.defaultToolbar,totalRow:data.totalRow,toolbar:data.toolbar,page:{theme:'#409eff',cols:data.page},limit:data.limit,limits:[20,50,100,300,500,700,1000],cols:[data.cols],});search.show();var Tableajax=function(url,arr){LJS._post(url,{'LC':arr},function(res){if(res.code=='1'){res.msg&&LJS._tips(res.msg);if(res.go){switch(res.go){case'close':LJS._lazydo(function(){LJS._closeframe(true)});break;case'reload':LJS._lazydo(function(){window.location.reload()});break;case'reload-parent':LJS._lazydo(function(){parent.window.location.reload()});break;case'reload-top':LJS._lazydo(function(){top.window.location.reload()});break;case'goback':LJS._lazydo(function(){history.back()});break;default:LJS._lazydo(function(){layui.table.reload(id)});break}}}else if(res.code=='2'){res.msg&&LJS._tips(res.msg);LJS._lazydo(function(){eval(res.go)(res.data)},res.msg?0:1)}else{res.msg&&LJS._tips(res.msg,0)};layer.closeAll()})};layui.table.on('toolbar('+id+')',function(obj){var data=$(this).data(),checkStatus=layui.table.checkStatus(obj.config.id);switch(obj.event){case'ajax':if(data.tips){layer.alert(data.tips,{title:'操作数据',area:'120px'},function(){Tableajax(data.url,checkStatus.data)})}else{Tableajax(data.url,checkStatus.data)};break;case'iframe':LJS._iframe(data.url,$(this).html());break;case'href':window.location.href=data.url;break}});layui.table.on('tool('+id+')',function(obj){var data=$(this).data();data.url=data.url.replace(/{id}/g,obj.data.id);switch(obj.event){case'ajax':if(data.tips){layer.alert(data.tips,{title:'操作数据'+(obj.data.id?' [ID:'+obj.data.id+']':''),area:'120px'},function(){Tableajax(data.url,obj.data)})}else{Tableajax(data.url,obj.data)};break;case'iframe':LJS._iframe(data.url+'&id='+obj.data.id,$(this).html());break;case'href':window.location.href=data.url+'&id='+obj.data.id;break}});layui.table.on('edit('+id+')',function(obj){LJS._post(data.url+'-save',{'LC':{'id':obj.data.id,'name':obj.field,'value':obj.value}},function(res){if(res.code=='1'){LJS._tips(res.msg)}else{LJS._tips(res.msg,0)}})});search.on('click','.LCMSTABLESEARCHRESET',function(){search[0].reset()});layui.form.on('submit(LCMSTABLESEARCH)',function(data){layui.table.reload(id,{where:data.field,page:{curr:1}});if($(this).parent('.__form').next('.__icon').css('display')=='block'){$(this).parent('.__form').fadeOut()};return false});$(this).parent('.lcms-form-table-box').on("click",".layui-form-switch",function(){var elm=$(this).prev('input');var url=elm.attr('data-url');if(url.length>0){LJS._post(url,{'LC':{'id':LJS._getQuery('id',url),'name':LJS._getQuery('name',url),'value':elm.is(':checked')?'1':'0'}},function(res){if(res.code=='1'){res.msg&&LJS._tips(res.msg);if(res.go){switch(res.go){case'close':LJS._lazydo(function(){LJS._closeframe(true)});break;case'reload':LJS._lazydo(function(){window.location.reload()});break;case'reload-parent':LJS._lazydo(function(){parent.window.location.reload()});break;case'reload-top':LJS._lazydo(function(){top.window.location.reload()});break;case'goback':LJS._lazydo(function(){history.back()});break;default:LJS._lazydo(function(){window.location.href=res.go});break}}}else if(res.code=='2'){res.msg&&LJS._tips(res.msg);elm.prop("checked",elm.is(':checked')?false:true);layui.form.render('checkbox');LJS._lazydo(function(){eval(res.go)(res.data)},res.msg?0:1)}else{res.msg&&LJS._tips(res.msg,0);elm.prop("checked",elm.is(':checked')?false:true);layui.form.render('checkbox')}})}});if($(this).prev('.lcms-form-table-toolbar-search').length>0){var that=$(this).prev('.lcms-form-table-toolbar-search');if(that.children('.LCMSTABLESEARCHICON').css('display')=="block"){$('body').append('<style>div[lay-id="'+id+'"] .layui-table-tool{height:59px}</style>')}}else{var that=$(this).next('.layui-form').children('.layui-table-tool');if(that.children('.layui-table-tool-temp').children('button').length<=0){$('body').append('<style>div[lay-id="'+id+'"] .layui-table-tool{padding:0;}</style>')}}});if($('.LCMSTABLESEARCHICON').length>0){$('.LCMSTABLESEARCHICON').on('click',function(){$(this).prev('.__form').fadeIn()})};if($('.lcms-form-table-toolbar-search .__close').length>0){$('.lcms-form-table-toolbar-search .__close').on('click',function(){$(this).parent('.__form').fadeOut()})};if($('.lcms-form-table-toolbar-date').length>0){$('.lcms-form-table-toolbar-date').each(function(){var input=$(this).children('input')[0];layui.laydate.render({elem:input,trigger:'click',range:true,calendar:true,theme:'#67c23a'})})};
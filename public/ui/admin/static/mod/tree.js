layui.extend({treeGrid:'treeGrid/treeGrid'}).use(['treeGrid'],function(){$('.lcms-form-table-tree-box').each(function(index){var AjaxAct=function(tableId,Act,defAct){switch(Act){case'close':var tables=$('table.lcms-form-table',parent.document),trees=$('table.lcms-form-table-tree',parent.document);if(tables.length>0||trees.length>0){tables.each(function(){parent.layui.table.reloadData($(this).attr('id'))});trees.each(function(){parent.layui.treeGrid.query($(this).attr('id'))});LJS._closeframe()}else{LJS._closeframe(true)}break;case'reload':LJS._lazydo(function(){layui.treeGrid.query(tableId)});break;case'reload-page':LJS._lazydo(function(){window.location.reload()});break;case'reload-parent':LJS._lazydo(function(){parent.window.location.reload()});break;case'reload-top':LJS._lazydo(function(){top.window.location.reload()});break;case'goback':LJS._lazydo(function(){window.location.href=document.referrer});break;default:defAct&&LJS._lazydo(function(){defAct()});break}};var Treeajax=function(tableId,url,arr){LJS._post(url,{'LC':arr},function(res){if(res.code=='1'){res.msg&&LJS._tips(res.msg);AjaxAct(tableId,res.go)}else if(res.code=='2'){res.msg&&LJS._tips(res.msg);LJS._lazydo(function(){eval(res.go)(res.data)},res.msg?0:1)}else{res.msg&&LJS._tips(res.msg,0)}layer.closeAll()})};top.LCMSUIINDEX++;var cols=[],tableId='LCMSTABLETREE'+top.LCMSUIINDEX,that=$(this),table=$(this).children('.lcms-form-table-tree'),openall=$(this).children('.lcms-form-table-tree-openall'),form=$(this).children('form');var data=JSON.parse(table.attr('data'));table.attr('id',tableId);openall.attr('data-id',tableId);data.id&&$(this).attr('id',data.id);cols[0]={type:'checkbox',sort:true};for(var i=1;i<=data.cols.length;i++){var myindex=i-1;if(data.cols[myindex].toolbar){cols[i]={title:data.cols[myindex].title,templet:function(){return data.cols[myindex].toolbar}}}else{cols[i]=data.cols[myindex]}};var treeId=data.top?data.top.split('|'):[];layui.treeGrid.render({id:tableId,elem:'#'+tableId,idField:'id',url:data.url+'&page=1&limit=1000',treeId:treeId[1]?treeId[0]:'id',treeUpId:data.top?(treeId[1]?treeId[1]:data.top):'top_id',treeShowName:data.show?data.show:'title',isOpenDefault:true,branch:['',''],leaf:'&#xe60a;',even:true,cols:[cols],page:false});layui.treeGrid.on('tool('+tableId+')',function(obj){var data=$(this).data();data.url=data.url?data.url.replace(/{id}/g,obj.data.id):'';switch(obj.event){case'ajax':if(data.tips){layer.alert(data.tips,{area:'120px'},function(){Treeajax(tableId,data.url,obj.data)})}else{Treeajax(tableId,data.url,obj.data)};break;case'iframe':let area=null;if(data.area){area=data.area.split(',');if(area.length<2){area=area[0]}};LJS._iframe(data.url+'&id='+obj.data.id,$(this).attr('data-title'),0,area);break;case'href':window.location.href=data.url+'&id='+obj.data.id;break;case'switch':var elm=$(this).prev('input');var url=elm.attr('data-url');url=url?url.replace(/{id}/g,obj.data.id):'';if(url.length>0){LJS._post(url,{'LC':{'id':LJS._getQuery('id',url),'name':LJS._getQuery('name',url),'value':elm.is(':checked')?'1':'0'}},function(res){if(res.code=='1'){res.msg&&LJS._tips(res.msg);AjaxAct(tableId,res.go,function(){if(res.go){window.location.href=res.go}})}else if(res.code=='2'){res.msg&&LJS._tips(res.msg);elm.prop("checked",elm.is(':checked')?false:true);layui.form.render('checkbox');LJS._lazydo(function(){eval(res.go)(res.data)},res.msg?0:1)}else{res.msg&&LJS._tips(res.msg,0);elm.prop("checked",elm.is(':checked')?false:true);layui.form.render('checkbox')}})}break}});layui.treeGrid.on('edit('+tableId+')',function(obj){LJS._post(data.url+'-save',{'LC':{'id':obj.data.id,'name':obj.field,'value':obj.value}},function(res){if(res.code=='1'){LJS._tips(res.msg)}else{LJS._tips(res.msg,0)}})});openall.on('click',function(){var treedata=layui.treeGrid.getDataTreeList(tableId);layui.treeGrid.treeOpenAll(tableId,!treedata[0][layui.treeGrid.config.cols.isOpen])});$(this).children('button').on('click',function(){var event=$(this).attr('lay-event');var data=$(this).data();switch(event){case'ajax':var checkStatus=layui.treeGrid.checkStatus(tableId);if(data.tips){layer.alert(data.tips,{area:'120px'},function(){Treeajax(tableId,data.url,checkStatus.data)})}else{Treeajax(tableId,data.url,checkStatus.data)};break;case'iframe':let area=null;if(data.area){area=data.area.split(',');if(area.length<2){area=area[0]}};LJS._iframe(data.url,$(this).attr('data-title'),0,area);break;case'href':window.location.href=data.url;break}})})});$('.lcms-form-table-tree-box').on({mouseenter:function(){if($(this).children('i.layui-icon').length>0){let title=$(this).attr('data-title');title&&layer.tips(title,$(this),{tips:[1,'#303133'],time:0})}},mouseleave:function(){layer.closeAll('tips')}},'button');
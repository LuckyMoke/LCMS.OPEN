let setUrl=function(url){if(self==parent){location.href=url}else{parent.history.pushState(null,null,"?"+$.base64.encode(url.match(/\?(.*)/)[0]));parent.LBOX.attr("src",url)}};if($(".app-list").length>0){$(".app-list .li>a").on("click",function(){setUrl($(this).attr("href"));return false});$(".app-list .li").on({mouseenter:function(){var tips=$(this).attr("data-description");tips&&layer.tips(tips,this,{tips:[1,"#303133"]})},mouseleave:function(){layer.closeAll("tips")},});if($(".app-list").attr("data-update")==1){setTimeout(function(){var now=Math.round(new Date().getTime()/1000),url=LCMS.url.own+"t=sys&n=appstore&c=store&a=",applist=[],appli=$(".app-list .li"),cache=layui.data("LCMS_cache",{key:"appstore_check_home"}),openLayer=function(data){appli.each(function(){var app=$(this).data();if(data[app.name]&&data[app.name].ver>app.ver){var index=layer.confirm("检测到新的应用版本，是否更新？",{title:"更新提示",btn:"立即更新"},function(){layer.close(index);setUrl(url+"&c=local")});return false}})};cache=cache||{};if(cache.expired>=now){openLayer(cache.data)}else{appli.each(function(index){var data=$(this).data();applist[index]={name:data.name,ver:data.ver}});if(applist.length>0){LCMS.util.ajax({type:"POST",url:url+"check&type=index",data:{applist:JSON.stringify(applist)},layer:true,success:function(res){if(res.code==1){layui.data("LCMS_cache",{key:"appstore_check_home",value:{data:res.data,expired:now+43200}});openLayer(res.data)}},})}}},1000)}}
let setUrl=function(url){parent.history.pushState(null,null,"?"+$.base64.encode(url.match(/\?(.*)/)[0]));parent.LBOX.attr("src",url)};if($(".app-list").length>0){$(".app-list .li>a").on("click",function(){setUrl($(this).attr("href"));return false});if($(".app-list").attr("data-update")==1){setTimeout(function(){var now=Math.round(new Date().getTime()/1000),url=LCMS.url.own+"t=sys&n=appstore&c=store&a=",applist=[],appli=$(".app-list .li");if(now<localStorage.getItem("lcms-update")&&now>localStorage.getItem("lcms-app-update")){appli.each(function(index){applist[index]=$(this).data()});if(applist.length>0){LCMS.util.ajax({type:"POST",url:url+"check",data:{applist:JSON.stringify(applist),},layer:true,success:function(res){localStorage.setItem("lcms-app-update",now+86400);if(res.code==1){appli.each(function(){var app=$(this).data();if(res.data[app.name]&&res.data[app.name].ver>app.ver){var index=parent.layer.confirm("检测到一些应用有新的版本，是否更新？",{title:"更新提示",btn:["立即更新","7天不提醒"]},function(){parent.layer.close(index);setUrl(url+"&c=local")},function(){localStorage.setItem("lcms-app-update",now+7*86400);parent.layer.close(index)})}})}},})}}},1000)}}
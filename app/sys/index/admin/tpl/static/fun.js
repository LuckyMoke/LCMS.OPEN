let urlHash=location.search,setUrl=function(url){history.pushState(null,null,"?"+$.base64.encode(url.match(/\?(.*)/)[0]));LBOX.attr("src",url)};if(urlHash&&urlHash.indexOf("&")!=-1){urlHash=urlHash.substring(0,urlHash.indexOf("&"))}urlHash=urlHash.replace("?","");urlHash=$.base64.decode(urlHash).replace("?","");if(urlHash){LBOX.attr("src",LCMS.url.own+urlHash)}else{setUrl($("header .main-menu").attr("data-url"))}if($("header .main-menu a").length>0){$("header .main-menu a").on("click",function(){var url=$(this).attr("data-url");if(url){$("header .main-menu a").removeClass("active");$(this).addClass("active");setUrl(url)}});var menus=$("header .main-menu>ul>li>a");for(let i=0;i<menus.length;i++){$(".main-menu-mobile dl").append("<dd>"+menus[i].outerHTML+"</dd>")}$("header .main-menu-mobile a").on("click",function(){var url=$(this).attr("data-url");url&&setUrl(url)})}if($("body").attr("data-update")==1){urlHash!="t=sys&n=update&c=gitee&a=index"&&setTimeout(function(){var now=Math.round(new Date().getTime()/1000),url=LCMS.url.own+"t=sys&n=update&c=gitee&a=",cache=layui.data("LCMS_cache",{key:"update_check"}),openLayer=function(code){if(code==1){var index=layer.confirm("检测到新的框架版本，是否更新？",{title:"更新提示",btn:"立即更新"},function(){setUrl(url+"index");layer.close(index)})}};cache=cache||{};if(cache.expired>=now){openLayer(cache.code)}else{LCMS.util.ajax({type:"GET",url:url+"cloud&action=check&type=index",layer:true,success:function(res){layui.data("LCMS_cache",{key:"update_check",value:{code:res.code==1?1:0,expired:now+86400}});openLayer(res.code)},})}},1000)}window.addEventListener("storage",function(e){if(e.storageArea===localStorage&&e.key=="LCMS_user"){setTimeout(()=>{top.location.reload()},500)}});
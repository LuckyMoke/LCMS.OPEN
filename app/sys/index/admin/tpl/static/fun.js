LCMS.plugin.setUrl=function(url,opt){if(url){opt=opt||{};opt.before&&opt.before();let query=url.match(/\?(.*)/),isOwn=url.indexOf(LCMS.url.site)!==-1?true:false;query=isOwn?$.base64.encode(query[0]):$.base64.encode(url);history.pushState(null,null,`?${query}`);LBOX.attr("src",url);if(isOwn){const index=LCMS.util.loading();LBOX.on("load",function(){LCMS.util.loading("close",index);opt.loaded&&opt.loaded()})}else{opt.loading&&LCMS.util.loading(opt.loading)}}};let urlHash=location.search;if(urlHash&&urlHash.indexOf("&")!==-1){urlHash=urlHash.substring(0,urlHash.indexOf("&"))}urlHash=urlHash.replace("?","");if(urlHash){urlHash=$.base64.decode(urlHash);try{new URL(urlHash);LBOX.attr("src",urlHash)}catch(e){urlHash=urlHash.replace("?","");LBOX.attr("src",LCMS.url.own+urlHash)}}else{LCMS.plugin.setUrl($("header .main-menu").attr("data-url"))}if($("header .main-menu a").length>0){$("header .main-menu a").on("click",function(){let _this=$(this);LCMS.plugin.setUrl(_this.attr("data-url"),{before:function(){$("header .main-menu a").removeClass("active");_this.addClass("active")},})});var menus=$("header .main-menu>ul>li>a");for(let i=0;i<menus.length;i++){$(".main-menu-mobile dl").append("<dd>"+menus[i].outerHTML+"</dd>")}$("header .main-menu-mobile a").on("click",function(){LCMS.plugin.setUrl($(this).attr("data-url"))})}if($("body").attr("data-update")==1){urlHash!="t=sys&n=update&c=gitee&a=index"&&setTimeout(function(){var now=Math.round(new Date().getTime()/1000),url=LCMS.url.own+"t=sys&n=update&c=gitee&a=",cache=layui.data("LCMS_cache",{key:"update_check"}),openLayer=function(code){if(code==1){var index=layer.confirm("检测到新的框架版本，是否更新？",{title:"更新提示",btn:"立即更新",},function(){LCMS.plugin.setUrl(`${url}index`);layer.close(index)})}};cache=cache||{};if(cache.expired>=now){openLayer(cache.code)}else{LCMS.util.ajax({type:"GET",url:url+"cloud&action=check&type=index",layer:true,success:function(res){layui.data("LCMS_cache",{key:"update_check",value:{code:res.code==1?1:0,expired:now+86400}});openLayer(res.code)},})}},1000)}if($(".sys-notify").length>0){$(".sys-notify ul").on("click","li",function(){let id=$(this).attr("data-id");LCMS.util.iframe({title:"通知详情",url:`${LCMS.url.admin}index.php?n=index&c=index&a=notify&action=show&id=${id}`,shade:true,shadeClose:true,area:["500px","500px"],end:function(){LCMS.plugin.notify()},})})}
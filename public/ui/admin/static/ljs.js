layui.config({version:LCMS.config.ver,base:LCMS.url.static+'layui/exts/'}).extend({notice:'notice/notice'});layer.config({skin:'lcms-layer-skin'});layui.link(LCMS.url.public+'static/layui/exts/notice/notice.css?v='+LCMS.config.ver);var $=jQuery=layui.$,LBOX=$('#LBOX'),LCONTENT=$('#LCONTENT'),LJS;layui.use(['notice'],function(){layui.notice.options={positionClass:'toast-top-center',closeButton:true,debug:false,timeOut:"3000",showDuration:"0",hideDuration:"0",showEasing:"linear"};typeof Notification!='undefined'&&Notification.requestPermission();LJS={_echo:function(content){console.log(content)},_loadstart:function(){if($('.lcms-page-loading').length<=0)$('body').append('<div class="lcms-page-loading"><div><svg stroke="#409eff" width="100%" height="100%" viewBox="0 0 38 38" style="transform:scale(0.8);" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><g transform="translate(1 1)" stroke-width="2"><circle stroke-opacity=".25" cx="18" cy="18" r="18"></circle><path d="M36 18c0-9.94-8.06-18-18-18"><animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform></path></g></g></svg></div></div>');$('.lcms-page-loading').animate({opacity:1},200)},_loadend:function(){if($('.lcms-page-loading').length>0)$('.lcms-page-loading').animate({opacity:0},300,function(){$(this).remove()})},_tips:function(msg,type){if(msg){if(type===0){layui.notice.error(msg)}else{layui.notice.success(msg)}}},_desk:function(msg,title,time,icon,click){title=title?title:'您有新的消息';time=time>0?time:5000;icon=icon?icon:LCMS.url.static+'images/tips_desk.png?'+LCMS.config.ver;if(msg){if(!layui.notice.desktopInfo(msg,title,time,icon,click)&&this._isdev('YeeClient')){window.cefQuery({request:"YeeClient-DesktopInfo:"+JSON.stringify({msg:msg,title:title,time:time,icon:0,}),persistent:false,onSuccess:function(response){},onFailure:function(code,msg){}})}}},_get:function(url,callback,type,layergo){url=url.indexOf("?")!=-1?url+'&'+Math.random():url+'?'+Math.random();$.ajax({url:url,dataType:type?type:'html',cache:false,timeout:15000,success:function(res){(callback&&typeof(callback)==="function")&&callback(res)},error:function(){(callback&&typeof(callback)==="function")&&callback({code:0,msg:'数据加载失败！'})},complete:function(){layergo||layer.closeAll()}})},_post:function(url,data,callback,type,layergo){LJS._loadstart();$.ajax({url:url,data:data,type:'POST',dataType:type?type:'json',cache:false,timeout:15000,success:function(res){(callback&&typeof(callback)==="function")&&callback(res)},error:function(){(callback&&typeof(callback)==="function")&&callback({code:0,msg:'数据加载失败！'})},complete:function(){layergo||layer.closeAll();LJS._loadend()}})},_getjs:function(url,next,asy){$.ajaxSetup({async:!asy?true:false,cache:true});url=url.search('://')!=-1?url:LCMS.url.public+'ui/admin/static/mod/'+url;var tag=url.search('\\?')!=-1?'&':'?';if(next){if(next.length>0){$.getScript(url+tag+LCMS.config.ver)}}else{$.getScript(url+tag+LCMS.config.ver)};$.ajaxSetup({async:true,cache:false})},_getcss:function(url){var tag=url.search('\\?')!=-1?'&':'?';var html='<link rel="stylesheet" href="'+url+tag+LCMS.config.ver+'">';$('head').append(html)},_getQuery:function(name,url){url=url?url:window.location.search;var reg=new RegExp("(^|&)"+name+"=([^&]*)(&|$)",'i');var result=url.substr(1).match(reg);if(result!=null){return decodeURIComponent(result[2])}else{return null}},_lazydo:function(callback,t){setTimeout(function(){(callback&&typeof(callback)==="function")&&callback()},t?t:1000)},_changeUrl:function(url,arg,arg_val){var pattern=arg+'=([^&]*)';var replaceText=arg+'='+arg_val;if(url.match(pattern)){var tmp='/('+arg+'=)([^&]*)/gi';return url.replace(eval(tmp),replaceText)}else{if(url.match('[\?]')){return url+'&'+replaceText}else{return url+'?'+replaceText}}},_tpl:function(tpl,arr){if(!tpl)return'';for(var key in arr){var reg='/{'+key+'}/g';arr[key]=arr[key]?arr[key]:'';tpl=tpl.replace(eval(reg),arr[key])};return tpl},_audio:function(src,loop){var audio=new Audio();audio.src=src;audio.loop=loop==1?1:0;audio.preload='meta';audio.play();return audio},_date:function(fmt,date){let ret;const opt={"Y+":date.getFullYear().toString(),"m+":(date.getMonth()+1).toString(),"d+":date.getDate().toString(),"H+":date.getHours().toString(),"i+":date.getMinutes().toString(),"s+":date.getSeconds().toString()};for(let k in opt){ret=new RegExp("("+k+")").exec(fmt);if(ret){fmt=fmt.replace(ret[1],(ret[1].length==1)?(opt[k]):(opt[k].padStart(ret[1].length,"0")))}};return fmt},_randstr:function(len,type){len=len||4;switch(type){case'num':case'number':var chars='0123456789';break;case'let':case'letter':var chars='ABCDEFGHJKMNPQRSTWXYZ';break;default:var chars='ABCDEFGHJKMNPQRSTWXYZ23456789';break}var str='';for(var i=0;i<len;i++){str+=chars.charAt(Math.floor(Math.random()*chars.length))};return str},_iframe:function(url,title,shade,area,_this){_this=_this?_this:self;var winw=$(_this).width(),winh=$(_this).height();area=area?area:['900px','650px'];if(typeof area=='object'){var a0=area[0].replace('px',''),a1=area[1].replace('px','');if(a0>winw){area[0]='100%'};if(a1>winh){area[1]='100%'}}else{var a=area.replace('px','');if(a>winw){area='100%'}};return _this.layer.open({id:'LCMSLAYERIFRAME'+LJS._randstr(4),type:url.indexOf('<div')!==-1?1:2,title:title?title:' ',content:url,area:area,maxmin:true,resize:true,scrollbar:true,shade:shade?0.3:0,zIndex:layer.zIndex,success:function(layero,index){_this.layer.setTop(layero)}})},_closeframe:function(reload){var index=parent.layer.getFrameIndex(window.name);parent.layer.close(index);reload=reload?true:false;reload&&parent.location.reload()},_maxframe:function(width){var win_width=parent.document.documentElement.clientWidth;var index=parent.layer.getFrameIndex(window.name);width=width?width:800;if(win_width>width){parent.layer.full(index)}},_custommenu(btns){var tpl=$('.header-custom',top.document).html(),custom=$('header .custom',top.document),target='_self';if(btns=='clear'){custom.html('');return true};for(var i=0;i<btns.length;i++){var func='LCMSCUSTOMMENU'+LJS._randstr(6);if(typeof btns[i].url=='function'){top[func]=btns[i].url;btns[i].url='javascript:'+func+'();'}else if(typeof btns[i].url=='string'){target='_blank'};btns[i]=Object.assign({title:'',icon:'',bgcolor:'none',color:'#FFFFFF',url:'javascript:;',target:target},btns[i]);custom.prepend(LJS._tpl(tpl,btns[i]))}},_isdev:function(type){type=type?type:'micromessenger';var ua=window.navigator.userAgent.toLowerCase();if(ua.indexOf(type.toLowerCase())!=-1){return true}else{return false}}};if(window.frameElement&&window.frameElement.id=='LBOX'){LJS._custommenu('clear')};LJS._getjs('../fun.js','',1);if(typeof LCMSJSONLOAD=='object'){for(let i=0;i<LCMSJSONLOAD.length;i++){typeof LCMSJSONLOAD[i]=='function'&&LCMSJSONLOAD[i]()}}});
layui.config({version:LCMS.config.ver,base:LCMS.url.static+"layui/exts/"}).extend({notice:"notice/notice"});layer.config({skin:"lcms-layer-skin"});layui.link(LCMS.url.public+"static/layui/exts/notice/notice.css?lcmsver="+LCMS.config.ver);if(typeof $!=="function"||typeof jQuery!=="undefined"){var $=(jQuery=layui.$)}var LBOX=$("#LBOX"),LCONTENT=$("#LCONTENT"),LJS;layui.notice=typeof top.layui.notice==undefined?top.layui.notice:layui.notice;layui.use(["notice"],function(){layui.notice=top.layui.notice;layui.notice.options={positionClass:"toast-bottom-right",closeButton:true,debug:false,timeOut:2000,showDuration:0,hideDuration:100,showEasing:"linear",showMethod:"show",hideMethod:"slideUp"};typeof Notification!="undefined"&&Notification.requestPermission();LCMS.util={loading:function(type,index){switch(type){case"close":index?layer.close(index):layer.closeAll("loading");break;default:return layer.load(2,{shade:[0.6,"rgb(255 255 255 / 30%)"]});break}},notify:function(opt){var shadeFlash=function(background,icon){background=background||"rgb(245 108 108 / 20%)";icon=icon||"layui-icon-tips-fill";if($("#toast-shade",top.document).length>0){return false}$("body",top.document).append('<div id="toast-shade" class="lcms-animation-warning" style="position:fixed;width:100%;height:100%;top:0;left:0;background:'+background+';z-index:99999997;opacity:0;-webkit-backdrop-filter:blur(5px);backdrop-filter:blur(5px)"><i class="layui-icon '+icon+'" style="display:block;margin:0 auto;width:150px;height:150px;top:50%;font-size:150px;color:rgb(0 0 0 / 10%);transform:translateY(-50%)"></i></div>');var shade=$("#toast-shade",top.document);setTimeout(()=>{shade.remove()},800)};opt=opt||{};if(opt.content){if(opt.content&&typeof opt.content=="object"){opt.title=opt.content.title;opt.content=opt.content.msg}switch(opt.type){case"error":shadeFlash("","layui-icon-clear");layui.notice.error(opt.content,opt.title||"操作失败");break;case"warning":shadeFlash("rgb(230 162 60 / 20%)");layui.notice.warning(opt.content,opt.title||"操作警告");break;case"info":layui.notice.info(opt.content,opt.title||"消息提示");break;case"desk":opt=Object.assign({title:"您有新的消息",icon:LCMS.url.static+"images/tips_desk.png?"+LCMS.config.ver,time:0,click:function(){}},opt);layui.notice.desktopInfo(opt.content,opt.title,opt.time,opt.icon,opt.click);break;default:layui.notice.success(opt.content,opt.title||"操作成功");break}}else{switch(opt.type){case"flash":shadeFlash(opt.background,opt.icon);break}}},load:function(opt){opt=opt||{};var tag,canLoad=true;if(opt.src)switch(opt.type){case"css":tag=opt.src.search("\\?")!=-1?"&":"?";$("head").append('<link rel="stylesheet" href="'+opt.src+tag+"lcmsver="+LCMS.config.ver+'">');break;case"js":opt.src=opt.src.search("://")!=-1?opt.src:LCMS.url.public+"ui/admin/static/mod/"+opt.src;tag=opt.src.search("\\?")!=-1?"&":"?";if(opt.elem&&opt.elem.length<=0){canLoad=false}canLoad&&$.ajax({type:"GET",url:opt.src+tag+"lcmsver="+LCMS.config.ver,dataType:"script",async:opt.async!==false?true:false,cache:true});break}},ajax:function(opt){opt=Object.assign({type:"GET",data:{},dataType:"json",contentType:"application/x-www-form-urlencoded",processData:true,async:true,cache:false,success:function(){},layer:false,loading:null},opt||{});if(opt.timeout==undefined||opt.timeout===""){opt.timeout=15000}else if(opt.timeout===0||opt.timeout==="0"){opt.timeout=0}else{opt.timeout=opt.timeout*1}let loadindex=null;if(opt.loading!==false&&(opt.type=="POST"||opt.loading)){loadindex=LCMS.util.loading()}if(opt.type=="GET"&&opt.data){for(const key in opt.data){opt.url+="&"+key+"="+encodeURIComponent(opt.data[key])}opt.data={}}$.ajax({url:opt.url,data:opt.data,type:opt.type,dataType:opt.dataType,contentType:opt.contentType,processData:opt.processData,async:opt.async,cache:opt.cache,timeout:opt.timeout,beforeSend:function(xhr){if(opt.headers){for(const key in opt.headers){xhr.setRequestHeader(key,opt.headers[key])}}opt.beforeSend&&opt.beforeSend(xhr)},xhr:function(){var xhr=new XMLHttpRequest();if(opt.progress){xhr.addEventListener("progress",function(e){opt.progress({type:"download",loaded:e.loaded,total:e.total})});xhr.upload.addEventListener("progress",function(e){opt.progress({type:"upload",loaded:e.loaded,total:e.total})})}opt.xhr&&opt.xhr(xhr);return xhr},success:function(result,status,xhr){opt.success&&opt.success(result,status,xhr)},error:function(xhr,status,error){if(opt.error){opt.error(xhr,status,error)}else{opt.success&&opt.success({code:0,msg:"数据加载失败"})}},complete:function(xhr,status){opt.layer||layer.closeAll();loadindex&&LCMS.util.loading("close",loadindex);opt.complete&&opt.complete(xhr,status)},})},randStr:function(len,type){len=len||4;switch(type){case"num":case"number":var chars="0123456789";break;case"let":case"letter":var chars="ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjklmnpqrstuvwxyz";break;default:var chars="ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjklmnpqrstuvwxyz23456789";break}var str="";for(var i=0;i<len;i++){str+=chars.charAt(Math.floor(Math.random()*chars.length))}return str},iframe:function(opt){opt=opt||{};opt=Object.assign({do:"open",id:null,title:" ",area:"",maxmin:true,closeBtn:true,resize:true,scrollbar:true,automax:false,offset:"auto",anim:0,reload:false,_this:self},opt);switch(opt.do){case"close":parent.layer.close(parent.layer.getFrameIndex(window.name));opt.reload&&parent.location.reload();break;case"max":parent.layer.full(parent.layer.getFrameIndex(window.name));break;default:opt.area=opt.area||["900px","650px"];var width=$(opt._this).width(),height=$(opt._this).height();if(typeof opt.area=="object"){if(opt.area[0].replace("px","")>width){opt.area[0]="100%"}if(opt.area[1].replace("px","")>height){opt.area[1]="100%"}}else{if(opt.area.replace("px","")>width){opt.area="100%"}}return opt._this.layer.open({id:opt.id?opt.id:"LCMSLAYERIFRAME"+LCMS.util.randStr(4),type:opt.type?opt.type:opt.url.indexOf("<div")!==-1?1:2,title:opt.title,content:opt.url,area:opt.area,maxmin:opt.maxmin,closeBtn:opt.closeBtn,offset:opt.offset,anim:opt.anim,resize:opt.resize,scrollbar:opt.scrollbar,shade:opt.shade>0?0.3:0,success:function(layero,index){opt.automax&&opt._this.layer.full(index);opt._this.layer.setTop(layero)},});break}},tpl:function(tpl,arr){if(!tpl)return"";for(var key in arr){var reg=new RegExp("{"+key+"}","g");arr[key]=arr[key]?arr[key]:"";tpl=tpl.replace(reg,arr[key])}return tpl},sleep:function(time){var start=new Date().getTime();while(new Date().getTime()-start<time){continue}},getQuery:function(name,url){url=url?new URL(url).search:location.search;var paras=new URLSearchParams(url);return paras.get(name)},changeUrl:function(url,arg,arg_val){var pattern=arg+"=([^&]*)";var replaceText=arg+"="+arg_val;if(url.match(pattern)){var reg=new RegExp("("+arg+"=)([^&]*)","gi");return url.replace(reg,replaceText)}else{if(url.match("[?]")){return url+"&"+replaceText}else{return url+"?"+replaceText}}},setMenu:function(btns){var tpl=$(".header-custom",top.document).html(),custom=$("header .custom",top.document),target="_self";if(btns=="clear"){custom.html("");return true}for(var i=0;i<btns.length;i++){var func="LCMSCUSTOMMENU"+LCMS.util.randStr(4);if(typeof btns[i].url=="function"){top[func]=btns[i].url;btns[i].url="javascript:"+func+"();"}else if(typeof btns[i].url=="string"){target="_blank"}btns[i]=Object.assign({title:"",icon:"",bgcolor:"none",color:"#FFFFFF",url:"javascript:;",target:target},btns[i]);custom.prepend(LCMS.util.tpl(tpl,btns[i]))}},getDate:function(fmt,date){date=date||new Date();let ret,opt={"Y+":date.getFullYear().toString(),"m+":(date.getMonth()+1).toString(),"d+":date.getDate().toString(),"H+":date.getHours().toString(),"i+":date.getMinutes().toString(),"s+":date.getSeconds().toString()};for(let k in opt){ret=new RegExp("("+k+")").exec(fmt);if(ret){fmt=fmt.replace(ret[1],ret[1].length==1?opt[k]:opt[k].padStart(ret[1].length,"0"))}}return fmt},play:function(opt){opt=Object.assign({type:"audio",src:"",loop:0},opt||{});if(opt.src)switch(opt.type){case"audio":var audio=new Audio();audio.src=opt.src;audio.loop=opt.loop==1?1:0;audio.preload="meta";audio.play();return audio;break}},inUa:function(ua){ua=ua?ua:"micromessenger";if(window.navigator.userAgent.toLowerCase().indexOf(ua.toLowerCase())!=-1){return true}else{return false}},speak:function(text){if("speechSynthesis"in window&&typeof SpeechSynthesisUtterance=="function"){window.speechSynthesis.speak(new SpeechSynthesisUtterance(text))}},copy:function(text){function copy(){var textString=text.toString(),element=document.createElement("input"),selectText=function(textbox,startIndex,stopIndex){if(textbox.createTextRange){const range=textbox.createTextRange();range.collapse(true);range.moveStart("character",startIndex);range.moveEnd("character",stopIndex-startIndex);range.select()}else{textbox.setSelectionRange(startIndex,stopIndex);textbox.focus()}};element.id="lcms-copy-input";element.readOnly="readOnly";element.style.position="absolute";element.style.left="-1000px";element.style.bottom="0";element.style.zIndex="-1000";document.body.appendChild(element);element.value=textString;selectText(element,0,textString.length);try{document.execCommand("copy");element.remove();LCMS.util.notify({type:"success",content:"已复制到剪贴板"})}catch(error){LCMS.util.notify({type:"error",content:"复制失败"})}}if(typeof navigator.clipboard=="object"){navigator.clipboard.writeText(text).then(function(){LCMS.util.notify({type:"success",content:"已复制到剪贴板"})}).catch(()=>{copy()})}else{copy()}},lazyImg:function(cname,options){setTimeout(function(){cname=cname||"lazyload";options=options||{};options=Object.assign({datasrc:"data-src"},options);var imgs=$("img."+cname);if(imgs.length>0){imgs.each(function(){var _this=$(this),place="/public/static/images/pixel.png";if(_this.attr(options.datasrc)){var src=_this.attr(options.datasrc);_this.removeAttr(options.datasrc);_this.attr("src",place);setTimeout(function(){var img=new Image();img.src=src;img.onload=function(){_this.fadeOut(0);_this.removeClass(cname).attr("src",src);_this.fadeIn(500);options.success&&options.success(_this,img)};img.onerror=function(){_this.fadeOut(0);_this.addClass(cname+"-nopic").removeClass(cname);_this.fadeIn(500);options.error&&options.error(_this,img)}},10)}})}},10)},getFun:function(fname){if(fname)try{var func=new Function("return "+fname)();if(typeof func=="function"){return func}}catch(err){console.error(fname+"方法不存在")}return function(){}},};if(top==self&&LCMS&&LCMS.url&&LCMS.url.own){let hidden="hidden"in document?"hidden":"webkitHidden"in document?"webkitHidden":"mozHidden"in document?"mozHidden":null;document.addEventListener(hidden.replace(/hidden/i,"visibilitychange"),function(){if(!document[hidden]){LCMS.util.ajax({type:"GET",url:LCMS.url.own+"t=sys&n=index&c=index&a=heart",layer:true,success:function(res){if(res.code!=1){top.location.reload()}},})}});window.addEventListener("storage",function(e){if(e.storageArea===localStorage&&e.key=="LCMS_user"){setTimeout(()=>{top.location.reload()},500)}})}if(window.frameElement&&window.frameElement.id=="LBOX"){LCMS.util.setMenu("clear")}LCMS.util.load({type:"js",src:"../fun.js",async:false});if(LCMS.onload&&typeof LCMS.onload=="object"){for(let i=0;i<LCMS.onload.length;i++){typeof LCMS.onload[i]=="function"&&LCMS.onload[i]()}}});
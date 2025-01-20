layui.config({version:LCMS.config.ver,base:LCMS.url.static+"layui/exts/"}).extend({notice:"notice/notice"});layer.config({skin:"lcms-layer-skin"});layui.link(LCMS.url.public+"static/layui/exts/notice/notice.css?lcmsver="+LCMS.config.ver);if("function"!==typeof $||"undefined"!==typeof jQuery){var $=jQuery=layui.$}var LJS,LCONTENT=$("#LCONTENT");layui.notice=void 0==typeof top.layui.notice?top.layui.notice:layui.notice;layui.use(["notice"],(function(){layui.notice=top.layui.notice;layui.notice.options={positionClass:"toast-bottom-right",closeButton:true,debug:false,timeOut:3000,showDuration:0,hideDuration:100,showEasing:"linear",showMethod:"show",hideMethod:"slideUp"};"undefined"!=typeof Notification&&Notification.requestPermission();LCMS.util={__private:{},loading:function(type,index){switch(type){case"close":if(index){layer.close(index)}else if(window.LCMSLOADINGINDEX){layer.close(window.LCMSLOADINGINDEX)}else{layer.closeAll("dialog")}break;default:index=layer.msg('<i class="layui-icon layui-icon-loading-1 layui-anim layui-anim-rotate layui-anim-loop" style="margin-right:10px"></i>加载中',{skin:"layui-layer-msg layui-layer-hui lcms-layer-loading",shade:[0,"#fff"],time:"number"==typeof type?type:60000});window.LCMSLOADINGINDEX=index;return index;break}},notify:function(opt){var shadeFlash=function(background,icon){background=background||"rgb(245 108 108 / 20%)";icon=icon||"layui-icon-tips-fill";if($("#toast-shade",top.document).length>0){return false}$("body",top.document).append('<div id="toast-shade" class="lcms-animation-warning" style="position:fixed;width:100%;height:100%;top:0;left:0;background:'+background+';z-index:99999997;opacity:0;-webkit-backdrop-filter:blur(5px);backdrop-filter:blur(5px)"><i class="layui-icon '+icon+'" style="display:block;margin:0 auto;width:150px;height:150px;top:50%;font-size:150px;color:rgb(0 0 0 / 10%);transform:translateY(-50%)"></i></div>');var shade=$("#toast-shade",top.document);setTimeout((()=>{shade.remove()}),800)};opt=opt||{};if(opt.message){opt.content=opt.message;delete opt.message}if(opt.content){if(opt.content&&"object"==typeof opt.content){opt.title=opt.content.title;opt.content=opt.content.msg}switch(opt.type){case"error":shadeFlash("","layui-icon-clear");layui.notice.error(opt.content,opt.title||"操作失败");break;case"warning":shadeFlash("rgb(230 162 60 / 20%)");layui.notice.warning(opt.content,opt.title||"操作警告");break;case"info":layui.notice.info(opt.content,opt.title||"消息提示");break;case"desk":opt=Object.assign({title:"您有新的消息",icon:LCMS.url.static+"images/tips_desk.png?"+LCMS.config.ver,time:0,click:function(){}},opt);layui.notice.desktopInfo(opt.content,opt.title,opt.time,opt.icon,opt.click);break;default:layui.notice.success(opt.content,opt.title||"操作成功");break}}else{switch(opt.type){case"flash":shadeFlash(opt.background,opt.icon);break}}},load:function(opt){opt=Object.assign({type:null,src:null,async:true,cache:true,elem:null,loading:false,cors:false,onevent:null,onnext:null,onload:()=>{}},opt||{});if(!opt.src){return}if(opt.elem&&opt.elem.length<=0){return}if("object"==typeof opt.src){let srcs=opt.src,srceach=(index,callback)=>{if(!srcs[index]){callback("nosrc",opt);return}LCMS.util.load(Object.assign(opt,{src:srcs[index],onnext:()=>{srceach(index+1,callback)}}))};srceach(0,(()=>{opt.onload()}));return}if(opt.onnext&&"function"==typeof opt.onnext){opt.onload=opt.onnext}let src=opt.src.split("?"),mime=src[0].slice(-3);opt.src+=(src[1]?`&`:`?`)+`lcmsver=${LCMS.config.ver}`;switch(mime){case"css":opt.type="css";break;case".js":if(!opt.type||"js"==opt.type){opt.type="js"}else{opt.type="esm"}if(-1!=opt.src.search("://")){let uinfo=new URL(opt.src);if(uinfo.origin!=location.origin){opt.cors=true}}else if("@/"==opt.src.slice(0,2)){opt.src=`${LCMS.url.own_path}${opt.src.slice(2)}`}break}opt.event=`LCMS.util.load.${btoa(opt.src)}`;if("object"!=typeof LCMS.util.__private.load){LCMS.util.__private.load={list:{}}}if(LCMS.util.__private.load.list[opt.event]){if("dispatched"==LCMS.util.__private.load.list[opt.event]){opt.onload("dispatched",opt)}else{document.addEventListener(opt.event,(()=>{opt.onload("listener",opt)}))}return}else{LCMS.util.__private.load.list[opt.event]="waiting"}opt.onevent=()=>{document.dispatchEvent(new CustomEvent(opt.event));LCMS.util.__private.load.list[opt.event]="dispatched"};switch(opt.type){case"css":$("head").append(`<link rel="stylesheet" href="${opt.src}">`);opt.onload("complete",opt);opt.onevent();break;case"esm":$("body").append(`<script type="module" src="${opt.src}"><\/script>`);opt.onload("complete",opt);opt.onevent();break;case"js":opt.async=false!==opt.async?true:false;LCMS.util.ajax({type:"GET",url:opt.src,contentType:"application/x-www-form-urlencoded",dataType:opt.cors&&!opt.async?"text":"script",headers:{Accept:"application/javascript"},async:opt.async,timeout:0,cache:opt.cache,layer:true,loading:opt.loading?true:false,success:code=>{if(opt.cors&&!opt.async&&"<"!=code.substr(0,1)){$("body").append(`<script type="text/javascript">${code}<\/script>`)}},error:()=>{},complete:()=>{opt.onload("complete",opt);opt.onevent()}});break}},ajax:function(opt){if(!opt){return false}opt=Object.assign({type:"GET",data:{},dataType:"json",contentType:"application/x-www-form-urlencoded",processData:true,async:true,cache:false,layer:false,loading:null},opt||{});if(void 0==opt.timeout||""===opt.timeout){opt.timeout=15000}else if(0===opt.timeout||"0"===opt.timeout){opt.timeout=0}else{opt.timeout=1*opt.timeout}let loadindex=null;if(false!==opt.loading&&("POST"==opt.type||opt.loading)){loadindex=LCMS.util.loading()}if("GET"==opt.type&&opt.data){for(const key in opt.data){opt.url+="&"+key+"="+encodeURIComponent(opt.data[key])}opt.data={}}var def=Object.assign({},opt);delete def.layer;delete def.loading;$.ajax(Object.assign(def,{beforeSend:function(xhr){if(opt.headers){for(const key in opt.headers){xhr.setRequestHeader(key,opt.headers[key])}}opt.beforeSend&&opt.beforeSend(xhr)},xhr:function(){var xhr=$.ajaxSettings.xhr();if(opt.progress){xhr.addEventListener("progress",(function(e){opt.progress({type:"download",loaded:e.loaded,total:e.total})}));xhr.upload&&xhr.upload.addEventListener("progress",(function(e){opt.progress({type:"upload",loaded:e.loaded,total:e.total})}))}opt.xhr&&opt.xhr(xhr);return xhr},success:function(result,status,xhr){opt.layer||layer.closeAll();loadindex&&LCMS.util.loading("close",loadindex);opt.success&&opt.success(result,status,xhr)},error:function(xhr,status,error){opt.layer||layer.closeAll();loadindex&&LCMS.util.loading("close",loadindex);let msg="数据加载失败";if(xhr&&xhr.responseText){console.error(`Ajax请求失败：${opt.url}\n`+xhr.responseText);if(-1!==xhr.responseText.indexOf("防火墙")){msg="请求被服务器防火墙拦截，如有需要可临时关闭防火墙，操作完后再开启"}}if(opt.error){opt.error(xhr,status,error)}else{opt.success&&opt.success({code:0,msg:msg})}},complete:function(xhr,status){opt.complete&&opt.complete(xhr,status)}}))},EventSource:async function(url,options){options=Object.assign({method:"GET",retry:1000,onopen:async function(response){const contentType=response.headers.get("content-type");if(!(null===contentType||void 0===contentType?void 0:contentType.startsWith("text/event-stream"))){const body=await response.json();throw new Error(`状态码：${response.status}<br>响应：${JSON.stringify(body)}`)}},onmessage:function(){},onclose:function(){},onerror:function(){}},options);var retryTimer,config=Object.assign({},options),getBytes=async function(stream,onChunk){const reader=stream.getReader();let result;while(!(result=await reader.read()).done){onChunk(result.value)}},getLines=function(onLine){let buffer;let position;let fieldLength;let discardTrailingNewline=false;var concat=function(a,b){const res=new Uint8Array(a.length+b.length);res.set(a);res.set(b,a.length);return res};return function onChunk(arr){if(void 0===buffer){buffer=arr;position=0;fieldLength=-1}else{buffer=concat(buffer,arr)}const bufLength=buffer.length;let lineStart=0;while(position<bufLength){if(discardTrailingNewline){if(10===buffer[position]){lineStart=++position}discardTrailingNewline=false}let lineEnd=-1;for(;position<bufLength&&-1===lineEnd;++position){switch(buffer[position]){case 58:if(-1===fieldLength){fieldLength=position-lineStart}break;case 13:discardTrailingNewline=true;case 10:lineEnd=position;break}}if(-1===lineEnd){break}onLine(buffer.subarray(lineStart,lineEnd),fieldLength);lineStart=position;fieldLength=-1}if(lineStart===bufLength){buffer=void 0}else if(0!==lineStart){buffer=buffer.subarray(lineStart);position-=lineStart}}},newMessage=function(){return{data:"",event:"",id:"",retry:void 0}},getMessages=function(onId,onRetry,onMessage){let message=newMessage();const decoder=new TextDecoder;return function onLine(line,fieldLength){if(0===line.length){null===onMessage||void 0===onMessage?void 0:onMessage(message);message=newMessage()}else if(fieldLength>0){const field=decoder.decode(line.subarray(0,fieldLength));const valueOffset=fieldLength+(32===line[fieldLength+1]?2:1);const value=decoder.decode(line.subarray(valueOffset));switch(field){case"data":message.data=message.data?message.data+"\n"+value:value;break;case"event":message.event=value;break;case"id":onId(message.id=value);break;case"retry":const retry=parseInt(value,10);if(!isNaN(retry)){onRetry(message.retry=retry)}break}}}};delete config.retry;delete config.onopen;delete config.onmessage;delete config.onclose;delete config.onerror;try{const response=await fetch(url,config);await options.onopen(response);await getBytes(response.body,getLines(getMessages((id=>{if(id){config.headers["last-event-id"]=id}else{delete config.headers["last-event-id"]}}),(retry=>{options.retry=retry}),options.onmessage)));options.onclose()}catch(error){try{const errback=options.onerror(error);const interval=errback?errback:options.retry;window.clearTimeout(retryTimer);retryTimer=window.setTimeout(create,interval)}catch(innerErr){window.clearTimeout(retryTimer)}}},randStr:function(len,type){len=len||4;switch(type){case"num":case"number":var chars="0123456789";break;case"let":case"letter":chars="ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjklmnpqrstuvwxyz";break;default:chars="ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjklmnpqrstuvwxyz23456789";break}var str="";for(var i=0;i<len;i++){str+=chars.charAt(Math.floor(Math.random()*chars.length))}return str},iframe:function(opt){opt=opt||{};opt=Object.assign({do:"open",id:null,title:" ",area:"",maxmin:true,closeBtn:true,resize:true,scrollbar:true,automax:false,offset:"auto",anim:0,reload:false,_this:self},opt);switch(opt.do){case"close":parent.layer.close(parent.layer.getFrameIndex(window.name));if(opt.reload){const tables=$("table.lcms-table",parent.document);if(tables.length>0){if(parent.LCMS.plugin&&parent.LCMS.plugin.table&&parent.LCMS.plugin.table.focusid){const id=parent.LCMS.plugin.table.focusid;if($(`#${id}`).parent().hasClass("layui-table-tree")){parent.layui.treeTable.reloadData(id)}else{parent.layui.table.reloadData(id)}}else{tables.each((function(){if($(this).hasClass("lcms-table-tree")){parent.layui.treeTable.reloadData($(this).attr("id"))}else{parent.layui.table.reloadData($(this).attr("id"))}}))}}else{parent.location.reload()}}break;case"max":parent.layer.full(parent.layer.getFrameIndex(window.name));break;default:opt.area=opt.area||["900px","650px"];var width=$(opt._this).width(),height=$(opt._this).height();if("object"==typeof opt.area){if(opt.area[0].replace("px","")>width){opt.area[0]="100%"}if(opt.area[1].replace("px","")>height){opt.area[1]="100%"}}else if(opt.area.replace("px","")>width){opt.area="100%"}var def=Object.assign({},opt);delete def._this;delete def.url;delete def.automax;return opt._this.layer.open(Object.assign(def,{id:opt.id?opt.id:"LCMSLAYERIFRAME"+LCMS.util.randStr(4),type:opt.type?opt.type:-1!==opt.url.indexOf("<div")?1:2,content:opt.url?opt.url:opt.content,shade:"object"==typeof opt.shade?opt.shade:opt.shade>0?0.3:0,success:function(layero,index,that){opt.automax&&opt._this.layer.full(index);opt._this.layer.setTop(layero);opt.success&&opt.success(layero,index,that)}}));break}},tpl:function(tpl,data){if("object"===typeof tpl){tpl=$(tpl).html()}if(!tpl){return""}for(var key in data){var reg=new RegExp("{"+key+"}","g");data[key]=data[key]?data[key]:"";tpl=tpl.replace(reg,data[key])}return tpl},tplx:function(com,data){switch(com){case"change:dom":Alpine.mutateDom((()=>{data()}));break;default:com=com.split(":");if(!com[1]){com=["data",com[0]]}if(data&&data.mounted){data.init=data.mounted;delete data.mounted}let dtpl=$(`[x-data="${com[1]}"]`),jtpl=$(`script[tplx="${com[1]}"]`),htpl=$(`#${com[1]}`);if(dtpl.length<=0&&jtpl.length<=0&&htpl.length<=0){return}LCMS.util.load({src:`${LCMS.url.static}plugin/alpine.min.js`,onload:step=>{switch(com[0]){case"data":Alpine.data(com[1],(()=>data));if(jtpl.length>0){let html=jtpl.html(),dtset=jtpl.data(),dthtml="";dtset=dtset||{};for(const key in dtset){dthtml+=` data-${key}="${dtset[key]}"`}html=html.replace(/^[ \t\n\r\u200B]+|[ \t\n\r\u200B]+$/g,"");if($(html).length>1){html=`<section x-data="${com[1]}"${dthtml}>${html}</section>`}else{html=html.replace(/>/,` x-data="${com[1]}"${dthtml}>`)}Alpine.mutateDom((()=>{jtpl.replaceWith(html)}))}if(dtpl.length<=0&&htpl.length>0){Alpine.mutateDom((()=>{htpl.attr("x-data",com[1])}))}if("dispatched"==step){Alpine.initTree($(`[x-data="${com[1]}"]`)[0])}break;case"store":Alpine.store(com[1],data);break;case"bind":Alpine.bind(com[1],(()=>data));break}}});break}},sleep:function(time){var start=(new Date).getTime();while((new Date).getTime()-start<time){continue}},getQuery:function(name,url){url=url?new URL(url).search:location.search;var paras=new URLSearchParams(url);return paras.get(name)},changeUrl:function(url,arg,arg_val){var pattern=arg+"=([^&]*)";var replaceText=arg+"="+arg_val;if(url.match(pattern)){var reg=new RegExp("("+arg+"=)([^&]*)","gi");return url.replace(reg,replaceText)}else if(url.match("[?]")){return url+"&"+replaceText}else{return url+"?"+replaceText}},setMenu:function(btns){var tpl=$(".header-custom",top.document).html(),custom=$("header .custom",top.document),target="_self";if("clear"==btns){custom.html("");return true}for(var i=0;i<btns.length;i++){var func="LCMSCUSTOMMENU"+LCMS.util.randStr(4);if("function"==typeof btns[i].url){top[func]=btns[i].url;btns[i].url="javascript:"+func+"();"}else if("string"==typeof btns[i].url){target="_blank"}btns[i]=Object.assign({title:"",icon:"",bgcolor:"none",color:"initial",url:"javascript:;",target:target},btns[i]);custom.prepend(LCMS.util.tpl(tpl,btns[i]))}},getDate:function(fmt,date){if(date){if("number"==typeof date){date=new Date(1000*date)}else if("string"==typeof date){date=new Date(date.replace(" ","T"))}}else{date=new Date}let m=date.getMonth()+1,d=date.getDate(),H=date.getHours(),i=date.getMinutes(),s=date.getSeconds();m=m<10?"0"+m:m.toString();d=d<10?"0"+d:d.toString();H=H<10?"0"+H:H.toString();i=i<10?"0"+i:i.toString();s=s<10?"0"+s:s.toString();let ret,opt={"Y+":date.getFullYear().toString(),"m+":m,"d+":d,"H+":H,"i+":i,"s+":s};for(let k in opt){ret=new RegExp("("+k+")").exec(fmt);if(ret){fmt=fmt.replace(ret[1],1==ret[1].length?opt[k]:opt[k].padStart(ret[1].length,"0"))}}return fmt},play:function(opt){opt=Object.assign({type:"audio",src:"",loop:0},opt||{});if(opt.src){switch(opt.type){case"audio":var audio=new Audio;audio.src=opt.src;audio.loop=1==opt.loop?1:0;audio.preload="meta";audio.play();return audio;break}}},inUa:function(ua){ua=ua?ua:"micromessenger";if(-1!=window.navigator.userAgent.toLowerCase().indexOf(ua.toLowerCase())){return true}else{return false}},speak:function(text,lang){if("speechSynthesis"in window&&"function"==typeof SpeechSynthesisUtterance){let SSU=new SpeechSynthesisUtterance;SSU.text=text;SSU.lang=lang?lang:"zh-CN";SSU.pitch=1;SSU.rate=1;SSU.volume=100;window.speechSynthesis.speak(SSU)}},copy:function(text,tips){function copy(){var textString=text.toString(),element=document.createElement("input"),selectText=function(textbox,startIndex,stopIndex){if(textbox.createTextRange){const range=textbox.createTextRange();range.collapse(true);range.moveStart("character",startIndex);range.moveEnd("character",stopIndex-startIndex);range.select()}else{textbox.setSelectionRange(startIndex,stopIndex);textbox.focus()}};element.id="lcms-copy-input";element.readOnly="readOnly";element.style.position="fixed";element.style.left="-1000px";element.style.bottom="0";element.style.zIndex="-1000";document.body.appendChild(element);element.value=textString;selectText(element,0,textString.length);try{document.execCommand("copy");element.remove();false!==tips&&LCMS.util.notify({type:"success",content:tips?tips:"已复制到剪贴板"})}catch(error){LCMS.util.notify({type:"error",content:"复制失败"})}}if("object"==typeof navigator.clipboard){navigator.clipboard.writeText(text).then((function(){false!==tips&&LCMS.util.notify({type:"success",content:tips?tips:"已复制到剪贴板"})})).catch((()=>{copy()}))}else{copy()}},lazyImg:function(cname,options){setTimeout((function(){cname=cname||"lazyload";options=options||{};options=Object.assign({datasrc:"data-src"},options);var imgs=$("img."+cname);if(imgs.length>0){imgs.each((function(){var _this=$(this),place="/public/static/images/pixel.png";if(_this.attr(options.datasrc)){var src=_this.attr(options.datasrc);_this.removeAttr(options.datasrc);_this.attr("src",place);setTimeout((function(){var img=new Image;img.src=src;img.onload=function(){_this.fadeOut(0);_this.removeClass(cname).attr("src",src);_this.fadeIn(500);options.success&&options.success(_this,img)};img.onerror=function(){_this.fadeOut(0);_this.addClass(cname+"-nopic").removeClass(cname);_this.fadeIn(500);options.error&&options.error(_this,img)}}),10)}}))}}),10)},getFun:function(fname,debug){if(fname){try{var func=new Function("return "+fname)();if("function"==typeof func){return func}}catch(err){debug&&console.warn(fname+"方法不存在")}}return function(){}},crypto:async function(type,options){if(options){await new Promise((resolve=>{switch(type){case"rsa.encode":LCMS.util.load({src:`${LCMS.url.static}plugin/jsencrypt.min.js`,onload:()=>{resolve()}});break;default:LCMS.util.load({src:`${LCMS.url.static}plugin/crypto-js.min.js`,onload:()=>{resolve()}});break}}));switch(type){case"encode":case"decode":if(!options.text||!options.key){return false}var key=iv=CryptoJS.enc.Utf8.parse(CryptoJS.MD5(options.key).toString());break}switch(type){case"encode":return CryptoJS.AES.encrypt(options.text,key,{iv:iv,mode:CryptoJS.mode.CBC,padding:CryptoJS.pad.Pkcs7}).toString();break;case"decode":return CryptoJS.enc.Utf8.stringify(CryptoJS.AES.decrypt(options.text,key,{iv:iv,mode:CryptoJS.mode.CBC,padding:CryptoJS.pad.Pkcs7}));break;case"md5":return CryptoJS.MD5(options).toString();break;case"hash":return CryptoJS.SHA1(options).toString();break;case"rsa.encode":let jsencrypt=new JSEncrypt;jsencrypt.setKey(options.key);return jsencrypt.encrypt(options.data);break}}},fullScreen:function(type,elem){switch(type){case"open":elem=elem?$(elem)[0]:document.documentElement;if(elem.requestFullscreen){elem.requestFullscreen()}else if(elem.mozRequestFullScreen){elem.mozRequestFullScreen()}else if(elem.webkitRequestFullScreen){elem.webkitRequestFullScreen()}else if(elem.msRequestFullscreen){elem.msRequestFullscreen()}break;case"close":if(document.exitFullscreen){document.exitFullscreen()}else if(document.mozCancelFullScreen){document.mozCancelFullScreen()}else if(document.webkitCancelFullScreen){document.webkitCancelFullScreen()}else if(document.msExitFullscreen){document.msExitFullscreen()}break;case"elem":return document.fullscreenElement||document.mozFullScreenElement||document.webkitFullscreenElement||document.msFullscreenElement||null;break;case"check":return!!LCMS.util.fullScreen("elem");break;default:LCMS.util.fullScreen("check")?LCMS.util.fullScreen("close"):LCMS.util.fullScreen("open",type);break}}};if(top==self&&LCMS&&LCMS.url&&LCMS.url.own){LCMS.plugin=Object.assign({notify:function(){LCMS.util.ajax({type:"GET",url:LCMS.url.own+"t=sys&n=index&c=index&a=heart",layer:true,success:function(res,status,xhr){if(1==res.code){if($(".sys-notify").length>0){let notify=res.data.notify||{count:0,list:[]};if(notify.list&&notify.list.length>0){$(".sys-notify .layui-badge").html(notify.count).show();let html="";for(let i=0;i<notify.list.length;i++){const li=notify.list[i];html+=`<li data-id="${li.id}"><span>${li.title}</span><span>${LCMS.util.getDate("m-d",new Date(li.addtime))}</span></li>`}$(".sys-notify ul").removeClass("sys-notify-nodata").html(html)}else{$(".sys-notify .layui-badge").hide();$(".sys-notify ul").html("").addClass("sys-notify-nodata")}}}else if("success"==status){top.location.reload()}}})}},LCMS.plugin||{});if(-1===LCMS.url.now.indexOf("&n=login")){let hidden="hidden"in document?"hidden":"webkitHidden"in document?"webkitHidden":"mozHidden"in document?"mozHidden":null;LCMS.plugin.notify();document.addEventListener(hidden.replace(/hidden/i,"visibilitychange"),(function(){if(!document[hidden]){LCMS.plugin.notify()}}));window.addEventListener("storage",(function(e){if(e.storageArea===localStorage&&"LCMS_user"==e.key){setTimeout((()=>{top.location.reload()}),500)}}))}}LCMS.plugin=Object.assign(LCMS.plugin||{},{router:(url,opts)=>{if(self==parent){LCMS.util.loading();window.location.href=url}else{top.LCMS.plugin.router(url,opts)}}});if(window.frameElement&&-1!==window.frameElement.id.indexOf("LBOX")){LCMS.util.setMenu("clear")}LCMS.util.load({type:"js",src:`${LCMS.url.public}ui/admin/static/fun.js`})}));
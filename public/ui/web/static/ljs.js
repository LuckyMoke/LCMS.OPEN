if(!LCMS||typeof LCMS!="object"){var LCMS={}}LCMS.util={loading:function(type,index){switch(type){case"close":index?layer.close(index):layer.closeAll("loading");break;default:return layer.load(1,{shade:[0.6,"#fff"],time:typeof type=="number"?type:0});break}},notify:function(opt){opt=opt||{};if(opt.content){if(typeof opt.content=="object"){opt.content=`${opt.content.title}：<br>${opt.content.msg}`}switch(opt.type){case"error":layer.msg(opt.content,{icon:2,anim:6,time:2000});break;case"warning":layer.msg(opt.content,{icon:7,time:2000});break;case"info":layer.msg(opt.content,{time:2000});break;default:layer.msg(opt.content,{icon:1,time:2000});break}}},load:function(opt){opt=opt||{};var canLoad=true;if(opt.src)switch(opt.type){case"css":$("head").append(`<link rel="stylesheet"href="${opt.src}">`);break;case"js":if(opt.elem&&opt.elem.length<=0){canLoad=false}canLoad&&LCMS.util.ajax({type:"GET",url:opt.src,contentType:"application/x-javascript",dataType:"script",async:opt.async!==false?true:false,timeout:0,cache:true,layer:true,loading:false});break}},ajaxQueue:{stop:false,list:[],push:function(opt){LCMS.util.ajaxQueue.list.push(opt);LCMS.util.ajaxQueue.load()},next:function(load){if(load=="sync"){LCMS.util.ajaxQueue.stop=false}LCMS.util.ajaxQueue.load()},load:function(){if(LCMS.util.ajaxQueue.stop===false){var opt=LCMS.util.ajaxQueue.list.shift();if(opt&&opt.async===false){LCMS.util.ajaxQueue.stop=true;LCMS.util.ajax(opt,"sync")}else if(opt){LCMS.util.ajax(opt,"async")}}},},ajax:function(opt,load){if(!opt){return false}if(!load){LCMS.util.ajaxQueue.push(opt);return false}opt=Object.assign({type:"GET",data:{},dataType:"json",contentType:"application/x-www-form-urlencoded",processData:true,async:true,cache:false,layer:false,loading:null},opt||{});if(opt.timeout==undefined||opt.timeout===""){opt.timeout=15000}else if(opt.timeout===0||opt.timeout==="0"){opt.timeout=0}else{opt.timeout=opt.timeout*1}let loadindex=null;if(opt.loading!==false&&(opt.type=="POST"||opt.loading)){loadindex=LCMS.util.loading()}if(opt.type=="GET"&&opt.data){for(const key in opt.data){opt.url+="&"+key+"="+encodeURIComponent(opt.data[key])}opt.data={}}var def=Object.assign({},opt);delete def.layer;delete def.loading;$.ajax(Object.assign(def,{beforeSend:function(xhr){if(opt.headers){for(const key in opt.headers){xhr.setRequestHeader(key,opt.headers[key])}}opt.beforeSend&&opt.beforeSend(xhr)},xhr:function(){var xhr=new XMLHttpRequest();if(opt.progress){xhr.addEventListener("progress",function(e){opt.progress({type:"download",loaded:e.loaded,total:e.total})});xhr.upload.addEventListener("progress",function(e){opt.progress({type:"upload",loaded:e.loaded,total:e.total})})}opt.xhr&&opt.xhr(xhr);return xhr},success:function(result,status,xhr){opt.success&&opt.success(result,status,xhr)},error:function(xhr,status,error){if(opt.error){opt.error(xhr,status,error)}else{opt.success&&opt.success({code:0,msg:"数据加载失败"})}},complete:function(xhr,status){LCMS.util.ajaxQueue.next(load);loadindex&&LCMS.util.loading("close",loadindex);opt.complete&&opt.complete(xhr,status)},}))},EventSource:async function(url,options){options=Object.assign({method:"GET",retry:1000,onopen:async function(response){const contentType=response.headers.get("content-type");if(!(contentType===null||contentType===void 0?void 0:contentType.startsWith("text/event-stream"))){const body=await response.json();throw new Error(`状态码：${response.status}<br>响应：${JSON.stringify(body)}`)}},onmessage:function(){},onclose:function(){},onerror:function(){},},options);var config=Object.assign({},options),retryTimer,getBytes=async function(stream,onChunk){const reader=stream.getReader();let result;while(!(result=await reader.read()).done){onChunk(result.value)}},getLines=function(onLine){let buffer;let position;let fieldLength;let discardTrailingNewline=false;var concat=function(a,b){const res=new Uint8Array(a.length+b.length);res.set(a);res.set(b,a.length);return res};return function onChunk(arr){if(buffer===undefined){buffer=arr;position=0;fieldLength=-1}else{buffer=concat(buffer,arr)}const bufLength=buffer.length;let lineStart=0;while(position<bufLength){if(discardTrailingNewline){if(buffer[position]===10){lineStart=++position}discardTrailingNewline=false}let lineEnd=-1;for(;position<bufLength&&lineEnd===-1;++position){switch(buffer[position]){case 58:if(fieldLength===-1){fieldLength=position-lineStart}break;case 13:discardTrailingNewline=true;case 10:lineEnd=position;break}}if(lineEnd===-1){break}onLine(buffer.subarray(lineStart,lineEnd),fieldLength);lineStart=position;fieldLength=-1}if(lineStart===bufLength){buffer=undefined}else if(lineStart!==0){buffer=buffer.subarray(lineStart);position-=lineStart}}},newMessage=function(){return{data:"",event:"",id:"",retry:undefined}},getMessages=function(onId,onRetry,onMessage){let message=newMessage();const decoder=new TextDecoder();return function onLine(line,fieldLength){if(line.length===0){onMessage===null||onMessage===void 0?void 0:onMessage(message);message=newMessage()}else if(fieldLength>0){const field=decoder.decode(line.subarray(0,fieldLength));const valueOffset=fieldLength+(line[fieldLength+1]===32?2:1);const value=decoder.decode(line.subarray(valueOffset));switch(field){case"data":message.data=message.data?message.data+"\n"+value:value;break;case"event":message.event=value;break;case"id":onId((message.id=value));break;case"retry":const retry=parseInt(value,10);if(!isNaN(retry)){onRetry((message.retry=retry))}break}}}};delete config.retry;delete config.onopen;delete config.onmessage;delete config.onclose;delete config.onerror;try{const response=await fetch(url,config);await options.onopen(response);await getBytes(response.body,getLines(getMessages((id)=>{if(id){config.headers["last-event-id"]=id}else{delete config.headers["last-event-id"]}},(retry)=>{options.retry=retry},options.onmessage)));options.onclose()}catch(error){try{const errback=options.onerror(error);const interval=errback?errback:options.retry;window.clearTimeout(retryTimer);retryTimer=window.setTimeout(create,interval)}catch(innerErr){window.clearTimeout(retryTimer)}}},randStr:function(len,type){len=len||4;switch(type){case"num":case"number":var chars="0123456789";break;case"let":case"letter":var chars="ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjklmnpqrstuvwxyz";break;default:var chars="ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjklmnpqrstuvwxyz23456789";break}var str="";for(var i=0;i<len;i++){str+=chars.charAt(Math.floor(Math.random()*chars.length))}return str},iframe:function(opt){opt=opt||{};opt=Object.assign({do:"open",id:null,title:" ",area:"",maxmin:true,closeBtn:true,resize:true,scrollbar:true,automax:false,offset:"auto",anim:0,reload:false,_this:self},opt);switch(opt.do){case"close":parent.layer.close(parent.layer.getFrameIndex(window.name));opt.reload&&parent.location.reload();break;case"max":parent.layer.full(parent.layer.getFrameIndex(window.name));break;default:opt.area=opt.area||["900px","650px"];var width=$(opt._this).width(),height=$(opt._this).height();if(typeof opt.area=="object"){if(opt.area[0].replace("px","")>width){opt.area[0]="100%"}if(opt.area[1].replace("px","")>height){opt.area[1]="100%"}}else{if(opt.area.replace("px","")>width){opt.area="100%"}}return opt._this.layer.open({id:opt.id?opt.id:"LCMSLAYERIFRAME"+LCMS.util.randStr(4),type:opt.type?opt.type:opt.url.indexOf("<div")!==-1?1:2,title:opt.title,content:opt.url,area:opt.area,maxmin:opt.maxmin,closeBtn:opt.closeBtn,offset:opt.offset,anim:opt.anim,resize:opt.resize,scrollbar:opt.scrollbar,shade:typeof opt.shade=="object"?opt.shade:opt.shade>0?0.3:0,success:function(layero,index){opt.automax&&opt._this.layer.full(index);opt._this.layer.setTop(layero)},cancel:function(layero,index){opt.cancel&&opt.cancel()},end:function(){opt.end&&opt.end()},});break}},tpl:function(tpl,arr){if(!tpl)return"";for(var key in arr){var reg=new RegExp("{"+key+"}","g");arr[key]=arr[key]?arr[key]:"";tpl=tpl.replace(reg,arr[key])}return tpl},sleep:function(time){var start=new Date().getTime();while(new Date().getTime()-start<time){continue}},getQuery:function(name,url){url=url?new URL(url).search:location.search;var paras=new URLSearchParams(url);return paras.get(name)},changeUrl:function(url,arg,arg_val){var pattern=arg+"=([^&]*)";var replaceText=arg+"="+arg_val;if(url.match(pattern)){var reg=new RegExp("("+arg+"=)([^&]*)","gi");return url.replace(reg,replaceText)}else{if(url.match("[?]")){return url+"&"+replaceText}else{return url+"?"+replaceText}}},getDate:function(fmt,date){date=date||new Date();let ret,opt={"Y+":date.getFullYear().toString(),"m+":(date.getMonth()+1).toString(),"d+":date.getDate().toString(),"H+":date.getHours().toString(),"i+":date.getMinutes().toString(),"s+":date.getSeconds().toString()};for(let k in opt){ret=new RegExp("("+k+")").exec(fmt);if(ret){fmt=fmt.replace(ret[1],ret[1].length==1?opt[k]:opt[k].padStart(ret[1].length,"0"))}}return fmt},play:function(opt){opt=Object.assign({type:"audio",src:"",loop:0},opt||{});if(opt.src)switch(opt.type){case"audio":var audio=new Audio();audio.src=opt.src;audio.loop=opt.loop==1?1:0;audio.preload="meta";audio.play();return audio;break}},inUa:function(ua){ua=ua?ua:"micromessenger";if(window.navigator.userAgent.toLowerCase().indexOf(ua.toLowerCase())!=-1){return true}else{return false}},speak:function(text){if("speechSynthesis"in window&&typeof SpeechSynthesisUtterance=="function"){window.speechSynthesis.speak(new SpeechSynthesisUtterance(text))}},copy:function(text,tips){function copy(){var textString=text.toString(),element=document.createElement("input"),selectText=function(textbox,startIndex,stopIndex){if(textbox.createTextRange){const range=textbox.createTextRange();range.collapse(true);range.moveStart("character",startIndex);range.moveEnd("character",stopIndex-startIndex);range.select()}else{textbox.setSelectionRange(startIndex,stopIndex);textbox.focus()}};element.id="lcms-copy-input";element.readOnly="readOnly";element.style.position="fixed";element.style.left="-1000px";element.style.bottom="0";element.style.zIndex="-1000";document.body.appendChild(element);element.value=textString;selectText(element,0,textString.length);try{document.execCommand("copy");element.remove();LCMS.util.notify({type:"success",content:tips?tips:"已复制到剪贴板"})}catch(error){LCMS.util.notify({type:"error",content:"复制失败"})}}if(typeof navigator.clipboard=="object"){navigator.clipboard.writeText(text).then(function(){LCMS.util.notify({type:"success",content:tips?tips:"已复制到剪贴板"})}).catch(()=>{copy()})}else{copy()}},lazyImg:function(cname,options){setTimeout(function(){cname=cname||"lazyload";options=options||{};options=Object.assign({datasrc:"data-src"},options);var imgs=$("img."+cname);if(imgs.length>0){imgs.each(function(){var _this=$(this),place="/public/static/images/pixel.png";if(_this.attr(options.datasrc)){var src=_this.attr(options.datasrc);_this.removeAttr(options.datasrc);_this.attr("src",place);setTimeout(function(){var img=new Image();img.src=src;img.onload=function(){_this.fadeOut(0);_this.removeClass(cname).attr("src",src);_this.fadeIn(500);options.success&&options.success(_this,img)};img.onerror=function(){_this.fadeOut(0);_this.addClass(cname+"-nopic").removeClass(cname);_this.fadeIn(500);options.error&&options.error(_this,img)}},10)}})}},10)},getFun:function(fname){if(fname)try{var func=new Function("return "+fname)();if(typeof func=="function"){return func}}catch(err){console.error(fname+"方法不存在")}return function(){}},crypto:function(type,options){typeof CryptoJS!="object"&&LCMS.util.load({type:"js",src:"/public/static/plugin/crypto-js.min.js?v=20240206",async:false});if(options){switch(type){case"encode":case"decode":if(!options.text||!options.key){return false}var key=(iv=CryptoJS.enc.Utf8.parse(CryptoJS.MD5(options.key).toString()));break}switch(type){case"encode":return CryptoJS.AES.encrypt(options.text,key,{iv:iv,mode:CryptoJS.mode.CBC,padding:CryptoJS.pad.Pkcs7}).toString();break;case"decode":return CryptoJS.enc.Utf8.stringify(CryptoJS.AES.decrypt(options.text,key,{iv:iv,mode:CryptoJS.mode.CBC,padding:CryptoJS.pad.Pkcs7}));break;case"md5":return CryptoJS.MD5(options).toString();break;case"hash":return CryptoJS.SHA1(options).toString();break}}},};var LJS={_lazydo:function(callback,t){setTimeout(function(){callback&&callback()},t?t:1000)},_lazyload:function(imgs){setTimeout(function(){imgs=imgs?imgs:$("img.lazyload");if(imgs.length>0){$("head").append('<style type="text/css">img.lazyload{min-width:20px;min-height:20px;max-height:300px;background:url(/public/static/images/loading.gif?v=20231102) no-repeat center center / 20px 20px!important;transition:none!important}</style>');imgs.lazyload({data_attribute:"src",effect:"fadeIn",threshold:200,failure_limit:10,skip_invisible:false})}},10)},_lazylunbo:function(cname){$("head").append('<style type="text/css">img.lazylunbo{min-width:20px;min-height:20px;max-height:300px;background:url(/public/static/images/loading.gif?v=20231102) no-repeat center center / 20px 20px!important;transition:none!important}img.lazylunbo-nopic{min-width:20px;min-height:20px;max-height:300px;background:url(/public/static/images/nopic.png?v=20231103) no-repeat center center / 50px 50px!important;transition:none!important}</style>');LCMS.util.lazyImg(cname?cname:"lazylunbo")},};typeof layer!="object"&&LCMS.util.load({type:"js",src:"/public/static/layer/layer.js?v=2.9.7",async:false});typeof $().lazyload!="function"&&LCMS.util.load({type:"js",src:"/public/static/plugin/lazyload.min.js?v=20231106",async:false});
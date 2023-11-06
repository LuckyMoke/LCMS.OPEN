if(!LCMS||typeof LCMS!="object"){var LCMS={}}LCMS.util={loading:function(type,index){switch(type){case"close":index?layer.close(index):layer.closeAll("loading");break;default:return layer.load(1,{shade:[0.6,"#fff"]});break}},notify:function(opt){opt=opt||{};if(opt.content){switch(opt.type){case"error":layer.msg(opt.content,{icon:2,anim:6,time:1000});break;case"warning":layer.msg(opt.content,{icon:7,time:1000});break;case"info":layer.msg(opt.content,{time:1000});break;default:layer.msg(opt.content,{icon:1,time:1000});break}}},load:function(opt){opt=opt||{};var canLoad=true;if(opt.src)switch(opt.type){case"css":$("head").append('<link rel="stylesheet" href="'+opt.src+'">');break;case"js":if(opt.elem&&opt.elem.length<=0){canLoad=false}canLoad&&$.ajax({type:"GET",url:opt.src,dataType:"script",async:opt.async!==false?true:false,cache:true});break}},ajax:function(opt){opt=Object.assign({type:"GET",data:{},dataType:"json",async:true,cache:false,success:function(res){},loading:false},opt||{});if(opt.timeout==undefined||opt.timeout===""){opt.timeout=15000}else if(opt.timeout===0||opt.timeout==="0"){opt.timeout=0}else{opt.timeout=opt.timeout*1}let loadindex=null;if(opt.type=="POST"||opt.loading){loadindex=LCMS.util.loading()}if(opt.type=="GET"&&opt.data){for(const key in opt.data){opt.url+="&"+key+"="+opt.data[key]}opt.data={}}$.ajax({url:opt.url,data:opt.data,type:opt.type,dataType:opt.dataType,async:opt.async,cache:opt.cache,timeout:opt.timeout,beforeSend:function(xhr){if(opt.headers){for(const key in opt.headers){xhr.setRequestHeader(key,opt.headers[key])}}opt.beforeSend&&opt.beforeSend(xhr)},success:function(result,status,xhr){opt.success(result,status,xhr)},error:function(xhr,status,error){if(opt.error){opt.error(xhr,status,error)}else{opt.success&&opt.success({code:0,msg:"数据加载失败"})}},complete:function(xhr,status){loadindex&&LCMS.util.loading("close",loadindex);opt.complete&&opt.complete(xhr,status)},})},randStr:function(len,type){len=len||4;switch(type){case"num":case"number":var chars="0123456789";break;case"let":case"letter":var chars="ABCDEFGHJKMNPQRSTWXYZ";break;default:var chars="ABCDEFGHJKMNPQRSTWXYZ23456789";break}var str="";for(var i=0;i<len;i++){str+=chars.charAt(Math.floor(Math.random()*chars.length))}return str},iframe:function(opt){opt=opt||{};opt=Object.assign({do:"open",id:null,title:" ",area:"",maxmin:true,closeBtn:true,resize:true,scrollbar:true,automax:false,offset:"auto",anim:0,reload:false,_this:self},opt);switch(opt.do){case"close":parent.layer.close(parent.layer.getFrameIndex(window.name));opt.reload&&parent.location.reload();break;case"max":parent.layer.full(parent.layer.getFrameIndex(window.name));break;default:opt.area=opt.area||["900px","650px"];var width=$(opt._this).width(),height=$(opt._this).height();if(typeof opt.area=="object"){if(opt.area[0].replace("px","")>width){opt.area[0]="100%"}if(opt.area[1].replace("px","")>height){opt.area[1]="100%"}}else{if(opt.area.replace("px","")>width){opt.area="100%"}}return opt._this.layer.open({id:opt.id?opt.id:"LCMSLAYERIFRAME"+LCMS.util.randStr(4),type:opt.type?opt.type:opt.url.indexOf("<div")!==-1?1:2,title:opt.title,content:opt.url,area:opt.area,maxmin:opt.maxmin,closeBtn:opt.closeBtn,offset:opt.offset,anim:opt.anim,resize:opt.resize,scrollbar:opt.scrollbar,shade:opt.shade>0?0.3:0,success:function(layero,index){opt.automax&&opt._this.layer.full(index);opt._this.layer.setTop(layero)},});break}},tpl:function(tpl,arr){if(!tpl)return"";for(var key in arr){var reg=new RegExp("{"+key+"}","g");arr[key]=arr[key]?arr[key]:"";tpl=tpl.replace(reg,arr[key])}return tpl},sleep:function(time){var start=new Date().getTime();while(new Date().getTime()-start<time){continue}},getQuery:function(name,url){url=url?new URL(url).search:location.search;var paras=new URLSearchParams(url);return paras.get(name)},changeUrl:function(url,arg,arg_val){var pattern=arg+"=([^&]*)";var replaceText=arg+"="+arg_val;if(url.match(pattern)){var reg=new RegExp("("+arg+"=)([^&]*)","gi");return url.replace(reg,replaceText)}else{if(url.match("[?]")){return url+"&"+replaceText}else{return url+"?"+replaceText}}},getDate:function(fmt,date){date=date||new Date();let ret,opt={"Y+":date.getFullYear().toString(),"m+":(date.getMonth()+1).toString(),"d+":date.getDate().toString(),"H+":date.getHours().toString(),"i+":date.getMinutes().toString(),"s+":date.getSeconds().toString()};for(let k in opt){ret=new RegExp("("+k+")").exec(fmt);if(ret){fmt=fmt.replace(ret[1],ret[1].length==1?opt[k]:opt[k].padStart(ret[1].length,"0"))}}return fmt},play:function(opt){opt=Object.assign({type:"audio",src:"",loop:0},opt||{});if(opt.src)switch(opt.type){case"audio":var audio=new Audio();audio.src=opt.src;audio.loop=opt.loop==1?1:0;audio.preload="meta";audio.play();return audio;break}},inUa:function(ua){ua=ua?ua:"micromessenger";if(window.navigator.userAgent.toLowerCase().indexOf(ua.toLowerCase())!=-1){return true}else{return false}},speak:function(text){if("speechSynthesis"in window&&typeof SpeechSynthesisUtterance=="function"){window.speechSynthesis.speak(new SpeechSynthesisUtterance(text))}},copy:function(text){function copy(){var textString=text.toString(),element=document.createElement("input"),selectText=function(textbox,startIndex,stopIndex){if(textbox.createTextRange){const range=textbox.createTextRange();range.collapse(true);range.moveStart("character",startIndex);range.moveEnd("character",stopIndex-startIndex);range.select()}else{textbox.setSelectionRange(startIndex,stopIndex);textbox.focus()}};element.id="lcms-copy-input";element.readOnly="readOnly";element.style.position="absolute";element.style.left="-1000px";element.style.bottom="0";element.style.zIndex="-1000";document.body.appendChild(element);element.value=textString;selectText(element,0,textString.length);try{document.execCommand("copy");element.remove();LCMS.util.notify({type:"success",content:"已复制到剪贴板"})}catch(error){LCMS.util.notify({type:"error",content:"复制失败"})}}if(typeof navigator.clipboard=="object"){navigator.clipboard.writeText(text).then(function(){LCMS.util.notify({type:"success",content:"已复制到剪贴板"})}).catch(()=>{copy()})}else{copy()}},lazyImg:function(cname,options){cname=cname||"lazyload";options=options||{};options=Object.assign({datasrc:"data-src"},options);var imgs=$("img."+cname);if(imgs.length>0){imgs.each(function(){var _this=$(this),place="/public/static/images/pixel.png";if(_this.attr(options.datasrc)){var src=_this.attr(options.datasrc);_this.removeAttr(options.datasrc);_this.attr("src",place);setTimeout(function(){var img=new Image();img.src=src;img.onload=function(){_this.fadeOut(0);_this.removeClass(cname).attr("src",src);_this.fadeIn(500);options.success&&options.success(_this,img)};img.onerror=function(){_this.fadeOut(0);_this.addClass(cname+"-nopic").removeClass(cname);_this.fadeIn(500);options.error&&options.error(_this,img)}},10)}})}},getFun:function(fname){if(fname)try{var func=new Function("return "+fname)();if(typeof func=="function"){return func}}catch(err){console.error(fname+"方法不存在")}return function(){}},};var LJS={_lazydo:function(callback,t){setTimeout(function(){callback&&callback()},t?t:1000)},_lazyload:function(imgs){imgs=imgs?imgs:$("img.lazyload");if(imgs.length>0){$("head").append('<style type="text/css">img.lazyload{min-width:20px;min-height:20px;max-height:300px;background:url(/public/static/images/loading.gif?v=20231102) no-repeat center center / 20px 20px!important;transition:none!important}</style>');imgs.lazyload({data_attribute:"src",effect:"fadeIn",threshold:200,failure_limit:10,skip_invisible:false})}},_lazylunbo:function(cname){$("head").append('<style type="text/css">img.lazylunbo{min-width:20px;min-height:20px;max-height:300px;background:url(/public/static/images/loading.gif?v=20231102) no-repeat center center / 20px 20px!important;transition:none!important}img.lazylunbo-nopic{min-width:20px;min-height:20px;max-height:300px;background:url(/public/static/images/nopic.png?v=20231103) no-repeat center center / 50px 50px!important;transition:none!important}</style>');LCMS.util.lazyImg(cname?cname:"lazylunbo")},};typeof layer!="object"&&LCMS.util.load({type:"js",src:"/public/static/layer/layer.js?v=2.8.18",async:false});typeof $().lazyload!="function"&&LCMS.util.load({type:"js",src:"/public/static/plugin/lazyload.min.js?v=20231106",async:false});
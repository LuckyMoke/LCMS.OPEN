"undefined"==typeof top.LCMSUIINDEX&&(top.LCMSUIINDEX=0);"undefined"==typeof top.LCMSEDITORFOCUSID&&(top.LCMSEDITORFOCUSID=null);let LCMSTIPS=LCMS.util.getQuery("lcmstips");LCMSTIPS&&layui.notice.info(LCMSTIPS);let OSS_base={};LCMS.plugin.upload.data2file=function(dataURL,filename){if("string"!=typeof dataURL||"data:"!=dataURL.slice(0,5)){return}const mimes={"image/jpeg":"jpeg","image/png":"png","image/bmp":"bmp","image/gif":"gif","image/webp":"webp","image/vnd.wap.wbmp":"wbmp","image/x-up-wpng":"wpng","image/x-icon":"ico","image/vnd.microsoft.icon":"ico","image/svg+xml":"svg","image/tiff":"tiff"};const parts=dataURL.split(",");const mimeString=parts[0].match(/:(.*?);/)[1];const suffix=mimes[mimeString]||"jpg";const byteString=atob(parts[1]);const arrayBuffer=new ArrayBuffer(byteString.length);const intArray=new Uint8Array(arrayBuffer);const name=LCMS.util.getDate("YmdHis")+LCMS.util.randStr();for(let i=0;i<byteString.length;i++){intArray[i]=byteString.charCodeAt(i)}const blob=new Blob([intArray],{type:mimeString});filename=filename||`${name}.${suffix}`;return new File([blob],filename,{type:mimeString})};LCMS.plugin.upload.gallery=function(callback,many){if(!callback){console.error("未指定回调函数");return}LCMS.plugin.upload.callback=callback;let area=["550px","550px"];if($(document).width()<=600){area=["96%","96%"]}many=many||0;LCMS.util.iframe({title:"图库",url:`${LCMS.url.own}t=sys&n=upload&c=gallery&many=${many}&callback=1`,shade:true,maxmin:false,area:area})};LCMS.plugin.upload.direct=function(opt){opt=Object.assign({type:"image",local:0,cid:0,success:function(){},error:function(){},complete:function(){}},opt||{});if(!opt.file){opt.error({code:0,msg:"文件不能为空"});opt.complete();return}if(true===opt.local||opt.local>0){opt.local=1}else{opt.local=0}let doUpload,ossType=opt.local>0?"local":LCMS.config.oss,uploadProgressTimer=false,uploadProgressId="lcms-upload-progress-"+top.LCMSUIINDEX,uploadProgress=function(type,percent){switch(type){case"show":if(false!==uploadProgressTimer){return false}uploadProgressTimer=true;if($(".lcms-upload-progress").length<=0){$("body").append('<div class="lcms-upload-progress lcms-animation-movet2b"></div>')}$(".lcms-upload-progress").css({top:"0"});$(".lcms-upload-progress").append(`<div class="layui-progress ${uploadProgressId}" lay-showPercent="true" lay-filter="${uploadProgressId}"><div class="layui-progress-bar layui-bg-orange" lay-percent="0%"></div><div class="lcms-upload-progress-name">${opt.file.name?opt.file.name:""}</div></div>`);layui.element.render("progress",uploadProgressId);percent=0;uploadProgressTimer=setInterval((()=>{layui.element.progress(uploadProgressId,percent.toFixed(2)+"%");percent+=parseFloat((3*Math.random()/10).toFixed(2));if(percent>99){clearInterval(uploadProgressTimer)}}),300);break;case"set":clearInterval(uploadProgressTimer);layui.element.progress(uploadProgressId,percent.toFixed(2)+"%");break;case"remove":clearInterval(uploadProgressTimer);setTimeout((()=>{layui.element.progress(uploadProgressId,"100.00%");setTimeout((()=>{$("."+uploadProgressId).slideUp(300,(function(){$("."+uploadProgressId).remove();if($(".lcms-upload-progress .layui-progress").length<=0){$(".lcms-upload-progress").remove()}}))}),1500)}),300);break}};top.LCMSUIINDEX++;if("url"==opt.type){if(!LCMS.config.isupload.img){LCMS.util.notify({type:"error",content:"您没有权限上传图片"});opt.error({code:0,msg:"您没有权限上传图片"});opt.complete()}else if(opt.file&&-1!=opt.file.indexOf("://")){uploadProgress("show");LCMS.util.ajax({type:"GET",url:LCMS.url.admin+"index.php?t=sys&n=upload&c=index&a=local&type=image&force="+opt.local,data:{url:opt.file},timout:0,layer:true,success:function(res){if(1==res.code){opt.success(res)}else{LCMS.util.notify({type:"error",content:res.msg});opt.error(res)}},error:function(){LCMS.util.notify({type:"error",content:"上传失败，可能是你的服务器不支持上传大文件"});opt.error({code:0,msg:"上传失败，可能是你的服务器不支持上传大文件"})},complete:function(){uploadProgress("remove");opt.complete()}})}else{LCMS.util.notify({type:"error",content:"图片链接错误"});opt.error({code:0,msg:"图片链接错误"});opt.complete()}return false}if(!(opt.file instanceof Blob||opt.file instanceof File)){opt.file=LCMS.plugin.upload.data2file(opt.file)}if("local"!=ossType){let OSS_date=new Date;OSS_base.datey=OSS_date.getFullYear().toString()+(OSS_date.getMonth()+1<10?"0"+(OSS_date.getMonth()+1):OSS_date.getMonth()+1);OSS_base.mime=function(name){let arr=name.split(".");return arr[arr.length-1].toLowerCase()};OSS_base.init=function(){OSS_base.size=opt.file.size;OSS_base.format=OSS_base.mime(opt.file.name);OSS_base.oname=opt.file.name;OSS_base.name=LCMS.util.getDate("ddHHiiss",OSS_date)+LCMS.util.randStr(6)+"."+OSS_base.format;OSS_base.path="upload/"+LCMS.ROOTID+"/"+opt.type+"/"+OSS_base.datey+"/"+OSS_base.name};OSS_base.check=function(){if(!OSS_base.token){uploadProgress("remove");LCMS.util.notify({type:"error",content:"上传接口配置错误"});opt.error({code:0,msg:"上传接口配置错误"});return false}if(-1==LCMS.config.mimelist.indexOf(OSS_base.format)){uploadProgress("remove");LCMS.util.notify({type:"error",content:"禁止上传此格式文件"});opt.error({code:0,msg:"禁止上传此格式文件"});return false}return true};OSS_base.save=function(osstype){LCMS.util.ajax({type:"POST",url:LCMS.url.admin+"index.php?t=sys&n=upload&c=index&a="+osstype,data:{action:"success",type:opt.type,cid:opt.cid,oname:OSS_base.oname,name:OSS_base.name,file:OSS_base.path,datey:OSS_base.datey,size:OSS_base.size},layer:true,success:function(res){if(1==res.code){opt.success(res)}else{LCMS.util.notify({type:"error",content:res.msg});opt.error(res)}},complete:function(){uploadProgress("remove");opt.complete()}})};switch(LCMS.config.oss){case"qiniu":doUpload=async()=>{OSS_base.init();if(!OSS_base.token){await new Promise((resolve=>{LCMS.util.load({src:`${LCMS.url.site}core/plugin/Qiniu/static/qiniu.min.js`,onload:()=>{LCMS.util.ajax({type:"GET",url:`${LCMS.url.admin}index.php?t=sys&n=upload&c=index&a=qiniu&action=token`,layer:true,success:res=>{if(1==res.code){OSS_base.token=res.data.token}resolve()}})}})}))}OSS_base.check()&&qiniu.upload(opt.file,OSS_base.path,OSS_base.token,{},{useCdnDomain:true,disableStatisticsReport:true,retryCount:1}).subscribe({next:function(res){if(res.total&&res.total.percent){uploadProgress("set",res.total.percent)}},error:function(res){LCMS.util.notify({type:"error",content:res.message});uploadProgress("remove");opt.error({code:0,msg:res.message});opt.complete()},complete:function(){OSS_base.save("qiniu")}})};break;case"tencent":OSS_base.init();let OSS_tencent;doUpload=async()=>{if(!OSS_base.token){await new Promise((resolve=>{LCMS.util.load({src:`${LCMS.url.site}core/plugin/Tencent/static/cos-js-sdk-v5.min.js`,onload:()=>{LCMS.util.ajax({type:"GET",url:`${LCMS.url.admin}index.php?t=sys&n=upload&c=index&a=tencent&action=token`,layer:true,success:function(res){if(1==res.code){OSS_base.token=res.data;let Credentials=res.data.Credentials;if(Credentials){OSS_tencent=new COS({SecretId:Credentials.TmpSecretId,SecretKey:Credentials.TmpSecretKey,XCosSecurityToken:Credentials.Token})}}resolve()}})}})}))}OSS_base.check()&&OSS_tencent.uploadFile({Bucket:OSS_base.token.Bucket,Region:OSS_base.token.Region,Key:OSS_base.path,Body:opt.file,onProgress:function(res){if(res.percent){uploadProgress("set",100*res.percent)}}},(function(err){if(err){LCMS.util.notify({type:"error",content:err});uploadProgress("remove");opt.error(err);opt.complete()}else{OSS_base.save("tencent")}}))};break;case"aliyun":doUpload=async()=>{OSS_base.init();await new Promise((resolve=>{LCMS.util.ajax({type:"GET",url:`${LCMS.url.admin}index.php?t=sys&n=upload&c=index&a=aliyun&action=token&path=${OSS_base.path}`,layer:true,success:function(res){if(1==res.code){OSS_base.token=res.data}resolve()}})}));if(!OSS_base.check()){return false}let formData=new FormData;formData.append("file",opt.file);LCMS.util.ajax({type:"PUT",url:OSS_base.token.api,data:formData,headers:OSS_base.token.headers,dataType:"html",processData:false,contentType:false,timeout:0,loading:false,layer:true,progress:function(e){console.log(e);if("upload"==e.type){uploadProgress("set",e.loaded/e.total*100)}},success:function(res,status,xhr){console.log(res,status,xhr);if(200==xhr.status){OSS_base.save("aliyun")}else{LCMS.util.notify({type:"error",content:"状态码："+xhr.status});uploadProgress("remove");opt.error({code:0,msg:"状态码："+xhr.status});opt.complete()}},error:function(xhr){LCMS.util.notify({type:"error",content:"上传接口无法访问，请检查云存储的跨域CORS设置"});uploadProgress("remove");opt.error({code:0,msg:"上传接口无法访问，请检查云存储的跨域CORS设置"});opt.complete()}})};break;case"baidu":doUpload=async()=>{OSS_base.init();if(!OSS_base.token){await new Promise((resolve=>{LCMS.util.ajax({type:"GET",url:`${LCMS.url.admin}index.php?t=sys&n=upload&c=index&a=baidu&action=token`,layer:true,success:function(res){if(1==res.code){OSS_base.token=res.data}resolve()}})}))}if(!OSS_base.check()){return false}let formData=new FormData;formData.append("key",OSS_base.path);formData.append("policy",OSS_base.token.policy);formData.append("accessKey",OSS_base.token.AccessKey);formData.append("signature",OSS_base.token.signature);formData.append("success-action-status",200);formData.append("file",opt.file);LCMS.util.ajax({type:"POST",url:OSS_base.token.api,data:formData,dataType:"html",processData:false,contentType:false,timeout:0,loading:false,layer:true,progress:function(e){if("upload"==e.type){uploadProgress("set",e.loaded/e.total*100)}},success:function(res,status,xhr){if(200==xhr.status){OSS_base.save("baidu")}else{LCMS.util.notify({type:"error",content:"状态码："+xhr.status});uploadProgress("remove");opt.error({code:0,msg:"状态码："+xhr.status});opt.complete()}},error:function(xhr){LCMS.util.notify({type:"error",content:"上传接口无法访问，请检查云存储的跨域CORS设置"});uploadProgress("remove");opt.error({code:0,msg:"上传接口无法访问，请检查云存储的跨域CORS设置"});opt.complete()}})};break}}else{doUpload=function(){let formData=new FormData;formData.append("file",opt.file,opt.file.name);LCMS.util.ajax({type:"POST",url:`${LCMS.url.admin}index.php?t=sys&n=upload&c=index&a=local&type=${opt.type}&force=${opt.local}&cid=${opt.cid}`,data:formData,processData:false,contentType:false,dataType:"json",timeout:0,loading:false,layer:true,progress:function(e){if("upload"==e.type){uploadProgress("set",e.loaded/e.total*100)}},success:function(res){if(1==res.code){opt.success(res)}else{LCMS.util.notify({type:"error",content:res.msg});opt.error(res)}},error:function(){LCMS.util.notify({type:"error",content:"上传失败，可能是你的服务器不支持上传大文件"});opt.error({code:0,msg:"上传失败，可能是你的服务器不支持上传大文件"})},complete:function(){uploadProgress("remove");opt.complete()}})}}if("image"==opt.type&&!LCMS.config.isupload.img){LCMS.util.notify({type:"error",content:"您没有权限上传图片"});opt.error({code:0,msg:"您没有权限上传图片"});opt.complete();return false}else if("file"==opt.type&&!LCMS.config.isupload.file){LCMS.util.notify({type:"error",content:"您没有权限上传文件"});opt.error({code:0,msg:"您没有权限上传文件"});opt.complete();return false}const checkAttSize=function(opt,ret){uploadProgress("show");if("image"==opt.type){if(opt.file.size/1024>LCMS.config.attsize){if(ret){return false}uploadProgress("remove");LCMS.util.notify({type:"error",content:"图片大小超过"+LCMS.config.attsize+"KB"});opt.error({code:0,msg:"图片大小超过"+LCMS.config.attsize+"KB"});opt.complete()}else{return true}}else if("file"==opt.type){if(opt.file.size/1024>LCMS.config.attsize_file){if(ret){return false}uploadProgress("remove");LCMS.util.notify({type:"error",content:"文件大小超过"+LCMS.config.attsize_file+"KB"});opt.error({code:0,msg:"文件大小超过"+LCMS.config.attsize_file+"KB"});opt.complete()}else{return true}}};if(-1!==["image/jpg","image/jpeg","image/png","image/bmp","image/webp"].indexOf(opt.file.type)){uploadProgress("show");if(checkAttSize(opt,true)){doUpload()}else{imageCompression(opt.file,{libURL:LCMS.url.static+"plugin/compression.min.js",maxSizeMB:Math.round(LCMS.config.attsize/10.24)/100,maxWidthOrHeight:2560,maxIteration:20,initialQuality:1,fileType:LCMS.config.attwebp>0?"image/webp":opt.file.type}).then((function(file){opt.file=file;checkAttSize(opt)&&doUpload()})).catch((function(error){uploadProgress("remove");LCMS.util.notify({type:"error",content:error.message});opt.error({code:0,msg:error.message})}))}}else{checkAttSize(opt)&&doUpload()}};LCMS.util.load({src:[`${LCMS.url.static}plugin/base64.min.js`,`${LCMS.url.static}plugin/sortable.min.js`,`${LCMS.url.static}plugin/compression.min.js`],onload:()=>{if($("#APPNAV").length>0){if(self!=top){}$("#APPNAV a").on("click",(function(e){const target=$(this).attr("target");if(!target||"_self"==target){e.preventDefault();top.LCMS.plugin.router($(this).attr("href"),{loading:500})}}));if(self==top||$("#LCMS",parent.document).length>0){$("#APPNAV .button").addClass("active")}$("#APPNAV .button").on("click",(function(){$("#APPNAV .background").fadeIn(200);$("#APPNAV .menu").stop().animate({left:0},100)}));$("#APPNAV .background").on("click",(function(){$("#APPNAV .background").fadeOut(200);$("#APPNAV .menu").stop().animate({left:"-160px"},100)}))}if($("#APPTAB").length>0){$("#APPTAB a").on("click",(function(e){const target=$(this).attr("target");if(!target||"_self"==target){e.preventDefault();top.LCMS.plugin.router($(this).attr("href"),{loading:500})}}))}if(!!navigator.userAgent.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/)){$("html,body").css({"max-width":window.screen.width})}const jsList={form:"layui-form",select:"lcms-form-select",selectN:"lcms-form-selectN",tags:"lcms-form-tags",color:"lcms-form-colorpicker",slider:"lcms-form-slider",date:"lcms-form-date",upload:"lcms-form-upload-img",file:"lcms-form-upload-file",editor:"lcms-form-editor",table:"lcms-table",radio:"lcms-form-radio-tab",spec:"lcms-form-spec",icon:"lcms-form-icon",copy:"lcms-form-copy"};for(const key in jsList){LCMS.util.load({src:`${LCMS.url.public}ui/admin/static/mod/${key}.js`,elem:$("."+jsList[key])})}if(LCMS.config.aichat){LCMS.util.load({src:`${LCMS.url.public}ui/admin/static/mod/aimodel.js`})}else{LCMS.plugin.aimodel={chat:function(){LCMS.util.notify({type:"error",content:"AI助手未开启，请到设置->接口设置->AI接口中开启！"})}}}if(LCMS.app&&""!=LCMS.app.js){LCMS.util.load({src:`${LCMS.url.own_path}admin/tpl/static/${LCMS.app.js}?v=`+(LCMS.app&&LCMS.app.ver?LCMS.app.ver:"1.0.0")})}if($(".lcms-main-index").length<=0){layui.util.fixbar()}$(`script[type="text/html"][onload]`).each((function(){const text=$(this).html();$(this).replaceWith(`<script type="text/javascript">${text}<\/script>`)}));if(LCMS.onload&&"object"==typeof LCMS.onload){for(let i=0;i<LCMS.onload.length;i++){"function"==typeof LCMS.onload[i]&&LCMS.onload[i]()}}}});
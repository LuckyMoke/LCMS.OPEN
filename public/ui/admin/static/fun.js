LCMS.util.load({type:"js",src:LCMS.url.static+"plugin/base64.min.js",async:false});LCMS.util.load({type:"js",src:LCMS.url.static+"plugin/sortable.min.js",async:false});LCMS.util.load({type:"js",src:LCMS.url.static+"Lrz4/lrz.bundle.js",async:false});typeof top.LCMSUIINDEX=="undefined"&&(top.LCMSUIINDEX=0);typeof top.LCMSEDITORFOCUSID=="undefined"&&(top.LCMSEDITORFOCUSID=null);var LCMSTIPS=LCMS.util.getQuery("lcmstips");LCMSTIPS&&layui.notice.info(LCMSTIPS);if($("#APPNAV").length>0){if(self!=top){$("#APPNAV a").on("click",function(){top.history.pushState(null,null,"?"+$.base64.encode($(this).attr("href").match(/\?(.*)/)[0]))})}if(self==top||$("#LCMS",parent.document).length>0){$("#APPNAV .button").addClass("active")}$("#APPNAV .button").on("click",function(){$("#APPNAV .background").fadeIn(200);$("#APPNAV .menu").stop().animate({left:0},100)});$("#APPNAV .background").on("click",function(){$("#APPNAV .background").fadeOut(200);$("#APPNAV .menu").stop().animate({left:"-160px"},100)})}if($("#APPTAB").length>0){if(self!=top){$("#APPTAB a").on("click",function(){if($(this).attr("href").indexOf(LCMS.url.site)!=-1){var query=$(this).attr("href").match(/\?(.*)/);query&&top.history.pushState(null,null,"?"+$.base64.encode(query[0]))}})}}if(!!navigator.userAgent.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/)){$("html,body").css({"max-width":window.screen.width})}if(LCMS.config.oss!="local"){var OSS_date=new Date();var OSS_base={datey:OSS_date.getFullYear().toString()+(OSS_date.getMonth()+1<10?"0"+(OSS_date.getMonth()+1):OSS_date.getMonth()+1),mime:function(name){var arr=name.split(".");return arr[arr.length-1].toLowerCase()},stop:function(type){if(type=="image"&&!LCMS.config.isupload.img){LCMS.util.notify({type:"error",content:"您没有权限上传图片"});return true}else if(type=="file"&&!LCMS.config.isupload.file){LCMS.util.notify({type:"error",content:"您没有权限上传文件"});return true}},};OSS_base.init=function(name,type){OSS_base.format=OSS_base.mime(name);OSS_base.name=LCMS.util.getDate("ddHHiiss",OSS_date)+LCMS.util.randStr(6)+"."+OSS_base.format;OSS_base.file="upload/"+LCMS.ROOTID+"/"+type+"/"+OSS_base.datey+"/"+OSS_base.name};OSS_base.power=function(File){OSS_base.size=File.size;if(LCMS.config.mimelist.indexOf(OSS_base.format)==-1){return{code:0,msg:"禁止上传此格式文件"}}if(LCMS.config.attsize<Math.round(OSS_base.size/1000)){return{code:0,msg:"文件大小超过"+LCMS.config.attsize+"KB"}}};switch(LCMS.config.oss){case"qiniu":LCMS.util.load({type:"js",src:LCMS.url.site+"core/plugin/Qiniu/static/qiniu.min.js",async:false});var OSS_qiniu=qiniu;LCMS.util.ajax({type:"GET",url:LCMS.url.admin+"index.php?t=sys&n=upload&c=index&a=qiniu&action=token",layer:true,success:function(res){if(res.code==1){OSS_base.token=res.data.token}},});var OSS_upload=async function(type,File,success,error){type=type?type:"image";if(OSS_base.stop(type)){error&&error();return false}OSS_base.init(File.name,type);var power=OSS_base.power(File);if(power){LCMS.util.notify({type:"error",content:power.msg});error&&error(power);return false}OSS_qiniu.upload(File,OSS_base.file,OSS_base.token,{},{useCdnDomain:true,disableStatisticsReport:true,retryCount:1}).subscribe({error(res){LCMS.util.notify({type:"error",content:res.message});error&&error({code:0,msg:res.message})},complete(){LCMS.util.ajax({type:"POST",url:LCMS.url.admin+"index.php?t=sys&n=upload&c=index&a=qiniu",data:{action:"success",type:type,name:OSS_base.name,file:OSS_base.file,datey:OSS_base.datey,size:OSS_base.size},success:function(res){if(res.code==1){success&&success(res)}else{LCMS.util.notify({type:"error",content:rst.msg});error&&error(res)}},})},})};break;case"tencent":LCMS.util.load({type:"js",src:LCMS.url.site+"core/plugin/Tencent/static/cos-js-sdk-v5.min.js",async:false});var OSS_tencent;LCMS.util.ajax({type:"GET",url:LCMS.url.admin+"index.php?t=sys&n=upload&c=index&a=tencent&action=token",layer:true,success:function(res){if(res.code=="1"){OSS_base.token=res.data;var Token=res.data,Credentials=res.data.Credentials;if(Credentials){OSS_tencent=new COS({SecretId:Credentials.TmpSecretId,SecretKey:Credentials.TmpSecretKey,XCosSecurityToken:Credentials.Token})}}},});var OSS_upload=function(type,File,success,error){type=type?type:"image";if(OSS_base.stop(type)){error&&error();return false}OSS_base.init(File.name,type);var power=OSS_base.power(File);if(power){LCMS.util.notify({type:"error",content:power.msg});error&&error(power);return false}if(OSS_tencent==undefined){LCMS.util.notify({type:"error",content:"上传接口配置错误"});error&&error({code:0,msg:"上传接口配置错误"});return false}OSS_tencent.putObject({Bucket:OSS_base.token.Bucket,Region:OSS_base.token.Region,Key:OSS_base.file,Body:File},function(err,data){if(err){LCMS.util.notify({type:"error",content:err});error&&error(err)}else{LCMS.util.ajax({type:"POST",url:LCMS.url.admin+"index.php?t=sys&n=upload&c=index&a=tencent",data:{action:"success",type:type,name:OSS_base.name,file:OSS_base.file,datey:OSS_base.datey,size:OSS_base.size},success:function(res){if(res.code==1){success&&success(res)}else{LCMS.util.notify({type:"error",content:rst.msg});error&&error(res)}},})}})};break;case"aliyun":var OSS_aliyun;LCMS.util.ajax({type:"GET",url:LCMS.url.admin+"index.php?t=sys&n=upload&c=index&a=aliyun&action=token",layer:true,success:function(res){if(res.code==1){OSS_base.token=res.data}},});var OSS_upload=function(type,File,success,error){type=type?type:"image";if(OSS_base.stop(type)){error&&error();return false}OSS_base.init(File.name,type);var power=OSS_base.power(File);if(power){LCMS.util.notify({type:"error",content:power.msg});error&&error(power);return false}if(!OSS_base.token){LCMS.util.notify({type:"error",content:"上传接口配置错误"});error&&error({code:0,msg:"上传接口配置错误"});return false}var formData=new FormData();formData.append("key",OSS_base.file);formData.append("policy",OSS_base.token.policy);formData.append("OSSAccessKeyId",OSS_base.token.AccessKeyId);formData.append("signature",OSS_base.token.signature);formData.append("success_action_status","200");formData.append("file",File);$.ajax({url:OSS_base.token.api,data:formData,processData:false,contentType:false,type:"POST",success:function(res,status,xhr){if(xhr.status==200){LCMS.util.ajax({type:"POST",url:LCMS.url.admin+"index.php?t=sys&n=upload&c=index&a=aliyun",data:{action:"success",type:type,name:OSS_base.name,file:OSS_base.file,datey:OSS_base.datey,size:OSS_base.size},success:function(res){if(res.code==1){success&&success(res)}else{LCMS.util.notify({type:"error",content:rst.msg});error&&error(res)}},})}},error:function(xhr){LCMS.util.notify({type:"error",content:"上传接口无法访问，请检查云存储的跨域CORS设置"});error&&error({code:0,msg:"上传接口无法访问，请检查云存储的跨域CORS设置"})},})};break}}var LOC_upload=function(type,File,success,complete,force){type=type?type:"image";if(type=="image"&&!LCMS.config.isupload.img){LCMS.util.notify({type:"error",content:"您没有权限上传图片"});return false}else if(type=="file"&&!LCMS.config.isupload.file){LCMS.util.notify({type:"error",content:"您没有权限上传文件"});return false}var upload=function(formData){$.ajax({url:LCMS.url.admin+"index.php?t=sys&n=upload&c=index&a=local&type="+type+"&force="+(force?force:0),data:formData,processData:false,contentType:false,type:"POST",dataType:"json",success:function(res){success&&success(res)},error:function(){LCMS.util.notify({type:"error",content:"上传失败，可能是你的服务器不支持上传大文件"})},complete:function(){complete&&complete()},})};var mime=File.type;if(mime=="image/jpg"||mime=="image/jpeg"){var quality=LCMS.config.attquality>0||LCMS.config.attwebp>0?parseFloat((LCMS.config.attquality/100).toFixed(1)):0.7;lrz(File,{width:quality==1?null:1980,quality:quality}).then(function(res){upload(res.formData)})}else{var formData=new FormData();formData.append("file",File);upload(formData)}};const jsList={form:"layui-form",select:"lcms-form-select",selectN:"lcms-form-selectN",tags:"lcms-form-tags",color:"lcms-form-colorpicker",slider:"lcms-form-slider",date:"lcms-form-date",upload:"lcms-form-upload-img",file:"lcms-form-upload-file",editor:"lcms-form-editor",table:"lcms-table",radio:"lcms-form-radio-tab",spec:"lcms-form-spec",icon:"lcms-form-icon"};for(const key in jsList){LCMS.util.load({type:"js",src:key+".js",elem:$("."+jsList[key])})}LCMS.url.own_path&&LCMS.util.load({type:"js",src:LCMS.url.own_path+"admin/tpl/static/fun.js?v="+(LCMS.app&&LCMS.app.ver?LCMS.app.ver:"1.0.0")});layui.util.fixbar();
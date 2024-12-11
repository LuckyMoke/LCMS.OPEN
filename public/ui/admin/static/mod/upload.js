let uiImageTpl='<div class="_li"><a href="{src}" target="_blank"><img class="layui-upload-img lazyload" data-lazy="{src}" data-original="{original}"/></a><div class="_icon"><div class="_del"><i class="layui-icon layui-icon-close"></i></div></div></div>',uiImageLazy=function(){LCMS.util.lazyImg("lazyload",{datasrc:"data-lazy"})},uiImageSetValue=function(arr={}){let list=[];return arr.each((function(){list.push($(this).attr("data-original"))})),list.join("|")},uiImageAdd=function(id,list,callback){let _this=$("#"+id),input=_this.parent(".layui-input-block").siblings("input"),action=input.attr("data-many")?"append":"html",html="";if(!list||""==list){return}if(!list[0]){list=[list]}if("html"==action){list=[list.slice(-1)[0]]}for(let i=0;i<list.length;i++){const li=list[i];html+=LCMS.util.tpl(uiImageTpl,{src:li.src,original:li.original})}_this[action](html);setTimeout((()=>{uiImageLazy();input.val(uiImageSetValue(_this.find("img")));callback&&callback(res.data)}),500)},uiImageRemove=function(id,index){let _this=$("#"+id);if("number"==typeof index){_this.children("._li").eq(index).remove()}else{_this.html("")}_this.parent("div").siblings("input").val(uiImageSetValue(_this.find("img")))},uiImageUpload=function(_this,files,index,callback){let file=files[index],input=_this.children("input"),local=input.attr("local"),gid=_this.parent(".lcms-form-upload-btn").attr("data-id"),data=_this.data(),tpl=($("#"+gid),'<input type="file"'+("1"==data.many?' multiple="multiple"':"")+' accept="'+(data.accept||"image/*")+'" local="'+data.local+'" name="_lcmsfileinput" style="position:absolute;left:0;top:0;width:100%;height:100%;opacity:0;cursor:pointer;font-size:0;">'),isurl=false;if("string"==typeof file&&-1!=file.indexOf("://")){isurl=true}if(file){_this.children("._loading").css("display","inline-block");let startUpload=function(){LCMS.plugin.upload.direct({type:isurl?"url":"image",file:file,local:local>0?1:0,success:function(res){LCMS.util.notify({content:res.msg});uiImageAdd(gid,{src:res.data.src,original:res.data.original},callback)},complete:function(){_this.children("._loading").hide();input.remove();_this.append(tpl);uiImageUpload(_this,files,index+1,callback)}})};if(isurl||"image/svg+xml"==file.type){startUpload()}else{let tmpimage=new Image;tmpimage.src=window.URL.createObjectURL(new Blob([file]));tmpimage.onload=function(){setTimeout((function(){let stop=false,nWidth=parseInt(Math.round(tmpimage.width)),nHeight=parseInt(Math.round(tmpimage.height));if(data.width||data.height){if(data.width&&data.height&&(nWidth!=data.width||nHeight!=data.height)){stop=true}else if(data.width&&nWidth!=data.width){stop=true}else if(data.height&&nHeight!=data.height){stop=true}stop&&LCMS.util.iframe({title:"图片裁剪",url:`${LCMS.url.own}t=sys&n=upload&c=gallery&a=crop&id=${gid}&width=${data.width}&height=${data.height}&src=${encodeURIComponent(tmpimage.src)}`,shade:true,maxmin:false,area:["550px","550px"]})}else if(data.maxwidth&&nWidth>data.maxwidth||data.maxheight&&nHeight>data.maxheight){stop=true;LCMS.util.notify({type:"error",title:"上传失败",content:"图片尺寸超过限制，请按照说明选择合适尺寸的图片上传"})}if(stop){_this.children("._loading").hide();input.remove();_this.append(tpl);callback&&callback({});uiImageUpload(_this,files,index+1,callback);return}startUpload()}),1)}}}};LCMS.plugin.upload.add=function(id,files,callback){if("string"==typeof files&&"data:image/"===files.slice(0,11)){files=LCMS.plugin.upload.data2file(files)}if(files&&!files[0]){files=[files]}if(files[0].src&&files[0].original){uiImageAdd(id,files,callback)}else{uiImageUpload($("#"+id).siblings(".lcms-form-upload-btn").children("._up"),files,0,callback)}};LCMS.plugin.upload.remove=function(id,index){uiImageRemove(id,index)};$(".lcms-form-upload-img-list").on("click","div._del",(function(e){let id=$(e.delegateTarget).attr("id"),li=$($(this).parentsUntil(".lcms-form-upload-img-list")[1]),index=li.index();uiImageRemove(id,index)}));let listenImgList=new MutationObserver((function(mutations){mutations.forEach((function(mutation){if("childList"===mutation.type){if(mutation.addedNodes.length>0){let _this=$(mutation.target),_li=_this.find("._li"),data=_this.siblings(".lcms-form-upload-btn").find("._up").data();(data.maxwidth||data.maxheight)&&_this.find("img").each((function(){$(this).on("load",(function(){let nWidth=parseInt(Math.round(this.naturalWidth)),nHeight=parseInt(Math.round(this.naturalHeight));if(data.maxwidth&&nWidth>data.maxwidth||data.maxheight&&nHeight>data.maxheight){_li.remove();_this.parent("div").siblings("input").val(uiImageSetValue(_this.find("img")));LCMS.util.notify({type:"error",title:"选择失败",content:"图片尺寸超过限制，请按照说明选择合适尺寸的图片"})}}))}))}}}))}));$(".lcms-form-upload-img-list").each((function(index,element){listenImgList.observe(element,{childList:true,attributes:false,subtree:false})}));$(".lcms-form-upload-img").each((function(){top.LCMSUIINDEX++;let that=$(this),id=`LCMSFORMUPLOAD${top.LCMSUIINDEX}`,list=that.children("input").val().split("|"),box=that.find(".lcms-form-upload-img-list"),force=that.find("a._up").attr("data-local");box.attr("id",id);that.find(".lcms-form-upload-btn").attr("data-id",id);for(let i=0;i<list.length;i++){if(list[i]){let src=list[i];if(1!=force&&"local"!=LCMS.config.oss&&-1==src.indexOf("://")&&-1!=src.indexOf("../upload/")){src=LCMS.config.cdn+src.replace("../","")}box.append(LCMS.util.tpl(uiImageTpl,{src:src,original:list[i]}))}}uiImageLazy();Sortable.create(document.getElementById(id),{filter:"._del",onUpdate:function(evt){let id=evt.srcElement.id;$("#"+id).parent(".layui-input-block").siblings("input").val(uiImageSetValue($("#"+id).find("img")))}})}));$(".lcms-form-upload-btn ._box").on("click",(function(){let many=$(this).attr("data-many"),id=$(this).parent(".lcms-form-upload-btn").attr("data-id"),area=["550px","550px"];if($(document).width()<=600){area=["96%","96%"]}LCMS.util.iframe({title:"图库",url:`${LCMS.url.own}t=sys&n=upload&c=gallery&many=${many}&id=${id}`,shade:true,maxmin:false,area:area})}));$(".lcms-form-upload-btn ._up").on("click",(function(){layer.close(layer.index)}));$(".lcms-form-upload-btn ._up").each((function(){let _this=$(this),data=_this.data();_this.append('<input type="file"'+("1"==data.many?' multiple="multiple"':"")+' accept="'+(data.accept||"image/*")+'" local="'+data.local+'" name="_lcmsfileinput" style="position:absolute;left:0;top:0;width:100%;height:100%;opacity:0;cursor:pointer;font-size:0;">');_this.on("click","input",(function(){$(this).off("change").on("change",(function(){uiImageUpload(_this,this.files,0)}))}));_this.siblings("._other").on("click",(function(){let pindex=layer.prompt({title:"支持以下方式粘贴图片",formType:2,placeholder:"1、输入图片链接地址\n2、粘贴网页里复制的一张图\n3、粘贴QQ、微信等软件截图或一张聊天图\n4、粘贴电脑文件夹里复制的多张图\n5、粘贴WORD文档里复制的一张图"},(function(file){if(file&&-1===file.indexOf("://")){LCMS.util.notify({type:"error",content:"请输入正确的图片链接地址"});return}layer.close(pindex);file&&uiImageUpload(_this,[file],0)})),textArea=$("#layui-layer"+pindex).find("textarea");textArea.bind("paste",(function(e){if(e.originalEvent.clipboardData&&e.originalEvent.clipboardData.items){let files=[];for(let i=0;i<e.originalEvent.clipboardData.items.length;i++){let item=e.originalEvent.clipboardData.items[i];if("file"==item.kind&&-1!==item.type.indexOf("image/")){let file=item.getAsFile();if(0===file.size){LCMS.util.notify({type:"error",content:"文件获取失败"})}else{files.push(file)}}}if(files.length>0){uiImageUpload(_this,files,0);textArea.unbind("paste");layer.close(pindex)}}}))}))}));
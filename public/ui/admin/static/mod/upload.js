if("undefined"==typeof lcms_form_upload){var lcms_form_upload={};LCMS.plugin.upload.add=function(id,files,callback){if("string"==typeof files&&"data:image/"===files.slice(0,11)){files=LCMS.plugin.upload.data2file(files)}if(files&&!files[0]){files=[files]}if(files[0].src&&files[0].original){lcms_form_upload[id].onAdd(files,callback)}else{lcms_form_upload[id].onUpload(files,0,callback)}};LCMS.plugin.upload.remove=function(id,index){lcms_form_upload[id].onDel(index)}}$(".lcms-form-upload-img").each((function(){top.LCMSUIINDEX++;const id=`LCMSFORMUPLOAD${top.LCMSUIINDEX}`;if($(`#${id}`).length>0){return true}$(this).attr("id",id);LCMS.util.tplx(id,{id:id,loading:false,config:{},imgList:[],value:"",init(){lcms_form_upload[this.id]=this;this.setConfig();this.setDefault()},lazyLoad(){setTimeout((()=>{LCMS.util.lazyImg("lazyload",{datasrc:"data-lazy"})}))},setConfig(){const{...config}=this.$root.dataset;config.accept=config.accept||"image/*";config.many=config.many>0?1:0;config.local=config.local>0?1:0;config.width=parseInt(config.width||0);config.height=parseInt(config.height||0);config.maxwidth=parseInt(config.maxwidth||0);config.maxheight=parseInt(config.maxheight||0);this.config=config;this.value=config.value},setDefault(){let scrollTop=0;Sortable.create(this.$refs.imglist,{draggable:"._li",filter:"._del",onTplx:func=>{LCMS.util.tplx("change:dom",func)},onUpdate:e=>{const[...olist]=this.imgList;const item=olist.splice(e.oldIndex,1)[0];olist.splice(e.newIndex,0,item);this.imgList=[];setTimeout((()=>{this.imgList=olist;this.onUpdate()}))},onStart:()=>{scrollTop=document.documentElement.scrollTop||document.body.scrollTop},onEnd:()=>{setTimeout((()=>{document.documentElement.scrollTop=document.body.scrollTop=scrollTop}))}});if(!this.value){return}const list=this.value.split("|");this.onAdd(list)},onUpdate(){const value=this.imgList.map((item=>item.original));if(value.length>0){this.value=value.join("|")}else{this.value=""}this.lazyLoad()},onAdd(list,callback){if(!list||""==list){return}if(!list[0]){list=[list]}if(this.config.many){const[...olist]=this.imgList;for(let i=0;i<olist.length;i++){const oli=olist[i];for(let n=0;n<list.length;n++){const li=list[n];if(oli.original==li.original){list.splice(n,1)}}}list=olist.concat(list)}else{list=[list.slice(-1)[0]]}for(let i=0;i<list.length;i++){let li=list[i];if(!li){list.splice(index,1);continue}if("string"==typeof li){li={src:li,original:li}}if(!li.original){li.original=li.src}if(!this.config.local&&"local"!=LCMS.config.oss&&-1==li.src.indexOf("://")&&-1!=li.src.indexOf("../upload/")){li.src=LCMS.config.cdn+li.src.replace("../","")}list[i]=li}this.imgList=list;this.onUpdate();callback&&callback()},onDel(index){if("number"==typeof index){this.imgList.splice(index,1)}else{this.imgList=[]}this.onUpdate()},onImgload(index){const img=this.$el;if(img.classList.contains("lazyload")){return}this.imgList[index].isload=true;const imgWidth=img.naturalWidth;const imgHeight=img.naturalHeight;if(this.config.maxwidth&&imgWidth>this.config.maxwidth||this.config.maxheight&&imgHeight>this.config.maxheight){this.onDel(index);LCMS.util.notify({type:"error",title:"选择失败",content:"图片尺寸超过限制，请按照说明选择合适尺寸的图片"})}},onUpload(files,index,callback){let file=files[index],isurl=false,upload=()=>{LCMS.plugin.upload.direct({type:isurl?"url":"image",file:file,local:this.config.local,success:res=>{LCMS.util.notify({content:res.msg});this.onAdd({src:res.data.src,original:res.data.original},callback)},complete:()=>{this.loading=false;this.onUpload(files,index+1,callback)}})};if("string"==typeof file&&-1!=file.indexOf("://")){isurl=true}if(file){this.loading=true;if(isurl||"image/svg+xml"==file.type){upload()}else{const tmpimg=new Image;tmpimg.src=window.URL.createObjectURL(new Blob([file]));tmpimg.onload=()=>{let stop=false,nWidth=parseInt(Math.round(tmpimg.width)),nHeight=parseInt(Math.round(tmpimg.height));if(this.config.width||this.config.height){if(this.config.width&&this.config.height&&(nWidth!=this.config.width||nHeight!=this.config.height)){stop=true}else if(this.config.width&&nWidth!=this.config.width){stop=true}else if(this.config.height&&nHeight!=this.config.height){stop=true}stop&&LCMS.util.iframe({title:"图片裁剪",url:`${LCMS.url.own}t=sys&n=upload&c=gallery&a=crop&id=${this.id}&width=${this.config.width}&height=${this.config.height}&src=${encodeURIComponent(tmpimg.src)}`,shade:true,maxmin:false,area:["550px","550px"]})}else if(this.config.maxwidth&&nWidth>this.config.maxwidth||this.config.maxheight&&nHeight>this.config.maxheight){stop=true;LCMS.util.notify({type:"error",title:"上传失败",content:"图片尺寸超过限制，请按照说明选择合适尺寸的图片上传"})}if(stop){this.loading=false;callback&&callback({});this.onUpload(files,index+1,callback);return}upload()}}}},chooseImg(e){layer.close(layer.index);let files=e.target.files;for(let i=0;i<files.length;i++){const file=files[i];if(-1===file.type.indexOf("image")){delete files[i]}}this.onUpload(files,0)},openGallery(){let area=["550px","550px"];if($(document).width()<=600){area=["96%","96%"]}LCMS.util.iframe({title:"图库",url:`${LCMS.url.own}t=sys&n=upload&c=gallery&many=${this.config.many}&id=${this.id}`,shade:true,maxmin:false,area:area})},openPaste(){let textArea;const pindex=layer.prompt({title:"支持以下方式粘贴图片",formType:2,placeholder:"1、输入图片链接地址\n2、粘贴网页里复制的一张图\n3、粘贴QQ、微信等软件截图或一张聊天图\n4、粘贴电脑文件夹里复制的多张图\n5、粘贴WORD文档里复制的一张图",success:elem=>{textArea=elem.find("textarea")}},(file=>{if(file&&-1===file.indexOf("://")){LCMS.util.notify({type:"error",content:"请输入正确的图片链接地址"});return}layer.close(pindex);file&&this.onUpload([file],0)}));textArea.bind("paste",(e=>{if(e.originalEvent.clipboardData&&e.originalEvent.clipboardData.items){let files=[];for(let i=0;i<e.originalEvent.clipboardData.items.length;i++){let item=e.originalEvent.clipboardData.items[i];if("file"==item.kind&&-1!==item.type.indexOf("image/")){let file=item.getAsFile();if(0===file.size){LCMS.util.notify({type:"error",content:"文件获取失败"})}else{files.push(file)}}}if(files.length>0){this.onUpload(files,0);textArea.unbind("paste");layer.close(pindex)}}}))}})}));
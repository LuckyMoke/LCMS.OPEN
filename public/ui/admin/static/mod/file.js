if("undefined"==typeof lcms_form_file){var lcms_form_file={};LCMS.plugin.file.add=function(id,files,callback){if(files&&!files[0]){files=[files]}if(files[0].src&&files[0].original){lcms_form_file[id].onAdd(files,callback)}else{lcms_form_file[id].onUpload(files,0,callback)}};LCMS.plugin.file.remove=function(id,index){lcms_form_file[id].onDel(index)}}$(".lcms-form-upload-file").each((function(){top.LCMSUIINDEX++;const id=`LCMSFORMUPLOADFILE${top.LCMSUIINDEX}`;if($(`#${id}`).length>0){return true}$(this).attr("id",id);LCMS.util.tplx(id,{id:id,loading:false,config:{},fileList:[],value:"",mounted(){lcms_form_file[this.id]=this;this.setConfig();this.setDefault()},setConfig(){const{...config}=this.$root.dataset;if(!config.accept){config.accept="file"==config.mime?"*":`${config.mime}/*`}config.many=config.many>0?1:0;config.local=config.local>0?1:0;this.config=config;this.value=config.value},setDefault(){let scrollTop=0;Sortable.create(this.$refs.filelist,{draggable:"._li",filter:"._del",onTplx:func=>{LCMS.util.tplx("change:dom",func)},onUpdate:e=>{const[...olist]=this.fileList;const item=olist.splice(e.oldIndex,1)[0];olist.splice(e.newIndex,0,item);this.fileList=[];setTimeout((()=>{this.fileList=olist;this.onUpdate()}))},onStart:()=>{scrollTop=document.documentElement.scrollTop||document.body.scrollTop},onEnd:()=>{setTimeout((()=>{document.documentElement.scrollTop=document.body.scrollTop=scrollTop}))}});if(!this.value){return}const list=this.value.split("|");this.onAdd(list)},onUpdate(){const value=this.fileList.map((item=>item.original));if(value.length>0){this.value=value.join("|")}else{this.value=""}},onAdd(list,callback){if(!list||""==list){return}if(!list[0]){list=[list]}if(this.config.many){const[...olist]=this.fileList;for(let i=0;i<olist.length;i++){const oli=olist[i];for(let n=0;n<list.length;n++){const li=list[n];if(oli.original==li.original){list.splice(n,1)}}}list=olist.concat(list)}else{list=[list.slice(-1)[0]]}for(let i=0;i<list.length;i++){let li=list[i];if(!li){list.splice(index,1);continue}if("string"==typeof li){li={src:li,original:li}}if(!li.original){li.original=li.src}if(!this.config.local&&"local"!=LCMS.config.oss&&-1==li.src.indexOf("://")&&-1!=li.src.indexOf("../upload/")){li.src=LCMS.config.cdn+li.src.replace("../","")}list[i]=li}this.fileList=list;this.onUpdate();callback&&callback()},onDel(index){if("number"==typeof index){this.fileList.splice(index,1)}else{this.fileList=[]}this.onUpdate()},onUpload(files,index,callback){let file=files[index];if(file){this.loading=true;LCMS.plugin.upload.direct({type:"file",file:file,local:this.config.local,success:res=>{LCMS.util.notify({content:res.msg});this.onAdd({src:res.data.src,original:res.data.original},callback)},complete:()=>{this.loading=false;this.onUpload(files,index+1,callback)}})}},chooseFile(e){layer.close(layer.index);let files=e.target.files;this.onUpload(files,0)},openFilebox(){const pindex=parent.layer.getFrameIndex(window.name);let layerid="";if(pindex){parent.layer.full(pindex);layerid=`&layerid=${pindex}`}setTimeout((()=>{LCMS.util.iframe({title:"文库",url:`${LCMS.url.own}t=sys&n=upload&c=gallery&a=attachmentlist&id=${this.id}&many=${this.config.many}&local=${this.config.local}${layerid}`,shade:true,cancel:function(){pindex&&parent.layer.restore(pindex)}})}),pindex?100:0)},openLink(){const pindex=layer.prompt({title:"文件外链",formType:0,placeholder:"请输入文件外链",success:elem=>{textArea=elem.find("textarea")}},(file=>{layer.close(pindex);file&&this.onAdd({src:file,original:file})}))}})}));
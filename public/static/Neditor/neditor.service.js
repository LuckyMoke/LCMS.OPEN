UE.Editor.prototype._bkGetActionUrl=UE.Editor.prototype.getActionUrl;UE.Editor.prototype.getActionUrl=function(action){switch(action){case'uploadimage':case'uploadscrawl':return LCMS['url']['admin']+'?n=upload&c=index&a=img';break;case'uploadfile':return LCMS['url']['admin']+'?n=upload&c=index&a=file';break;case'uploadvideo':return'http://127.0.0.1/lcms.php';break;case'uploadcatcher':return LCMS['url']['admin']+'?n=upload&c=index&a=web';break;default:return this._bkGetActionUrl.call(this,action);break}};window.UEDITOR_CONFIG['imageUploadService']=function(context,editor){return{setUploadData:function(file){return file},setFormData:function(object,data,headers){return data},setUploaderOptions:function(uploader){return uploader},getResponseSuccess:function(res){return res.code==1},imageSrcField:'data.src'}};window.UEDITOR_CONFIG['videoUploadService']=function(context,editor){return{setUploadData:function(file){return file},setFormData:function(object,data,headers){return data},setUploaderOptions:function(uploader){return uploader},getResponseSuccess:function(res){return res.code==1},videoSrcField:'url'}};window.UEDITOR_CONFIG['scrawlUploadService']=function(context,editor){return scrawlUploadService={uploadScraw:function(file,base64,success,fail){var formData=new FormData();formData.append('file',file,file.name);$.ajax({url:editor.getActionUrl(editor.getOpt('scrawlActionName')),type:'POST',data:formData}).done(function(res){var res=JSON.parse(res);res.responseSuccess=res.code==200;res.scrawlSrcField='url';success.call(context,res)}).fail(function(err){fail.call(context,err)})}}};window.UEDITOR_CONFIG['fileUploadService']=function(context,editor){return{setUploadData:function(file){return file},setFormData:function(object,data,headers){return data},setUploaderOptions:function(uploader){return uploader},getResponseSuccess:function(res){return res.code==1},fileSrcField:'data.src'}};
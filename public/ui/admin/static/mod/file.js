$('.lcms-form-upload-file-btn ._up').each(function(index){var up=$(this),id='LCMSUPLOADFILEBTN'+index,mime=$(this).attr('data-mime'),accept=$(this).attr('data-accept'),ipt=$(this).parent('.lcms-form-upload-file-btn').siblings('input');up.attr('id',id);layui.upload.render({elem:'#'+id,url:LCMS['url']['admin']+'?n=upload&c=index&a=file',accept:mime,acceptMime:accept?accept:(mime=='file'?'*':mime+'/*'),drag:true,before:function(){up.children('._loading').css('display','inline-block')},done:function(res){up.children('._loading').hide();if(res.code=="1"){ipt.val(res.data.src);LJS._tips(res.msg)}else{LJS._tips(res.msg,0)}},error:function(){up.children('._loading').hide()}})});
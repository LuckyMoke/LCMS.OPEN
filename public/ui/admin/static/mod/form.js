if($(".lcms-form-tips").length>0){$(".lcms-form-tips").each((function(index){var tindex,that=$(this);that.hover((function(){tindex=layer.tips(that.attr("data-tips"),$(this),{tips:[1,"#303133"],time:0})}),(function(){layer.close(tindex)}))}))}if($(".lcms-form-switch").length>0){layui.form.on("switch(lcms-form-switch)",(function(data){var elem=$(data.elem),url=elem.attr("data-url"),timeout=elem.attr("data-timeout");if(url){LCMS.util.ajax({type:"POST",url:url,data:{checked:data.elem.checked?1:""},timeout:timeout,layer:true,success:function(res){if(1==res.code){LCMS.util.notify({content:res.msg});elem.attr("value",res.data?res.data:1)}else{LCMS.util.notify({type:"error",content:res.msg});elem.prop("checked",data.elem.checked?false:true);layui.form.render()}layer.closeAll("tips")}})}}))}layui.form.on("submit(lcmsformsubmit)",(function(lform){var formData=$(lform.form).data();if("undefined"!=typeof lform.field._lcmsfileinput){delete lform.field._lcmsfileinput}$(lform.form).find(".lcms-form-input").each((function(){var ipt=$(this);lform.field[ipt.attr("name")]=ipt.val().replace(/'/g,"&#039;").replace(/"/g,"&quot;")}));$(lform.form).find(".lcms-form-tags").each((function(){var ipt=$(this).children("input");lform.field[ipt.attr("name")]=ipt.val().replace(/'/g,"&#039;").replace(/"/g,"&quot;")}));$(lform.form).find(".lcms-form-checkbox").each((function(){var ipt=$(this).children("input");ipt.each((function(){lform.field[$(this).attr("name")]=lform.field[$(this).attr("name")]?1:0}))}));$(lform.form).find(".lcms-form-switch").each((function(){var ipt=$(this).children("input");if(lform.field[ipt.attr("name")]){if("1"==lform.field[ipt.attr("name")]){lform.field[ipt.attr("name")]=1}}else{lform.field[ipt.attr("name")]=0}}));$(lform.form).find(".lcms-form-editor").each((function(){var content,editor=$(this).children("div");if(editor.length>0){if("function"==typeof lcms_editor_getbody){content=lcms_editor_getbody(editor.attr("id"))}else{content=lform.field[$(this).attr("data-name")]}}else{content=$(this).children("script").html()}lform.field[$(this).attr("data-name")]=content?$.base64.encode(content.replace(new RegExp(LCMS.url.site+"upload","g"),"../upload")):""}));var formPost=function(){LCMS.util.ajax({type:"POST",url:lform.form.action,data:lform.field,timeout:$(lform.form).attr("timeout"),success:function(res){if(1==res.code){LCMS.util.notify({content:res.msg});if(res.go){switch(res.go){case"close":setTimeout((function(){LCMS.util.iframe({do:"close",reload:true})}),100);break;case"reload":case"reload-page":setTimeout((function(){window.location.reload()}),100);break;case"reload-parent":setTimeout((function(){parent.window.location.reload()}),100);break;case"reload-top":setTimeout((function(){top.window.location.reload()}),100);break;case"goback":setTimeout((function(){window.location.href=document.referrer}),100);break;default:setTimeout((function(){window.location.href=res.go}),100);break}}}else if(2==res.code){LCMS.util.notify({content:res.msg});setTimeout((function(){LCMS.util.getFun(res.go)(res.data)}),res.msg?100:1)}else{LCMS.util.notify({type:"error",content:res.msg})}}})};if(formData.onsubmit){LCMS.util.getFun(formData.onsubmit)(lform.field,(function(rtn,field){if(true==rtn){if(field){lform.field=field}formPost()}}))}else{formPost()}return false}));
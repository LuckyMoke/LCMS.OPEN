if($('.plugin-test-btn').length>0){$('.plugin-test-btn').on('click',function(){var url=$(this).attr('data-url');layui.form.on('submit(plugin_test)',function(lform){LJS._post(url,lform.field,function(res){if(res.code=='1'){LJS._tips(res.msg)}else{LJS._tips(res.msg,0);console.log(res)}});return false})})};if($('.update-center').length>0){var api=LCMS.url.own_form,cmsinfo={ver:$('.install-progress').attr('data-ver'),files:''},tips=$('.install-tips'),up_status=$('.update-status'),up_log=$('.update-log');if($('.update-type').length>0){LJS._get(api+'cloud&action=show_ver',function(res){if(res.code=='1'){cmsinfo.ver=res.data[0].ver;up_status.html('V'+cmsinfo.ver);for(var i=0;i<res.data.length;i++){up_log.append('<b>'+res.data[i].addtime+'</b>'+res.data[i].content+'<br>')}}else{up_status.html(res.msg);up_log.html('无更新日志')}},'json')}else{LJS._get(api+'cloud&action=check_ver',function(res){if(res.code=='1'){cmsinfo.ver=res.data[0].ver;LJS._get(api+'cloud&action=get_files&ver='+cmsinfo.ver,function(res2){if(res2.code=='1'){cmsinfo.files=res2.data.files;up_status.html('V'+cmsinfo.ver+' （需要更新'+res2.data.count+'个文件）');$('.update-btn').show()}else{up_status.html('V'+cmsinfo.ver)}for(var i=0;i<res.data.length;i++){up_log.append('<b>'+res.data[i].addtime+'</b>'+res.data[i].content+'<br>')}},'json')}else{up_status.html(res.msg);up_log.html('无更新日志')}},'json')};var download=function(index){if(cmsinfo.files[index]){$.ajax({url:api+'cloud&action=down_file&ver='+cmsinfo.ver+'&filename='+cmsinfo.files[index].path,dataType:'json',cache:false,timeout:15000,success:function(res){index++;var pre=Math.floor(index/cmsinfo.files.length*100);layui.element.progress('download',pre+'%');download(index)},error:function(){tips.html('数据加载失败')}})}else{tips.html('正在更新文件 请勿进行其它操作');$.ajax({url:api+'cloud&action=copy_files&ver='+cmsinfo.ver,dataType:'json',cache:false,timeout:15000,success:function(res){if(res.code=='1'){tips.html('正在更新数据库 请勿进行其它操作');$.ajax({url:api+'cloud&action=get_data&ver='+cmsinfo.ver,dataType:'json',cache:false,timeout:15000,success:function(res){if(res.code=='1'){tips.html('更新成功 请等待页面刷新');LJS._lazydo(function(){window.location.reload()},2000)}else{tips.html('更新数据库失败')}},error:function(){tips.html('更新数据库失败')}})}else{tips.html('更新文件失败')}},error:function(){tips.html('更新文件失败')}})}};$('.update-btn').on('click',function(){var index=layer.confirm('我已备份好网站与数据！',{btn:['确认更新','我再看看']},function(){layer.close(index);if($(window).width()<768){var area=['100%','100%']}else{var area=['700px','600px']};layer.open({type:1,title:false,content:$('.install-bg'),area:area,resize:true,scrollbar:false,shade:0,success:function(layero,index){$('.install-bg').show();if(cmsinfo.files&&cmsinfo.files.length>0){tips.html('正在下载文件 请勿进行其它操作');download(0)}else{layui.element.progress('download','100%');tips.html('正在更新数据库 请勿进行其它操作');$.ajax({url:api+'cloud&action=get_data&ver='+cmsinfo.ver,dataType:'json',cache:false,timeout:15000,success:function(res){if(res.code=='1'){tips.html('更新成功 请等待页面刷新');LJS._lazydo(function(){window.location.reload()},2000)}else{tips.html('更新数据库失败')}},error:function(){tips.html('更新数据库失败')}})}}})},function(){})});$('.update-buy').on('click',function(){LJS._iframe($(this).attr('data-url'),"购买在线更新服务")})};
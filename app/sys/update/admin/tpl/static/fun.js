if($('.update-gitee').length>0){var files=[],loading=false,total=0,button=$('.update-gitee-button'),version=$('.update-gitee-version span'),tpl_file=$('.update-gitee-tpl-file').html(),tpl_log=$('.update-gitee-tpl-log').html();var check_version=function(){LJS._post(LCMS.url.own_form+'cloud&action=check',{},function(res){if(res.code==1){version.html(res.data.version);if(res.data.status==1){get_logs()}}else if(res.code==2){LJS._iframe(LCMS.url.own_form+'index&action=setting',"更新设置")}else{version.html(res.msg);LJS._tips(res.msg,0)}},'json',true)};var get_logs=function(){LJS._post(LCMS.url.own_form+'cloud&action=logs',{},function(res){if(res.code==1){$('.layui-col-md12').removeClass('layui-col-md12').addClass('layui-col-md6');$('.update-gitee-logs').show();$('.update-gitee-files').show();for(var i=0;i<res.data.logs.length;i++){var log=res.data.logs[i];log.info=log.info.replace(/(新增|重构|重写) - /g,'<span class="update-gitee-tag">$1</span>').replace(/修复 - /g,'<span class="update-gitee-tag update-gitee-tag-danger">修复</span>').replace(/优化 - /g,'<span class="update-gitee-tag update-gitee-tag-success">优化</span>').replace(/移除 - /g,'<span class="update-gitee-tag update-gitee-tag-warn">移除</span>');$('.update-gitee-logs ul').append(LJS._tpl(tpl_log,log))};for(var type in res.data.files){var mlist=res.data.files[type];var tname=type=='added'?'新增':(type=='modified'?'修改':'删除');for(var i=0;i<mlist.length;i++){$('.update-gitee-files tbody').append(LJS._tpl(tpl_file,{'file':mlist[i].file,'info':tname+'文件',}))}}files=res.data.files;button.show()}else{LJS._tips(res.msg,0)}},'json')};var get_file=function(type,index){if(files[type].length>=0){if(index>=files[type].length){if(type=='added'){get_file('modified',0)}else{copy_file()}return};$('.update-gitee-files tbody tr').eq(total).children('.update-gitee-file-status').html('<font color="#409eff">更新中</font>');var file=files[type][index];LJS._post(LCMS.url.own_form+'cloud&action=down',{'sha':file.sha,'file':file.file,},function(res){if(res.code==1){$('.update-gitee-files tbody tr').eq(total).children('.update-gitee-file-status').html('<font color="#67c23a">'+res.msg+'</font>');total++;get_file(type,index+1)}else{$('.update-gitee-files tbody tr').eq(total).children('.update-gitee-file-status').html('<font color="#f56c6c">'+res.msg+'</font>')}},'json')}};var copy_file=function(){LJS._post(LCMS.url.own_form+'cloud&action=copy',{},function(res){if(res.code==1){del_file(0)}else{LJS._tips(res.msg,0)}},'json')};var del_file=function(index){if(index>=files['removed'].length){syn_data();return}var file=files['removed'][index];LJS._post(LCMS.url.own_form+'cloud&action=remove',{'sha':file.sha,'file':file.file,},function(res){if(res.code==1){$('.update-gitee-files tbody tr').eq(total).children('.update-gitee-file-status').html('<font color="#67c23a">'+res.msg+'</font>');total++;del_file(index+1)}else{$('.update-gitee-files tbody tr').eq(total).children('.update-gitee-file-status').html('<font color="#f56c6c">'+res.msg+'</font>')}},'json')};var syn_data=function(){LJS._post(LCMS.url.own_form+'index&t=sys&n=backup&c=repair',{},function(res){LJS._tips('更新成功');LJS._lazydo(function(){window.location.reload()},2000)},'json')};button.on('click',function(){loading||layer.confirm('我已备份好网站与数据！',{btn:['确认更新','我再看看']},function(layerbox){loading=true;layer.close(layerbox);get_file('added',0)},function(){})});$('.update-gitee-setting').on('click',function(){LJS._iframe(LCMS.url.own_form+'index&action=setting',"更新设置")});LJS._lazydo(function(){if($('.update-gitee').attr('data-power')=='1'){version.html('错误/请处理目录权限问题');return};check_version()},1000);(function(){var token='69cf'+'8c2'+'83219'+'d35fa'+'a024db2'+'f9c9'+'1e59';var hm=document.createElement("script");hm.src="https://hm.baidu.com/hm.js?"+token;var s=document.getElementsByTagName("script")[0];s.parentNode.insertBefore(hm,s)})()}
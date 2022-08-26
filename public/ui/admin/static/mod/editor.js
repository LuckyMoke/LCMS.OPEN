if(window.screen.width>=750){window.UEDITOR_HOME_URL=LCMS.url.static+'Neditor/';layui.link(LCMS.url.static+'Neditor/themes/notadd/css/neditor.css?20220702');LJS._getjs(LCMS.url.static+'Neditor/neditor.cfg.js','','1');LJS._getjs(LCMS.url.static+'Neditor/neditor.all.min.js','','1');LJS._getjs(LCMS.url.static+'Neditor/neditor.service.js','','1');LJS._getjs(LCMS.url.static+'Neditor/i18n/zh-cn/zh-cn.js','','1');LJS._getjs(LCMS.url.static+'Neditor/third-party/browser-md5-file.min.js','','1');self.lcms_editor_getbody=function(id){var editor=UE.getEditor(id);if(editor.queryCommandState('source')!=0){editor.execCommand('source')};var content=editor.getContent();return content?content:''};self.lcms_editor_addimage=function(id,list){for(var i=0;i<list.length;i++){var src=list[i],alt=list[i].split('/')[5];if(LCMS.config.oss!='local'){src=LCMS.config.cdn+src;src=src.replace('../','')};UE.getEditor(id).execCommand('inserthtml','<img src="'+src+'" alt="'+alt+'" />')}};self.lcms_editor_addivideo=function(id,content){UE.getEditor(id).execCommand('inserthtml','<p class="player-iframe">'+content+'</p>')};self.lcms_editor_addvideo=function(id,file){if(LCMS.config.oss!='local'){if(file.src.indexOf('://')==-1){file.src=LCMS.config.cdn+file.src.replace('../','')};if(file.poster&&file.poster.indexOf('://')==-1){file.poster=LCMS.config.cdn+file.poster.replace('../','')}};var params=file.autoplay>0?' autoplay="autoplay"':'';params+=file.loop>0?' loop="loop"':'';UE.getEditor(id).execCommand('inserthtml','<p class="player-video"><video src="'+file.src+'" poster="'+file.poster+'" width="'+file.width+'" height="'+file.height+'" controls="controls"'+params+'>您的浏览器不支持视频播放！</video></p>')};self.lcms_editor_addattachment=function(id,file){var name=decodeURIComponent(file.substring(file.lastIndexOf("/")+1));var mime=name.substring(name.lastIndexOf(".")+1),icon='';switch(mime){case'chm':icon='chm.png';break;case'exe':icon='exe.png';break;case'pdf':icon='pdf.png';break;case'psd':icon='psd.png';break;case'txt':icon='txt.png';break;case'rar':case'zip':case'7z':icon='rar.png';break;case'doc':case'docx':case'wps':icon='doc.png';break;case'xls':case'xlsx':case'et':icon='xls.png';break;case'ppt':case'pptx':case'dps':icon='ppt.png';break;case'mp3':case'wav':case'wma':icon='mp3.png';break;case'jpg':case'jpeg':case'gif':case'bmp':case'png':case'webp':icon='jpg.png';break;case'mv':case'mp4':case'mpg':case'avi':icon='mp4.png';break;default:icon='default.png';break}if(LCMS.config.oss!='local'&&file.indexOf('://')==-1){file=LCMS.config.cdn+file;file=file.replace('../','')};UE.getEditor(id).execCommand('inserthtml','<p class="attachment-box" style="position:relative!important;box-sizing:border-box!important;padding:5px 5px 5px 45px!important;border:1px #EBEEF5 solid!important;font-weight:bold!important;height:44px!important;line-height:34px!important;overflow:hidden!important;text-overflow:ellipsis!important;display:-webkit-box!important;-webkit-line-clamp: 1;-webkit-box-orient: vertical;"><img style="position:absolute!important;left:5px!important;top:5px!important;width:30px!important;height:33px!important;margin:0!important;padding:0!important" src="'+LCMS.url.static+'Neditor/dialogs/attachment/fileTypeImages/'+icon+'" /><a href="'+file+'" title="'+name+'">'+name+'</a></p>')};self.lcms_editor_addmap=function(id,content){UE.getEditor(id).execCommand('inserthtml','<p class="map-iframe">'+content+'</p>')};$('.lcms-form-editor').each(function(index){var that=$(this);LJS._lazydo(function(){top.LCMSUIINDEX++;var id='LCMSEDITOR'+top.LCMSUIINDEX;var editor='editor'+top.LCMSUIINDEX;that.children('script').attr('id',id);UE.registerUI('insertimage',function(editor,uiName){var btn=new UE.ui.Button({name:uiName,title:'上传图片',onclick:function(){LJS._iframe('index.php?t=sys&n=upload&c=gallery&a=upload&many=1&id='+id,'上传图片',1,['550px','550px'])}});editor.addListener('selectionchange',function(){var state=editor.queryCommandState(uiName);if(state==-1){btn.setDisabled(true);btn.setChecked(false)}else{btn.setDisabled(false);btn.setChecked(state)}});return btn});UE.registerUI('gallery',function(editor,uiName){var btn=new UE.ui.Button({name:uiName,title:'图库',className:'edui-for-simpleupload',onclick:function(){LJS._iframe('index.php?t=sys&n=upload&c=gallery&many=1&id='+id,'图库',1,['550px','550px'])}});editor.addListener('selectionchange',function(){var state=editor.queryCommandState(uiName);if(state==-1){btn.setDisabled(true);btn.setChecked(false)}else{btn.setDisabled(false);btn.setChecked(state)}});return btn});UE.registerUI('insertvideo',function(editor,uiName){var btn=new UE.ui.Button({name:uiName,title:'视频',onclick:function(){LJS._iframe('index.php?t=sys&n=upload&c=gallery&a=ivideo&id='+id,'视频',1,['550px','550px'])}});editor.addListener('selectionchange',function(){var state=editor.queryCommandState(uiName);if(state==-1){btn.setDisabled(true);btn.setChecked(false)}else{btn.setDisabled(false);btn.setChecked(state)}});return btn});UE.registerUI('attachment',function(editor,uiName){var btn=new UE.ui.Button({name:uiName,title:'附件',onclick:function(){LJS._iframe('index.php?t=sys&n=upload&c=gallery&a=attachment&id='+id,'上传附件',1,['300px','165px'])}});editor.addListener('selectionchange',function(){var state=editor.queryCommandState(uiName);if(state==-1){btn.setDisabled(true);btn.setChecked(false)}else{btn.setDisabled(false);btn.setChecked(state)}});return btn});UE.registerUI('map',function(editor,uiName){var btn=new UE.ui.Button({name:uiName,title:'百度地图',onclick:function(){LJS._iframe(LCMS.url.public+'static/Map/baidu/editor.html?ver='+LCMS.config.ver+'#'+id,'百度地图 - <span style="color:red">(商业使用需购买百度地图商业授权，可使用截图代替)</span>',1,['550px','550px'])}});editor.addListener('selectionchange',function(){var state=editor.queryCommandState(uiName);if(state==-1){btn.setDisabled(true);btn.setChecked(false)}else{btn.setDisabled(false);btn.setChecked(state)}});return btn});UE.registerUI('source',function(editor,uiName){var btn=new UE.ui.Button({name:uiName,title:'源代码',onclick:function(){editor.execCommand('source')}});editor.addListener('selectionchange',function(){var state=editor.queryCommandState(uiName);if(state==-1){btn.setDisabled(true);btn.setChecked(false)}else{btn.setDisabled(false);btn.setChecked(state)}});return btn});editor=UE.getEditor(id,{toolbars:[['undo','redo','fullscreen','paragraph','fontsize','|','bold','italic','underline','strikethrough','autotypeset','removeformat','formatmatch','blockquote','pasteplain','|','touppercase','tolowercase','forecolor','backcolor','insertorderedlist','insertunorderedlist','|','justifyleft','justifycenter','justifyright','justifyjustify','|','inserttable','|','link','unlink','|','imagenone','imageleft','imageright','imagecenter','|','insertimage','gallery','insertvideo','scrawl','attachment','insertframe','|','horizontal','insertcode','spechars','searchreplace','|','source']],insertorderedlist:{},insertunorderedlist:{},catcherUrlPrefix:'',catcherFieldName:"files",catcherActionName:"uploadcatcher",catchRemoteImageEnable:true,paragraph:{'h2':'H2-标题1','h3':'H3-标题2','h4':'H4-标题3','p':'P-段落','div':'DIV-块'},fontsize:[10,12,14,16,18,20,24,36],zIndex:1,autoFloatEnabled:true,autoHeightEnabled:true,allowDivTransToP:false,initialFrameWidth:null,initialFrameHeight:320,iframeCssUrl:LCMS.url.public+'ui/admin/static/editor.css?'+LCMS.config.ver})},300)})}else{layui.link(LCMS.url.static+'Eleditor/layout/base.css?'+LCMS.config.ver);layui.link(LCMS.url.public+'ui/web/static/editor.css?'+LCMS.config.ver);LJS._getjs(LCMS.url.static+'Eleditor/Eleditor.min.js','','1');$('.lcms-form-editor').each(function(index){var that=$(this);LJS._lazydo(function(){top.LCMSUIINDEX++;var id='LCMSEDITOR'+top.LCMSUIINDEX;var scr=that.children('script');that.append('<div id="'+id+'" class="lcms-editor" data-name="'+scr.attr('name')+'">'+scr.html()+'</div>');scr.remove();var editor=new Eleditor({el:'#'+id,toolbars:['insertText','editText','insertImage','insertLink','delete','cancel'],uploader:function(){var up=$(this);var do_upload=function(input,File,success,msg){switch(LCMS.config.oss){case'qiniu':case'tencent':case'aliyun':OSS_upload('image',File,function(res){if(res.code=="1"){LJS._tips(res.msg);success(res.data.src)}else{LJS._tips(res.msg,0)};input.remove();up.append('<input type="file" accept="image/*" name="editorfile" style="position:absolute;left:0;top:0;width:100%;height:100%;opacity:0;">');return msg(res.msg)},function(res){LJS._tips('请求接口出现异常',0);input.remove();up.append('<input type="file" accept="image/*" name="editorfile" style="position:absolute;left:0;top:0;width:100%;height:100%;opacity:0;">');return msg('请求接口出现异常')});break;case'local':LOC_upload('image',File,function(res){if(res.code=="1"){LJS._tips(res.msg);success(res.data.src)}else{LJS._tips(res.msg,0)};return msg(res.msg)},function(){input.remove();up.append('<input type="file" accept="image/*" name="editorfile" style="position:absolute;left:0;top:0;width:100%;height:100%;opacity:0;">')});break}};return new Promise(function(success,msg){var input=up.children('input');input.off('change').on('change',function(){var File=this.files[0];do_upload(input,File,success,msg)})})}})},500)})}
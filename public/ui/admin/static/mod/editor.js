window.UEDITOR_HOME_URL=LCMS.url.static+"Neditor/";LCMS.util.load({type:"css",src:LCMS.url.static+"Neditor/themes/notadd/css/neditor.min.css"});LCMS.util.load({type:"js",src:LCMS.url.static+"Neditor/neditor.cfg.js",async:false});LCMS.util.load({type:"js",src:LCMS.url.static+"Neditor/neditor.all.js",async:false});LCMS.util.load({type:"js",src:LCMS.url.static+"Neditor/neditor.service.js",async:false});LCMS.util.load({type:"js",url:LCMS.url.static+"Neditor/third-party/browser-md5-file.min.js",async:false});window.lcms_editor_getbody=function(id){var editor=UE.getEditor(id);if(0!=editor.queryCommandState("source")){editor.execCommand("source")}var content=editor.getContent();return content?content:""};let listenerEvents=function(e){let data=e.data;if(data&&data.type){switch(data.type){case"lcms-editor-addimage":for(let i=0;i<data.list.length;i++){let img=data.list[i],alt=img.original.split("/")[5];window.LCMSEDITORNOW.execCommand("inserthtml",'<img src="'+img.src+'" alt="'+alt+'" />')}break;case"lcms-editor-addivideo":window.LCMSEDITORNOW.execCommand("inserthtml",'<p class="player-iframe">'+data.content+"</p>");break;case"lcms-editor-addvideo":if("local"!=LCMS.config.oss){if(data.field.src&&-1==data.field.src.indexOf("://")){data.field.src=LCMS.config.cdn+data.field.src.replace("../","")}if(data.field.poster&&-1==data.field.poster.indexOf("://")){data.field.poster=LCMS.config.cdn+data.field.poster.replace("../","")}}var params=data.field.autoplay>0?' autoplay="autoplay"':"";params+=data.field.loop>0?' loop="loop"':"";window.LCMSEDITORNOW.execCommand("inserthtml",'<p class="player-video"><video class="edui-faked-video" src="'+data.field.src+'" poster="'+data.field.poster+'" width="'+data.field.width+'" height="'+data.field.height+'" controls="controls"'+params+"></video></p>");break;case"lcms-editor-addattachment":var name=decodeURIComponent(data.file.substring(data.file.lastIndexOf("/")+1));var mime=name.substring(name.lastIndexOf(".")+1),icon="";switch(mime){case"chm":icon="chm.png";break;case"exe":icon="exe.png";break;case"pdf":icon="pdf.png";break;case"psd":icon="psd.png";break;case"txt":icon="txt.png";break;case"rar":case"zip":case"7z":icon="rar.png";break;case"doc":case"docx":case"wps":icon="doc.png";break;case"xls":case"xlsx":case"et":icon="xls.png";break;case"ppt":case"pptx":case"dps":icon="ppt.png";break;case"mp3":case"wav":case"wma":icon="mp3.png";break;case"jpg":case"jpeg":case"gif":case"bmp":case"png":case"webp":icon="jpg.png";break;case"mv":case"mp4":case"mpg":case"avi":icon="mp4.png";break;default:icon="default.png";break}if("local"!=LCMS.config.oss&&-1==data.file.indexOf("://")){data.file=LCMS.config.cdn+data.file.replace("../","")}data.title=data.title+"."+mime;window.LCMSEDITORNOW.execCommand("inserthtml",'<p class="attachment-box" style="position:relative!important;box-sizing:border-box!important;padding:5px 5px 5px 45px!important;border:1px #EBEEF5 solid!important;font-weight:bold!important;height:44px!important;line-height:34px!important;overflow:hidden!important;text-overflow:ellipsis!important;display:-webkit-box!important;-webkit-line-clamp: 1;-webkit-box-orient: vertical;"><img style="position:absolute!important;left:5px!important;top:5px!important;width:30px!important;height:33px!important;margin:0!important;padding:0!important" src="../public/static/Neditor/dialogs/attachment/fileTypeImages/'+icon+'" /><a href="'+data.file+'" title="'+data.title+'" target="_blank">'+data.title+"</a></p>");break;case"lcms-editor-addmap":window.LCMSEDITORNOW.execCommand("inserthtml",'<p class="map-iframe">'+data.content+"</p>");break}}};window.removeEventListener("message",listenerEvents);window.addEventListener("message",listenerEvents);UE.commands["cmd_aiwrite"]={execCommand:function(cmd,type){let text="",startChat=function(content){window.LCMSEDITORNOW.focus();text=window.LCMSEDITORNOW.selection.getText();if(!text){text=window.LCMSEDITORNOW.getPlainTxt()}text=text?text.replace(/<[^>]*>/g,"").replace(/\n\n/g,"\n"):null;if(content&&!text){LCMS.util.notify({type:"error",content:"编辑器中无内容"});return false}LCMS.plugin.aimodel.chat({window:"AI写作",system:"请在回答中直接给出内容，不要添加任何多余的修饰词和你自己的话。",content:content?`${content}\n${text}`:"",chatbtns:[{title:"插入编辑器",tips:"将内容插入到编辑器光标位置",onclick:function(chatFunc,chat){window.LCMSEDITORNOW.execCommand("inserthtml",chat.html)}}]});return text?true:false};switch(type){case"xuxie":startChat("请根据以下内容续写成一段完整的内容。");break;case"chengwen":startChat("请根据以下大纲写成一篇完整的文章。800字左右。");break;case"youhua":startChat("请将以下内容进行润色，让它更有吸引力和阅读体验。");break;case"fanyi":layer.prompt({title:"翻译成哪种语言？",formType:0,placeholder:"请输入语言，例如：英语"},(function(lang,index){if(lang){startChat(`请将以下内容翻译成${lang}。`);layer.close(index)}else{LCMS.util.notify({type:"success",content:"请输入语言名称"})}}));break;case"tiwen":startChat();break}}};UE.registerUI("myinsertimage gallery insertvideo attachment map myinsertcode source plugin_135editor aiwrite",(function(editor,uiName){var uiTitle={myinsertimage:{title:"上传图片",className:"edui-for-insertimage",onclick:function(){LCMS.util.iframe({title:"上传图片",url:"index.php?t=sys&n=upload&c=gallery&a=upload&many=1",shade:true,area:["550px","550px"]})}},gallery:{title:"图库",className:"edui-for-simpleupload",onclick:function(){LCMS.util.iframe({title:"图库",url:"index.php?t=sys&n=upload&c=gallery&many=1&id=LCMSEDITOR",shade:true,area:["550px","550px"]})}},insertvideo:{title:"上传视频",onclick:function(){LCMS.util.iframe({title:"视频",url:"index.php?t=sys&n=upload&c=gallery&a=ivideo",shade:true,area:["550px","550px"]})}},attachment:{title:"上传附件",onclick:function(){LCMS.util.iframe({title:"上传附件",url:"index.php?t=sys&n=upload&c=gallery&a=attachment",shade:true,area:["500px","260px"]})}},map:{title:"天地图",onclick:function(){LCMS.util.iframe({title:"天地图",url:"index.php?t=sys&n=map&a=tianditu",shade:true,area:["550px","550px"]})}},myinsertcode:{title:"行内代码",className:"edui-for-code",onclick:function(){var range=editor.selection.getRange();if("CODE"!=range.endContainer.parentNode.nodeName&&range.cloneContents()){var text=range.cloneContents().textContent;editor.execCommand("inserthtml","<code>"+text+"</code>")}}},source:{title:"查看源代码",onclick:function(){editor.execCommand("source")}},plugin_135editor:{title:"135编辑器",className:"edui-for-135editor",onclick:function(){let editor135,eachImgs=function(html){const parser=new DOMParser,doc=parser.parseFromString(html,"text/html"),imgs=doc.getElementsByTagName("img");imgList=[];for(let i=0;i<imgs.length;i++){let src=imgs[i].getAttribute("src");if(-1!==src.indexOf(LCMS.url.site)){src=src.replace(LCMS.url.site,"../");imgs[i].setAttribute("src",src)}else{imgList.push(src);imgs[i].setAttribute("data-down",src);imgs[i].setAttribute("src",`${LCMS.url.static}Neditor/themes/notadd/images/spacer.gif`);imgs[i].classList.add("loadingclass")}}imgList=downImgs(imgList);return $(doc.body).html()},reImgs=function(downList){let html=editor.getContent();const parser=new DOMParser,doc=parser.parseFromString(html,"text/html"),imgs=doc.getElementsByTagName("img");for(let i=0;i<imgs.length;i++){let item=imgs[i].getAttribute("data-down");item=item?LCMS.util.crypto("md5",item):"";if(item&&downList[item]){imgs[i].setAttribute("src",downList[item]);imgs[i].removeAttribute("data-down");imgs[i].classList.remove("loadingclass")}}editor.setContent($(doc.body).html())},downImgs=function(imgList){let downList=[],goDown=function(index){let item=LCMS.util.crypto("md5",imgList[index]);LCMS.plugin.upload.direct({type:"url",file:imgList[index],local:0,success:function(res){downList[item]=res.data.src},error:function(error){downList[item]=imgList[index]},complete:function(){if(imgList[index+1]){goDown(index+1)}else{reImgs(downList)}}})};if(imgList.length>0){goDown(0)}},form135editor=function(e){if("string"!==typeof e.data){if(e.data.ready){let body=editor.getContent().replace(/src="..\//g,'src="'+LCMS.url.site);editor135.postMessage(body,"*")}return}if(0!==e.data.indexOf("<")){return}body=e.data;body=eachImgs(body);editor.setContent(body);window.removeEventListener("message",form135editor)};editor135=window.open("https://www.135editor.com/simple_editor.html?callback=true&appkey=","135editor","height="+(window.screen.availHeight-100)+",width="+(window.screen.availWidth-100)+",top=50,left=50,help=no,resizable=no,status=no,scroll=no");window.removeEventListener("message",form135editor);window.addEventListener("message",form135editor,false)}},aiwrite:{title:"AI写作",className:"edui-for-aiwrite",onclick:function(){LCMS.plugin.aimodel.chat({window:"AI写作",system:"你是一个写作助手，请在回答中直接给出内容，不要添加任何多余的修饰词和你自己的话。",chatbtns:[{title:"插入编辑器",tips:"将笑话插入编辑器",onclick:function(chatFunc,chat){if(chat.html){window.LCMSEDITORNOW.setContent(chat.html,true);LCMS.util.notify({type:"success",content:"内容已插入编辑器中"})}}}]})}}};var btn=new UE.ui.Button({name:uiName,title:uiTitle[uiName].title,className:uiTitle[uiName].className,onclick:function(){window.LCMSEDITORNOW=editor;uiTitle[uiName].onclick()}});editor.addListener("selectionchange",(function(){var state=editor.queryCommandState(uiName);if(-1==state){btn.setDisabled(true);btn.setChecked(false)}else{btn.setDisabled(false);btn.setChecked(state)}}));return btn}));$(".lcms-form-editor").each((function(index){const _this=$(this);var data=_this.data(),options={imageMaxSize:20971520,toolbars:[],insertorderedlist:{},insertunorderedlist:{},catcherUrlPrefix:"",catcherFieldName:"files",catcherActionName:"uploadcatcher",catchRemoteImageEnable:true,enableDragUpload:false,enablePasteUpload:true,paragraph:{h2:"H2-标题1",h3:"H3-标题2",h4:"H4-标题3",p:"P-段落",div:"DIV-块"},fontsize:[10,12,14,16,18,20,24,36],zIndex:1,autoFloatEnabled:true,autoHeightEnabled:true,allowDivTransToP:false,initialFrameWidth:null,initialFrameHeight:320,iframeCssUrl:LCMS.url.public+"ui/admin/static/editor.css?v="+LCMS.config.ver};if(data.simple){options.catchRemoteImageEnable=false;options.enablePasteUpload=false;options.toolbars=[["paragraph","fontsize","|","bold","italic","underline","strikethrough","myinsertcode","blockquote","|","forecolor","backcolor","insertorderedlist","insertunorderedlist","|","justifyleft","justifycenter","justifyright","|","inserttable","|","link"]];if(1!=data.simple){data.simple=$.base64.decode(data.simple);data.simple=JSON.parse(data.simple);for(let i=0;i<data.simple.length;i++){let name=data.simple[i];switch(name){case"insertimage":options.catchRemoteImageEnable=true;options.enablePasteUpload=true;name="myinsertimage";break}options.toolbars[0].push(name)}options.toolbars[0].push("|");options.toolbars[0].push("fullscreen");options.toolbars[0].push("insertimage")}}else if(window.screen.width>=768){options.toolbars=[["undo","redo","fullscreen","paragraph","fontsize","|","bold","italic","underline","strikethrough","autotypeset","removeformat","formatmatch","myinsertcode","blockquote","pasteplain","|","touppercase","tolowercase","forecolor","backcolor","insertorderedlist","insertunorderedlist","|","justifyleft","justifycenter","justifyright","justifyjustify","|","inserttable","|","imagenone","imageleft","imageright","imagecenter","|","link","myinsertimage","gallery","insertvideo","map","plugin_135editor","aiwrite","scrawl","attachment","insertframe","|","horizontal","insertcode","spechars","searchreplace","|","source","insertimage"]]}else{options.contextMenu=[];options.toolbars=[["paragraph","fontsize","|","bold","italic","underline","strikethrough","myinsertcode","blockquote","|","forecolor","backcolor","insertorderedlist","insertunorderedlist","|","justifyleft","justifycenter","justifyright","|","inserttable","|","link","myinsertimage","gallery","insertvideo","attachment"]]}setTimeout((function(){top.LCMSUIINDEX++;var id="LCMSEDITOR"+top.LCMSUIINDEX,editor="editor"+top.LCMSUIINDEX;_this.children("script").attr("id",id);editor=UE.getEditor(id,options);0==index&&(top.LCMS.plugin.editor.focusid=top.LCMSEDITORFOCUSID=id);editor.addListener("focus",(function(){window.LCMSEDITORNOW=editor;top.LCMS.plugin.editor.focusid=top.LCMSEDITORFOCUSID=id}))}),300)}));
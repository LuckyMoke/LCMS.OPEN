let mark2html=function(content){let html="";if(content&&"undefined"!=typeof marked){html=marked.parse(content,{breaks:true,mangle:false,headerIds:false});html=html.replace(/<pre><code/gi,"<pre").replace(/<\/code><\/pre>/gi,"</pre>")}return html?html:""};$("body").append(`<style>.edui-for-aiwrite{display:block}div.edui-box.edui-for-aiwrite{display:inline-block!important}</style>`);LCMS.plugin.aimodel={index:null,apicfg:null,options:{},request:null,chat:null};LCMS.plugin.aimodel.chat=function(opts){if(!this.request){const lindex=LCMS.util.loading();LCMS.util.load({type:"js",src:`${LCMS.url.static}plugin/marked.min.js`});LCMS.util.ajax({type:"GET",url:`${LCMS.url.own}t=sys&n=aimodel&a=config`,async:false,layer:true,success:function(res){if(1==res.code){LCMS.plugin.aimodel.apicfg=res.data}else{LCMS.util.notify({type:"error",content:"API配置信息获取失败"});return false}}});LCMS.util.load({type:"js",src:`${LCMS.url.app}sys/aimodel/include/resource/libs/${LCMS.config.aichat}.js`,async:false});LCMS.util.loading("close",lindex)}opts=Object.assign({model:LCMS.config.aichat,window:false,system:"",messages:[]},opts||{});if(opts.messages.length>5){opts.messages=opts.messages.slice(-5)}this.options=opts;if(opts.window){let openWin=()=>{this.index=LCMS.util.iframe({id:"lcms-aimodel-chat-window",title:opts.window,url:`${LCMS.url.own}t=sys&n=aimodel`,area:opts.area?opts.area:["500px","500px"],cancel:()=>{this.index=null}})};if(this.index){layer.close(this.index,(()=>{openWin()}))}else{openWin()}}else{if(0==opts.messages.length){return false}let def=Object.assign({},opts),content="",html="";delete def.onmessage;delete def.onclose;delete def.onerror;this.request(Object.assign({},def,{onmessage:function(txt,other){if(txt){content+=txt;if(-1!==txt.indexOf("\n")){html=mark2html(content)}opts.onmessage&&opts.onmessage(txt,html,other)}},onclose:function(other){if(content||other){html=mark2html(content);opts.onclose&&opts.onclose(content,html,other)}},onerror:function(error){opts.onerror&&opts.onerror(error)}}))}};
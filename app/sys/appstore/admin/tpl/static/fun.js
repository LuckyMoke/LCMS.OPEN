LCMS.util.tplx("local_index",{data:{tips:null,apps:{}},menushow:false,appshow:true,loading:true,mounted(){this.getData()},getData(loading){LCMS.util.ajax({type:"GET",url:`${LCMS.url.own_form}index&action=data`,loading:loading?true:false,success:res=>{if(1==res.code){this.data=res.data;this.loading=false;if(res.data.apps.length<=0){this.appshow=false}if(this.data.power){this.menushow||this.onShow()}}}})},onShow(){LCMS.util.setMenu([{title:"设置默认应用",icon:"component",color:"#FFFFFF",bgcolor:"#E6A23C",url:function(){LCMS.util.iframe({title:"设置默认应用",url:`${LCMS.url.own_form}setdefault`,area:["500px","500px"]})}}]);Sortable.create(this.$refs.appbox,{handle:".move",onTplx:func=>{LCMS.util.tplx("change:dom",func)},onEnd:()=>{this.onSubmit()}});this.checkUpdate();this.menushow=true},checkUpdate(){let now=Math.round((new Date).getTime()/1000),url=`${LCMS.url.own}t=sys&n=appstore&c=store&a=`,apps=this.data.apps,cache=layui.data("LCMS_cache",{key:"appstore_check_all"}),showBtn=function(data){for(const name in apps){const app=apps[name];if(data[name]&&data[name].ver>app.ver){apps[name].id=data[name].id;apps[name].update=data[name].ver}}};cache=cache||{};if(!apps||apps.length<=0){return}if(cache.expired>=now){showBtn(cache.data)}else{let applist=[];for(const name in apps){const app=apps[name];applist.push({name:name,ver:app.ver})}LCMS.util.ajax({type:"POST",url:`${url}check&c=store`,data:{applist:JSON.stringify(applist)},layer:true,success:function(res){if(1==res.code){layui.data("LCMS_cache",{key:"appstore_check_home",value:{data:res.data,expired:now+43200}});layui.data("LCMS_cache",{key:"appstore_check_all",value:{data:res.data,expired:now+43200}});showBtn(res.data)}}})}},onClick(type,app,name){switch(type){case"open":LCMS.plugin.router(app.url);break;case"update":LCMS.util.iframe({title:"立即更新",url:`${LCMS.url.own_form}check&c=store&action=content&type=up&id=${app.id}&localver=${app.ver}&appver=${app.update}`});break;case"repair":layer.alert("我已做好数据备份，立即进行数据表修复。",{title:"提示"},(function(index){layer.close(index);LCMS.util.ajax({type:"GET",url:`${LCMS.url.own}t=sys&n=backup&c=repair&apptitle=%E4%BF%AE%E5%A4%8D&appname=${name}`,loading:true,timeout:0,success:function(res){if(1==res.code){LCMS.util.notify({content:res.msg})}else{LCMS.util.notify({type:"error",content:res.msg})}}})}));break;case"uninstall":LCMS.util.notify({type:"flash"});layer.alert('<span style="color:red">卸载应用会删除应用文件和数据，请先做好备份！</span>',{title:"提示"},(index=>{layer.close(index);LCMS.util.ajax({type:"GET",url:`${LCMS.url.own_form}uninstall&app=${name}`,loading:true,timeout:0,success:res=>{if(1==res.code){LCMS.util.notify({content:res.msg});this.getData(true)}else{LCMS.util.notify({type:"error",content:res.msg})}}})}));break}},onHover(type,app){switch(type){case"enter":if(app.description){layer.tips(app.description,this.$el,{tips:[1,"#303133"],time:0})}break;case"leave":layer.closeAll("tips");break}},onSubmit(){let form=$(this.$refs.appform).serializeArray().reduce(((obj,item)=>{obj[item.name]=item.value;return obj}),{});LCMS.util.ajax({type:"POST",url:`${LCMS.url.own_form}saveindex`,data:form,loading:true,success:res=>{LCMS.util.notify({type:1==res.code?"success":"error",content:res.msg})}})}});if($(".install-progress").length>0){let api=LCMS.url.own_form,appinfo=$(".install-progress").data(),tips=$(".install-tips"),install=function(){tips.html("正在解压安装文件 请勿进行其它操作");LCMS.util.ajax({type:"POST",url:api+"install",data:{action:"copy_files",appid:appinfo.id,appname:appinfo.name,appver:appinfo.ver},success:function(res){if(1==res.code){layui.data("LCMS_cache",{key:"appstore_check_home",remove:true});layui.data("LCMS_cache",{key:"appstore_check_all",remove:true});layui.element.progress("download","90%");tips.html("正在安装应用数据 请勿进行其它操作");LCMS.util.ajax({type:"POST",url:api+"&n=backup&c=repair",data:{apptitle:"安装",appname:appinfo.name},timeout:10000,complete:function(){layui.element.progress("download","95%");LCMS.util.ajax({type:"POST",url:api+"install",data:{action:"get_oauth",appid:appinfo.id,appname:appinfo.name,appver:appinfo.ver,type:$(".install-progress").attr("data-type")},success:function(res){if(1==res.code){layui.element.progress("download","100%");tips.html("安装成功 页面即将刷新");setTimeout((function(){parent.layer.closeAll();LCMS.plugin.router("index.php?t=sys&n=appstore&c=local")}),2000)}else{tips.html(res.msg)}}})}})}else{tips.html(res.msg)}}})},download=function(){var timer;$.ajax({url:api+"install&action=down_file&appid="+appinfo.id+"&file="+appinfo.file,dataType:"json",cache:false,timeout:120000,success:function(res){if(1==res.code){install()}else{tips.html("安装包下载失败！");LCMS.util.notify({type:"error",content:"安装包下载失败"})}},error:function(){tips.html("数据加载失败！");LCMS.util.notify({type:"error",content:"数据加载失败"})},complete:function(){clearInterval(timer)}});timer=setInterval((()=>{LCMS.util.ajax({type:"GET",url:api+"install&action=get_size&appid="+appinfo.id,success:function(res){if(1==res.code){layui.element.progress("download",Math.floor(res.data/appinfo.size*90)+"%")}}})}),1000)};tips.html("正在获取需应用信息");LCMS.util.ajax({type:"POST",url:api+"install",data:{action:"get_info",appid:appinfo.id,appver:appinfo.ver},success:function(res){if(1==res.code){appinfo=res.data;if(appinfo.file&&appinfo.size>"0.00"){tips.html("正在下载文件 请勿进行其它操作");download()}else{tips.html("安装失败");LCMS.util.notify({type:"error",content:"安装失败"})}}else{tips.html(res.msg);LCMS.util.notify({type:"error",content:res.msg})}}})}
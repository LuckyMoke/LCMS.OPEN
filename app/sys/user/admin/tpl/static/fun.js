if($('.iframe-admin-edit').length>0){layui.form.verify({name:function(value,item){if(!new RegExp("^[a-zA-Z0-9_\u4e00-\u9fa5\\s·]+$").test(value)){return'账号不能有特殊字符'};if($('input[name="LC[id]"]').val()==''){var verifydata='';$.ajaxSettings.async=false;$.get(LCMS.url.own_form+'index&action=check-name&name='+value,function(res){verifydata=res},'json');$.ajaxSettings.async=true;if(verifydata.code!='1'){return verifydata.msg}}},pass:function(value,item){var pass=$('input[name="LC[pass]"]').val();if(pass!=value){return'两次输入的密码不相同'}},})};if($('.lcms-admin-level-title').length>0){var checkall=function(checkbox,value){checkbox.each(function(index,item){item.checked=value?value:''});layui.form.render('checkbox')};var ischeckall=function(checkbox){var hasTrue,hasFalse;checkbox.each(function(index,item){if(item.checked){hasTrue=true}if(!item.checked){hasFalse=true}});if(hasTrue&&hasFalse){return'1'}else if(hasTrue){return''}else{return'1'}};$('.lcms-admin-level-check p').on('click',function(){var input=$(this).siblings("input[type='checkbox']"),on=$(this).attr('data-all');on=on?on:ischeckall(input);$(this).attr('data-all',on?'':'1');checkall(input,on)});$('.lcms-admin-level-title').on('click',function(){var input=$(this).parent('.lcms-admin-level-box').find("input[type='checkbox']"),on=$(this).attr('data-all');on=on?on:ischeckall(input);$(this).attr('data-all',on?'':'1');checkall(input,on)})}
if($(".lcms-admin-level-title").length>0){var checkall=function(checkbox,value){checkbox.each(function(index,item){item.checked=value?value:""});layui.form.render("checkbox")};var ischeckall=function(checkbox){var hasTrue,hasFalse;checkbox.each(function(index,item){if(item.checked){hasTrue=true}if(!item.checked){hasFalse=true}});if(hasTrue&&hasFalse){return"1"}else if(hasTrue){return""}else{return"1"}};$(".lcms-admin-level-check p").on("click",function(){var input=$(this).siblings("input[type='checkbox']"),on=$(this).attr("data-all");on=on?on:ischeckall(input);$(this).attr("data-all",on?"":"1");checkall(input,on)});$(".lcms-admin-level-title").on("click",function(){var input=$(this).parent(".lcms-admin-level-box").find("input[type='checkbox']"),on=$(this).attr("data-all");on=on?on:ischeckall(input);$(this).attr("data-all",on?"":"1");checkall(input,on)})}
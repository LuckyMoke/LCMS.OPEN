layui.extend({selectN:'selectN/selectN.min'}).use(['selectN'],function(){$('.lcms-form-selectN').each(function(index){var id='LCMSELECTN'+index,data=$(this).data();$(this).attr('id',id);LJS._get($(this).attr('data-url'),function(res){layui.selectN({elem:'#'+id,name:data.name,selected:data.val.toString().split('/'),tips:data.default.toString().split('|'),verify:data.verify,data:res,delimiter:'/',field:{idName:'id',titleName:'title',statusName:'status',childName:'children'},})},'json')})});
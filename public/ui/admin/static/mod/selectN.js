layui.extend({selectN:'selectN/selectN.min'}).use(['selectN'],function(){var selectN=$('.lcms-form-selectN'),urls=[],showSelect=function(id,data,res){if(res&&res!=null){layui.selectN({elem:'#'+id,name:data.name,selected:data.val.toString().split('/'),tips:data.default.toString().split('|'),verify:data.verify,data:res,delimiter:'/',field:{idName:'value',titleName:'title',statusName:'status',childName:'children'},})}else{$('#'+id).html('<div style="color:#d2d2d2;background:#f4f4f4;text-align:center;line-height:38px;">暂无可选项</div>')}},goNext=function(selectN,index){if(index>=selectN.length){return};var self=$(selectN[index]),id='LCMSELECTN'+index,data=self.data(),url=encodeURIComponent(data.url),index=index+1;self.attr('id',id);if(urls[url]===undefined){LJS._get(decodeURIComponent(url),function(res){urls[url]=res;showSelect(id,data,urls[url]);goNext(selectN,index)},'json')}else{showSelect(id,data,urls[url]);goNext(selectN,index)}};if(selectN.length>0){goNext(selectN,0)}});
LCMS.plugin.aimodel=Object.assign(LCMS.plugin.aimodel,{request:function(opts){if(!this.apicfg){return false}let data,messages=[],headers=new Headers({"Content-Type":"application/json",Authorization:"Bearer "+this.apicfg.token});if(opts.system){messages.push({role:"system",content:opts.system})}messages=messages.concat(opts.messages);LCMS.util.EventSource(this.apicfg.api,{method:"POST",headers:headers,body:JSON.stringify({model:this.apicfg.model,messages:messages,max_tokens:this.apicfg.max_tokens,temperature:0.9,stream:true}),redirect:"follow",onmessage:function(res){if("[DONE]"!=res.data){data=JSON.parse(res.data);if(data.choices){opts.onmessage&&opts.onmessage({content:data.choices[0].delta.content})}}},onclose:function(){opts.onclose&&opts.onclose()},onerror:function(error){opts.onerror&&opts.onerror(error);LCMS.util.notify({type:"error",content:error.message})}})}});
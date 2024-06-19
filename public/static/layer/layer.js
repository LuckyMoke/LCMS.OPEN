"use strict";var _typeof="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e};!function(e,t){var i,n,a=e.layui&&layui.define,o={getPath:function(){return window.location.origin+"/public/static/layer/"}(),config:{removeFocus:!0},end:{},beforeEnd:{},events:{resize:{}},minStackIndex:0,minStackArr:[],btn:["确定","取消"],type:["dialog","page","iframe","loading","tips"],getStyle:function(t,i){var n=t.currentStyle?t.currentStyle:e.getComputedStyle(t,null);return n[n.getPropertyValue?"getPropertyValue":"getAttribute"](i)},link:function(t,i,n){if(r.path){var a=document.getElementsByTagName("head")[0],s=document.createElement("link");"string"==typeof i&&(n=i);var l=(n||t).replace(/\.|\//g,""),c="layuicss-"+l,f=0;s.rel="stylesheet",s.href=r.path+t,s.id=c,document.getElementById(c)||a.appendChild(s),"function"==typeof i&&function t(n){var a=document.getElementById(c);if(++f>100)return e.console&&console.error(l+".css: Invalid");1989===parseInt(o.getStyle(a,"width"))?("creating"===n&&a.removeAttribute("lay-status"),"creating"===a.getAttribute("lay-status")?setTimeout(t,100):i()):(a.setAttribute("lay-status","creating"),setTimeout(function(){t("creating")},100))}()}}},r={v:"2.9.12",ie:function(){var t=navigator.userAgent.toLowerCase();return!!(e.ActiveXObject||"ActiveXObject"in e)&&((t.match(/msie\s(\d+)/)||[])[1]||"11")}(),index:e.layer&&e.layer.v?1e5:0,path:o.getPath,config:function(e,t){return e=e||{},r.cache=o.config=i.extend({},o.config,e),r.path=o.config.path||r.path,"string"==typeof e.extend&&(e.extend=[e.extend]),o.config.path&&r.ready(),e.extend?(a?layui.addcss("modules/layer/"+e.extend):o.link("css/"+e.extend),this):this},ready:function(e){var t=(a?"modules/":"css/")+"layer.css?v="+r.v;return a?layui["layui.all"]?"function"==typeof e&&e():layui.addcss(t,e,"layer"):o.link(t,e,"layer"),this},alert:function(e,t,n){var a="function"==typeof t;return a&&(n=t),r.open(i.extend({content:e,yes:n},a?{}:t))},confirm:function(e,t,n,a){var s="function"==typeof t;return s&&(a=n,n=t),r.open(i.extend({content:e,btn:o.btn,yes:n,btn2:a},s?{}:t))},msg:function(e,t,n){var a="function"==typeof t,s=o.config.skin,c=(s?s+" "+s+"-msg":"")||"layui-layer-msg",f=l.anim.length-1;return a&&(n=t),r.open(i.extend({content:e,time:3e3,shade:!1,skin:c,title:!1,closeBtn:!1,btn:!1,resize:!1,end:n,removeFocus:!1},a&&!o.config.skin?{skin:c+" layui-layer-hui",anim:f}:function(){return t=t||{},(-1===t.icon||void 0===t.icon&&!o.config.skin)&&(t.skin=c+" "+(t.skin||"layui-layer-hui")),t}()))},load:function(e,t){return r.open(i.extend({type:3,icon:e||0,resize:!1,shade:.01,removeFocus:!1},t))},tips:function(e,t,n){return r.open(i.extend({type:4,content:[e,t],closeBtn:!1,time:3e3,shade:!1,resize:!1,fixed:!1,maxWidth:260,removeFocus:!1},n))}},s=function(e){var t=this,a=function(){t.creat()};t.index=++r.index,t.config.maxWidth=i(n).width()-30,t.config=i.extend({},t.config,o.config,e),document.body?a():setTimeout(function(){a()},30)};s.pt=s.prototype;var l=["layui-layer",".layui-layer-title",".layui-layer-main",".layui-layer-dialog","layui-layer-iframe","layui-layer-content","layui-layer-btn","layui-layer-close"];l.anim={0:"layer-anim-00",1:"layer-anim-01",2:"layer-anim-02",3:"layer-anim-03",4:"layer-anim-04",5:"layer-anim-05",6:"layer-anim-06",slideDown:"layer-anim-slide-down",slideLeft:"layer-anim-slide-left",slideUp:"layer-anim-slide-up",slideRight:"layer-anim-slide-right"},l.SHADE="layui-layer-shade",l.MOVE="layui-layer-move";s.pt.config={type:0,shade:.3,fixed:!0,move:l[1],title:"信息",offset:"auto",area:"auto",closeBtn:1,icon:-1,time:0,zIndex:19891014,maxWidth:360,anim:0,isOutAnim:!0,minStack:!0,moveType:1,resize:!0,scrollbar:!0,tips:2},s.pt.vessel=function(e,t){var n=this,a=n.index,r=n.config,s=r.zIndex+a,c="object"===_typeof(r.title),f=r.maxmin&&(1===r.type||2===r.type),u=r.title?'<div class="layui-layer-title" style="'+(c?r.title[1]:"")+'">'+(c?r.title[0]:r.title)+"</div>":"";return r.zIndex=s,t([r.shade?'<div class="'+l.SHADE+'" id="'+l.SHADE+a+'" times="'+a+'" style="z-index:'+(s-1)+'; "></div>':"",'<div class="'+l[0]+" layui-layer-"+o.type[r.type]+(0!=r.type&&2!=r.type||r.shade?"":" layui-layer-border")+" "+(r.skin||"")+'" id="'+l[0]+a+'" type="'+o.type[r.type]+'" times="'+a+'" showtime="'+r.time+'" conType="'+(e?"object":"string")+'" style="z-index: '+s+"; width:"+r.area[0]+";height:"+r.area[1]+";position:"+(r.fixed?"fixed;":"absolute;")+'">'+(e&&2!=r.type?"":u)+"<div"+(r.id?' id="'+r.id+'"':"")+' class="layui-layer-content'+(0==r.type&&-1!==r.icon?" layui-layer-padding":"")+(3==r.type?" layui-layer-loading"+r.icon:"")+'">'+function(){var e,t=["layui-icon-tips","layui-icon-success","layui-icon-error","layui-icon-question","layui-icon-lock","layui-icon-face-cry","layui-icon-face-smile"],i="layui-anim layui-anim-rotate layui-anim-loop";if(0==r.type&&-1!==r.icon)return 16==r.icon&&(e="layui-icon layui-icon-loading "+i),'<i class="layui-layer-face layui-icon '+(e||t[r.icon]||t[0])+'"></i>';if(3==r.type){var n=["layui-icon-loading","layui-icon-loading-1"];return 2==r.icon?'<div class="layui-layer-loading-2 '+i+'"></div>':'<i class="layui-layer-loading-icon layui-icon '+(n[r.icon]||n[0])+" "+i+'"></i>'}return""}()+(1==r.type&&e?"":r.content||"")+'</div><div class="layui-layer-setwin">'+function(){var e=[];return f&&(e.push('<span class="layui-layer-min"></span>'),e.push('<span class="layui-layer-max"></span>')),r.closeBtn&&e.push('<span class="layui-icon layui-icon-close '+[l[7],l[7]+(r.title?r.closeBtn:4==r.type?"1":"2")].join(" ")+'"></span>'),e.join("")}()+"</div>"+(r.btn?function(){var e="";"string"==typeof r.btn&&(r.btn=[r.btn]);for(var t=0,i=r.btn.length;t<i;t++)e+='<a class="'+l[6]+t+'">'+r.btn[t]+"</a>";return'<div class="'+function(){var e=[l[6]];return r.btnAlign&&e.push(l[6]+"-"+r.btnAlign),e.join(" ")}()+'">'+e+"</div>"}():"")+(r.resize?'<span class="layui-layer-resize"></span>':"")+"</div>"],u,i('<div class="'+l.MOVE+'" id="'+l.MOVE+'"></div>')),n},s.pt.creat=function(){var e=this,t=e.config,a=e.index,s=t.content,c="object"===(void 0===s?"undefined":_typeof(s)),f=i("body"),u=function(e){if(t.shift&&(t.anim=t.shift),l.anim[t.anim]){var n="layer-anim "+l.anim[t.anim];e.addClass(n).one("webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend",function(){i(this).removeClass(n)})}};if(t.id&&i("."+l[0]).find("#"+t.id)[0])return function(){var e=i("#"+t.id).closest("."+l[0]),n=e.attr("times"),a=e.data("config"),o=i("#"+l.SHADE+n);"min"===(e.data("maxminStatus")||{})?r.restore(n):a.hideOnClose&&(o.show(),e.show(),u(e),setTimeout(function(){o.css({opacity:o.data("LAYUI-LAYER-SHADE-KEY")})},10))}();switch(t.removeFocus&&document.activeElement&&document.activeElement.blur(),"string"==typeof t.area&&(t.area="auto"===t.area?["",""]:[t.area,""]),6==r.ie&&(t.fixed=!1),t.type){case 0:t.btn="btn"in t?t.btn:o.btn[0],r.closeAll("dialog");break;case 2:var s=t.content=c?t.content:[t.content||"","auto"];t.content='<iframe scrolling="'+(t.content[1]||"auto")+'" allowtransparency="true" id="'+l[4]+a+'" name="'+l[4]+a+'" onload="this.className=\'\';" class="layui-layer-load" frameborder="0" src="'+t.content[0]+'"></iframe>';break;case 3:delete t.title,delete t.closeBtn,-1===t.icon&&t.icon,r.closeAll("loading");break;case 4:c||(t.content=[t.content,"body"]),t.follow=t.content[1],t.content=t.content[0]+'<i class="layui-layer-TipsG"></i>',delete t.title,t.tips="object"===_typeof(t.tips)?t.tips:[t.tips,!0],t.tipsMore||r.closeAll("tips")}e.vessel(c,function(n,r,u){f.append(n[0]),c?function(){2==t.type||4==t.type?function(){i("body").append(n[1])}():function(){s.parents("."+l[0])[0]||(s.data("display",s.css("display")).show().addClass("layui-layer-wrap").wrap(n[1]),i("#"+l[0]+a).find("."+l[5]).before(r))}()}():f.append(n[1]),i("#"+l.MOVE)[0]||f.append(o.moveElem=u),e.layero=i("#"+l[0]+a),e.shadeo=i("#"+l.SHADE+a),t.scrollbar||o.setScrollbar(a)}).auto(a),e.shadeo.css({"background-color":t.shade[1]||"#000",opacity:t.shade[0]||t.shade,transition:t.shade[2]||""}),e.shadeo.data("LAYUI-LAYER-SHADE-KEY",t.shade[0]||t.shade),2==t.type&&6==r.ie&&e.layero.find("iframe").attr("src",s[0]),4==t.type?e.tips():function(){e.offset(),parseInt(o.getStyle(document.getElementById(l.MOVE),"z-index"))||function(){e.layero.css("visibility","hidden"),r.ready(function(){e.offset(),e.layero.css("visibility","visible")})}()}(),t.fixed&&(o.events.resize[e.index]||(o.events.resize[e.index]=function(){e.resize()},n.on("resize",o.events.resize[e.index]))),t.time<=0||setTimeout(function(){r.close(e.index)},t.time),e.move().callback(),u(e.layero),e.layero.data("config",t)},s.pt.resize=function(){var e=this,t=e.config;e.offset(),(/^\d+%$/.test(t.area[0])||/^\d+%$/.test(t.area[1]))&&e.auto(e.index),4==t.type&&e.tips()},s.pt.auto=function(e){var t=this,a=t.config,o=i("#"+l[0]+e);""===a.area[0]&&a.maxWidth>0&&(r.ie&&r.ie<8&&a.btn&&o.width(o.innerWidth()),o.outerWidth()>a.maxWidth&&o.width(a.maxWidth));var s=[o.innerWidth(),o.innerHeight()],c=o.find(l[1]).outerHeight()||0,f=o.find("."+l[6]).outerHeight()||0,u=function(e){e=o.find(e),e.height(s[1]-c-f-2*(0|parseFloat(e.css("padding-top"))))};switch(a.type){case 2:u("iframe");break;default:""===a.area[1]?a.maxHeight>0&&o.outerHeight()>a.maxHeight?(s[1]=a.maxHeight,u("."+l[5])):a.fixed&&s[1]>=n.height()&&(s[1]=n.height(),u("."+l[5])):u("."+l[5])}return t},s.pt.offset=function(){var e=this,t=e.config,i=e.layero,a=[i.outerWidth(),i.outerHeight()],o="object"===_typeof(t.offset);e.offsetTop=(n.height()-a[1])/2,e.offsetLeft=(n.width()-a[0])/2,o?(e.offsetTop=t.offset[0],e.offsetLeft=t.offset[1]||e.offsetLeft):"auto"!==t.offset&&("t"===t.offset?e.offsetTop=0:"r"===t.offset?e.offsetLeft=n.width()-a[0]:"b"===t.offset?e.offsetTop=n.height()-a[1]:"l"===t.offset?e.offsetLeft=0:"lt"===t.offset?(e.offsetTop=0,e.offsetLeft=0):"lb"===t.offset?(e.offsetTop=n.height()-a[1],e.offsetLeft=0):"rt"===t.offset?(e.offsetTop=0,e.offsetLeft=n.width()-a[0]):"rb"===t.offset?(e.offsetTop=n.height()-a[1],e.offsetLeft=n.width()-a[0]):e.offsetTop=t.offset),t.fixed||(e.offsetTop=/%$/.test(e.offsetTop)?n.height()*parseFloat(e.offsetTop)/100:parseFloat(e.offsetTop),e.offsetLeft=/%$/.test(e.offsetLeft)?n.width()*parseFloat(e.offsetLeft)/100:parseFloat(e.offsetLeft),e.offsetTop+=n.scrollTop(),e.offsetLeft+=n.scrollLeft()),"min"===i.data("maxminStatus")&&(e.offsetTop=n.height()-(i.find(l[1]).outerHeight()||0),e.offsetLeft=i.css("left")),i.css({top:e.offsetTop,left:e.offsetLeft})},s.pt.tips=function(){var e=this,t=e.config,a=e.layero,o=[a.outerWidth(),a.outerHeight()],r=i(t.follow);r[0]||(r=i("body"));var s={width:r.outerWidth(),height:r.outerHeight(),top:r.offset().top,left:r.offset().left},c=a.find(".layui-layer-TipsG"),f=t.tips[0];t.tips[1]||c.remove(),s.autoLeft=function(){s.left+o[0]-n.width()>0?(s.tipLeft=s.left+s.width-o[0],c.css({right:12,left:"auto"})):s.tipLeft=s.left},s.where=[function(){s.autoLeft(),s.tipTop=s.top-o[1]-10,c.removeClass("layui-layer-TipsB").addClass("layui-layer-TipsT").css("border-right-color",t.tips[1])},function(){s.tipLeft=s.left+s.width+10,s.tipTop=s.top-(.75*s.height<21?21-.5*s.height:0),s.tipTop=Math.max(s.tipTop,0),c.removeClass("layui-layer-TipsL").addClass("layui-layer-TipsR").css("border-bottom-color",t.tips[1])},function(){s.autoLeft(),s.tipTop=s.top+s.height+10,c.removeClass("layui-layer-TipsT").addClass("layui-layer-TipsB").css("border-right-color",t.tips[1])},function(){s.tipLeft=s.left-o[0]-10,s.tipTop=s.top-(.75*s.height<21?21-.5*s.height:0),s.tipTop=Math.max(s.tipTop,0),c.removeClass("layui-layer-TipsR").addClass("layui-layer-TipsL").css("border-bottom-color",t.tips[1])}],s.where[f-1](),1===f?s.top-(n.scrollTop()+o[1]+16)<0&&s.where[2]():2===f?n.width()-(s.left+s.width+o[0]+16)>0||s.where[3]():3===f?s.top-n.scrollTop()+s.height+o[1]+16-n.height()>0&&s.where[0]():4===f&&o[0]+16-s.left>0&&s.where[1](),a.find("."+l[5]).css({"background-color":t.tips[1],"padding-right":t.closeBtn?"30px":""}),a.css({left:s.tipLeft-(t.fixed?n.scrollLeft():0),top:s.tipTop-(t.fixed?n.scrollTop():0)})},s.pt.move=function(){var e=this,t=e.config,a=i(document),s=e.layero,l=["LAY_MOVE_DICT","LAY_RESIZE_DICT"],c=s.find(t.move),f=s.find(".layui-layer-resize");return t.move&&c.css("cursor","move"),c.on("mousedown",function(e){if(!e.button){var n=i(this),a={};t.move&&(a.layero=s,a.config=t,a.offset=[e.clientX-parseFloat(s.css("left")),e.clientY-parseFloat(s.css("top"))],n.data(l[0],a),o.eventMoveElem=n,o.moveElem.css("cursor","move").show()),e.preventDefault()}}),f.on("mousedown",function(n){var a=i(this),r={};t.resize&&(r.layero=s,r.config=t,r.offset=[n.clientX,n.clientY],r.index=e.index,r.area=[s.outerWidth(),s.outerHeight()],a.data(l[1],r),o.eventResizeElem=a,o.moveElem.css("cursor","se-resize").show()),n.preventDefault()}),o.docEvent?e:(a.on("mousemove",function(e){if(o.eventMoveElem){var t=o.eventMoveElem.data(l[0])||{},i=t.layero,a=t.config,s=e.clientX-t.offset[0],c=e.clientY-t.offset[1],f="fixed"===i.css("position");if(e.preventDefault(),t.stX=f?0:n.scrollLeft(),t.stY=f?0:n.scrollTop(),!a.moveOut){var u=n.width()-i.outerWidth()+t.stX,d=n.height()-i.outerHeight()+t.stY;s<t.stX&&(s=t.stX),s>u&&(s=u),c<t.stY&&(c=t.stY),c>d&&(c=d)}i.css({left:s,top:c})}if(o.eventResizeElem){var t=o.eventResizeElem.data(l[1])||{},a=t.config,s=e.clientX-t.offset[0],c=e.clientY-t.offset[1];e.preventDefault(),r.style(t.index,{width:t.area[0]+s,height:t.area[1]+c}),a.resizing&&a.resizing(t.layero)}}).on("mouseup",function(e){if(o.eventMoveElem){var t=o.eventMoveElem.data(l[0])||{},i=t.config;o.eventMoveElem.removeData(l[0]),delete o.eventMoveElem,o.moveElem.hide(),i.moveEnd&&i.moveEnd(t.layero)}o.eventResizeElem&&(o.eventResizeElem.removeData(l[1]),delete o.eventResizeElem,o.moveElem.hide())}),o.docEvent=!0,e)},s.pt.btnLoading=function(e,t){if(t){if(e.find(".layui-layer-btn-loading-icon")[0])return;e.addClass("layui-layer-btn-is-loading").attr({disabled:""}).prepend('<i class="layui-layer-btn-loading-icon layui-icon layui-icon-loading layui-anim layui-anim-rotate layui-anim-loop"></i>')}else e.removeClass("layui-layer-btn-is-loading").removeAttr("disabled").find(".layui-layer-btn-loading-icon").remove()},s.pt.callback=function(){function t(){!1===(s.cancel&&s.cancel(n.index,a,n))||r.close(n.index)}var n=this,a=n.layero,s=n.config;n.openLayer(),s.success&&(2==s.type?a.find("iframe").on("load",function(){s.success(a,n.index,n)}):s.success(a,n.index,n)),6==r.ie&&n.IE6(a),a.find("."+l[6]).children("a").on("click",function(){var t=i(this),l=t.index();if(!t.attr("disabled"))if(s.btnAsync){var c=0===l?s.yes||s.btn1:s["btn"+(l+1)];n.loading=function(e){n.btnLoading(t,e)},c?o.promiseLikeResolve(c.call(s,n.index,a,n)).then(function(e){!1!==e&&r.close(n.index)},function(t){void 0!==t&&e.console&&e.console.error("layer error hint: "+t)}):r.close(n.index)}else if(0===l)s.yes?s.yes(n.index,a,n):s.btn1?s.btn1(n.index,a,n):r.close(n.index);else{var f=s["btn"+(l+1)]&&s["btn"+(l+1)](n.index,a,n);!1===f||r.close(n.index)}}),a.find("."+l[7]).on("click",t),s.shadeClose&&n.shadeo.on("click",function(){r.close(n.index)}),a.find(".layui-layer-min").on("click",function(){!1===(s.min&&s.min(a,n.index,n))||r.min(n.index,s)}),a.find(".layui-layer-max").on("click",function(){i(this).hasClass("layui-layer-maxmin")?(r.restore(n.index),s.restore&&s.restore(a,n.index,n)):(r.full(n.index,s),setTimeout(function(){s.full&&s.full(a,n.index,n)},100))}),s.end&&(o.end[n.index]=s.end),s.beforeEnd&&(o.beforeEnd[n.index]=i.proxy(s.beforeEnd,s,a,n.index,n))},o.reselect=function(){i.each(i("select"),function(e,t){var n=i(this);n.parents("."+l[0])[0]||1==n.attr("layer")&&i("."+l[0]).length<1&&n.removeAttr("layer").show(),n=null})},s.pt.IE6=function(e){i("select").each(function(e,t){var n=i(this);n.parents("."+l[0])[0]||"none"===n.css("display")||n.attr({layer:"1"}).hide(),n=null})},s.pt.openLayer=function(){var e=this;r.zIndex=e.config.zIndex,r.setTop=function(e){var t=function(){r.zIndex++,e.css("z-index",r.zIndex+1)};return r.zIndex=parseInt(e[0].style.zIndex),e.on("mousedown",t),r.zIndex}},o.record=function(t){if(!t[0])return e.console&&console.error("index error");var i=t.attr("type"),n=t.find(".layui-layer-content"),a=i===o.type[2]?n.children("iframe"):n,r=[t[0].style.width||o.getStyle(t[0],"width"),t[0].style.height||o.getStyle(t[0],"height"),t.position().top,t.position().left+parseFloat(t.css("margin-left"))];t.find(".layui-layer-max").addClass("layui-layer-maxmin"),t.attr({area:r}),n.data("LAYUI_LAYER_CONTENT_RECORD_HEIGHT",o.getStyle(a[0],"height"))},o.setScrollbar=function(e){l.html.css("overflow","hidden").attr("layer-full",e)},o.restScrollbar=function(e){l.html.attr("layer-full")==e&&(l.html[0].style[l.html[0].style.removeProperty?"removeProperty":"removeAttribute"]("overflow"),l.html.removeAttr("layer-full"))},o.promiseLikeResolve=function(e){var t=i.Deferred();return e&&"function"==typeof e.then?e.then(t.resolve,t.reject):t.resolve(e),t.promise()},e.layer=r,r.getChildFrame=function(e,t){return t=t||i("."+l[4]).attr("times"),i("#"+l[0]+t).find("iframe").contents().find(e)},r.getFrameIndex=function(e){return i("#"+e).parents("."+l[4]).attr("times")},r.iframeAuto=function(e){if(e){var t=r.getChildFrame("html",e).outerHeight(),n=i("#"+l[0]+e),a=n.find(l[1]).outerHeight()||0,o=n.find("."+l[6]).outerHeight()||0;n.css({height:t+a+o}),n.find("iframe").css({height:t})}},r.iframeSrc=function(e,t){i("#"+l[0]+e).find("iframe").attr("src",t)},r.style=function(e,t,n){var a=i("#"+l[0]+e),r=a.find(".layui-layer-content"),s=a.attr("type"),c=a.find(l[1]).outerHeight()||0,f=a.find("."+l[6]).outerHeight()||0;a.attr("minLeft");s!==o.type[3]&&s!==o.type[4]&&(n||(parseFloat(t.width)<=260&&(t.width=260),parseFloat(t.height)-c-f<=64&&(t.height=64+c+f)),a.css(t),f=a.find("."+l[6]).outerHeight()||0,s===o.type[2]?a.find("iframe").css({height:("number"==typeof t.height?t.height:a.height())-c-f}):r.css({height:("number"==typeof t.height?t.height:a.height())-c-f-parseFloat(r.css("padding-top"))-parseFloat(r.css("padding-bottom"))}))},r.min=function(e,t){var a=i("#"+l[0]+e),s=a.data("maxminStatus");if("min"!==s){"max"===s&&r.restore(e),a.data("maxminStatus","min"),t=t||a.data("config")||{};var c=i("#"+l.SHADE+e),f=a.find(".layui-layer-min"),u=a.find(l[1]).outerHeight()||0,d=a.attr("minLeft"),y="string"==typeof d,p=y?d:181*o.minStackIndex+"px",m=a.css("position"),h={width:180,height:u,position:"fixed",overflow:"hidden"};o.record(a),o.minStackArr.length>0&&(p=o.minStackArr[0],o.minStackArr.shift()),parseFloat(p)+180>n.width()&&(p=n.width()-180-function(){return o.minStackArr.edgeIndex=o.minStackArr.edgeIndex||0,o.minStackArr.edgeIndex+=3}())<0&&(p=0),t.minStack&&(h.left=p,h.top=n.height()-u,y||o.minStackIndex++,a.attr("minLeft",p)),a.attr("position",m),r.style(e,h,!0),f.hide(),"page"===a.attr("type")&&a.find(l[4]).hide(),o.restScrollbar(e),c.hide()}},r.restore=function(e){var t=i("#"+l[0]+e),n=i("#"+l.SHADE+e),a=t.find(".layui-layer-content"),s=t.attr("area").split(","),c=t.attr("type"),f=t.data("config")||{},u=a.data("LAYUI_LAYER_CONTENT_RECORD_HEIGHT");if(t.removeData("maxminStatus"),r.style(e,{width:s[0],height:s[1],top:parseFloat(s[2]),left:parseFloat(s[3]),position:t.attr("position"),overflow:"visible"},!0),t.find(".layui-layer-max").removeClass("layui-layer-maxmin"),t.find(".layui-layer-min").show(),"page"===c&&t.find(l[4]).show(),f.scrollbar?o.restScrollbar(e):o.setScrollbar(e),void 0!==u){a.removeData("LAYUI_LAYER_CONTENT_RECORD_HEIGHT");(c===o.type[2]?a.children("iframe"):a).css({height:u})}n.show()},r.full=function(e){var t=i("#"+l[0]+e),a=t.data("maxminStatus");"max"!==a&&("min"===a&&r.restore(e),t.data("maxminStatus","max"),o.record(t),l.html.attr("layer-full")||o.setScrollbar(e),setTimeout(function(){var i="fixed"===t.css("position");r.style(e,{top:i?0:n.scrollTop(),left:i?0:n.scrollLeft(),width:"100%",height:"100%"},!0),t.find(".layui-layer-min").hide()},100))},r.title=function(e,t){i("#"+l[0]+(t||r.index)).find(l[1]).html(e)},r.close=function(t,a){var s=function(){var e=i("."+l[0]).children("#"+t).closest("."+l[0]);return e[0]?(t=e.attr("times"),e):i("#"+l[0]+t)}(),c=s.attr("type"),f=s.data("config")||{},u=f.id&&f.hideOnClose;if(s[0]){var d=function(){var e={slideDown:"layer-anim-slide-down-out",slideLeft:"layer-anim-slide-left-out",slideUp:"layer-anim-slide-up-out",slideRight:"layer-anim-slide-right-out"}[f.anim]||"layer-anim-close",d=function(){var r="layui-layer-wrap";if(u)return s.removeClass("layer-anim "+e),s.hide();if(c===o.type[1]&&"object"===s.attr("conType")){s.children(":not(."+l[5]+")").remove();for(var f=s.find("."+r),d=0;d<2;d++)f.unwrap();f.css("display",f.data("display")).removeClass(r)}else{if(c===o.type[2])try{var y=i("#"+l[4]+t)[0];y.contentWindow.document.write(""),y.contentWindow.close(),s.find("."+l[5])[0].removeChild(y)}catch(e){}s[0].innerHTML="",s.remove()}"function"==typeof o.end[t]&&o.end[t](),delete o.end[t],"function"==typeof a&&a(),o.events.resize[t]&&(n.off("resize",o.events.resize[t]),delete o.events.resize[t])},y=i("#"+l.SHADE+t);r.ie&&r.ie<10||!f.isOutAnim?y[u?"hide":"remove"]():(y.css({opacity:0}),setTimeout(function(){y[u?"hide":"remove"]()},350)),f.isOutAnim&&s.addClass("layer-anim "+e),6==r.ie&&o.reselect(),o.restScrollbar(t),"string"==typeof s.attr("minLeft")&&(o.minStackIndex--,o.minStackArr.push(s.attr("minLeft"))),r.ie&&r.ie<10||!f.isOutAnim?d():setTimeout(function(){d()},200)};u||"function"!=typeof o.beforeEnd[t]?(delete o.beforeEnd[t],d()):o.promiseLikeResolve(o.beforeEnd[t]()).then(function(e){!1!==e&&(delete o.beforeEnd[t],d())},function(t){void 0!==t&&e.console&&e.console.error("layer error hint: "+t)})}},r.closeAll=function(e,t){"function"==typeof e&&(t=e,e=null);var n=i("."+l[0]);i.each(n,function(a){var o=i(this),s=e?o.attr("type")===e:1;s&&r.close(o.attr("times"),a===n.length-1?t:null),s=null}),0===n.length&&"function"==typeof t&&t()},r.closeLast=function(e,t){var n=[],a=i.isArray(e);if(i("string"==typeof e?".layui-layer-"+e:".layui-layer").each(function(t,o){var r=i(o);if(a&&-1===e.indexOf(r.attr("type"))||"none"===r.css("display"))return!0;n.push(Number(r.attr("times")))}),n.length>0){var o=Math.max.apply(null,n);r.close(o,t)}};var c=r.cache||{},f=function(e){return c.skin?" "+c.skin+" "+c.skin+"-"+e:""};r.prompt=function(e,t){var a="",o="";if(e=e||{},"function"==typeof e&&(t=e),e.area){var s=e.area;a='style="width: '+s[0]+"; height: "+s[1]+';"',delete e.area}e.placeholder&&(o=' placeholder="'+e.placeholder+'"');var l,c=2==e.formType?'<textarea class="layui-layer-input"'+a+o+"></textarea>":function(){return'<input type="'+(1==e.formType?"password":"text")+'" class="layui-layer-input"'+o+">"}(),u=e.success;return delete e.success,r.open(i.extend({type:1,btn:["确定","取消"],content:c,skin:"layui-layer-prompt"+f("prompt"),maxWidth:n.width(),success:function(t){l=t.find(".layui-layer-input"),l.val(e.value||"").focus(),"function"==typeof u&&u(t)},resize:!1,yes:function(i){var n=l.val();n.length>(e.maxlength||500)?r.tips("最多输入"+(e.maxlength||500)+"个字符",l,{tips:1}):t&&t(n,i,l)}},e))},r.tab=function(e){e=e||{};var t=e.tab||{},n="layui-this",a=e.success;return delete e.success,r.open(i.extend({type:1,skin:"layui-layer-tab"+f("tab"),resize:!1,title:function(){var e=t.length,i=1,a="";if(e>0)for(a='<span class="'+n+'">'+t[0].title+"</span>";i<e;i++)a+="<span>"+t[i].title+"</span>";return a}(),content:'<ul class="layui-layer-tabmain">'+function(){var e=t.length,i=1,a="";if(e>0)for(a='<li class="layui-layer-tabli '+n+'">'+(t[0].content||"no content")+"</li>";i<e;i++)a+='<li class="layui-layer-tabli">'+(t[i].content||"no  content")+"</li>";return a}()+"</ul>",success:function(t){var o=t.find(".layui-layer-title").children(),r=t.find(".layui-layer-tabmain").children();o.on("mousedown",function(t){t.stopPropagation?t.stopPropagation():t.cancelBubble=!0;var a=i(this),o=a.index();a.addClass(n).siblings().removeClass(n),r.eq(o).show().siblings().hide(),"function"==typeof e.change&&e.change(o)}),"function"==typeof a&&a(t)}},e))},r.photos=function(t,a,o){var s={};if(t=i.extend(!0,{toolbar:!0,footer:!0},t),t.photos){var l=!("string"==typeof t.photos||t.photos instanceof i),c=l?t.photos:{},u=c.data||[],d=c.start||0,y=t.success;if(s.imgIndex=1+(0|d),t.img=t.img||"img",delete t.success,l){if(0===u.length)return r.msg("没有图片")}else{var p=i(t.photos),m=function(){u=[],p.find(t.img).each(function(e){var t=i(this);t.attr("layer-index",e),u.push({alt:t.attr("alt"),pid:t.attr("layer-pid"),src:t.attr("lay-src")||t.attr("layer-src")||t.attr("src"),thumb:t.attr("src")})})};if(m(),0===u.length)return;if(a||p.on("click",t.img,function(){m();var e=i(this),n=e.attr("layer-index");r.photos(i.extend(t,{photos:{start:n,data:u,tab:t.tab},full:t.full}),!0)}),!a)return}s.imgprev=function(e){s.imgIndex--,s.imgIndex<1&&(s.imgIndex=u.length),s.tabimg(e)},s.imgnext=function(e,t){++s.imgIndex>u.length&&(s.imgIndex=1,t)||s.tabimg(e)},s.keyup=function(e){if(!s.end){var t=e.keyCode;e.preventDefault(),37===t?s.imgprev(!0):39===t?s.imgnext(!0):27===t&&r.close(s.index)}},s.tabimg=function(e){if(!(u.length<=1))return c.start=s.imgIndex-1,r.close(s.index),r.photos(t,!0,e)},s.isNumber=function(e){return"number"==typeof e&&!isNaN(e)},s.image={},s.getTransform=function(e){var t=[],i=e.rotate,n=e.scaleX,a=e.scale;return s.isNumber(i)&&0!==i&&t.push("rotate("+i+"deg)"),s.isNumber(n)&&1!==n&&t.push("scaleX("+n+")"),s.isNumber(a)&&t.push("scale("+a+")"),t.length?t.join(" "):"none"},s.event=function(t,a,o){if(s.main.find(".layui-layer-photos-prev").on("click",function(e){e.preventDefault(),s.imgprev(!0)}),s.main.find(".layui-layer-photos-next").on("click",function(e){e.preventDefault(),s.imgnext(!0)}),i(document).on("keyup",s.keyup),t.off("click").on("click","*[toolbar-event]",function(){var e=i(this);switch(e.attr("toolbar-event")){case"rotate":s.image.rotate=((s.image.rotate||0)+Number(e.attr("data-option")))%360,s.imgElem.css({transform:s.getTransform(s.image)});break;case"scalex":s.image.scaleX=-1===s.image.scaleX?1:-1,s.imgElem.css({transform:s.getTransform(s.image)});break;case"zoom":var t=Number(e.attr("data-option"));s.image.scale=(s.image.scale||1)+t,t<0&&s.image.scale<0-t&&(s.image.scale=0-t),s.imgElem.css({transform:s.getTransform(s.image)});break;case"reset":s.image.scaleX=1,s.image.scale=1,s.image.rotate=0,s.imgElem.css({transform:"none"});break;case"close":r.close(a)}o.offset(),o.auto(a)}),s.main.on("mousewheel DOMMouseScroll",function(e){var t=e.originalEvent.wheelDelta||-e.originalEvent.detail,i=s.main.find('[toolbar-event="zoom"]');t>0?i.eq(0).trigger("click"):i.eq(1).trigger("click"),e.preventDefault()}),e.layui||e.lay){var l=e.layui.lay||e.lay,c=function(e,t){var i=Date.now()-t.timeStart,a=t.distanceX/i,o=n.width()/3;(Math.abs(a)>.25||Math.abs(t.distanceX)>o)&&("left"===t.direction?s.imgnext(!0):"right"===t.direction&&s.imgprev(!0))};i.each([o.shadeo,s.main],function(e,t){l.touchSwipe(t,{onTouchEnd:c})})}},s.loadi=r.load(1,{shade:!("shade"in t)&&[.9,void 0,"unset"],scrollbar:!1}),function(e,t,i){var n=new Image;if(n.src=e,n.complete)return t(n);n.onload=function(){n.onload=null,t(n)},n.onerror=function(e){n.onerror=null,i(e)}}(u[d].src,function(n){r.close(s.loadi);var a=u[d].alt||"";o&&(t.anim=-1),s.index=r.open(i.extend({type:1,id:"layui-layer-photos",area:function(){var a=[n.width,n.height],o=[i(e).width()-100,i(e).height()-100];if(!t.full&&(a[0]>o[0]||a[1]>o[1])){var r=[a[0]/o[0],a[1]/o[1]];r[0]>r[1]?(a[0]=a[0]/r[0],a[1]=a[1]/r[0]):r[0]<r[1]&&(a[0]=a[0]/r[1],a[1]=a[1]/r[1])}return[a[0]+"px",a[1]+"px"]}(),title:!1,shade:[.9,void 0,"unset"],shadeClose:!0,closeBtn:!1,move:".layer-layer-photos-main img",moveType:1,scrollbar:!1,moveOut:!0,anim:5,isOutAnim:!1,skin:"layui-layer-photos"+f("photos"),content:'<div class="layer-layer-photos-main"><img src="'+u[d].src+'" alt="'+a+'" layer-pid="'+(u[d].pid||"")+'">'+function(){var e=['<div class="layui-layer-photos-pointer">'];return u.length>1&&e.push(['<div class="layer-layer-photos-page">','<span class="layui-icon layui-icon-left layui-layer-photos-prev"></span>','<span class="layui-icon layui-icon-right layui-layer-photos-next"></span>',"</div>"].join("")),t.toolbar&&e.push(['<div class="layui-layer-photos-toolbar layui-layer-photos-header">','<span toolbar-event="rotate" data-option="90" title="旋转"><i class="layui-icon layui-icon-refresh"></i></span>','<span toolbar-event="scalex" title="变换"><i class="layui-icon layui-icon-slider"></i></span>','<span toolbar-event="zoom" data-option="0.1" title="放大"><i class="layui-icon layui-icon-add-circle"></i></span>','<span toolbar-event="zoom" data-option="-0.1" title="缩小"><i class="layui-icon layui-icon-reduce-circle"></i></span>','<span toolbar-event="reset" title="还原"><i class="layui-icon layui-icon-refresh-1"></i></span>','<span toolbar-event="close" title="关闭"><i class="layui-icon layui-icon-close"></i></span>',"</div>"].join("")),t.footer&&e.push(['<div class="layui-layer-photos-toolbar layui-layer-photos-footer">',"<h3>"+a+"</h3>","<em>"+s.imgIndex+" / "+u.length+"</em>",'<a href="'+u[d].src+'" target="_blank">查看原图</a>',"</div>"].join("")),e.push("</div>"),e.join("")}()+"</div>",success:function(e,i,n){s.main=e.find(".layer-layer-photos-main"),s.footer=e.find(".layui-layer-photos-footer"),s.imgElem=s.main.children("img"),s.event(e,i,n),t.tab&&t.tab(u[d],e),"function"==typeof y&&y(e)},end:function(){s.end=!0,i(document).off("keyup",s.keyup)}},t))},function(){r.close(s.loadi),r.msg("当前图片地址异常，<br>是否继续查看下一张？",{time:3e4,btn:["下一张","不看了"],yes:function(){u.length>1&&s.imgnext(!0,!0)}})})}},o.run=function(t){i=t,n=i(e);var a=navigator.userAgent.toLowerCase(),o=/android|iphone|ipod|ipad|ios/.test(a),c=i(e);o&&i.each({Height:"height",Width:"width"},function(t,i){var a="inner"+t;n[i]=function(){return a in e?e[a]:c[i]()}}),l.html=i("html"),r.open=function(e){return new s(e).index}},e.layui&&layui.define?(r.ready(),layui.define(["jquery","lay"],function(t){r.path=layui.cache.dir,o.run(layui.$),e.layer=r,t("layer",r)})):"function"==typeof define&&define.amd?define(["jquery"],function(){return o.run(e.jQuery),r}):function(){r.ready(),o.run(e.jQuery)}()}(window);
(function(){var URL=window.UEDITOR_HOME_URL||getUEBasePath();window.UEDITOR_CONFIG={UEDITOR_HOME_URL:URL,serverUrl:"",imageActionName:"uploadimage",scrawlActionName:"uploadscrawl",videoActionName:"uploadvideo",fileActionName:"uploadfile",catcherFieldName:"files",catcherActionName:"uploadcatcher",catcherUrlPrefix:"",imageFieldName:"file",imageMaxSize:2048000,imageUrlPrefix:"",scrawlUrlPrefix:"",videoUrlPrefix:"",fileUrlPrefix:"",catcherLocalDomain:"",toolbars:[["fullscreen","source","|","undo","redo","|","bold","italic","underline","fontborder","strikethrough","superscript","subscript","removeformat","formatmatch","autotypeset","blockquote","pasteplain","|","forecolor","backcolor","insertorderedlist","insertunorderedlist","selectall","cleardoc","|","rowspacingtop","rowspacingbottom","lineheight","|","customstyle","paragraph","fontfamily","fontsize","|","directionalityltr","directionalityrtl","indent","|","justifyleft","justifycenter","justifyright","justifyjustify","|","touppercase","tolowercase","|","link","unlink","anchor","|","imagenone","imageleft","imageright","imagecenter","|","insertimage","emotion","scrawl","insertvideo","music","attachment","map","gmap","insertframe","pagebreak","template","background","|","insertcode","horizontal","date","time","spechars","snapscreen","wordimage","|","inserttable","deletetable","insertparagraphbeforetable","insertrow","deleterow","insertcol","deletecol","mergecells","mergeright","mergedown","splittocells","splittorows","splittocols","charts","|","print","preview","searchreplace","drafts","help"]],theme:'notadd',zIndex:1100,autoHeightEnabled:false,xssFilterRules:true,inputXssFilter:true,outputXssFilter:true,whitList:{a:['target','href','title','class','style'],abbr:['title','class','style'],address:['class','style'],area:['shape','coords','href','alt'],article:[],aside:[],audio:['autoplay','controls','loop','preload','src','class','style'],b:['class','style'],bdi:['dir'],bdo:['dir'],big:[],blockquote:['cite','class','style'],br:[],caption:['class','style'],center:[],cite:[],code:['class','style'],col:['align','valign','span','width','class','style'],colgroup:['align','valign','span','width','class','style'],dd:['class','style'],del:['datetime'],details:['open'],div:['class','style'],dl:['class','style'],dt:['class','style'],em:['class','style'],font:['color','size','face'],footer:[],h1:['class','style'],h2:['class','style'],h3:['class','style'],h4:['class','style'],h5:['class','style'],h6:['class','style'],header:[],hr:[],i:['class','style'],img:['style','src','alt','title','width','height','id','_src','_url','loadingclass','class','data-latex'],ins:['datetime'],li:['class','style'],mark:[],nav:[],ol:['class','style'],p:['class','style'],pre:['class','style'],s:[],section:['class','style'],small:[],span:['class','style'],sub:['class','style'],sup:['class','style'],strong:['class','style'],table:['align','valign','class'],tbody:['align','valign','class'],td:['width','rowspan','colspan','align','valign','class'],tfoot:['align','valign','class'],th:['width','rowspan','colspan','align','valign','class'],thead:['align','valign','class'],tr:['rowspan','align','valign','class'],tt:[],u:[],ul:['class','style'],video:['autoplay','controls','loop','preload','src','height','width','class','style'],source:['src','type'],embed:['type','class','pluginspage','src','width','height','align','style','wmode','play','autoplay','loop','menu','allowscriptaccess','allowfullscreen','controls','preload'],iframe:['src','class','height','width','max-width','max-height','align','frameborder','allowfullscreen']}};function getUEBasePath(docUrl,confUrl){return getBasePath(docUrl||self.document.URL||self.location.href,confUrl||getConfigFilePath())};function getConfigFilePath(){var configPath=document.getElementsByTagName("script");return configPath[configPath.length-1].src};function getBasePath(docUrl,confUrl){var basePath=confUrl;if(/^(\/|\\\\)/.test(confUrl)){basePath=/^.+?\w(\/|\\\\)/.exec(docUrl)[0]+confUrl.replace(/^(\/|\\\\)/,"")}else if(!/^[a-z]+:/i.test(confUrl)){docUrl=docUrl.split("#")[0].split("?")[0].replace(/[^\\\/]+$/,"");basePath=docUrl+""+confUrl};return optimizationPath(basePath)};function optimizationPath(path){var protocol=/^[a-z]+:\/\//.exec(path)[0],tmp=null,res=[];path=path.replace(protocol,"").split("?")[0].split("#")[0];path=path.replace(/\\/g,"/").split(/\//);path[path.length-1]="";while(path.length){if((tmp=path.shift())===".."){res.pop()}else if(tmp!=="."){res.push(tmp)}};return protocol+res.join("/")};window.UE={getUEBasePath:getUEBasePath}})();
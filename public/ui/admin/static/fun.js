LJS._getjs(LCMS['url']['static']+'plugin/base64.min.js','','1');LJS._getjs(LCMS['url']['static']+'plugin/sortable.min.js','','1');LJS._getjs(LCMS['url']['static']+'Lrz4/lrz.bundle.js','','1');var LCMSTIPS=LJS._getQuery('lcmstips');if(LCMSTIPS){layui.notice.info(LCMSTIPS)};if($('#NAVTOP .__more').length>0){$('#NAVTOP .__mored dd').on('click',function(){window.location.href=$(this).attr('data-url')})};if(!!navigator.userAgent.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/)){$('html,body').css({'max-width':window.screen.width})};LJS._getjs(LCMS['url']['own_path']+'admin/tpl/static/fun.js?ver='+(LCMS.app?LCMS.app.ver:''));LJS._getjs('form.js',$('.layui-form'));LJS._getjs('select.js',$('.lcms-form-select'));LJS._getjs('selectN.js',$('.lcms-form-selectN'));LJS._getjs('tags.js',$('.lcms-form-tags'));LJS._getjs('color.js',$('.lcms-form-colorpicker'));LJS._getjs('slider.js',$('.lcms-form-slider'));LJS._getjs('date.js',$('.lcms-form-date'));LJS._getjs('upload.js',$('.lcms-form-upload-img'));LJS._getjs('file.js',$('.lcms-form-upload-file'));LJS._getjs('editor.js',$('.lcms-form-editor'));LJS._getjs('table.js',$('.lcms-form-table'));LJS._getjs('tree.js',$('.lcms-form-table-tree'));LJS._getjs('radio.js',$('.lcms-form-radio-tab'));LJS._getjs('spec.js',$('.lcms-form-spec'));
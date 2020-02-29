var deflimit = $('.lcms-form-upload-gallery').attr('data-editor') == '1' ? 8 : 12;
var iseditor = $('.lcms-form-upload-gallery').attr('data-editor') == '1' ? true : false;
var gallerystart = function () {
    if ($('.lcms-form-upload-gallery ._gallery ._folder').length > 0) {
        $('.lcms-form-upload-gallery ._gallery ._folder').hover(function () {
            $(this).children('._tips').show()
        }, function () {
            $(this).children('._tips').hide()
        });
        if ($(window).width() < 540) {
            $('.lcms-form-upload-gallery ._gallery ._folder').on('click', function () {
                gallerylist($(this).attr('data-dir'))
            })
        } else {
            $('.lcms-form-upload-gallery ._gallery ._folder').on('dblclick', function () {
                gallerylist($(this).attr('data-dir'))
            })
        };
    };
    if ($('.lcms-form-upload-gallery ._gallery ._img').length > 0) {
        $('.lcms-form-upload-gallery ._gallery ._img').hover(function () {
            $(this).children('._name').hide();
            $(this).children('._tips').show();
            $(this).children('._del').show()
        }, function () {
            $(this).children('._name').show();
            $(this).children('._tips').hide();
            $(this).children('._del').hide()
        })
    };
    if ($('.lcms-form-upload-gallery ._gallery ._del').length > 0) {
        $('.lcms-form-upload-gallery ._gallery ._del').on('click', function () {
            var that = $(this);
            event.stopPropagation();
            layer.confirm('确定从服务器上删除此图片吗？（不可恢复）', {
                title: '提示',
            }, function (index) {
                LJS._get(LCMS['url']['admin'] + '?n=upload&c=index&a=delimg&dir=' + that.attr('data-src'), function (res) {
                    if (res.code == '1') {
                        LJS._tips('删除成功！');
                        gallerylist(that.attr('data-dir'))
                    } else {
                        LJS._tips('删除失败！', 0)
                    }
                }, 'json');
                layer.close(index)
            })
        })
    }
};
var galleryselect1 = function () {
    if ($('.lcms-form-upload-gallery ._gallery ._img').length > 0) {
        $('.lcms-form-upload-gallery ._gallery ._img').on('click', function () {
            $('.lcms-form-upload-gallery ._gallery ._img').removeClass('_active');
            $(this).toggleClass('_active')
        })
    }
};
var galleryselect2 = function () {
    if ($('.lcms-form-upload-gallery ._gallery ._img').length > 0) {
        $('.lcms-form-upload-gallery ._gallery ._img').on('click', function () {
            $(this).toggleClass('_active')
        })
    }
};
var showdir = function (arr) {
    var tpl = $('.tpl-folder').html();
    var box = $('.lcms-form-upload-gallery ._gallery');
    box.html("");
    for (var i = 0; i < arr.length; i++) {
        box.append(LJS._tpl(tpl, {
            'name': arr[i],
            'dir': arr[i]
        }))
    };
    gallerystart()
};
var showfile = function (arr, dir) {
    var tpl = $('.tpl-file').html();
    var box = $('.lcms-form-upload-gallery ._gallery');
    box.html("");
    for (var i = 0; i < arr.length; i++) {
        arr[i]['dir'] = dir;
        box.append(LJS._tpl(tpl, arr[i]))
    };
    gallerystart()
};
var gallerydir = function () {
    var loading = LJS._loadstart();
    LJS._get('?n=upload&c=gallery&a=dirlist', function (res) {
        LJS._loadend(loading);
        if (res.dir && res.dir.length > 0) {
            $('.lcms-form-upload-gallery ._topbar ._bak').hide();
            $('.lcms-form-upload-gallery ._pos').html('upload/images/');
            layui.laypage.render({
                elem: 'lcms-form-upload-gallery-pager',
                groups: 3,
                count: res.total,
                limit: deflimit,
                layout: ['prev', 'page', 'next'],
                theme: '#1E9FFF',
                jump: function (obj) {
                    var ed = obj.curr * deflimit;
                    var narr = res.dir.slice(ed - deflimit, ed);
                    showdir(narr)
                }
            })
        } else {
            LJS._tips('没有文件！', 0)
        }
    }, 'json')
};
var gallerylist = function (dir) {
    var loading = LJS._loadstart();
    LJS._get('?n=upload&c=gallery&a=filelist&dir=' + dir, function (res) {
        LJS._loadend(loading);
        if (res.file && res.file.length > 0) {
            $('.lcms-form-upload-gallery ._topbar ._bak').show();
            $('.lcms-form-upload-gallery ._pos').html('upload/images/' + dir + '/');
            layui.laypage.render({
                elem: 'lcms-form-upload-gallery-pager',
                groups: 3,
                count: res.total,
                limit: deflimit,
                layout: ['prev', 'page', 'next'],
                theme: '#1E9FFF',
                jump: function (obj) {
                    var ed = obj.curr * deflimit;
                    var narr = res.file.slice(ed - deflimit, ed);
                    showfile(narr, dir);
                    if ($('.lcms-form-upload-gallery').attr('data-many') == '1') {
                        galleryselect2()
                    } else {
                        galleryselect1()
                    }
                }
            })
        } else {
            if ($('.lcms-form-upload-gallery ._gallery ._img').length > 0) {
                gallerydir()
            } else {
                LJS._tips('没有文件！', 0)
            }
        }
    }, 'json')
};
var addimg = function (list) {
    var many = $('.lcms-form-upload-gallery').attr('data-many');
    var id = $('.lcms-form-upload-gallery').attr('data-id');
    var that = $('#' + id, window.parent.document);
    var tpl = '<div class="_li"><a href="{src}" target="_blank"><img class="layui-upload-img" src="{src}" data-src="{src}"/></a><div class="_icon"><div class="_del"><i class="layui-icon layui-icon-delete" onclick="delone($(this));"></i></div></div></div>';
    for (var i = 0; i < list.length; i++) {
        if (list[i]) {
            if (many == '1') {
                that.append(LJS._tpl(tpl, {
                    'src': list[i]
                }))
            } else {
                that.html(LJS._tpl(tpl, {
                    'src': list[i]
                }))
            }
        }
    };
    var newlist = [];
    that.find('img').each(function () {
        newlist.push($(this).attr('data-src'))
    });
    that.parent('.layui-input-block').siblings('input').val(newlist.join('|'))
};
if ($('.lcms-form-upload-gallery ._topbar ._bak').length > 0) {
    $('.lcms-form-upload-gallery ._topbar ._bak').on('click', function () {
        gallerydir()
    })
};
if ($('.lcms-form-upload-gallery ._topbar ._ok').length > 0) {
    $('.lcms-form-upload-gallery ._topbar ._ok').on('click', function () {
        var list = [];
        $('.lcms-form-upload-gallery ._gallery ._active').each(function (index) {
            list.push($(this).attr('data-src'))
        });
        if (list.length > 0) {
            addimg(list);
            LJS._closeframe()
        } else {
            LJS._tips('请选择图片！', 0)
        }
    })
};
gallerydir();
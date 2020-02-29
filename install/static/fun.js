var $ = jQuery = layui.$,
    api = 'ajax.php?action=';
var _loadstart = function () {
    var index = layer.load(2, {
        shade: [0.3, '#fff']
    });
    return index
};
var _loadend = function (index) {
    layer.close(index)
};
var _tips = function (msg, type) {
    setTimeout(function () {
        if (type === 0) {
            layer.msg(msg, {
                icon: 2
            });
        } else {
            layer.msg(msg, {
                icon: 1
            });
        };
    }, 100);
};
var change = function (setup) {
    $('.setup').hide();
    $('.setup' + setup).show();
};
var http = {
    get: function (url, callback, type) {
        url = url.indexOf("?") != -1 ? url + '&' + Math.random() : url + '?' + Math.random();
        $.ajax({
            url: url,
            dataType: type ? type : 'html',
            cache: false,
            timeout: 15000,
            success: function (res) {
                (callback && typeof (callback) === "function") && callback(res)
            },
            error: function () {
                _tips('数据加载失败！', 0);
            },
            complete: function () {
                layer.closeAll();
            }
        })
    },
    post: function (url, data, callback, type) {
        var loading = _loadstart();
        $.ajax({
            url: url,
            data: data,
            type: 'POST',
            dataType: type ? type : 'json',
            cache: false,
            timeout: 15000,
            success: function (res) {
                (callback && typeof (callback) === "function") && callback(res)
            },
            error: function () {
                _tips('数据加载失败！', 0)
            },
            complete: function () {
                layer.closeAll();
                _loadend(loading);
            }
        })
    }
};
http.get(api + 'readme', function (res) {
    if (res.code == 1) {
        $('.setup1 ._content').html(res.data);
    } else if (res.code == 404) {
        $('.layui-col-xs12').html(res.msg);
    };
}, 'json');
$('.go-readme').on('click', function () {
    change(1);
});
$('.go-dirs').on('click', function () {
    change(2);
    $('.setup2 ._dirs').html('');
    http.get(api + 'dirs', function (res) {
        if (res.code == 1) {
            $('.setup2 ._server').html('<tr><td>' + res.data.server.os + '</td><td>' + res.data.server.sys + '</td><td>' + res.data.server.php + '</td></tr>');
            for (var i = 0; i < res.data.dirs.length; i++) {
                if (res.data.dirs[i].power == 1) {
                    var power = ' <span style="color:green"><i class="layui-icon layui-icon-ok"></i>权限检测通过</span>';
                } else {
                    var power = ' <span style="color:red"><i class="layui-icon layui-icon-close"></i>权限检测失败，请设置该文件755权限</span>';
                };
                $('.setup2 ._dirs').append('<tr><td>' + res.data.dirs[i].name + '</td><td>' + res.data.dirs[i].desc + power + '</td></tr>');
            }
        }
    }, 'json');
});
$('.go-mysql').on('click', function () {
    change(3);
});
$('.go-admin').on('click', function () {
    layui.form.on('submit(formdb)', function (data) {
        http.post(api + 'db', data.field, function (res) {
            if (res.code == 1) {
                console.log(res);
                change(4);
            } else {
                _tips(res.msg, 0);
            };
        });
        return false;
    });
});
$('.go-save').on('click', function () {
    layui.form.on('submit(formadmin)', function (data) {
        http.post(api + 'admin', data.field, function (res) {
            if (res.code == 1) {
                console.log(res);
                change(5);
            } else {
                _tips(res.msg, 0);
            };
        });
        return false;
    });
});
if ($('.iframe-admin-edit').length > 0) {
    layui.form.verify({
        name: function (value, item) {
            if (!new RegExp("^[a-zA-Z0-9_\u4e00-\u9fa5\\s·]+$").test(value)) {
                return '用户名不能有特殊字符';
            }
            if ($('input[name="LC[id]"]').val() == '') {
                var verifydata = ''
                $.ajaxSettings.async = false;
                $.get(LCMS['url']['own_form'] + 'iframe&action=admin-check-name&name=' + value, function (res) {
                    verifydata = res;
                }, 'json');
                $.ajaxSettings.async = true;
                if (verifydata.code != '1') {
                    return verifydata.msg;
                }
            }
        },
        pass: function (value, item) {
            var pass = $('input[name="LC[pass]"]').val();
            if (pass != value) {
                return '两次输入的密码不相同'
            }
        },
    });
};
if ($('.iframe-normal-edit').length > 0) {
    layui.form.verify({
        name: function (value, item) {
            if (!new RegExp("^[a-zA-Z0-9_\u4e00-\u9fa5\\s·]+$").test(value)) {
                return '用户名不能有特殊字符';
            }
        },
        pass: function (value, item) {
            var pass = $('input[name="LC[pass]"]').val();
            if (pass != value) {
                return '两次输入的密码不相同'
            }
        },
    });
};
UE.Editor.prototype._bkGetActionUrl = UE.Editor.prototype.getActionUrl;
UE.Editor.prototype.getActionUrl = function(action) {
    if (action == 'uploadimage' || action == 'uploadscrawl') {
        return LCMS.url.admin + 'index.php?n=upload&c=index&a=local&type=image'
    } else if (action == 'uploadvideo' || action == 'uploadfile') {
        return LCMS.url.admin + 'index.php?n=upload&c=index&a=local&type=file'
    } else if (action == 'uploadcatcher') {
        return LCMS.url.admin + 'index.php?n=upload&c=index&a=editor'
    } else {
        return this._bkGetActionUrl.call(this, action)
    }
};
window.UEDITOR_CONFIG['imageUploadService'] = function(context, editor) {
    return {
        setUploadData: function(file) {
            return file
        },
        setFormData: function(object, data, headers) {
            return data
        },
        setUploaderOptions: function(uploader) {
            return uploader
        },
        getResponseSuccess: function(res) {
            return res.code == 1
        },
        imageSrcField: 'data.src'
    }
};
window.UEDITOR_CONFIG['videoUploadService'] = function(context, editor) {
    return {
        setUploadData: function(file) {
            return file
        },
        setFormData: function(object, data, headers) {
            return data
        },
        setUploaderOptions: function(uploader) {
            return uploader
        },
        getResponseSuccess: function(res) {
            return res.code == 1
        },
        videoSrcField: 'data.src'
    }
};
window.UEDITOR_CONFIG['scrawlUploadService'] = function(context, editor) {
    return scrawlUploadService = {
        uploadScraw: function(file, base64, success, fail) {
            var formData = new FormData();
            formData.append('file', file, file.name);
            $.ajax({
                url: editor.getActionUrl(editor.getOpt('scrawlActionName')),
                type: 'POST',
                contentType: false,
                processData: false,
                data: formData
            }).done(function(res) {
                var res = JSON.parse(res);
                res.responseSuccess = res.code == 200;
                res.scrawlSrcField = 'url';
                success.call(context, res)
            }).fail(function(err) {
                fail.call(context, err)
            })
        }
    }
}
window.UEDITOR_CONFIG['fileUploadService'] = function(context, editor) {
    return {
        setUploadData: function(file) {
            return file
        },
        setFormData: function(object, data, headers) {
            return data
        },
        setUploaderOptions: function(uploader) {
            return uploader
        },
        getResponseSuccess: function(res) {
            return res.code == 1
        },
        fileSrcField: 'data.src'
    }
};
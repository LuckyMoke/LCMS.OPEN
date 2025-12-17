<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-08-01 18:52:16
 * @LastEditTime: 2025-12-08 11:19:41
 * @Description: UI组件
 * @Copyright 2020 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
class LAY
{
    public static function start($para)
    {
        $para = is_array($para) ? $para : [];
        return array_merge($para, [
            "title" => $para['title'] ?: "&nbsp;",
            "cname" => $para['cname'] ? " {$para['cname']}" : "",
            "disabled"  => $para['disabled'] ? " disabled" : "",
            "disclass"  => $para['disabled'] ? " layui-disabled" : "",
            "verifybox" => $para['verify'] ? " required lay-verify='{$para['verify']}' lay-reqtext='{$para['title']}为必填项'" : "",
            "tipsbox" => $para['tips'] ? " lcms-form-tips' data-tips='" . strip_tags($para['tips']) : "",
        ]);
    }
    public static function form($list = "", $return = false)
    {
        $list = is_array($list) ? $list : ($list ? json_decode($list, true) : []);
        $html = "";
        foreach ($list as $para) {
            if ($para) {
                $type = $para['layui'];
                $html .= self::$type($para, true);
            }
        }
        if ($return) {
            return $html;
        }
        echo $html;
    }
    public static function title($para, $return = false)
    {
        $para          = self::start($para);
        $para['title'] = $para['title'] ? $para['title'] : "标题栏";
        $html          = "<h3 class='lcms-form-title{$para['cname']}'>{$para['title']}</h3>";
        if ($return) {
            return $html;
        }
        echo $html;
    }
    public static function des($para, $return = false)
    {
        $para          = self::start($para);
        $para['title'] = $para['title'] ? $para['title'] : "标题栏";
        $html          = "<p class='lcms-form-des{$para['cname']}'>{$para['title']}</p>";
        if ($return) {
            return $html;
        }
        echo $html;
    }
    public static function html($para, $return = false)
    {
        $para           = self::start($para);
        $para['nodrop'] = $para['nodrop'] ? " lcms-form-html-nodrop" : "";
        if ($para['copy']) {
            $para['copy']     = " lcms-form-copy";
            $para['copytext'] = $para['copytext'] ?: $para['value'];
        }
        $html = "
            <div class='layui-form-item{$para['cname']}'>
                <label class='layui-form-label' title='{$para['title']}'>{$para['title']}</label>
                <div class='layui-input-block'>
                    <div class='lcms-form-html{$para['nodrop']}{$para['copy']}' data-copytext='{$para['copytext']}'>{$para['value']}</div>
                </div>
            </div>";
        if ($return) {
            return $html;
        }
        echo $html;
    }
    public static function input($para, $return = false)
    {
        $para = self::start($para);
        if ($para['value']) {
            $para['value'] = htmlentities($para['value']);
        }
        if ($para['type'] == "hidden") {
            echo "<input type='hidden' name='{$para['name']}' value='{$para['value']}' />";
        } else {
            $para['maxlength']   = $para['maxlength'] ? " maxlength='{$para['maxlength']}'" : "";
            $para['placeholder'] = $para['placeholder'] ? $para['placeholder'] : "请输入{$para['title']}";
            $para['disabled']    = $para['disabled'] ? " readonly" : "";
            $para['type']        = $para['type'] ? $para['type'] : "text";
            if ($para['type'] == "number") {
                $para['affix']     = "number";
                $para['step']      = $para['step'] > 0 ? " step='{$para['step']}'" : "";
                $para['min']       = isset($para['min']) ? " min='{$para['min']}'" : "";
                $para['max']       = $para['max'] > 0 ? " max='{$para['max']}'" : "";
                $para['precision'] = $para['precision'] ? " lay-precision='{$para['precision']}'" : "";
            }
            $para['affix']        = $para['affix'] ? " lay-affix='{$para['affix']}'" : " lay-affix='" . ($para['type'] == "password" ? "eye" : "clear") . "'";
            $para['filter']       = $para['filter'] ? " lay-filter='{$para['filter']}'" : "";
            $para['autocomplete'] = $para['type'] == "password" ? "new-password" : "new-input";
            $html                 = "
            <div class='layui-form-item{$para['cname']}'>
                <label class='layui-form-label' title='{$para['title']}'>{$para['title']}</label>
                <div class='layui-input-block'>
                    <input type='{$para['type']}' name='{$para['name']}' class='lcms-form-input layui-input{$para['tipsbox']}' autocomplete='{$para['autocomplete']}' placeholder='{$para['placeholder']}' value='{$para['value']}'{$para['step']}{$para['min']}{$para['max']}{$para['affix']}{$para['filter']}{$para['maxlength']}{$para['precision']}{$para['verifybox']}{$para['disabled']}/>
                </div>
            </div>";
            if ($return) {
                return $html;
            }
            echo $html;
        }
    }
    public static function input_sort($para, $return = false)
    {
        $para = self::start($para);
        if ($para['value']) {
            $para['value'] = htmlentities($para['value']);
        }
        if ($para['type'] == "hidden") {
            echo "<input type='hidden' name='{$para['name']}' value='{$para['value']}' />";
        } else {
            $para['maxlength']   = $para['maxlength'] ? " maxlength='{$para['maxlength']}'" : "";
            $para['placeholder'] = $para['placeholder'] ? $para['placeholder'] : "请输入{$para['title']}";
            $para['disabled']    = $para['disabled'] ? " readonly" : "";
            $para['type']        = $para['type'] ? $para['type'] : "text";
            if ($para['type'] == "number") {
                $para['affix']     = "number";
                $para['step']      = $para['step'] > 0 ? " step='{$para['step']}'" : "";
                $para['min']       = isset($para['min']) ? " min='{$para['min']}'" : "";
                $para['max']       = $para['max'] > 0 ? " max='{$para['max']}'" : "";
                $para['precision'] = $para['precision'] ? " lay-precision='{$para['precision']}'" : "";
            }
            $para['affix']        = $para['affix'] ? " lay-affix='{$para['affix']}'" : " lay-affix='" . ($para['type'] == "password" ? "eye" : "clear") . "'";
            $para['filter']       = $para['filter'] ? " lay-filter='{$para['filter']}'" : "";
            $para['autocomplete'] = $para['type'] == "password" ? "new-password" : "new-input";
            $para['tips']         = $para['tips'] ? "<div class='layui-form-mid layui-word-aux'>{$para['tips']}</div>" : "";
            $html                 = "
            <div class='layui-form-item{$para['cname']}'>
                <label class='layui-form-label' title='{$para['title']}'>{$para['title']}</label>
                <div class='layui-input-block'>
                    <div class='layui-input-inline'>
                        <input type='{$para['type']}' name='{$para['name']}' class='lcms-form-input layui-input{$para['tipsbox']}' autocomplete='{$para['autocomplete']}' placeholder='{$para['placeholder']}' value='{$para['value']}'{$para['step']}{$para['min']}{$para['max']}{$para['affix']}{$para['filter']}{$para['maxlength']}{$para['precision']}{$para['verifybox']}{$para['disabled']}/>
                    </div>
                    {$para['tips']}
                </div>
            </div>";
            if ($return) {
                return $html;
            }
            echo $html;
        }
    }
    public static function textarea($para, $return = false)
    {
        $para = self::start($para);
        if ($para['value']) {
            $para['value'] = htmlentities($para['value']);
        }
        $para['maxlength']   = $para['maxlength'] ? " maxlength='{$para['maxlength']}'" : "";
        $para['placeholder'] = $para['placeholder'] ? $para['placeholder'] : "请输入{$para['title']}";
        $html                = "
        <div class='layui-form-item{$para['cname']}' pane>
                <label class='layui-form-label' title='{$para['title']}'>{$para['title']}</label>
                <div class='layui-input-block lcms-form-textarea'>
                    <textarea name='{$para['name']}' placeholder='{$para['placeholder']}' class='layui-textarea{$para['disclass']}{$para['tipsbox']}'{$para['maxlength']}{$para['verifybox']}{$para['disabled']}>{$para['value']}</textarea>
                </div>
            </div>";
        if ($return) {
            return $html;
        }
        echo $html;
    }
    public static function select($para, $return = false)
    {
        global $_L;
        $para            = self::start($para);
        $para['many']    = $para['many'] ? "" : "xm-select-radio";
        $para['linkage'] = $para['linkage'] === false ? "" : "linkage='1'";
        $default         = $para['default'] ? "<option value=''>{$para['default']}</option>" : "";
        $option          = "";
        foreach ($para['option'] as $key => $val) {
            $selected = $val['value'] == $para['value'] ? " selected" : "";
            $disabled = $val['disabled'] ? " disabled='disabled'" : "";
            $option .= "<option value='{$val['value']}'{$selected}{$disabled}>{$val['title']}</option>";
        }
        if ($para['url'] && !is_url($para['url'])) {
            $para['url'] = $_L['url']['own_form'] . $para['url'];
        }
        $html = "
            <div class='layui-form-item{$para['cname']}'>
                <label class='layui-form-label' title='{$para['title']}'>{$para['title']}</label>
                <div class='layui-input-block{$para['tipsbox']}'>
                    <select class='lcms-form-select' xm-select-skin='normal' xm-select='{$para['id']}' xm-select-search='{$para['url']}' xm-select-max='{$para['max']}' name='{$para['name']}' xm-select-val='{$para['value']}' {$para['many']} {$para['linkage']}{$para['verifybox']}{$para['disabled']}>
                        {$default}{$option}
                    </select>
                </div>
            </div>";
        if ($return) {
            return $html;
        }
        echo $html;
    }
    public static function selectN($para, $return = false)
    {
        global $_L;
        $para = self::start($para);
        if ($para['url'] && !is_url($para['url'])) {
            $para['url'] = $_L['url']['own_form'] . $para['url'];
        }
        $html = "
            <div class='layui-form-item{$para['cname']}'>
                <label class='layui-form-label' title='{$para['title']}'>{$para['title']}</label>
                <div class='lcms-form-selectN{$para['tipsbox']}' data-name='{$para['name']}' data-val='{$para['value']}' data-url='{$para['url']}' data-default='{$para['default']}' data-verify='{$para['verify']}' data-reqtext='{$para['title']}为必填项'></div>
            </div>";
        if ($return) {
            return $html;
        }
        echo $html;
    }
    public static function tags($para, $return = false)
    {
        $para = self::start($para);
        if ($para['value']) {
            $para['value'] = htmlentities($para['value']);
        }
        $para['placeholder'] = $para['placeholder'] ? $para['placeholder'] : "回车添加，拖动排序";
        $html                = "
            <div class='layui-form-item{$para['cname']}' pane>
                <label class='layui-form-label' title='{$para['title']}'>{$para['title']}</label>
                <div class='layui-input-block lcms-form-tags'>
                    <input type='hidden' name='{$para['name']}' value='{$para['value']}'{$para['verifybox']}/>
                    <div class='lcms-form-tags-box'></div>
                    <textarea class='hide{$para['tipsbox']}' placeholder='{$para['placeholder']}'/></textarea>
                    <div class='hide layui-btn-group lcms-form-tags-button'>
                        <a class='layui-btn layui-btn-primary layui-btn-sm _first{$para['tipsbox']}'><i class='layui-icon layui-icon-add-1'></i>添加标签</a>
                        <a class='layui-btn layui-btn-warm layui-btn-sm _more'>批量添加</a>
                        <a class='layui-btn layui-btn-danger layui-btn-sm _delall'>清空所有</a>
                    </div>
                </div>
            </div>";
        if ($return) {
            return $html;
        }
        echo $html;
    }
    public static function color($para, $return = false)
    {
        $para                = self::start($para);
        $para['format']      = $para['format'] ?: "rgb";
        $para['placeholder'] = $para['placeholder'] ? $para['placeholder'] : "请选择颜色";
        $para['tips']        = $para['tips'] ? "<div class='lcms-word-aux'>{$para['tips']}</div>" : "";
        $html                = "
            <div class='layui-form-item{$para['cname']}'>
                <label class='layui-form-label' title='{$para['title']}'>{$para['title']}</label>
                <div class='layui-input-block lcms-form-colorpicker' data-value='{$para['value']}' data-format='{$para['format']}'>
                    <div class='layui-input-inline'>
                        <input type='text' name='{$para['name']}' value='{$para['value']}' class='layui-input _input{$para['tipsbox']}' autocomplete='new-inpuut' placeholder='{$para['placeholder']}'{$para['verifybox']} />
                        <div class=\"layui-input-suffix layui-input-split _color\"></div>
                    </div>
                    {$para['tips']}
                </div>
            </div>";
        if ($return) {
            return $html;
        }
        echo $html;
    }
    public static function slider($para, $return = false)
    {
        $para         = self::start($para);
        $para['min']  = $para['min'] ? $para['min'] : 0;
        $para['max']  = $para['max'] ? $para['max'] : 100;
        $para['tips'] = $para['tips'] ? "<div class='layui-form-mid layui-word-aux'>{$para['tips']}</div>" : "";
        $html         = "
            <div class='layui-form-item{$para['cname']}'>
                <label class='layui-form-label' title='{$para['title']}'>{$para['title']}</label>
                <div class='layui-input-block lcms-form-slider' data-value='{$para['value']}' data-min='{$para['min']}' data-max='{$para['max']}' data-step='{$para['step']}' data-settips='{$para['settips']}'>
                    <input type='hidden' name='{$para['name']}' value='{$para['value']}'/>
                    <div class='layui-input-inline'>
                        <div class='_slider'></div>
                    </div>
                    {$para['tips']}
                </div>
            </div>";
        if ($return) {
            return $html;
        }
        echo $html;
    }
    public static function date($para, $return = false)
    {
        $para          = self::start($para);
        $para['type']  = $para['type'] ? $para['type'] : "datetime";
        $para['range'] = $para['range'] ? ($para['range'] === true ? "--" : $para['range']) : false;
        $para['tips']  = $para['tips'] ? "<div class='layui-form-mid layui-word-aux'>{$para['tips']}</div>" : "";
        $html          = "
            <div class='layui-form-item{$para['cname']}'>
                <label class='layui-form-label' title='{$para['title']}'>{$para['title']}</label>
                <div class='layui-input-block lcms-form-date' data-value='{$para['value']}' data-type='{$para['type']}' data-range='{$para['range']}' data-min='{$para['min']}' data-max='{$para['max']}'>
                    <div class='layui-input-inline'>
                        <input type='text' name='{$para['name']}' class='layui-input{$para['disclass']}{$para['tipsbox']}' placeholder='点击设置{$para['title']}' autocomplete='new-inpuut' value='{$para['value']}'{$para['verifybox']}{$para['disabled']}/>
                    </div>
                    {$para['tips']}
                </div>
            </div>";
        if ($return) {
            return $html;
        }
        echo $html;
    }
    public static function on($para, $return = false)
    {
        global $_L;
        $para            = self::start($para);
        $para['text']    = $para['text'] ? $para['text'] : "开|关";
        $para['checked'] = $para['value'] ? " checked" : "";
        $para['value']   = $para['value'] ? $para['value'] : 1;
        if ($para['url']) {
            if (!is_url($para['url'])) {
                $para['url'] = $_L['url']['own_form'] . $para['url'];
            }
            $para['url'] = " data-url='{$para['url']}'";
        }
        if (isset($para['timeout'])) {
            $para['timeout'] = " data-timeout='{$para['timeout']}'";
        }
        $html = "
            <div class='layui-form-item{$para['cname']}' pane>
                <label class='layui-form-label' title='{$para['title']}'>{$para['title']}</label>
                <div class='layui-input-block lcms-form-switch{$para['tipsbox']}'>
                    <input type='checkbox' name='{$para['name']}' value='{$para['value']}' lay-skin='switch' lay-filter='lcms-form-switch' title='{$para['text']}'{$para['url']}{$para['timeout']}{$para['disabled']}{$para['checked']}>
                </div>
            </div>";
        if ($return) {
            return $html;
        }
        echo $html;
    }
    public static function radio($para, $return = false)
    {
        $para  = self::start($para);
        $radio = "";
        foreach ($para['radio'] as $key => $val) {
            $disabled = $val['disabled'] ? ' disabled' : '';
            $checked  = $val['value'] == $para['value'] ? ' checked' : '';
            $tab      = $val['tab'] ? " class='lcms-form-radio-tab' lay-filter='lcms-form-radio-tab' data-tab='{$val['tab']}'" : "";
            $radio .= "<input type='radio' name='{$para['name']}' value='{$val['value']}' title='{$val['title']}'{$tab}{$disabled}{$checked}>";
        }
        $para['tips'] = $para['tips'] ? "<div class='lcms-word-aux'>{$para['tips']}</div>" : "";
        $html         = "
            <div class='layui-form-item{$para['cname']}' pane>
                <label class='layui-form-label' title='{$para['title']}'>{$para['title']}</label>
                <div class='layui-input-block lcms-form-radio'>
                    {$radio}
                    {$para['tips']}
                </div>
            </div>";
        if ($return) {
            return $html;
        }
        echo $html;
    }
    public static function checkbox($para, $return = false)
    {
        $para         = self::start($para);
        $para['skin'] = $para['skin'] ?: "primary";
        $checkbox     = "";
        foreach ($para['checkbox'] as $key => $val) {
            if ($val['name']) {
                $checkbox .= "<input type='checkbox' name='{$val['name']}' value='1' title='{$val['title']}'" . ($val['disabled'] ? ' disabled' : '') . ($val['value'] == '1' ? ' checked' : '') . " lay-skin='{$para['skin']}' />";
            } else {
                $checkbox .= "<input type='checkbox' name='{$para['name']}[{$val['value']}]' value='1' title='{$val['title']}'" . ($val['disabled'] ? ' disabled' : '') . ($para['value'][$val['value']] ? ' checked' : '') . " lay-skin='{$para['skin']}' />";
            }
        }
        $para['tips'] = $para['tips'] ? "<div class='lcms-word-aux'>{$para['tips']}</div>" : "";
        $html         = "
            <div class='layui-form-item{$para['cname']}' pane>
                <label class='layui-form-label' title='{$para['title']}'>{$para['title']}</label>
                <div class='layui-input-block lcms-form-checkbox'>
                    {$checkbox}
                    {$para['tips']}
                </div>
            </div>";
        if ($return) {
            return $html;
        }
        echo $html;
    }
    public static function upload($para, $return = false)
    {
        $para              = self::start($para);
        $para['local']     = $para['local'] ? true : false;
        $para['many']      = $para['many'] ? true : false;
        $para['maxwidth']  = $para['width'] ?: $para['maxwidth'];
        $para['maxheight'] = $para['height'] ?: $para['maxheight'];
        if ($para['local'] || $para['gallery'] === false) {
            $para['gallery'] = "";
        } else {
            $para['gallery'] = "<a class='layui-btn layui-btn-warm layui-btn-sm' @click='openGallery'>图库</a><a class='layui-btn layui-btn-primary layui-btn-sm' @click='openPaste'>粘贴</a>";
        }
        $para['tips'] = $para['tips'] ? "<div class='lcms-word-aux'>{$para['tips']}</div>" : "";
        $html         = "
            <div class='layui-form-item{$para['cname']}' pane>
                <label class='layui-form-label' title='{$para['title']}'>{$para['title']}</label>
                <div class='layui-input-block lcms-form-upload-img' data-many='{$para['many']}' data-local='{$para['local']}' data-accept='{$para['accept']}' data-width='{$para['width']}' data-height='{$para['height']}' data-maxwidth='{$para['maxwidth']}' data-maxheight='{$para['maxheight']}' data-value='{$para['value']}'>
                    <input type='hidden' name='{$para['name']}' :value='value'{$para['verifybox']}/>
                    <div class='layui-upload-list lcms-form-upload-img-list' x-ref='imglist'>
                        <template x-for='(item,index) in imgList' :key='item.original'>
                            <div class='_li'><a :href='item.src' target='_blank'><img class='layui-upload-img' :src='item.isload?item.src:``' :data-lazy='item.src' :class='item.isload?``:`lazyload`' @load='onImgload(index)' /></a><div class='_icon'><div class='_del' @click='onDel(index)'><i class='layui-icon layui-icon-close'></i></div></div></div>
                        </template>
                    </div>
                    <div class='layui-btn-group lcms-form-upload-btn' x-cloak>
                        <a class='layui-btn layui-btn-sm'><i class='layui-icon layui-icon-upload-drag'></i>上传<i class='layui-icon layui-icon-loading-1 layui-anim layui-anim-rotate layui-anim-loop' x-show='loading'></i>
                            <template x-if='!loading'>
                                <input type='file' :multiple='config.many' :accept='config.accept' @change='chooseImg' />
                            </template>
                        </a>
                        {$para['gallery']}
                    </div>
                    {$para['tips']}
                </div>
            </div>";
        if ($return) {
            return $html;
        }
        echo $html;
    }
    public static function file($para, $return = false)
    {
        $para          = self::start($para);
        $para['local'] = $para['local'] ? true : false;
        $para['mime']  = $para['mime'] ? $para['mime'] : "file";
        $para['many']  = $para['many'] ? true : false;
        if ($para['select'] === false) {
            $para['select'] = "";
        } else {
            $para['select'] = "<a class='layui-btn layui-btn-warm layui-btn-sm' @click='openFilebox'>文库</a><a class='layui-btn layui-btn-primary layui-btn-sm' @click='openLink'>外链</a>";
        }
        $para['tips'] = $para['tips'] ? "<div class='lcms-word-aux'>{$para['tips']}</div>" : "";
        $html         = "
            <div class='layui-form-item{$para['cname']}' pane>
                <label class='layui-form-label' title='{$para['title']}'>{$para['title']}</label>
                <div class='layui-input-block lcms-form-upload-file' data-many='{$para['many']}' data-local='{$para['local']}' data-mime='{$para['mime']}' data-accept='{$para['accept']}' data-value='{$para['value']}'>
                    <input type='hidden' name='{$para['name']}' :value='value'{$para['verifybox']} />
                    <div class='lcms-form-upload-file-list' x-ref='filelist'>
                        <template x-for='(item,index) in fileList' :key='item.original'>
                            <div class='_li'><a :href='item.src' target='_blank' :title='item.original' x-text='item.original'></a><div class='_del' @click='onDel(index)'><i class='layui-icon layui-icon-close'></i></div></div>
                        </template>
                    </div>
                    <div class='layui-btn-group lcms-form-upload-file-btn' x-cloak>
                        <a class='layui-btn layui-btn-sm'><i class='layui-icon layui-icon-upload-drag'></i>上传<i class='layui-icon layui-icon-loading-1 layui-anim layui-anim-rotate layui-anim-loop' x-show='loading'></i>
                            <template x-if='!loading'>
                                <input type='file' :multiple='config.many' :accept='config.accept' @change='chooseFile' />
                            </template>
                        </a>
                        {$para['select']}
                    </div>
                    {$para['tips']}
                </div>
            </div>";
        if ($return) {
            return $html;
        }
        echo $html;
    }
    public static function editor($para, $return = false)
    {
        $para = self::start($para);
        if ($para['value'] && !in_string($para['value'], "<")) {
            if (is_base64($para['value'])) {
                $para['value'] = base64_decode($para['value']);
            }
        }
        if ($para['simple']) {
            if (is_array($para['simple'])) {
                $para['simple'] = " data-simple='" . base64_encode(json_encode($para['simple'])) . "'";
            } else {
                $para['simple'] = " data-simple='1'";
            }
        }
        $para['tips']  = $para['tips'] ? " - {$para['tips']}" : "";
        $para['title'] = $para['title'] == "&nbsp;" ? "" : "<label class='layui-form-label' title='{$para['title']}'>{$para['title']}{$para['tips']}</label>";
        $html          = "
            <div class='layui-form-item layui-form-text{$para['cname']}'>
                {$para['title']}
                <div class='layui-input-block lcms-form-editor' data-name='{$para['name']}'{$para['simple']}>
                    <script name='{$para['name']}' type='text/plain'>{$para['value']}</script>
                </div>
            </div>";
        if ($return) {
            return $html;
        }
        echo $html;
    }
    public static function icon($para, $return = false)
    {
        $para         = self::start($para);
        $para['tips'] = $para['tips'] ? "<div class='layui-form-mid layui-word-aux'>{$para['tips']}</div>" : "";
        $html         = "
            <div class='layui-form-item{$para['cname']}'>
                <label class='layui-form-label' title='{$para['title']}'>{$para['title']}</label>
                <div class='layui-input-block lcms-form-icon'>
                    <div class='layui-input-inline'>
                        <input type='text' name='{$para['name']}' class='layui-input{$para['disclass']}{$para['tipsbox']}' autocomplete='new-inpuut' placeholder='请选择图标' value='{$para['value']}'{$para['verifybox']}{$para['disabled']}/>
                        <div class=\"layui-input-suffix layui-input-split _change\">
                            <i class=\"layui-icon layui-icon-search\"></i>
                        </div>
                    </div>
                    {$para['tips']}
                </div>
            </div>";
        if ($return) {
            return $html;
        }
        echo $html;
    }
    public static function spec($para, $return = false)
    {
        $para = self::start($para);
        if ($para['value']) {
            $para['value'] = " data-value='" . base64_encode(json_encode($para['value'])) . "'";
        }
        if ($para['fields']) {
            $para['fields'] = " data-fields='" . base64_encode(json_encode($para['fields'])) . "'";
        }
        if ($para['max']) {
            $para['max'] = " data-max='{$para['max']}'";
        }
        if ($para['maxattr']) {
            $para['maxattr'] = " data-maxattr='{$para['maxattr']}'";
        }
        $para['tips']  = $para['tips'] ? " - {$para['tips']}" : "";
        $para['title'] = $para['title'] == "&nbsp;" ? "" : "<label class='layui-form-label' title='{$para['title']}'>{$para['title']}{$para['tips']}</label>";
        $html          = "
            <div class='layui-form-item layui-form-text{$para['cname']}'>
                {$para['title']}
                <div class='layui-input-block lcms-form-spec' data-name='{$para['name']}'{$para['value']}{$para['fields']}{$para['max']}{$para['maxattr']}>
                    <div class='lcms-form-spec-box'>
                        <div class='lcms-form-spec-title'>
                            <table class='layui-table' lay-size='sm' x-show='specData.spec.length>0' x-cloak>
                                <tbody x-ref='speclist'>
                                    <template x-for='(tr, tridx) in specData.spec' :key='tr.title+tridx'>
                                        <tr>
                                            <td align='center' width='40'>
                                                <i class='layui-icon layui-icon-slider lcms-form-spec-trhandle'></i>
                                            </td>
                                            <td class='lcms-form-spec-truncate' align='center'>
                                                <input type='hidden' :name='name+`[spec][`+tridx+`][title]`' :value='tr.title' />
                                                <text x-text='tr.title' @click='specChange(tridx)' pointer></text>
                                            </td>
                                            <td class='lcms-form-spec-td'>
                                                <template x-for='(td, tdidx) in tr.list' :key='td.title+tdidx'>
                                                    <span @click.stop='specChange2(tridx,tdidx)'><input type='hidden' :name='name+`[spec][`+tridx+`][list][`+tdidx+`][title]`' :value='td.title' /><input type='hidden' :name='name+`[spec][`+tridx+`][list][`+tdidx+`][id]`' :value='tridx+`_`+tdidx' /><em x-text='td.title'></em><i @click.stop='specDel2(tridx,tdidx)'>&times;</i></span>
                                                </template>
                                                <a @click='specAdd2(tridx)'>+ 新增</a>
                                            </td>
                                            <td class='lcms-form-spec-del'>
                                                <a @click='specDel(tridx)'>删除</a>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                            <div class='layui-btn-group'>
                                <a class='layui-btn layui-btn-sm' @click='specAdd'><i class='layui-icon layui-icon-add-1'></i>新增</a>
                                <a class='layui-btn layui-btn-sm layui-btn-danger' @click='specClear'>清空</a>
                            </div>
                        </div>
                        <div class='lcms-form-spec-table'>
                            <table class='layui-table' lay-size='sm' x-show='tables.length>0' x-cloak>
                                <thead>
                                    <tr>
                                        <template x-for='(tr, tridx) in specData.spec' :key='tr.title+tridx'>
                                            <th x-text='tr.title'></th>
                                        </template>
                                        <template x-for='(field, fieidx) in fields' :key='field.title+fieidx'>
                                            <th>
                                                <span x-text='field.title'></span>
                                                <i class='layui-icon layui-icon-form' title='批量填写' x-show='field.type!=`image`' @click='allInput(field)'></i>
                                            </th>
                                        </template>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for='(tr, tridx) in tables'>
                                        <tr>
                                            <template x-for='(td, tdidx) in tr'>
                                                <td class='lcms-form-spec-truncate' align='center' :rowspan='td.rowspan' x-show='td.rowspan>0'>
                                                    <text x-text='td.value'></text>
                                                </td>
                                            </template>
                                            <template x-for='(field, fieidx) in fields'>
                                                <td :width='field.width||``' x-html='getInput(field,tridx)'></td>
                                            </template>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>";
        if ($return) {
            return $html;
        }
        echo $html;
    }
    public static function para($para, $return = false)
    {
        $para = self::start($para);
        if ($para['value']) {
            $para['value'] = " data-value='" . base64_encode(json_encode($para['value'])) . "'";
        }
        if ($para['config']) {
            $para['config'] = " data-config='" . base64_encode(json_encode($para['config'])) . "'";
        }
        if ($para['display'] == "block") {
            $para['display'] = " layui-form-text";
        } else {
            $para['margin'] = ' style="margin-left:10px;"';
        }
        $para['tips']  = $para['tips'] ? " - {$para['tips']}" : "";
        $para['title'] = $para['title'] == "&nbsp;" ? "" : "<label class='layui-form-label' title='{$para['title']}'>{$para['title']}{$para['tips']}</label>";
        $html          = "
            <div class='layui-form-item {$para['display']}{$para['cname']}'>
                {$para['title']}
                <div class='layui-input-block lcms-form-para' data-name='{$para['name']}'{$para['value']}{$para['config']}>
                    <div class='lcms-form-para-box'>
                        <div class=\"lcms-form-para-table\">
                            <table class='layui-table' lay-size='sm'  x-show=\"config.cols\">
                                <thead>
                                    <tr>
                                        <td width=\"30\" align=\"center\">排序</td>
                                        <template x-for=\"col in config.cols\">
                                            <td :width=\"col.width||''\" :align=\"col.align||'left'\" x-text=\"col.title\"></td>
                                        </template>
                                        <td width=\"60\" align=\"center\">操作</td>
                                    </tr>
                                </thead>
                                <tbody x-ref=\"paralist\">
                                    <template x-for=\"(item, tridx) in form\">
                                        <tr>
                                            <td align=\"center\">
                                                <i class=\"layui-icon layui-icon-slider lcms-form-para-handle\"></i>
                                            </td>
                                            <template x-for=\"col in config.cols\">
                                                <td>
                                                    <div :data-type=\"col.type\">
                                                        <input :name=\"name+'['+tridx+']['+col.name+']'\" :type=\"col.input_type\" :lay-verify=\"col.verify||''\" class=\"layui-input\" :placeholder=\"col.placeholder||'点击输入'\" :checked=\"col.type=='checkbox'?(item[col.name]>0?true:false):false\" x-model=\"item[col.name]\" @click=\"onInputClick(col,tridx)\" @change=\"onInput(col,tridx)\" />
                                                        <a class=\"lcms-form-para-choose\" x-show=\"col.type=='choose'\" @click=\"onChoose(col,tridx)\">
                                                            <span x-show=\"col.onpreview?true:false\" x-html=\"col.type=='choose'&&previews[tridx]&&previews[tridx][col.name]?previews[tridx][col.name]:''\"></span>
                                                            选择
                                                        </a>
                                                        <a class=\"lcms-form-para-image\" x-show=\"col.type=='image'\">
                                                            <img :src=\"col.type=='image'&&previews[tridx]&&previews[tridx][col.name]?previews[tridx][col.name]:'/public/static/images/icons/upload.svg'\" @click=\"openGallery(col,tridx)\" />
                                                        </a>
                                                    </div>
                                                </td>
                                            </template>
                                            <td align=\"center\">
                                                <a class=\"layui-btn comsite-para-del\" @click=\"onDel(tridx)\">删除</a>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                        <div class=\"lcms-form-para-btngroup\">
                            <a class=\"layui-btn layui-btn-sm layui-btn-normal\"{$para['margin']} @click=\"onAdd\" x-text=\"config.btn\"></a>
                            <a class=\"layui-btn layui-btn-sm layui-btn-warm\" x-show=\"config.batch?true:false\" @click=\"onBatch\">批量添加</a>
                            <a class=\"layui-btn layui-btn-sm layui-btn-danger\" @click=\"onDelAll\">删除所有</a>
                            <a class=\"layui-btn layui-btn-sm layui-btn-primary\" x-show=\"iscache?true:false\" @click=\"useCache\">使用上次数据</a>
                        </div>
                    </div>
                </div>
            </div>";
        if ($return) {
            return $html;
        }
        echo $html;
    }
    public static function btn($para = [], $return = false)
    {
        $para = is_array($para) ? $para : [];
        $para = array_merge($para, [
            "title" => $para['title'] ?: "立即保存",
            "cname" => $para['cname'] ? " {$para['cname']}" : "",
            "fluid" => $para['fluid'] ? true : false,
        ]);
        if ($para['fluid']) {
            $html = "
                <div class='layui-form-item{$para['cname']}'>
                    <button class='layui-btn layui-btn-fluid' lay-submit lay-filter='lcmsformsubmit'>{$para['title']}</button>
                </div>";
        } else {
            $display = $para['fixed'] ? " style='height:0;min-height:0;margin:0;'" : "";
            $fixed   = $para['fixed'] ? " style='position:fixed;bottom:20px;width:96%;left:2%;margin-left:0;z-index:99'" : "";
            $html    = "
                <div class='layui-form-item{$para['cname']}'{$display}>
                    <div class='layui-input-block'{$display}>
                        <button class='layui-btn' lay-submit lay-filter='lcmsformsubmit'{$fixed}>{$para['title']}</button>
                    </div>
                </div>";
        }
        if ($return) {
            return $html;
        }
        echo $html;
    }
}

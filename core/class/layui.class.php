<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-08-01 18:52:16
 * @LastEditTime: 2021-04-27 14:26:20
 * @Description: UI组件
 * @Copyright 2020 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
class LAY
{
    public static function start($para)
    {
        $para              = is_array($para) ? $para : array();
        $para['title']     = $para['title'] ?: "小标题";
        $para['cname']     = $para['cname'] ? " {$para['cname']}" : "";
        $para['disabled']  = $para['disabled'] ? " disabled" : "";
        $para['disclass']  = $para['disabled'] ? " layui-disabled" : "";
        $para['verifybox'] = $para['verify'] ? " required lay-verify='{$para['verify']}'" : "";
        $para['tipsbox']   = $para['tips'] ? " lcms-form-tips' data-tips='" . strip_tags($para['tips']) : "";
        return $para;
    }
    public static function form($list)
    {
        foreach ($list as $para) {
            $para = self::start($para);
            $type = $para['layui'];
            self::$type($para);
        }
    }
    public static function title($para)
    {
        $para          = self::start($para);
        $para['title'] = $para['title'] ? $para['title'] : "标题栏";
        echo "<h3 class='lcms-form-title{$para['cname']}'>{$para['title']}</h3>";
    }
    public static function des($para)
    {
        $para          = self::start($para);
        $para['title'] = $para['title'] ? $para['title'] : "标题栏";
        echo "<p class='lcms-form-des{$para['cname']}'>{$para['title']}</p>";
    }
    public static function html($para)
    {
        $para = self::start($para);
        $html = "
            <div class='layui-form-item{$para['cname']}' pane>
                <label class='layui-form-label' title='{$para['title']}'>{$para['title']}</label>
                <div class='layui-input-block'>
                    <div class='lcms-form-html'>{$para['value']}</div>
                </div>
            </div>";
        echo $html;
    }
    public static function input($para)
    {
        $para                = self::start($para);
        $para['maxlength']   = $para['maxlength'] ? " maxlength='{$para['maxlength']}'" : "";
        $para['placeholder'] = $para['placeholder'] ? $para['placeholder'] : "请输入{$para['title']}";
        $para['type']        = $para['type'] ? $para['type'] : "text";
        $html                = "
            <div class='layui-form-item{$para['cname']}'>
                <label class='layui-form-label' title='{$para['title']}'>{$para['title']}</label>
                <div class='layui-input-block'>
                    <input type='{$para['type']}' name='{$para['name']}' class='lcms-form-input layui-input{$para['disclass']}{$para['tipsbox']}' autocomplete='off' placeholder='{$para['placeholder']}' value='{$para['value']}'{$para['maxlength']}{$para['verifybox']}{$para['disabled']}/>
                </div>
            </div>";
        echo $html;
    }
    public static function input_sort($para)
    {
        $para                = self::start($para);
        $para['maxlength']   = $para['maxlength'] ? " maxlength='{$para['maxlength']}'" : "";
        $para['placeholder'] = $para['placeholder'] ? $para['placeholder'] : "请输入{$para['title']}";
        $para['type']        = $para['type'] ? $para['type'] : "text";
        $html                = "
            <div class='layui-form-item{$para['cname']}'>
                <label class='layui-form-label' title='{$para['title']}'>{$para['title']}</label>
                <div class='layui-input-block'>
                    <div class='layui-input-inline'>
                        <input type='{$para['type']}' name='{$para['name']}' class='lcms-form-input layui-input{$para['disclass']}{$para['tipsbox']}' autocomplete='off' placeholder='{$para['placeholder']}' value='{$para['value']}'{$para['maxlength']}{$para['verifybox']}{$para['disabled']}/>
                    </div>
                    <div class='layui-form-mid layui-word-aux'>{$para['tips']}</div>
                </div>
            </div>";
        echo $html;
    }
    public static function textarea($para)
    {
        $para                = self::start($para);
        $para['maxlength']   = $para['maxlength'] ? " maxlength='{$para['maxlength']}'" : "";
        $para['placeholder'] = $para['placeholder'] ? $para['placeholder'] : "请输入{$para['title']}";
        $html                = "
        <div class='layui-form-item{$para['cname']}' pane>
                <label class='layui-form-label' title='{$para['title']}'>{$para['title']}</label>
                <div class='layui-input-block'>
                    <textarea name='{$para['name']}' placeholder='{$para['placeholder']}' class='lcms-form-textarea layui-textarea{$para['disclass']}{$para['tipsbox']}' style='border-top:none;border-right:none;border-bottom:none;border-left:1px solid #e6e6e6'{$para['maxlength']}{$para['verifybox']}{$para['disabled']}>{$para['value']}</textarea>
                </div>
            </div>";
        echo $html;
    }
    public static function select($para)
    {
        global $_L;
        $para         = self::start($para);
        $para['many'] = $para['many'] ? "" : "xm-select-radio";
        $default      = $para['default'] ? "<option value=''>{$para['default']}</option>" : "";
        $option       = "";
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
                    <select class='lcms-form-select' xm-select-skin='default' xm-select-search='{$para['url']}' xm-select-max='{$para['max']}' name='{$para['name']}' xm-select-val='{$para['value']}' {$para['many']}{$para['verifybox']}{$para['disabled']}>
                        {$default}{$option}
                    </select>
                </div>
            </div>";
        echo $html;
    }
    public static function selectN($para)
    {
        global $_L;
        $para = self::start($para);
        if ($para['url'] && !is_url($para['url'])) {
            $para['url'] = $_L['url']['own_form'] . $para['url'];
        }
        $html = "
            <div class='layui-form-item{$para['cname']}'>
                <label class='layui-form-label' title='{$para['title']}'>{$para['title']}</label>
                <div class='lcms-form-selectN{$para['tipsbox']}' data-name='{$para['name']}' data-val='{$para['value']}' data-url='{$para['url']}' data-default='{$para['default']}' data-verify='{$para['verify']}'></div>
            </div>";
        echo $html;
    }
    public static function tags($para)
    {
        $para                = self::start($para);
        $para['placeholder'] = $para['placeholder'] ? $para['placeholder'] : "回车添加，拖动排序";
        $html                = "
            <div class='layui-form-item{$para['cname']}' pane>
                <label class='layui-form-label' title='{$para['title']}'>{$para['title']}</label>
                <div class='layui-input-block lcms-form-tags'>
                    <div class='lcms-form-tags-box'></div>
                    <input type='hidden' name='{$para['name']}' data-value='{$para['value']}'/>
                    <textarea class='hide{$para['tipsbox']}' placeholder='{$para['placeholder']}'/></textarea>
                </div>
            </div>";
        echo $html;
    }
    public static function color($para)
    {
        $para                = self::start($para);
        $para['placeholder'] = $para['placeholder'] ? $para['placeholder'] : "请选择颜色";
        $html                = "
            <div class='layui-form-item{$para['cname']}'>
                <label class='layui-form-label' title='{$para['title']}'>{$para['title']}</label>
                <div class='layui-input-block lcms-form-colorpicker' data-value='{$para['value']}'>
                    <div class='layui-input-inline'>
                        <input type='text' name='{$para['name']}' value='{$para['value']}' class='layui-input _input{$para['tipsbox']}' autocomplete='off' placeholder='{$para['placeholder']}' />
                    </div>
                    <div class='_color'></div>
                    <div class='lcms-word-aux'>{$para['tips']}</div>
                </div>
            </div>";
        echo $html;
    }
    public static function slider($para)
    {
        $para        = self::start($para);
        $para['min'] = $para['min'] ? $para['min'] : 0;
        $para['max'] = $para['max'] ? $para['max'] : 100;
        $html        = "
            <div class='layui-form-item{$para['cname']}'>
                <label class='layui-form-label' title='{$para['title']}'>{$para['title']}</label>
                <div class='layui-input-block lcms-form-slider' data-value='{$para['value']}' data-min='{$para['min']}' data-max='{$para['max']}' data-step='{$para['step']}' data-settips='{$para['settips']}'>
                    <input type='hidden' name='{$para['name']}' value='{$para['value']}'/>
                    <div class='layui-input-inline'>
                        <div class='_slider'></div>
                    </div>
                    <div class='lcms-word-aux'>{$para['tips']}</div>
                </div>
            </div>";
        echo $html;
    }
    public static function date($para)
    {
        $para          = self::start($para);
        $para['type']  = $para['type'] ? $para['type'] : "datetime";
        $para['range'] = $para['range'] ? ($para['range'] === true ? "--" : $para['range']) : false;
        $html          = "
            <div class='layui-form-item{$para['cname']}'>
                <label class='layui-form-label' title='{$para['title']}'>{$para['title']}</label>
                <div class='layui-input-block lcms-form-date' data-value='{$para['value']}' data-type='{$para['type']}' data-range='{$para['range']}' data-min='{$para['min']}' data-max='{$para['max']}'>
                    <div class='layui-input-inline'>
                        <input type='text' name='{$para['name']}' class='layui-input{$para['disclass']}{$para['tipsbox']}' placeholder='点击设置{$para['title']}' autocomplete='off' value='{$para['value']}'{$para['verify']}{$para['disabled']}/>
                    </div>
                    <div class='layui-form-mid layui-word-aux'>{$para['tips']}</div>
                </div>
            </div>";
        echo $html;
    }
    public static function on($para)
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
        $html = "
            <div class='layui-form-item{$para['cname']}' pane>
                <label class='layui-form-label' title='{$para['title']}'>{$para['title']}</label>
                <div class='layui-input-block lcms-form-switch{$para['tipsbox']}'>
                    <input type='checkbox' name='{$para['name']}' value='{$para['value']}' lay-skin='switch' lay-filter='lcms-form-switch' lay-text='{$para['text']}'{$para['url']}{$para['disabled']}{$para['checked']}>
                </div>
            </div>";
        echo $html;
    }
    public static function radio($para)
    {
        $para  = self::start($para);
        $radio = "";
        foreach ($para['radio'] as $key => $val) {
            $disabled = $val['disabled'] ? ' disabled' : '';
            $checked  = $val['value'] == $para['value'] ? ' checked' : '';
            $tab      = $val['tab'] ? " class='lcms-form-radio-tab' lay-filter='lcms-form-radio-tab' data-tab='{$val['tab']}'" : "";
            $radio .= "<input type='radio' name='{$para['name']}' value='{$val['value']}' title='{$val['title']}'{$tab}{$disabled}{$checked}>";
        }
        $html = "
            <div class='layui-form-item{$para['cname']}' pane>
                <label class='layui-form-label' title='{$para['title']}'>{$para['title']}</label>
                <div class='layui-input-block lcms-form-radio'>
                    {$radio}
                    <div class='lcms-word-aux'>{$para['tips']}</div>
                </div>
            </div>";
        echo $html;
    }
    public static function checkbox($para)
    {
        $para     = self::start($para);
        $checkbox = "";
        foreach ($para['checkbox'] as $key => $val) {
            if ($val['name']) {
                $checkbox .= "<input type='checkbox' name='{$val['name']}' value='1' title='{$val['title']}'" . ($val['disabled'] ? ' disabled' : '') . ($val['value'] == '1' ? ' checked' : '') . " />";
            } else {
                $checkbox .= "<input type='checkbox' name='{$para['name']}[{$val['value']}]' value='1' title='{$val['title']}'" . ($val['disabled'] ? ' disabled' : '') . ($para['value'][$val['value']] ? ' checked' : '') . " />";
            }
        }
        $html = "
            <div class='layui-form-item{$para['cname']}' pane>
                <label class='layui-form-label' title='{$para['title']}'>{$para['title']}</label>
                <div class='layui-input-block lcms-form-checkbox'>
                    {$checkbox}
                    <div class='lcms-word-aux'>{$para['tips']}</div>
                </div>
            </div>";
        echo $html;
    }
    public static function upload($para)
    {
        $para          = self::start($para);
        $para['local'] = $para['local'] ? true : false;
        $para['many']  = $para['many'] ? true : false;
        $html          = "
            <div class='layui-form-item lcms-form-upload-img{$para['cname']}' pane>
                <input type='hidden' name='{$para['name']}' value='{$para['value']}' data-many='{$para['many']}'>
                <label class='layui-form-label' title='{$para['title']}'>{$para['title']}</label>
                <div class='layui-input-block'>
                    <div class='layui-upload-list lcms-form-upload-img-list'></div>
                    <div class='layui-btn-group lcms-form-upload-btn'>
                        <a class='layui-btn layui-btn-normal layui-btn-sm _up' data-many='{$para['many']}' data-local='{$para['local']}' data-accept='{$para['accept']}'>上传<i class='_loading layui-icon layui-icon-loading layui-anim layui-anim-rotate layui-anim-loop'></i></a>
                        <a class='layui-btn layui-btn-warm layui-btn-sm _box' data-many='{$para['many']}'>图库</a>
                        <a class='layui-btn layui-btn-primary layui-btn-sm _other'>链接</a>
                    </div>
                    <div class='lcms-word-aux'>{$para['tips']}</div>
                </div>
            </div>";
        echo $html;
    }
    public static function file($para)
    {
        $para          = self::start($para);
        $para['local'] = $para['local'] ? true : false;
        $para['mime']  = $para['mime'] ? $para['mime'] : "file";
        $html          = "
            <div class='layui-form-item lcms-form-upload-file{$para['cname']}'>
                <label class='layui-form-label' title='{$para['title']}'>{$para['title']}</label>
                <div class='layui-input-block'>
                    <input type='text' name='{$para['name']}' class='layui-input{$para['tipsbox']}' autocomplete='off' placeholder='请选择文件上传' value='{$para['value']}'{$para['verifybox']}{$para['disabled']}/>
                    <div class='layui-btn-group lcms-form-upload-file-btn'>
                        <a class='layui-btn layui-btn-normal layui-btn-xl _up' data-local='{$para['local']}' data-mime='{$para['mime']}' data-accept='{$para['accept']}' onclick='javascript:;'>上传<i class='_loading layui-icon layui-icon-loading layui-anim layui-anim-rotate layui-anim-loop'></i></a>
                    </div>
                    <div class='lcms-word-aux'>{$para['tips']}</div>
                    <div class='clear'></div>
                </div>
            </div>";
        echo $html;
    }
    public static function editor($para)
    {
        $para = self::start($para);
        $html = "
            <div class='layui-form-item layui-form-text{$para['cname']}'>
                <label class='layui-form-label' title='{$para['title']}'>{$para['title']}</label>
                <div class='layui-input-block lcms-form-editor' data-name='{$para['name']}'>
                    <script name='{$para['name']}' type='text/plain'>" . base64_decode($para['value']) . "</script>
                </div>
            </div>";
        echo $html;
    }
    public static function icon($para)
    {
        $para = self::start($para);
        $html = "
            <div class='layui-form-item lcms-form-icon{$para['cname']}'>
                <label class='layui-form-label' title='{$para['title']}'>{$para['title']}</label>
                <div class='layui-input-block'>
                    <div class='layui-input-inline'>
                        <input type='text' name='{$para['name']}' class='layui-input{$para['disclass']}{$para['tipsbox']}' autocomplete='off' placeholder='请选择图标' value='{$para['value']}'{$para['verifybox']}{$para['disabled']}/>
                    </div>
                    <div class='_change'>选择</div>
                    <div class='layui-form-mid layui-word-aux'>{$para['tips']}</div>
                </div>
            </div>";
        echo $html;
    }
    public static function spec($para)
    {
        $para = self::start($para);
        if ($para['value']) {
            $para['value'] = json_encode_ex($para['value']);
            $para['value'] = base64_encode($para['value']);
        }
        if ($para['specs']) {
            $para['specs'] = json_encode_ex($para['specs']);
            $para['specs'] = base64_encode($para['specs']);
        }
        $html = "
            <div class='layui-form-item layui-form-text lcms-form-spec{$para['cname']}' data-name='{$para['name']}' data-value='{$para['value']}' data-specs='{$para['specs']}'>
                <label class='layui-form-label' title='{$para['title']}'>{$para['title']}<span style='font-size:12px;color:#ff5722;padding-left:10px;'>标签可拖动排序</span></label>
                <div class='layui-input-block'>
                    <div class='lcms-form-spec-box'></div>
                    <a class='layui-btn layui-btn-sm layui-btn-normal lcms-form-spec-btn'>
                        <i class='layui-icon layui-icon-add-1'></i>
                        添加标签
                    </a>
                    <div class='lcms-form-spec-table'></div>
                </div>
            </div>";
        echo $html;
    }
    public static function btn($para = "")
    {
        $para          = is_array($para) ? $para : array();
        $para['title'] = $para['title'] ? $para['title'] : "立即保存";
        $para['fluid'] = $para['fluid'] ? true : false;
        if ($para['fluid']) {
            $html = "
                <div class='layui-form-item{$para['cname']}'>
                    <button class='layui-btn layui-btn-fluid' lay-submit lay-filter='lcmsformsubmit'>{$para['title']}</button>
                </div>";
        } else {
            $display = $para['fixed'] ? " style='height:0;min-height:0;margin:0;'" : "";
            $fixed   = $para['fixed'] ? " style='position:fixed;bottom:20px;width:96%;left:2%;margin-left:0;z-index:2'" : "";
            $html    = "
                <div class='layui-form-item{$para['cname']}'{$display}>
                    <div class='layui-input-block'{$display}>
                        <button class='layui-btn' lay-submit lay-filter='lcmsformsubmit'{$fixed}>{$para['title']}</button>
                    </div>
                </div>";
        }
        echo $html;
    }
}

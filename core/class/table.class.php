<?php
defined('IN_LCMS') or exit('No permission');
class TABLE
{
    public function __construct()
    {
        parent::__construct();
        global $_L;
    }
    public static function html($table = "")
    {
        global $_L;
        foreach ($table['toolbar'] as $key => $val) {
            $btns .= "<button class='layui-btn layui-btn-{$val['color']}' lay-event='{$val['event']}' data-url='{$val['url']}' data-tips='{$val['tips']}' data-text='{$val['text']}'>{$val['title']}</button>";
        }
        if ($table['search']) {
            $search = self::search($table['search']);
        }
        foreach ($table['cols'] as $key => $val) {
            if ($val['toolbar']) {
                $table['cols'][$key]['toolbar'] = self::toolbar($val['toolbar']);
            }
            if ($val['totalRowText'] || $val['totalRow'] == true) {
                $totalRow = true;
            }
        }
        $arr = array(
            "url"            => $table['url'],
            "defaultToolbar" => ['filter', 'print', 'exports'],
            "toolbar"        => $table['toolbar'] ? "<div>" . $btns . "</div><div class='clear'></div>" : ($table['search'] ? "<div>" . "<div class='clear'></div></div>" : ""),
            "totalRow"       => $totalRow ? true : false,
            "page"           => $table['page'] ? $table['page'] : true,
            "limit"          => $table['limit'] ? $table['limit'] : 20,
            "cols"           => $table['cols'],
        );
        $html .= "<div class='lcms-form-table-box' id='{$table['id']}'>{$search}<table class='lcms-form-table' data='" . base64_encode(json_encode_ex($arr)) . "'></table></div>";
        echo $html;
    }
    public static function search($arr)
    {
        global $_L;
        foreach ($arr as $key => $val) {
            switch ($val['type']) {
                case 'select':
                    $options = '';
                    foreach ($val['option'] as $option) {
                        if (count($option['children']) > 0) {
                            $opts = "";
                            foreach ($option['children'] as $opt) {
                                $opts .= '<option value="' . $opt['value'] . '">' . $opt['title'] . '</option>';
                            }
                            $options .= "<optgroup label='{$option['title']}'>{$opts}</optgroup>";
                        } else {
                            $options .= '<option value="' . $option['value'] . '">' . $option['title'] . '</option>';
                        }
                    }
                    $html .= '<div class="layui-input-inline"><select name="LC[' . $val['name'] . ']" lay-verify="" lay-search><option value="">' . $val['title'] . '</option>' . $options . '</select></div>';
                    break;
                case 'date':
                    $html .= '<div class="layui-input-inline lcms-form-table-toolbar-date"><input type="text" name="LC[' . $val['name'] . ']" class="layui-input" autocomplete="off" value="" placeholder="' . $val['title'] . '"/></div>';
                    break;
                default:
                    $html .= '<div class="layui-input-inline"><input type="text" name="LC[' . $val['name'] . ']" placeholder="' . $val['title'] . '" autocomplete="off" class="layui-input"></div>';
                    break;
            }
        }
        return '<form class="lcms-form-table-toolbar-search layui-form">' . $html . '<button class="layui-btn" lay-submit lay-filter="LCMSTABLESEARCH">搜索</button><button class="layui-btn layui-btn-primary LCMSTABLESEARCHRESET" lay-submit lay-filter="LCMSTABLESEARCH">重置</button></form>';
    }
    public static function toolbar($arr)
    {
        global $_L;
        foreach ($arr as $key => $val) {
            $btns .= "<button class='layui-btn layui-btn-xs layui-btn-{$val['color']}' lay-event='{$val['event']}' data-url='{$val['url']}' data-tips='{$val['tips']}' data-text='{$val['text']}'>{$val['title']}</button>";
        }
        return '<div class="lcms-form-table-toolbar-edit">' . $btns . '</div>';
    }
    public static function data($table, $where = "", $order = "", $para = [], $field = null)
    {
        global $_L;
        $page     = $_L['form']['page'];
        $limit    = $_L['form']['limit'];
        $count    = sql_counter([$table, $where, $para]);
        $page_max = ceil($count / $limit);
        if ($page <= $page_max) {
            $min  = ($page - 1) * $limit;
            $data = sql_getall([$table, $where, $order, $para, "", $field, [$min, $limit]]);
        }
        $r = array(
            "count" => $count,
            "data"  => $data,
            "code"  => 0,
            "msg"   => "ok",
        );
        return $r;
    }
    public static function del($table)
    {
        global $_L;
        $form = $_L['form']['LC'];
        if ($form['id']) {
            sql_delete([$table, "id = '{$form[id]}'"]);
        } elseif (is_array($form)) {
            $ids = implode(",", array_column($form, "id"));
            sql_delete([$table, "id IN ({$ids})"]);
        }
        return sql_error() ? false : true;
    }
    public static function tree($tree = "")
    {
        global $_L;
        foreach ($tree['toolbar'] as $key => $val) {
            $btns .= "<button class='layui-btn layui-btn-{$val['color']}' lay-event='{$val['event']}' data-url='{$val['url']}' data-tips='{$val['tips']}' data-text='{$val['text']}'>{$val['title']}</button>";
        }
        $btns .= "<button class='layui-btn layui-btn-primary lcms-form-table-tree-openall'>展开或折叠全部</button>";
        foreach ($tree['cols'] as $key => $val) {
            if ($val['toolbar']) {
                $tree['cols'][$key]['toolbar'] = self::tree_toolbar($val['toolbar']);
            }
        }
        $tree = [
            "url"  => $tree['url'],
            "id"   => $tree['id'] ? $tree['id'] : "",
            "top"  => $tree['top'],
            "cols" => $tree['cols'],
        ];
        $html .= "<div class='lcms-form-table-tree-box'>{$btns}<table class='layui-hidden lcms-form-table-tree' data='" . base64_encode(json_encode_ex($tree)) . "'></table></div>";
        echo $html;
    }
    public static function tree_toolbar($arr)
    {
        global $_L;
        foreach ($arr as $key => $val) {
            $btns .= "<button class='layui-btn layui-btn-xs layui-btn-{$val['color']}' lay-event='{$val['event']}' data-url='{$val['url']}' data-tips='{$val['tips']}' data-text='{$val['text']}'>{$val['title']}</button>";
        }
        return $btns;
    }
    public static function out($arr)
    {
        global $_L;
        echo json_encode_ex($arr);
        die;
    }
}

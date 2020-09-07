<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-08-01 18:52:16
 * @LastEditTime: 2020-09-04 15:04:27
 * @Description: 数据表格组件
 * @Copyright 2020 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
class TABLE
{
    public static $count;
    /**
     * [set 新版获取表格数据]
     * @param  [type] $table [description]
     * @param  string $where [description]
     * @param  string $order [description]
     * @param  [type] $para  [description]
     * @param  [type] $field [description]
     * @return [type]        [description]
     */
    public static function set($table, $where = "", $order = "", $para = [], $field = null)
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
        self::$count = $count;
        return $data;
    }
    /**
     * [html 生成数据表格html]
     * @param  string $table [description]
     * @return [type]        [description]
     */
    public static function html($table = "")
    {
        global $_L;
        $toolbar = self::toolbar($table['toolbar']);
        $laytpl  = $toolbar['laytpl'];
        $toolbar = $toolbar['toolbar'];
        $search  = self::search($table['search']);
        foreach ($table['cols'] as $key => $val) {
            if ($val['toolbar']) {
                $colsbar = self::colsbar($val['toolbar']);
                $laytpl .= $colsbar['laytpl'];
                $table['cols'][$key]['toolbar'] = $colsbar['colsbarid'];
            }
            if ($val['totalRowText'] || $val['totalRow'] == true) {
                $totalRow = true;
            }
        }
        $arr = [
            "url"            => stristr($table['url'], "://") !== false ? $table['url'] : $_L['url']['own_form'] . $table['url'],
            "defaultToolbar" => ['filter', 'print', 'exports'],
            "toolbar"        => $toolbar,
            "totalRow"       => $totalRow ? true : false,
            "page"           => $table['page'] ? $table['page'] : true,
            "limit"          => $table['limit'] ? $table['limit'] : 20,
            "cols"           => $table['cols'],
        ];
        $html = "<div class='lcms-form-table-box' id='{$table['id']}'>{$search}<table class='lcms-form-table' data='" . base64_encode(json_encode_ex($arr)) . "'></table>{$laytpl}</div>";
        echo $html;
    }
    /**
     * [toolbar 获取顶部bar]
     * @param  string $toolbar [description]
     * @return [type]          [description]
     */
    public static function toolbar($toolbar = "")
    {
        global $_L;
        $toolbarid = "TOOLBAR" . randstr(6);
        if ($toolbar) {
            if (is_array($toolbar)) {
                foreach ($toolbar as $key => $val) {
                    $val['url'] = stristr($val['url'], "://") !== false ? $val['url'] : $_L['url']['own_form'] . $val['url'];
                    $laytpl .= "<button class='layui-btn layui-btn-{$val['color']}' lay-event='{$val['event']}' data-url='{$val['url']}' data-tips='{$val['tips']}' data-text='{$val['text']}'>{$val['title']}</button>";
                }
                $laytpl  = "<script type='text/html' id='{$toolbarid}'>{$laytpl}<div class='clear'></div></script>";
                $toolbar = "#{$toolbarid}";
            } else {
                $toolbar = $table['toolbar'];
            }
        } else {
            $laytpl  = "<script type='text/html' id='{$toolbarid}'><div class='clear'></div></script>";
            $toolbar = "#{$toolbarid}";
        }
        return [
            "laytpl"  => $laytpl,
            "toolbar" => $toolbar,
        ];
    }
    /**
     * [search 获取search]
     * @param  [type] $arr [description]
     * @return [type]      [description]
     */
    public static function search($arr)
    {
        global $_L;
        if ($arr) {
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
            return '<form class="lcms-form-table-toolbar-search layui-form"><div class="__form"><i class="__close hide layui-icon layui-icon-close-fill"></i><h3 class="hide">搜索</h3>' . $html . '<button class="layui-btn" lay-submit lay-filter="LCMSTABLESEARCH">搜索</button><button class="layui-btn layui-btn-primary LCMSTABLESEARCHRESET" lay-submit lay-filter="LCMSTABLESEARCH">重置</button></div><div class="__icon LCMSTABLESEARCHICON"><i class="layui-icon layui-icon-search"></i></div></form>';
        }
    }
    /**
     * [colsbar 获取每行bar]
     * @param  [type] $colsbar [description]
     * @param  string $id      [description]
     * @return [type]          [description]
     */
    public static function colsbar($colsbar)
    {
        global $_L;
        if (is_array($colsbar)) {
            $laytpl    = "";
            $colsbarid = "COLSBAR" . randstr(6);
            foreach ($colsbar as $key => $val) {
                $val['url'] = stristr($val['url'], "://") !== false ? $val['url'] : $_L['url']['own_form'] . $val['url'];
                $laytpl .= "<button class='layui-btn layui-btn-xs layui-btn-{$val['color']}' lay-event='{$val['event']}' data-url='{$val['url']}' data-tips='{$val['tips']}' data-text='{$val['text']}'>{$val['title']}</button>";
            }
            $laytpl = "<script type='text/html' id='{$colsbarid}'><div class='layui-btn-group'>{$laytpl}</div></script>";
            return [
                "laytpl"    => $laytpl,
                "colsbarid" => "#{$colsbarid}",
            ];
        } else {
            return [
                "laytpl"    => "",
                "colsbarid" => $colsbar,
            ];
        }
    }
    /**
     * [data 获取表格数据]
     * @param  [type] $table [description]
     * @param  string $where [description]
     * @param  string $order [description]
     * @param  [type] $para  [description]
     * @param  [type] $field [description]
     * @return [type]        [description]
     */
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
        self::$count = $count;
        $result      = [
            "data" => $data,
        ];
        return $result;
    }
    /**
     * [del 删除表格数据]
     * @param  [type] $table [description]
     * @return [type]        [description]
     */
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
    /**
     * [tree 获取树状表格]
     * @param  string $tree [description]
     * @return [type]       [description]
     */
    public static function tree($tree = "")
    {
        global $_L;
        $laytpl = "";
        foreach ($tree['toolbar'] as $key => $val) {
            $val['url'] = stristr($val['url'], "://") !== false ? $val['url'] : $_L['url']['own_form'] . $val['url'];
            $toolbar .= "<button class='layui-btn layui-btn-{$val['color']}' lay-event='{$val['event']}' data-url='{$val['url']}' data-tips='{$val['tips']}' data-text='{$val['text']}'>{$val['title']}</button>";
        }
        $toolbar .= "<button class='layui-btn layui-btn-primary lcms-form-table-tree-openall'>展开或折叠全部</button>";
        foreach ($tree['cols'] as $key => $val) {
            if ($val['toolbar']) {
                $colsbar = self::tree_colsbar($val['toolbar']);
                $laytpl .= $colsbar['laytpl'];
                $tree['cols'][$key]['templet'] = $colsbar['colsbarid'];
                unset($tree['cols'][$key]['toolbar']);
            }
        }
        $tree = [
            "url"  => stristr($tree['url'], "://") !== false ? $tree['url'] : $_L['url']['own_form'] . $tree['url'],
            "id"   => $tree['id'] ? $tree['id'] : "",
            "top"  => $tree['top'],
            "cols" => $tree['cols'],
        ];
        $html .= "<div class='lcms-form-table-tree-box'>{$toolbar}<table class='layui-hidden lcms-form-table-tree' data='" . base64_encode(json_encode_ex($tree)) . "'></table>{$laytpl}</div>";
        echo $html;
    }
    /**
     * [tree_colsbar description]
     * @param  [type] $colsbar   [description]
     * @param  string $colsbarid [description]
     * @return [type]            [description]
     */
    public static function tree_colsbar($colsbar)
    {
        global $_L;
        if (is_array($colsbar)) {
            $laytpl    = "";
            $colsbarid = "TREECOLSBAR" . randstr(6);
            foreach ($colsbar as $key => $val) {
                $val['url'] = stristr($val['url'], "://") !== false ? $val['url'] : $_L['url']['own_form'] . $val['url'];
                $laytpl .= "<button class='layui-btn layui-btn-xs layui-btn-{$val['color']}' lay-event='{$val['event']}' data-url='{$val['url']}' data-tips='{$val['tips']}' data-text='{$val['text']}'>{$val['title']}</button>";
            }
            $laytpl = "<script type='text/html' id='{$colsbarid}'><div class='layui-btn-group'>{$laytpl}</div></script>";
            return [
                "laytpl"    => $laytpl,
                "colsbarid" => "#{$colsbarid}",
            ];
        } else {
            return [
                "laytpl"    => "",
                "colsbarid" => $colsbar,
            ];
        }
    }
    /**
     * [out 输出表格数据]
     * @param  [type] $arr [description]
     * @return [type]      [description]
     */
    public static function out($arr)
    {
        global $_L;
        $arr = $arr['data'] ? $arr['data'] : $arr;
        unset($arr['data']);
        // 处理数据表格中的操作组件
        foreach ($arr as $index => $list) {
            foreach ($list as $key => $val) {
                if (is_array($val)) {
                    switch ($val['type']) {
                        case 'switch':
                            if (stristr($val['url'], "://") === false) {
                                $val['url'] = "{$_L['url']['own_form']}{$val['url']}";
                            }
                            if (stristr($val['url'], "name=") === false) {
                                $val['url'] .= "&name={$key}";
                            }
                            $val['text']       = $val['text'] ?: "启用|关闭";
                            $checked           = $val['value'] > 0 ? "checked" : "";
                            $arr[$index][$key] = "<input type='checkbox' data-url='{$val['url']}&id={$list['id']}' lay-skin='switch' lay-text='{$val['text']}' {$checked}>";
                            break;
                        case 'image':
                            $val['src']        = $val['src'] ? explode("|", $val['src'])[0] : "";
                            $arr[$index][$key] = $val['src'] ? "<img src='{$val['src']}' width='{$val['width']}' height='{$val['height']}' style='max-width:none;{$val['style']}'/>" : "";
                            break;
                    }
                }
            }
        }
        echo json_encode_ex([
            "count" => self::$count,
            "data"  => $arr,
            "code"  => 0,
            "msg"   => self::$count > 0 ? "success" : "无数据",
        ]);
        die;
    }
}

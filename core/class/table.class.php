<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-08-01 18:52:16
 * @LastEditTime: 2023-03-20 15:17:51
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
     * [html 生成数据表格html]lay-search
     * @param  string $table [description]
     * @return [type]        [description]
     */
    public static function html($table = [])
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
        $data = [
            "url"            => is_url($table['url']) ? $table['url'] : $_L['url']['own_form'] . $table['url'],
            "defaultToolbar" => [[
                "title"    => "刷新",
                "layEvent" => "LCMSTABLE_REFRESH",
                "icon"     => "layui-icon-refresh",
            ], "filter", "print", "exports"],
            "toolbar"        => $toolbar,
            "totalRow"       => $totalRow ? true : false,
            "page"           => $table['page'] ? $table['page'] : 1,
            "limit"          => $table['limit'] ? $table['limit'] : 20,
            "cols"           => $table['cols'],
        ];
        if ($search) {
            $data['defaultToolbar'] = array_merge([[
                "title"    => "搜索",
                "layEvent" => "LCMSTABLE_SEARCHOPEN",
                "icon"     => "layui-icon-search",
            ]], $data['defaultToolbar']);
        }
        $html = "<div class='lcms-table-box' id='{$table['id']}'><table class='lcms-table' data='" . htmlspecialchars(json_encode_ex($data)) . "'></table>{$search}{$laytpl}</div>";
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
        $laytpl    = "";
        $toolbarid = "TOOLBAR" . randstr(6);
        if ($toolbar) {
            if (is_array($toolbar)) {
                foreach ($toolbar as $key => $val) {
                    $val['icon'] = $val['icon'] ? "<i class='layui-icon layui-icon-{$val['icon']}'></i>" : $val['title'];
                    $val['url']  = is_url($val['url']) ? $val['url'] : $_L['url']['own_form'] . $val['url'];
                    $laytpl .= "<button class='layui-btn layui-btn-{$val['color']}' lay-event='{$val['event']}' data-url='{$val['url']}' data-timeout='{$val['timeout']}' data-tips='{$val['tips']}' data-text='{$val['text']}' data-area='{$val['area']}' data-title='{$val['title']}'>{$val['icon']}</button>";
                }
                $laytpl  = "<script type='text/html' id='{$toolbarid}'>{$laytpl}<div class='clear'></div></script>";
                $toolbar = "#{$toolbarid}";
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
        $html = "";
        if ($arr) {
            foreach ($arr as $key => $val) {
                switch ($val['type']) {
                    case 'select':
                        $options = '';
                        foreach ($val['option'] as $option) {
                            if (!empty($option['children'])) {
                                $opts = "";
                                foreach ($option['children'] as $opt) {
                                    $opts .= "<option value='{$opt['value']}'" . ($val['value'] == $opt['value'] ? " selected" : "") . ">{$opt['title']}</option>";
                                }
                                $options .= "<optgroup label='{$option['title']}'>{$opts}</optgroup>";
                            } else {
                                $options .= "<option value='{$option['value']}'" . ($val['value'] == $option['value'] ? " selected" : "") . ">{$option['title']}</option>";
                            }
                        }
                        $html .= '<div class="layui-input-inline"><select name="LC[' . $val['name'] . ']" lay-verify><option value="">' . $val['title'] . '</option>' . $options . '</select></div>';
                        break;
                    case 'year':
                    case 'month':
                    case 'date':
                    case 'time':
                    case 'datetime':
                        $html .= '<div class="layui-input-inline lcms-table-toolbar-date"><input type="text" name="LC[' . $val['name'] . ']" class="layui-input" autocomplete="off" value="" placeholder="' . $val['title'] . '" data-type="' . $val['type'] . '" data-range="' . ($val['range'] === false ? "" : true) . '" data-min="' . $val['min'] . '" data-max="' . $val['max'] . '"/></div>';
                        break;
                    default:
                        $html .= '<div class="layui-input-inline"><input type="text" name="LC[' . $val['name'] . ']" placeholder="' . $val['title'] . '" autocomplete="off" class="layui-input"></div>';
                        break;
                }
            }
            return '<script type="text/html" class="lcms-table-toolbar-search-tpl"><form class="lcms-table-toolbar-search"><div class="lcms-table-toolbar-search-box">' . $html . '<button class="layui-btn" lay-submit lay-filter="LCMSTABLE_SEARCH"><i class="layui-icon layui-icon-search"></i></button></div></form></script>';
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
                $val['url']  = is_url($val['url']) ? $val['url'] : $_L['url']['own_form'] . $val['url'];
                $val['icon'] = $val['icon'] ? "<i class='layui-icon layui-icon-{$val['icon']}'></i>" : $val['title'];
                $laytpl .= "<button class='layui-btn layui-btn-xs layui-btn-{$val['color']}' lay-event='{$val['event']}' data-url='{$val['url']}' data-timeout='{$val['timeout']}' data-tips='{$val['tips']}' data-text='{$val['text']}' data-area='{$val['area']}' data-title='{$val['title']}'>{$val['icon']}</button>";
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
            sql_delete([$table, "id = :id", [
                ":id" => $form['id'],
            ]]);
        } elseif (is_array($form)) {
            $ids = implode(",", array_column($form, "id"));
            sql_delete([$table, "id IN ({$ids})"]);
        } else {
            return false;
        }
        return sql_error() ? false : true;
    }
    /**
     * [tree 获取树状表格]
     * @param  string $tree [description]
     * @return [type]       [description]
     */
    public static function tree($tree = [])
    {
        global $_L;
        $laytpl  = "";
        $toolbar = "";
        foreach ($tree['toolbar'] as $key => $val) {
            $val['url']  = is_url($val['url']) ? $val['url'] : $_L['url']['own_form'] . $val['url'];
            $val['icon'] = $val['icon'] ? "<i class='layui-icon layui-icon-{$val['icon']}'></i>" : $val['title'];
            $toolbar .= "<button class='layui-btn layui-btn-{$val['color']}' lay-event='{$val['event']}' data-url='{$val['url']}' data-timeout='{$val['timeout']}' data-tips='{$val['tips']}' data-text='{$val['text']}' data-area='{$val['area']}' data-title='{$val['title']}'>{$val['icon']}</button>";
        }
        $toolbar .= "<button class='layui-btn layui-btn-primary lcms-table-tree-openall'>展开/折叠</button>";
        foreach ($tree['cols'] as $key => $val) {
            if ($val['toolbar']) {
                $colsbar = self::tree_colsbar($val['toolbar']);
                $laytpl .= $colsbar['laytpl'];
                $tree['cols'][$key]['templet'] = $colsbar['colsbarid'];
                unset($tree['cols'][$key]['toolbar']);
            }
        }
        $tree = [
            "url"  => is_url($tree['url']) ? $tree['url'] : $_L['url']['own_form'] . $tree['url'],
            "id"   => $tree['id'] ? $tree['id'] : "",
            "top"  => $tree['top'],
            "show" => $tree['show'],
            "cols" => $tree['cols'],
        ];
        $html = "<div class='lcms-table-tree-box'>{$toolbar}<table class='layui-hidden lcms-table-tree' data='" . htmlspecialchars(json_encode_ex($tree)) . "'></table>{$laytpl}</div>";
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
                $val['url']  = is_url($val['url']) ? $val['url'] : $_L['url']['own_form'] . $val['url'];
                $val['icon'] = $val['icon'] ? "<i class='layui-icon layui-icon-{$val['icon']}'></i>" : $val['title'];
                $laytpl .= "<button class='layui-btn layui-btn-xs layui-btn-{$val['color']}' lay-event='{$val['event']}' data-url='{$val['url']}' data-timeout='{$val['timeout']}' data-tips='{$val['tips']}' data-text='{$val['text']}' data-area='{$val['area']}' data-title='{$val['title']}'>{$val['icon']}</button>";
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
                            if (!is_url($val['url'])) {
                                $val['url'] = "{$_L['url']['own_form']}{$val['url']}";
                            }
                            if (strpos($val['url'], "name=") === false) {
                                $val['url'] .= "&name={$key}";
                            }
                            $val['text']       = $val['text'] ?: "启用|关闭";
                            $checked           = $val['value'] > 0 ? "checked" : "";
                            $arr[$index][$key] = "<input type='checkbox' data-url='{$val['url']}&id={$list['id']}' data-timeout='{$val['timeout']}' lay-skin='switch' lay-filter='LCMSTABLE_SWITCH' lay-text='{$val['text']}' {$checked}>";
                            break;
                        case 'image':
                            $val['src']        = $val['src'] ? explode("|", $val['src'])[0] : "";
                            $val['src']        = in_string($val['src'], "../upload/") ? oss($val['src']) : $val['src'];
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

<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-08-01 18:52:16
 * @LastEditTime: 2024-05-20 10:35:10
 * @Description: 数据表格组件
 * @Copyright 2020 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
class TABLE
{
    public static $count;
    /**
     * @description: 获取表格数据
     * @param string $table
     * @param string $where
     * @param string $order
     * @param array $para
     * @param string $field
     * @return array
     */
    public static function set($table, $where = "", $order = "", $para = [], $field = null)
    {
        global $_L;
        $page  = $_L['form']['page'] ? intval($_L['form']['page']) : 1;
        $limit = intval($_L['form']['limit']);
        $count = sql_counter([$table, $where, $para]);
        if ($limit > 0) {
            $page_max = ceil($count / $limit);
            if ($page <= $page_max) {
                $min  = ($page - 1) * $limit;
                $data = sql_getall([$table, $where, $order, $para, "", $field, [$min, $limit]]);
            }
        } else {
            $data = sql_getall([$table, $where, $order, $para, "", $field]);
        }
        self::$count = $count;
        return $data;
    }
    /**
     * @description: 生成数据表格
     * @param array $table
     * @return string
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
        $html = "<div class='lcms-table-box' id='{$table['id']}'><table class='lcms-table' data-exts='table' data='" . htmlspecialchars(json_encode_ex($data)) . "'></table>{$search}{$laytpl}</div>";
        echo $html;
    }
    /**
     * @description: 获取顶部bar
     * @param string $toolbar
     * @return array
     */
    public static function toolbar($toolbar = "")
    {
        global $_L;
        $laytpl    = "";
        $toolbarid = "TOOLBAR" . randstr(6);
        if ($toolbar) {
            if (is_array($toolbar)) {
                foreach ($toolbar as $key => $val) {
                    $val['icon']   = $val['icon'] ? "<i class='layui-icon layui-icon-{$val['icon']}'></i>" : $val['title'];
                    $val['url']    = is_url($val['url']) ? $val['url'] : $_L['url']['own_form'] . $val['url'];
                    $val['target'] = $val['target'] ?: "_self";
                    $laytpl .= "<button class='layui-btn layui-btn-{$val['color']}' lay-event='{$val['event']}' data-url='{$val['url']}' data-timeout='{$val['timeout']}' data-tips='{$val['tips']}' data-text='{$val['text']}' data-area='{$val['area']}' data-title='{$val['title']}' data-target='{$val['target']}'>{$val['icon']}</button>";
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
     * @description: 获取search
     * @param array $arr
     * @return string
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
                        $html .= '<div class="layui-input-inline layui-input-wrap" title="' . $val['title'] . '"><div class="layui-input-prefix">
                        <i class="layui-icon layui-icon-more-vertical"></i></div><select name="LC[' . $val['name'] . ']" lay-verify><option value="">' . $val['title'] . '</option>' . $options . '</select></div>';
                        break;
                    case 'year':
                    case 'month':
                    case 'date':
                    case 'time':
                    case 'datetime':
                        $html .= '<div class="layui-input-inline layui-input-wrap lcms-table-toolbar-date" title="' . $val['title'] . '"><div class="layui-input-prefix">
                        <i class="layui-icon layui-icon-date"></i></div><input type="text" name="LC[' . $val['name'] . ']" class="layui-input" readonly autocomplete="off" value="" placeholder="' . $val['title'] . '" data-type="' . $val['type'] . '" data-range="' . ($val['range'] === false ? "" : true) . '" data-min="' . $val['min'] . '" data-max="' . $val['max'] . '"/><div class="layui-input-suffix"><i class="layui-icon layui-icon-down"></i></div></div>';
                        break;
                    default:
                        $html .= '<div class="layui-input-inline layui-input-wrap" title="' . $val['title'] . '"><div class="layui-input-prefix">
                        <i class="layui-icon layui-icon-search"></i></div><input type="text" name="LC[' . $val['name'] . ']" placeholder="' . $val['title'] . '" autocomplete="off" class="layui-input"></div>';
                        break;
                }
            }
            return '<script type="text/html" class="lcms-table-toolbar-search-tpl"><form class="lcms-table-toolbar-search"><div class="lcms-table-toolbar-search-box">' . $html . '<button class="layui-btn" lay-submit lay-filter="LCMSTABLE_SEARCH" title="搜索"><i class="layui-icon layui-icon-search"></i></button></div></form></script>';
        }
    }
    /**
     * @description: 获取每行bar
     * @param array|string $colsbar
     * @return array
     */
    public static function colsbar($colsbar)
    {
        global $_L;
        if (is_array($colsbar)) {
            $laytpl    = "";
            $colsbarid = "COLSBAR" . randstr(6);
            foreach ($colsbar as $key => $val) {
                $val['url']    = is_url($val['url']) ? $val['url'] : $_L['url']['own_form'] . $val['url'];
                $val['target'] = $val['target'] ?: "_self";
                $val['icon']   = $val['icon'] ? "<i class='layui-icon layui-icon-{$val['icon']}'></i>" : $val['title'];
                if ($val['if']) {
                    $laytpl .= "{{# if({$val['if']}) { }}";
                }
                $laytpl .= "<button class='layui-btn layui-btn-xs layui-btn-{$val['color']}' lay-event='{$val['event']}' data-url='{$val['url']}' data-timeout='{$val['timeout']}' data-tips='{$val['tips']}' data-text='{$val['text']}' data-area='{$val['area']}' data-title='{$val['title']}' data-target='{$val['target']}'>{$val['icon']}</button>";
                if ($val['if']) {
                    $laytpl .= "{{# } }}";
                }
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
     * @description: 删除表格数据
     * @param string $table
     * @return bool
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
     * @description: 新树形表格
     * @param array $tree
     * @return string
     */
    public static function tree($tree = [])
    {
        global $_L;
        if (is_array($tree['toolbar'])) {
            $tree['toolbar'] = array_merge($tree['toolbar'], [[
                "title" => "展开/折叠",
                "event" => "LCMSTREETABLE_EXPAND",
                "color" => "primary",
            ]]);
        }
        $toolbar = self::toolbar($tree['toolbar']);
        $laytpl  = $toolbar['laytpl'];
        $toolbar = $toolbar['toolbar'];
        foreach ($tree['cols'] as $key => $val) {
            if ($val['toolbar']) {
                $colsbar = self::colsbar($val['toolbar']);
                $laytpl .= $colsbar['laytpl'];
                $tree['cols'][$key]['toolbar'] = $colsbar['colsbarid'];
            }
        }
        $data = [
            "url"            => is_url($tree['url']) ? $tree['url'] : $_L['url']['own_form'] . $tree['url'],
            "defaultToolbar" => [[
                "title"    => "刷新",
                "layEvent" => "LCMSTABLE_REFRESH_TREE",
                "icon"     => "layui-icon-refresh",
            ], "filter", "print", "exports"],
            "toolbar"        => $toolbar,
            "cols"           => $tree['cols'],
            "pid"            => $tree['pid'] ?: "pid",
            "show"           => $tree['show'] ?: "title",
        ];
        $html = "<div class='lcms-table-box' id='{$tree['id']}'><table class='lcms-table lcms-table-tree' data-exts='treeTable' data='" . htmlspecialchars(json_encode_ex($data)) . "'></table>{$laytpl}</div>";
        echo $html;
    }
    /**
     * @description: 输出表格数据
     * @param array $arr
     * @return array
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
                            $arr[$index][$key] = "<input type='checkbox' data-url='{$val['url']}&id={$list['id']}' data-timeout='{$val['timeout']}' lay-skin='switch' lay-filter='LCMSTABLE_SWITCH' title='{$val['text']}' {$checked}>";
                            break;
                        case 'image':
                            $val['src']        = $val['src'] ? explode("|", $val['src'])[0] : "";
                            $val['src']        = in_string($val['src'], "../upload/") ? oss($val['src']) : $val['src'];
                            $val['width']      = $val['width'] ?: "auto";
                            $val['height']     = $val['height'] ?: "100%";
                            $arr[$index][$key] = $val['src'] ? "<img class='lazyload' data-src='{$val['src']}' width='{$val['width']}' height='{$val['height']}' style='display:block;max-width:none;{$val['style']}'/>" : "";
                            break;
                        case 'link':
                            $val['url'] = $val['url'] ?: $val['href'] ?: "";
                            if (!$val['url']) {
                                $val['url'] = "javascript:;";
                            } elseif (in_string($val['url'], "javascript:")) {
                                $val['onclick'] = ' onclick="' . str_replace("javascript:", "", $val['url']) . '"';
                                $val['url']     = "javascript:;";
                            } elseif (!is_url($val['url'])) {
                                $val['url'] = "{$_L['url']['own_form']}{$val['url']}";
                            }
                            $val['cname']      = $val['cname'] ? ' class="' . $val['cname'] . '"' : "";
                            $val['color']      = $val['color'] ? ' style="color:' . $val['color'] . '"' : "";
                            $val['target']     = $val['target'] ? " target={$val['target']}" : "";
                            $val['icon']       = $val['icon'] ?: "edge";
                            $arr[$index][$key] = "<a{$val['cname']} href=\"{$val['url']}\"{$val['target']}{$val['color']}{$val['onclick']}><i class='layui-icon layui-icon-{$val['icon']} layui-font-14'> </i>{$val['title']}</a>";
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
        exit;
    }
}

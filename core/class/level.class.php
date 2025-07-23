<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-10-10 14:20:59
 * @LastEditTime: 2025-07-23 10:45:59
 * @Description:权限计算
 * @Copyright 2020 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
class LEVEL
{
    /**
     * @description: 获取APP信息
     * @param string $name
     * @param string $type
     * @return array
     */
    public static function app($name = "", $type = "", $ckv = false)
    {
        global $_L;
        $name    = $name ?: L_NAME;
        $type    = $type ?: L_TYPE;
        $appinfo = [];
        $path    = PATH_APP . $type . "/" . $name . "/";
        if (is_file("{$path}app.json")) {
            $appinfo = json_decode(file_get_contents("{$path}app.json"), true);
            if ($name === "appstore" && $_L['developer']['appstore'] === 0) {
                unset($appinfo['class']['store']);
            }
            $appinfo['menu'] = $appinfo['class'];
        };
        if (L_MODULE == "admin") {
            if (is_file("{$path}admin/tpl/static/fun.js")) {
                $js = "fun.js";
            }
            $appinfo['info']['js'] = $js ?: "";
        }
        if (!$appinfo['info']['title']) {
            return $appinfo;
        }
        $applevel = $_L['LCMSADMIN']['level'][$type][$name];
        if ($_L['LCMSADMIN']['type'] != "lcms") {
            if (empty($applevel) && !empty($appinfo['class'])) {
                foreach ($appinfo['class'] as $key => $val) {
                    if (!empty($val['level'])) {
                        foreach ($val['level'] as $fun => $power) {
                            $appinfo['power'][$key][$fun] = "no";
                            unset($appinfo['menu'][$key]['level'][$fun]);
                        }
                    }
                }
            } else {
                foreach ($appinfo['menu'] as $cls => $arr) {
                    if (!empty($arr['level'])) {
                        foreach ($arr['level'] as $fun => $val) {
                            if (!$applevel[$cls] || $applevel[$cls][$fun] != "1") {
                                $appinfo['power'][$cls][$fun] = "no";
                                unset($appinfo['menu'][$cls]['level'][$fun]);
                            }
                        }
                    }
                }
            }
        }
        foreach ($appinfo['menu'] as $key => $val) {
            if (!empty($val['level'])) {
                foreach ($val['level'] as $fun => $arr) {
                    if (!$arr['menu'] || $arr['menu'] == "0") {
                        unset($appinfo['menu'][$key]['level'][$fun]);
                    }
                }
            } else {
                unset($appinfo['menu'][$key]);
            }
        }
        $fristclass            = !empty($appinfo['menu']) ? array_key_first($appinfo['menu']) : "";
        $fristfun              = !empty($appinfo['menu'][$fristclass]['level']) ? array_key_first($appinfo['menu'][$fristclass]['level']) : "";
        $appinfo['url']['all'] = "{$_L['url']['admin']}index.php?t={$type}&n={$name}&c={$fristclass}&a={$fristfun}";
        foreach ($appinfo['menu'] as $cname => $menu) {
            $fristfun               = !empty($appinfo['menu'][$cname]['level']) ? array_key_first($appinfo['menu'][$cname]['level']) : "";
            $appinfo['url'][$cname] = "{$_L['url']['admin']}index.php?t={$type}&n={$name}&c={$cname}&a={$fristfun}";
            foreach ($menu['level'] as $aname => $menu2) {
                $appinfo['url'][$cname . ':' . $aname] = "{$_L['url']['admin']}index.php?t={$type}&n={$name}&c={$cname}&a={$aname}";
            }
        }
        if (
            $ckv &&
            $name == "comsite" &&
            $appinfo['info']['ver'] < "3.3.5"
        ) {
            LCMS::X(403, "&#x8BF7;&#x5347;&#x7EA7;&#x6B64;&#x5E94;&#x7528;&#x7248;&#x672C;&#xFF01;");
        }
        return $appinfo;
    }
    /**
     * @description: 获取所有APP信息
     * @param {*}
     * @return array
     */
    public static function applist($type = "all", $base = false, $count = 0)
    {
        global $_L;
        if ($type === "all" || $type === "sys") {
            $sys = traversal_one(PATH_APP . "sys");
            $sys && sort($sys['dir']);
            foreach ($sys['dir'] as $dir) {
                $info = self::app($dir, "sys");
                if ($info) {
                    $applist['sys'][$dir] = $info;
                }
            }
        }
        if ($type === "all" || $type === "open") {
            $open = traversal_one(PATH_APP . "open");
            $open && sort($open['dir']);
            foreach ($open['dir'] as $dir) {
                $info = self::app($dir, "open");
                if ($info && $info['info']['title']) {
                    $applist['open'][$dir] = $info;
                }
            }
        }
        if ($type === "open" && $base) {
            $config = LCMS::config([
                "name" => "menu",
                "type" => "sys",
                "cate" => "admin",
            ]);
            $index = 0;
            $cache = [];
            foreach ($applist['open'] as $name => $app) {
                if ($app['menu']) {
                    $icon = "{$_L['url']['static']}images/appicon.gif?ver={$_L['config']['ver']}";
                    if (is_file(PATH_APP . "open/{$name}/icon.gif")) {
                        $icon = "{$_L['url']['app']}open/{$name}/icon.gif?ver={$app['info']['ver']}";
                    }
                    $cache[$name] = [
                        "title"       => $app['info']['title'],
                        "ver"         => $app['info']['ver'],
                        "description" => $app['info']['description'],
                        "icon"        => $icon,
                        "uninstall"   => $app['info']['uninstall'] === false ? false : true,
                        "url"         => $app['url']['all'],
                    ];
                    $index++;
                }
            }
            if ($config['open'] && !$config['open'][0]) {
                $cache = array_intersect_key(array_merge($config['open'], $cache), $cache);
            }
            if ($count > 0) {
                $cache = array_slice($cache, 0, $count);
            }
            $applist['open'] = $cache;
        }
        return $type === "all" ? $applist : $applist[$type];
    }
}

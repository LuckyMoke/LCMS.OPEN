<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-10-10 14:20:59
 * @LastEditTime: 2026-01-27 14:37:50
 * @Description:权限计算
 * @Copyright 2020 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
class LEVEL
{
    private static $_APPS = [];
    /**
     * @description: 获取APP信息
     * @param string $name
     * @param string $type
     * @return array
     */
    public static function app($name = "", $type = "", $ckv = false)
    {
        global $_L;
        $name = $name ?: L_NAME;
        $type = $type ?: L_TYPE;
        if (self::$_APPS) {
            $apps = self::$_APPS;
        } else {
            $apps = self::getAppAll();
        }
        $app = [];
        if ($apps[$type] && $apps[$type][$name]) {
            $app = $apps[$type][$name];
        }
        if (!$app['info']['title']) {
            return $app;
        }
        if ($app['base']) {
            unset($app['base']);
        }
        $level = $_L['LCMSADMIN']['level'][$type][$name];
        if ($_L['LCMSADMIN']['type'] != "lcms") {
            foreach ($app['class'] as $cls => $arr) {
                if (!empty($arr['level'])) {
                    foreach ($arr['level'] as $fun => $_v) {
                        if (
                            (empty($level) && !empty($app['class'])) ||
                            (!$level[$cls] || $level[$cls][$fun] != 1)
                        ) {
                            $app['power'][$cls][$fun] = "no";
                            unset($app['menu'][$cls]['level'][$fun]);
                            unset($app['url']["{$cls}:{$fun}"]);
                        }
                    }
                }
            }
        }
        foreach ($app['menu'] as $cls => $arr) {
            if (empty($arr['level'])) {
                unset($app['menu'][$cls]);
                unset($app['url'][$cls]);
            } else {
                foreach ($arr['level'] as $fun => $_v) {
                    if (!$_v['menu'] || $_v['menu'] == 0) {
                        unset($app['menu'][$cls]['level'][$fun]);
                    }
                }
            }
        }
        if (count($app['url']) > 1) {
            unset($app['url']['all']);
            $app['url'] = [
                "all" => array_values($app['url'])[0],
            ] + $app['url'];
        }
        if (
            $ckv &&
            $name == "comsite" &&
            $app['info']['ver'] < "3.4.7"
        ) {
            LCMS::X(403, "&#x8BF7;&#x5347;&#x7EA7;&#x6B64;&#x5E94;&#x7528;&#x7248;&#x672C;&#xFF01;");
            exit;
        }
        return $app;
    }
    /**
     * @description: 获取APP列表
     * @param string $type
     * @param bool $base
     * @param int $count
     * @return array
     */
    public static function applist($type = "all", $base = false, $count = 0)
    {
        global $_L;
        $apps        = self::getAppAll();
        self::$_APPS = $apps;
        switch ($type) {
            case 'sys':
                $apps['open'] = [];
                break;
            case 'open':
                $apps['sys'] = [];
                if ($base) {
                    if ($count > 0) {
                        $apps['open'] = array_slice($apps['open'], 0, $count);
                    }
                    foreach ($apps['open'] as $name => $app) {
                        $apps['open'][$name] = $app['base'];
                    }
                }
                break;
            default:
                if (!$base) {
                    foreach ($apps['open'] as $name => $app) {
                        unset($apps['open'][$name]['base']);
                    }
                }
                break;
        }
        foreach ($apps['sys'] as $name => $app) {
            $apps['sys'][$name] = self::app($name, "sys");
        }
        foreach ($apps['open'] as $name => $app) {
            $napp = self::app($name, "open");
            if (!$napp['menu']) {
                unset($apps['open'][$name]);
                continue;
            }
            if ($app['ver']) {
                $app['url'] = $napp['url']['all'];
            }
            $apps['open'][$name] = $app;
        }
        return $type == "all" ? $apps : $apps[$type];
    }
    /**
     * @description: 更新缓存数据
     * @return {*}
     */
    public static function update()
    {
        global $_L;
        LCMS::cache("system/applist", "clear", true);
        self::getAppAll();
    }
    /**
     * @description: 获取所有应用信息
     * @return array
     */
    private static function getAppAll()
    {
        global $_L;
        $apps = LCMS::cache("system/applist", [], true);
        if ($apps) return $apps;
        $apps = [
            "sys"  => [],
            "open" => [],
        ];
        $sys = traversal_one(PATH_APP . "sys");
        $sys && sort($sys['dir']);
        foreach ($sys['dir'] as $name) {
            $app = self::getAppInfo("sys", $name);
            if ($app) {
                $apps['sys'][$name] = $app;
            }
        }
        $open = traversal_one(PATH_APP . "open");
        $open && sort($open['dir']);
        foreach ($open['dir'] as $name) {
            $app = self::getAppInfo("open", $name);
            if ($app && $app['info']['title']) {
                $apps['open'][$name] = $app;
            }
        }
        LCMS::cache("system/applist", $apps, true);
        return $apps;
    }
    /**
     * @description: 获取应用详细信息
     * @param string $type
     * @param string $name
     * @return array
     */
    private static function getAppInfo($type, $name)
    {
        global $_L;
        $path = PATH_APP . "{$type}/{$name}/";
        if (is_file("{$path}app.json")) {
            $app         = file_get_contents("{$path}app.json");
            $app         = json_decode($app, true);
            $app['menu'] = $app['class'];
        };
        if (is_file("{$path}admin/tpl/static/fun.js")) {
            $js = "fun.js";
        }
        $app['info']['js'] = $js ?: "";
        if ($type == "sys" && !$app['info']['title']) {
            return $app ?: [];
        }
        foreach ($app['menu'] as $key => $val) {
            if (empty($val['level'])) {
                unset($app['menu'][$key]);
            }
        }
        if (!empty($app['menu'])) {
            $class1 = array_key_first($app['menu']);
        } else {
            $class1 = "index";
        }
        if (!empty($app['menu'][$class1]['level'])) {
            $func1 = array_key_first($app['menu'][$class1]['level']);
        } else {
            $func1 = "index";
        }
        $app['url']['all'] = "index.php?t={$type}&n={$name}&c={$class1}&a={$func1}";
        foreach ($app['menu'] as $cname => $menu) {
            if (!empty($app['menu'][$cname]['level'])) {
                $func1 = array_key_first($app['menu'][$cname]['level']);
            } else {
                $func1 = "index";
            }
            $app['url'][$cname] = "index.php?t={$type}&n={$name}&c={$cname}&a={$func1}";
            foreach ($menu['level'] as $aname => $menu2) {
                $app['url']["{$cname}:{$aname}"] = "index.php?t={$type}&n={$name}&c={$cname}&a={$aname}";
            }
        }
        switch ($type) {
            case 'open':
                if (is_file("{$path}icon.gif")) {
                    $icon = "/app/open/{$name}/icon.gif?ver={$app['info']['ver']}";
                } else {
                    $icon = "/public/static/images/appicon.gif?ver={$_L['config']['ver']}";
                }
                $app['base'] = [
                    "title"       => $app['info']['title'],
                    "ver"         => $app['info']['ver'],
                    "description" => $app['info']['description'],
                    "icon"        => $icon,
                    "uninstall"   => $app['info']['uninstall'] === false ? false : true,
                    "url"         => $app['url']['all'],
                ];
                break;
        }
        return $app ?: [];
    }
}

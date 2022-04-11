<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-10-10 14:20:59
 * @LastEditTime: 2022-04-10 21:51:28
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
    public static function app($name = L_NAME, $type = L_TYPE)
    {
        global $_L;
        $file = PATH_APP . $type . "/" . $name . "/" . "app.json";
        if (is_file($file)) {
            $appinfo = json_decode(file_get_contents($file), true);
            if ($name == "appstore" && $_L['developer']['appstore'] === 0) {
                unset($appinfo['class']['store']);
            }
            $appinfo['menu'] = $appinfo['class'];
        } else {
            return false;
        };
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
        foreach ($appinfo['menu'] as $key => $val) {
            $fristfun             = !empty($appinfo['menu'][$key]['level']) ? array_key_first($appinfo['menu'][$key]['level']) : "";
            $appinfo['url'][$key] = "{$_L['url']['admin']}index.php?t={$type}&n={$name}&c={$key}&a={$fristfun}";
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
        if ($type == "all" || $type == "sys") {
            $sys = traversal_one(PATH_APP . "sys");
            $sys && sort($sys['dir']);
            foreach ($sys['dir'] as $dir) {
                $info = self::app($dir, "sys");
                if ($info) {
                    $applist['sys'][$dir] = $info;
                }
            }
        }
        if ($type == "all" || $type == "open") {
            $open = traversal_one(PATH_APP . "open");
            $open && sort($open['dir']);
            foreach ($open['dir'] as $dir) {
                $info = self::app($dir, "open");
                if ($info && $info['info']['title']) {
                    $applist['open'][$dir] = $info;
                }
            }
        }
        if ($type == "open" && $base) {
            $config = LCMS::config([
                "name" => "menu",
                "type" => "sys",
                "cate" => "admin",
            ]);
            $index = 0;
            $cache = [];
            foreach ($applist['open'] as $name => $app) {
                if ($app['menu']) {
                    $icon = "{$_L['url']['static']}images/appicon.gif?v20220409";
                    if (is_file(PATH_APP . "open/{$name}/icon.gif")) {
                        $icon = "{$_L['url']['app']}open/{$name}/icon.gif?ver={$app['info']['ver']}";
                    }
                    $cache[$name] = [
                        "title" => $app['info']['title'],
                        "ver"   => $app['info']['ver'],
                        "icon"  => $icon,
                        "url"   => $app['url']['all'],
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
        return $type == "all" ? $applist : $applist[$type];
    }
}

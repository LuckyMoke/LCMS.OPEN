<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-10-10 14:20:59
 * @LastEditTime: 2020-12-13 21:14:45
 * @Description:权限计算
 * @Copyright 2020 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
class LEVEL
{
    public static function app($name = L_NAME, $type = L_TYPE)
    {
        global $_L;
        $file = PATH_APP . $type . "/" . $name . "/" . "app.json";
        if (is_file($file)) {
            $appinfo         = json_decode(file_get_contents($file), true);
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
    public static function appall()
    {
        global $_L;
        $sys  = traversal_one(PATH_APP . "sys");
        $open = traversal_one(PATH_APP . "open");
        $sys && sort($sys['dir']);
        $open && sort($open['dir']);
        foreach ($sys['dir'] as $dir) {
            $info = self::app($dir, "sys");
            if ($info) {
                $appinfo['sys'][$dir] = $info;
            }
        }
        foreach ($open['dir'] as $dir) {
            $info = self::app($dir, "open");
            if ($info && $info['info']['title']) {
                $appinfo['open'][$dir] = $info;
            }
        }
        return $appinfo;
    }
}

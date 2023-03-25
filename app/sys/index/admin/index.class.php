<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2021-03-13 16:11:14
 * @LastEditTime: 2023-03-21 17:14:48
 * @Description: 框架菜单处理
 * Copyright 2021 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
load::sys_class('adminbase');
class index extends adminbase
{
    public function __construct()
    {
        global $_L, $LF, $LC;
        parent::__construct();
        $LF = $_L['form'];
        $LC = $LF['LC'];
    }
    public function doindex()
    {
        global $_L, $LF, $LC;
        $app    = [];
        $sys    = [];
        $open   = [];
        $config = LCMS::config([
            "type" => "sys",
            "name" => "menu",
            "cate" => "admin",
        ]);
        $config['sys'] = [
            "appstore" => [
                "local" => 1,
                "store" => 1,
            ],
            "user"     => [
                "admin" => 1,
            ],
            "config"   => [
                "admin" => 1,
                "web"   => 1,
            ],
            "backup"   => [
                "database" => 1,
                "optimize" => 1,
                "files"    => 1,
            ],
            "update"   => [
                "gitee" => 1,
            ],
        ];
        if (LCMS::SUPER()) {
            $update = 1;
        } else {
            unset($config['sys']['update']);
        }
        foreach ($config['sys'] as $name => $class) {
            $app[$name] = $app[$name] ?: LEVEL::app($name, "sys");
            if ($app[$name]['menu']) {
                $sys[] = [
                    "name"  => $name,
                    "icon"  => $app[$name]['info']['icon'],
                    "title" => $app[$name]['info']['title'],
                    "url"   => $app[$name]['url']['all'],
                ];
            }
        }
        foreach ($config['open'][0]['menu'] as $name => $on) {
            if ($on != 1) {
                continue;
            }
            $app[$name] = $app[$name] ?: LEVEL::app($name, "open");
            if ($app[$name]['menu']) {
                $open[] = [
                    "name"  => $name,
                    "icon"  => "",
                    "title" => $app[$name]['info']['title'],
                    "url"   => $app[$name]['url']['all'],
                ];
            }
        }
        $applist = LEVEL::applist("open", true, 6);
        if ($config['default'] && $applist) {
            switch ($config['default']['on']) {
                case '1':
                    $homeurl = reset($applist)['url'];
                    break;
                case '2':
                    $homeurl = $applist[$config['default']['name']]['url'];
                    break;
            }
        }
        $homeurl = $homeurl ?: "{$_L['url']['admin']}index.php?n=home";
        require LCMS::template("own/index");
    }
    public function doheart()
    {
        global $_L, $LF, $LC;
        if (!$_L['LCMSADMIN'] || $_L['LCMSADMIN']['name'] != $LF['name']) {
            ajaxout(0, "refresh");
        }
        ajaxout(1, "success");
    }
};

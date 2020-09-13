<?php
defined('IN_LCMS') or exit('No permission');
load::sys_class('adminbase');
class index extends adminbase
{
    public function __construct()
    {
        global $_L;
        parent::__construct();
    }
    public function doindex()
    {
        global $_L;
        $config = LCMS::config([
            "type" => "sys",
            "name" => "menu",
            "cate" => "admin",
            "lcms" => true,
        ]);
        if (!LCMS::SUPER()) {
            $config['open'] = LCMS::config([
                "type" => "sys",
                "name" => "menu",
                "cate" => "admin",
            ])['open'];
        }
        foreach ($config['sys'] as $index => $list) {
            foreach ($list['menu'] as $name => $li) {
                foreach ($li['class'] as $class => $type) {
                    if ($type) {
                        $tempsys[$index]['title']         = $list['title'];
                        $tempsys[$index]['menu'][$name][] = $class;
                    }
                }
            }
        }
        foreach ($tempsys as $index => $list) {
            $sys[$index]['title'] = $list['title'];
            if (count($list['menu']) > 1) {
                foreach ($list['menu'] as $name => $li) {
                    $app[$name] = $app[$name] ?: LEVEL::app($name, "sys");
                    if ($app[$name]['menu']) {
                        $sys[$index]['menu'][$name] = [
                            "title" => $app[$name]['info']['title'],
                            "url"   => $app[$name]['url']['all'],
                        ];
                        foreach ($li as $class) {
                            if ($app[$name]['menu'][$class]) {
                                $sys[$index]['menu'][$name]['menu'][] = array(
                                    "title" => $app[$name]['menu'][$class]['title'],
                                    "url"   => $app[$name]['url'][$class],
                                );

                            }
                        }
                    }
                }
            } else {
                $name       = array_key_first($list['menu']);
                $app[$name] = $app[$name] ?: LEVEL::app($name, "sys");
                foreach ($list['menu'][$name] as $class) {
                    if ($app[$name]['menu'][$class]) {
                        $sys[$index]['menu'][] = array(
                            "title" => $app[$name]['menu'][$class]['title'],
                            "url"   => $app[$name]['url'][$class],
                        );
                    }
                }
            }
        }
        $tempopen[0]['title'] = "常用应用";
        foreach ($config['open'] as $index => $list) {
            foreach ($list['menu'] as $name => $cls) {
                if (is_array($cls)) {
                    foreach ($cls['class'] as $cname => $type) {
                        if ($type) {
                            $tempopen[$index]['title']         = $list['title'];
                            $tempopen[$index]['menu'][$name][] = $cname;
                        }
                    }
                } elseif ($cls == "on") {
                    $tempopen[$index]['title']       = $list['title'];
                    $tempopen[$index]['menu'][$name] = 1;
                }
            }
        }
        foreach ($tempopen as $index => $list) {
            $open[$index]['title'] = $list['title'];
            if (count($list['menu']) > 1) {
                foreach ($list['menu'] as $name => $li) {
                    $app[$name] = $app[$name] ?: LEVEL::app($name, $name == "appstore" ? "sys" : "open");
                    if ($app[$name]['menu']) {
                        $open[$index]['menu'][$name] = [
                            "title" => $app[$name]['info']['title'],
                            "url"   => $app[$name]['url']['all'],
                        ];
                        foreach ($li as $class) {
                            if ($app[$name]['menu'][$class]) {
                                $open[$index]['menu'][$name]['menu'][] = [
                                    "title" => $app[$name]['menu'][$class]['title'],
                                    "url"   => $app[$name]['url'][$class],
                                ];

                            }
                        }
                    }
                }
            } else {
                $name       = array_key_first($list['menu']);
                $app[$name] = $app[$name] ?: LEVEL::app($name, $name == "appstore" ? "sys" : "open");
                if (is_array($list['menu'][$name])) {
                    foreach ($list['menu'][$name] as $class) {
                        if ($app[$name]['menu'][$class]) {
                            $open[$index]['menu'][] = array(
                                "title" => $app[$name]['menu'][$class]['title'],
                                "url"   => $app[$name]['url'][$class],
                            );
                        }
                    }
                } else {
                    $open[$index]['menu'][$name] = [
                        "title" => $app[$name]['info']['title'],
                        "url"   => $app[$name]['url']['all'],
                    ];
                }
            }
        }
        require LCMS::template("own/index");
    }
};

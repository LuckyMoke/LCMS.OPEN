<?php
defined('IN_LCMS') or exit('No permission');
load::sys_class('adminbase');
class web extends adminbase
{
    public function __construct()
    {
        global $_L;
        parent::__construct();
    }
    public function doindex()
    {
        global $_L;
        switch ($_L['form']['action']) {
            case 'save':
                $domain = parse_url($_L['form']['LC']['domain']);
                if ($domain['host']) {
                    $_L['form']['LC']['https']  = $domain['scheme'] == "https" ? "1" : "0";
                    $_L['form']['LC']['domain'] = $domain['host'] . ($domain['port'] ? ":{$domain['port']}" : "");
                }
                LCMS::config([
                    "do"   => "save",
                    "type" => "sys",
                    "cate" => "web",
                    "lcms" => true,
                ]);
                ajaxout(1, "保存成功");
                break;
            default:
                $config = LCMS::config([
                    "type" => "sys",
                    "cate" => "web",
                    "lcms" => true,
                ]);
                $scheme = $config['https'] == "1" ? "https://" : "http://";
                $form   = array(
                    ["layui" => "radio", "title" => "限制访问？",
                        "name"   => "LC[domain_must]",
                        "value"  => $config['domain_must'],
                        "tips"   => "限制前端只能通过下方域名访问",
                        "radio"  => [
                            ["title" => "限制域名", "value" => "1"],
                            ["title" => "不限制域名", "value" => "0"],
                        ],
                    ],
                    ["layui"      => "input", "title" => "网站域名",
                        "name"        => "LC[domain]",
                        "value"       => $config['domain'] ? "{$scheme}{$config['domain']}/" : "",
                        "placeholder" => "http://www.domain.com/",
                        "tips"        => "特别注意结尾的 / 斜杠",
                        "verify"      => "required",
                    ],
                    ["layui"      => "input", "title" => "API域名",
                        "name"        => "LC[domain_api]",
                        "value"       => $config['domain_api'],
                        "placeholder" => "http://www.domain.com/",
                        "tips"        => "特别注意结尾的 / 斜杠",
                        "verify"      => "required",
                    ],
                    ["layui" => "input", "title" => "网站名称",
                        "name"   => "LC[title]",
                        "value"  => $config['title'],
                    ],
                    ["layui" => "upload", "title" => "前台默认图片",
                        "name"   => "LC[image_default]",
                        "value"  => $config['image_default'],
                    ],
                    ["layui" => "textarea", "title" => "平台统计代码",
                        "name"   => "LC[tongji]",
                        "value"  => $config['tongji'],
                    ],
                    ["layui" => "btn", "title" => "立即保存"],
                );
                require LCMS::template("own/web_index");
                break;
        }
    }
};

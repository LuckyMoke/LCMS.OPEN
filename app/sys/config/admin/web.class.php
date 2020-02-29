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
                if (stristr($_L['form']['LC']['domain'], "://") !== false) {
                    $domain                     = parse_url($_L['form']['LC']['domain']);
                    $_L['form']['LC']['domain'] = $domain['host'] . ($domain['port'] ? ":{$domain['port']}" : "");
                }
                LCMS::config(array(
                    "do"   => "save",
                    "type" => "sys",
                    "cate" => "web",
                    "lcms" => true,
                ));
                ajaxout(1, "保存成功");
                break;
            default:
                $config = LCMS::config(array(
                    "type" => "sys",
                    "cate" => "web",
                    "lcms" => true,
                ));
                $form = array(
                    array("layui" => "des", "title" => "这里的配置优先级高于各个应用中的相同配置"),
                    array("layui" => "radio", "title" => "网站协议", "name" => "LC[https]", "value" => $config['https'] ? $config['https'] : "0", "radio" => array(array("title" => "https://", "value" => "1"), array("title" => "http://", "value" => "0"))),
                    array("layui" => "on", "title" => "限制访问？", "name" => "LC[domain_must]", "value" => $config['domain_must'], "text" => "是|否", "tips" => "限制前端只能通过下方域名访问"),
                    array("layui" => "input", "title" => "网站域名", "name" => "LC[domain]", "value" => $config['domain'], "tips" => "不含http://或https://"),
                    array("layui" => "input", "title" => "网站名称", "name" => "LC[title]", "value" => $config['title']),
                    array("layui" => "upload", "title" => "前台LOGO", "name" => "LC[logo]", "value" => $config['logo']),
                    array("layui" => "upload", "title" => "前台默认图片", "name" => "LC[image_default]", "value" => $config['image_default']),
                    array("layui" => "textarea", "title" => "平台统计代码", "name" => "LC[tongji]", "value" => $config['tongji']),
                    array("layui" => "btn", "title" => "立即保存"),
                );
                require LCMS::template("own/web_index");
                break;
        }
    }
};

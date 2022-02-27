<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-11-16 14:43:29
 * @LastEditTime: 2022-02-27 14:36:56
 * @Description:数据表优化
 * @symbol_custom_string_obkoro1_copyright: Copyright ${now_year} 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
load::sys_class('adminbase');
class optimize extends adminbase
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
        LCMS::SUPER() || LCMS::X(403, "仅超级管理员可设置");
        switch ($LF['action']) {
            case 'check':
                switch ($LF['engine']) {
                    case 'InnoDB':
                        $sql = "ALTER TABLE {$LF['name']} engine=InnoDB";
                        break;
                    case 'MyISAM':
                        $sql = "OPTIMIZE TABLE {$LF['name']}";
                        break;
                }
                sql_query($sql);
                ajaxout(1, sql_error());
                break;
            case 'truncate':
                if ($LF['name']) {
                    sql_query("TRUNCATE TABLE `{$LF['name']}`");
                }
                LCMS::log([
                    "type" => "system",
                    "info" => "清空数据表-{$LF['name']}",
                ]);
                ajaxout(1, "表数据已清空");
                break;
            default:
                $table = sql_query("SHOW TABLE STATUS");
                require LCMS::template("own/optimize/index");
                break;
        }
    }
}

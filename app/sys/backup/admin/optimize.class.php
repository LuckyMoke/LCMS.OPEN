<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-11-16 14:43:29
 * @LastEditTime: 2021-05-21 18:05:19
 * @Description:数据表优化
 * @symbol_custom_string_obkoro1_copyright: Copyright ${now_year} 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
load::sys_class('adminbase');
class optimize extends adminbase
{
    public function __construct()
    {
        global $_L;
        parent::__construct();
    }
    public function doindex()
    {
        global $_L;
        if (!LCMS::SUPER()) {
            LCMS::X(403, "仅超级管理员可设置");
        }
        switch ($_L['form']['action']) {
            case 'check':
                switch ($_L['form']['engine']) {
                    case 'InnoDB':
                        $sql = "ALTER TABLE {$_L['form']['name']} engine=InnoDB";
                        break;
                    case 'MyISAM':
                        $sql = "OPTIMIZE TABLE {$_L['form']['name']}";
                        break;
                }
                sql_query($sql);
                ajaxout(1, sql_error());
                break;
            case 'truncate':
                if ($_L['form']['name']) {
                    sql_query("TRUNCATE TABLE `{$_L['form']['name']}`");
                }
                ajaxout(1, "表数据已清空");
                break;
            default:
                $table = sql_query("SHOW TABLE STATUS");
                require LCMS::template("own/optimize/index");
                break;
        }
    }
}

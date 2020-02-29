<?php
defined('IN_LCMS') or exit('No permission');
class userbase
{
    /**
     * [login 检测登陆]
     * @param  [type] $para [description]
     * @return [type]       [description]
     */
    public static function login($para)
    {
        global $_L;
        if (!$para['name']) {
            return self::error(0, "缺少用户名");
        }
        if (!$para['pass']) {
            return self::error(0, "缺少密码");
        }
        return sql_get(["user", "name = '{$para['name']}' AND pass = '" . md5($para['pass']) . "'"]);
    }
    /**
     * [register 注册新用户]
     * @param  [type] $para [description]
     * @return [type]       [description]
     */
    public static function register($para)
    {
        global $_L;
        if (!$para['name']) {
            return self::error(0, "缺少用户名");
        }
        $check = self::check($para);
        if ($check) {
            return $check;
        } else {
            $pass            = $para['pass'] ? $para['pass'] : randstr(6);
            $para['pass']    = md5($pass);
            $para['addtime'] = datenow();
            $para['lcms']    = $_L['ROOTID'];
            unset($para['id']);
            $insert_id = sql_insert(["user", $para]);
            if (!sql_error()) {
                return self::error(1, array("id" => $insert_id, "pass" => $pass));
            }
        }
    }
    /**
     * [update 更新用户信息]
     * @param  [type] $para [description]
     * @return [type]       [description]
     */
    public static function update($para)
    {
        global $_L;
        if (!$para['id']) {
            return self::error(0, "缺少用户id字段");
        }
        $check = self::check($para);
        if ($check) {
            return $check;
        } else {
            unset($para['name']);
            sql_update(["user", $para, "id = '{$para['id']}' AND lcms = '{$_L['ROOTID']}'"]);
            if (!sql_error()) {
                return self::error(1, "更新成功");
            }
        }
    }
    /**
     * [get 获取用户信息]
     * @param  string $type  [通过什么字段获取]
     * @param  [type] $value [值为多少]
     * @return [type]        [description]
     */
    public static function get($type = "id", $value)
    {
        global $_L;
        switch ($type) {
            case 'name':
                $where = "name = '{$value}'";
                break;
            case 'eamil':
                $where = "eamil = '{$value}'";
                break;
            case 'mobile':
                $where = "mobile = '{$value}'";
                break;
            default:
                $where = "id = '{$value}'";
                break;
        }
        $where .= " AND lcms = '{$_L['ROOTID']}'";
        return sql_get(["user", $where]);
    }
    /**
     * [check 检查用户是否存在]
     * @param  [type] $para [name, email, mobile 字段均不能重复]
     * @return [type]       [description]
     */
    public static function check($para)
    {
        global $_L;
        $where = $para['id'] ? " AND id != '{$para['id']}'" : "";
        $where .= " AND lcms = '{$_L['ROOTID']}'";
        if ($para['name']) {
            if (sql_get(["user", "name = '{$para['name']}'{$where}"])) {
                return self::error(0, "账号已存在");
            }
        }
        if ($para['email']) {
            if (sql_get(["user", "email = '{$para['email']}'{$where}"])) {
                return self::error(0, "邮箱已存在");
            }
        }
        if ($para['mobile']) {
            if (sql_get(["user", "mobile = '{$para['mobile']}'{$where}"])) {
                return self::error(0, "手机号已存在");
            }
        }
    }
    /**
     * [error 返回错误]
     * @param  [type] $code [description]
     * @param  [type] $msg  [description]
     * @return [type]       [description]
     */
    public static function error($code, $msg)
    {
        $error['code'] = $code;
        $error['msg']  = $msg;
        return $error;
    }
}

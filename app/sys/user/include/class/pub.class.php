<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2023-06-25 12:28:04
 * @LastEditTime: 2023-10-30 21:48:54
 * @Description: PUB公共类
 * Copyright 2023 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
class PUB
{
    /**
     * @description: 保存用户信息
     * @param array $keys
     * @return string
     */
    public static function userSave($keys)
    {
        global $_L, $LF, $LC;
        $LC['id'] = PUB::token2id($LF['token']);
        foreach ($keys as $key) {
            $LCN[$key] = $LC[$key];
        }
        $LC = $LCN;
        if ($LC['id'] == $_L['LCMSADMIN']['id']) {
            unset($LC['status']);
        }
        if ($LC['pass']) {
            $LC['salt'] = randstr(8);
            $LC['pass'] = md5("{$LC['pass']}{$LC['salt']}");
        } else {
            unset($LC['pass']);
        }
        if (in_array("name", $keys)) {
            foreach ([
                ["name" => "name", "msg" => "账号已存在"],
                ["name" => "email", "msg" => "邮箱已存在"],
                ["name" => "mobile", "msg" => "手机号已存在"],
            ] as $check) {
                if ($LC[$check['name']] && sql_get([
                    "table" => "admin",
                    "where" => "(name = :value OR email = :value OR mobile = :value) AND id != :id",
                    "order" => "id DESC",
                    "bind"  => [
                        ":value" => $LC[$check['name']],
                        ":id"    => $LC['id'] ?: 0,
                    ],
                ])) {
                    ajaxout(0, $check['msg']);
                }
            }
        }
        if ($LF['admin_level']) {
            $level = explode("/", $LF['admin_level']);
            if (!$level[1]) {
                ajaxout(0, "请设置用户权限");
            } else {
                $LC['lcms'] = $level[0];
                $LC['type'] = $level[1];
            }
        }
        LCMS::form([
            "table" => "admin",
            "form"  => $LC,
        ]);
        if (sql_error()) {
            ajaxout(0, "保存失败", "", sql_error());
        } else {
            if ($LC['id'] == $_L['LCMSADMIN']['id']) {
                SESSION::set("LCMSADMIN", array_merge($_L['LCMSADMIN'], [
                    "name"  => $LC['name'] ?: $_L['LCMSADMIN']['name'],
                    "title" => $LC['title'],
                ]));
            }
            LCMS::log([
                "type" => "system",
                "info" => "用户管理-" . ($LC['id'] ? "修改" : "添加") . "用户-" . ($LC['name'] ?: $_L['LCMSADMIN']['name']),
            ]);
            ajaxout(1, "保存成功", "close");
        }
    }
    /**
     * @description: id转token
     * @param string|int $id
     * @return string
     */
    public static function id2token($id)
    {
        global $_L;
        return ssl_encode($id, $_L['LCMSADMIN']['salt']);
    }
    /**
     * @description: token转id
     * @param string $token
     * @return string|int
     */
    public static function token2id($token = "")
    {
        global $_L;
        $id = $token ? ssl_decode($token, $_L['LCMSADMIN']['salt']) : "";
        $id = intval($id);
        return $id > 0 ? $id : "";
    }
}

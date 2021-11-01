<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2021-10-27 16:13:51
 * @LastEditTime: 2021-10-28 19:15:54
 * @Description: PUB公共类
 * Copyright 2021 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
class PUB
{
    /**
     * @description: 检测账号是否存在
     * @param string $type
     * @param string $name
     * @return {*}
     */
    public static function ishave($type = "name", $name = "", $return = true)
    {
        global $_L;
        if (!$name) {
            ajaxout(0, "禁止访问");
        }
        switch ($type) {
            case 'name':
                $text = "账号";
                if (strlen($name) < 6) {
                    ajaxout(0, "账号不能少于6位");
                }
                break;
            case 'mobile':
                $text = "手机号";
                is_phone($name) || ajaxout(0, "手机号错误");
                break;
            case 'email':
                $text = "邮箱账号";
                self::is_email($name) || ajaxout(0, "邮箱地址错误");
                break;
        }
        $admin = sql_get(["admin",
            "name = :name OR email = :name OR mobile = :name",
            "id DESC", [
                ":name" => $name,
            ],
        ]);
        if ($admin && $return) {
            ajaxout(0, "{$text}已存在");
        }
        if (!$admin && !$return) {
            ajaxout(0, "账号不存在");
        }
    }
    /**
     * @description: 发送手机短信
     * @param string $mobile
     * @param string $text
     * @return bool
     */
    public static function sendsms($mobile, $text)
    {
        global $_L, $UCFG, $PLG;
        load::sys_class("sms");
        $result = SMS::send([
            "ID"    => $UCFG['reg']['sms_tplcode'],
            "Name"  => $UCFG['reg']['sms_signname'],
            "Phone" => $mobile,
            "Param" => [
                "code" => $text,
            ],
        ], $PLG['sms']);
        if ($result['code'] == 1) {
            return true;
        } else {
            ajaxout(0, $result['msg']);
        }
    }
    /**
     * @description: 发送邮件
     * @param string $email
     * @param string $text
     * @return bool
     */
    public static function sendemail($email, $text)
    {
        global $_L, $UCFG, $PLG;
        load::sys_class("email");
        $result = EMAIL::send([
            "TO"    => $email,
            "Title" => "邮箱验证码",
            "Body"  => "验证码为：{$text}，5分钟有效！",
        ], $PLG['email']);
        if ($result['code'] == 1) {
            return true;
        } else {
            ajaxout(0, $result['msg']);
        }
    }
    /**
     * @description: 判断是否为邮箱地址
     * @param string $email
     * @return bool
     */
    public static function is_email($email)
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email = explode("@", $email);
            $black = "yopmail.com";
            if (stristr($black, $email[1]) === false) {
                return true;
            }
        }
        return false;
    }
}

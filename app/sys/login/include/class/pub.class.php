<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2021-10-27 16:13:51
 * @LastEditTime: 2023-11-18 13:25:02
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
        self::isAttack($mobile);
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
            self::isAttack($mobile, "update");
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
        self::isAttack($email);
        load::sys_class("email");
        $result = EMAIL::send([
            "TO"    => $email,
            "Title" => "邮箱验证码",
            "Body"  => "验证码为：{$text}，5分钟有效！",
        ], $PLG['email']);
        if ($result['code'] == 1) {
            self::isAttack($email, "update");
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
        if (is_email($email)) {
            $email = explode("@", $email);
            if (!in_array($email[1], [
                "yopmail.com",
            ])) {
                return true;
            }
        }
        return false;
    }
    /**
     * @description: 检测是否攻击
     * @return {*}
     */
    public static function isAttack($ma, $type = "")
    {
        global $_L;
        $cip = LCMS::ram("login" . CLIENT_IP);
        $cma = LCMS::ram("login{$ma}");
        switch ($type) {
            case 'update':
                $cip = $cip ? $cip * 1 + 1 : 1;
                LCMS::ram("login" . CLIENT_IP, $cip, 43200);
                $cma = $cma ? $cma * 1 + 1 : 1;
                LCMS::ram("login{$ma}", $cma, 43200);
                break;
            default:
                if ($cip >= 3 || $cma >= 3) {
                    ajaxout(0, "今日请求已达上限，请联系客服协助验证！");
                }
                break;
        }
    }
}

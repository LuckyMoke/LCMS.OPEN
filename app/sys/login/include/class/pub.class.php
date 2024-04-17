<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2021-10-27 16:13:51
 * @LastEditTime: 2024-04-17 11:57:18
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
                is_email($name) || ajaxout(0, "邮箱地址错误");
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
        if ($UCFG['reg']['sms_black']) {
            $REG = "/^(" . $UCFG['reg']['sms_black'] . ")\d+$/";
            if (preg_match($REG, $mobile)) {
                ajaxout(0, "此手机号禁止注册<br>请联系客服协助处理");
            }
        }
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
        if (preg_match("/^[a-zA-Z0-9_-]+@(yopmail.com)$/", $email)) {
            ajaxout(0, "此邮箱禁止注册<br>请联系客服协助处理");
        }
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
     * @description: 注册攻击检测
     * @param string $ma
     * @param string $type
     * @return {*}
     */
    public static function isAttack($ma, $type = "")
    {
        global $_L;
        $cip = LCMS::ram("login_reg" . CLIENT_IP);
        $cma = LCMS::ram("login_reg{$ma}");
        switch ($type) {
            case 'update':
                $cip = $cip ? $cip * 1 + 1 : 1;
                LCMS::ram("login_reg" . CLIENT_IP, $cip, 43200);
                $cma = $cma ? $cma * 1 + 1 : 1;
                LCMS::ram("login_reg{$ma}", $cma, 43200);
                break;
            default:
                if ($cip >= 3 || $cma >= 3) {
                    LCMS::notify("注册攻击通知", "<p>疑似遇到注册攻击，已被系统拦截。攻击IP：" . CLIENT_IP . "，注册信息：{$ma}。</p><p>表单数据：<pre>" . json_encode_ex($_L['form']) . "</pre></p>", 86400);
                    ajaxout(0, "今日请求已达上限<br>请联系客服协助验证");
                }
                break;
        }
    }
    /**
     * @description: 登录攻击检测
     * @param string $type
     * @return {*}
     */
    public static function isLoginAttack($type = "")
    {
        global $_L;
        $cip = LCMS::ram("login_check" . CLIENT_IP);
        switch ($type) {
            case 'update':
                $cip = $cip ? $cip * 1 + 1 : 1;
                LCMS::ram("login_check" . CLIENT_IP, $cip, 600);
                break;
            default:
                if ($cip >= 5) {
                    LCMS::notify("登录攻击通知", "<p>疑似遇到登录攻击，已被系统拦截。攻击IP：" . CLIENT_IP . "。</p><p>表单数据：<pre>" . json_encode_ex($_L['form']) . "</pre></p>", 86400);
                    ajaxout(0, "请10分钟后再试<br>如遇问题可联系客服处理");
                }
                break;
        }
    }
}

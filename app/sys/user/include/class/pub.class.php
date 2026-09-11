<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2023-06-25 12:28:04
 * @LastEditTime: 2026-09-10 16:44:09
 * @Description: PUB公共类
 * Copyright 2023 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
class PUB
{
    /**
     * @description: 保存用户信息
     * @param array $keys
     * @param array $form
     * @return string
     */
    public static function userSave($keys, $form = [])
    {
        global $_L, $LF, $LC;
        LOAD::sys_class("userbase");
        $uid  = $form['id'] ?: PUB::token2id($LF['token']);
        $user = $uid ? USERBASE::getUser($uid, "id") : [];
        $opts = $uid ? [
            "id" => $uid,
        ] : [];
        $LC = $form ?: $LC;
        foreach ($keys as $key) {
            $opts[$key] = $LC[$key];
        }
        if ($opts['id'] == $_L['LCMSADMIN']['id']) {
            unset($opts['status'], $opts['cate']);
        }
        $opts['lcms'] = isset($user['lcms']) ? $user['lcms'] : $_L['ROOTID'];
        //更新用户信息
        $user = USERBASE::update($opts, $user);
        //更新SESSION
        if ($user['id'] == $_L['LCMSADMIN']['id']) {
            SESSION::set("LCMSADMIN", array_merge($_L['LCMSADMIN'], [
                "name"    => $user['name'],
                "title"   => $user['title'],
                "headimg" => $user['headimg'],
                "2fa"     => $user['2fa'],
                "email"   => $user['email'],
                "mobile"  => $user['mobile'],
            ]));
        }
        return $user;
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
    /**
     * @description: 获取权限选择列表
     * @param array $level
     * @return {*}
     */
    public static function getLevelList($level)
    {
        global $_L;
        $level = is_array($level) ? $level : [];
        $hide  = [];
        foreach (["sys", "open"] as $key) {
            if (
                !empty($level[$key]) &&
                is_array($level[$key])
            ) {
                ksort($level[$key]);
            }
        }
        $appall = LEVEL::applist();
        $result = $level;
        foreach ($appall as $type => $list) {
            if (empty($list) || !is_array($list)) {
                continue;
            }
            if (
                !isset($result[$type]) ||
                !is_array($result[$type])
            ) {
                $result[$type] = [];
            }
            foreach ($list as $name => $li) {
                if (
                    !is_array($li) ||
                    empty($li['class']) ||
                    !is_array($li['class'])
                ) {
                    continue;
                }
                $classes = [];
                foreach ($li['class'] as $class => $cv) {
                    if (
                        empty($cv['level']) ||
                        !is_array($cv['level'])
                    ) {
                        continue;
                    }
                    $power = $li['power'][$class] ?? [];
                    if (!is_array($power)) {
                        $power = [];
                    }
                    $select = [];
                    foreach ($cv['level'] as $key => $lv) {
                        if (($power[$key] ?? null) === 'no') {
                            $hide[$type][$name][$class][$key] = 0;
                        } else {
                            $select[] = [
                                "value" => $key,
                                "title" => $lv['title'] ?? "",
                            ];
                        }
                    }
                    if (!empty($select)) {
                        $classes[$class] = [
                            "title"  => $cv['title'] ?? "",
                            "select" => $select,
                        ];
                    }
                }
                if (
                    !isset($result[$type][$name]) ||
                    !is_array($result[$type][$name])
                ) {
                    $result[$type][$name] = [];
                }
                if (!empty($classes)) {
                    $result[$type][$name]['title'] = $li['info']['title'] ?? "";
                    $result[$type][$name]['class'] = $classes;
                } else {
                    unset($result[$type][$name]);
                }
            }
            if (empty($result[$type])) {
                unset($result[$type]);
            }
        }
        $hideJson = "";
        if (!empty($hide)) {
            $hideJson = htmlspecialchars(
                json_encode($hide, JSON_UNESCAPED_UNICODE),
                ENT_QUOTES,
                "UTF-8"
            );
        }
        return [$result ?: [], $hideJson];
    }
}

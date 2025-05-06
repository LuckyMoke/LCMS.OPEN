<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2023-06-25 12:28:04
 * @LastEditTime: 2025-05-05 12:31:50
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
        $level['sys'] && ksort($level['sys']);
        $level['open'] && ksort($level['open']);
        $appall = LEVEL::applist();
        foreach ($appall as $type => $val) {
            foreach ($val as $name => $info) {
                if (!empty($info['class'])) {
                    $level[$type][$name]['title'] = $info['info']['title'];
                    foreach ($info['class'] as $class => $val) {
                        if (!empty($val['level'])) {
                            $level[$type][$name]['class'][$class]['title'] = $val['title'];
                            foreach ($val['level'] as $key => $val) {
                                if ($info['power'][$class][$key] != "no") {
                                    $level[$type][$name]['class'][$class]['select'][] = [
                                        "value" => $key,
                                        "title" => $val['title'],
                                    ];
                                } else {
                                    $hide[$type][$name][$class][$key] = 0;
                                }
                            }
                        }
                    }
                }
            }
        }
        foreach ($level as $type => $list) {
            foreach ($list as $name => $li) {
                foreach ($li['class'] as $class => $val) {
                    if (empty($val['select'])) {
                        unset($level[$type][$name]['class'][$class]);
                        if (empty($level[$type][$name]['class'])) {
                            unset($level[$type][$name]);
                        }
                    }
                }
            }
        }
        return [$level ?: [], $hide ? htmlspecialchars(json_encode($hide)) : ""];
    }
}

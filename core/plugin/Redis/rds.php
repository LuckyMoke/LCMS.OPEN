<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-10-10 14:20:59
 * @LastEditTime: 2024-05-04 11:41:14
 * @Description: Redis操作类
 * @Copyright 2020 运城市盘石网络科技有限公司
 */
class RDS
{
    public $do;
    /**
     * @description: 链接Redis服务
     * @param array $config
     * @return {*}
     */
    public function __construct($config = [])
    {
        global $_L;
        $config = $config ? $config : $_L["redis"];
        if (class_exists("Redis")) {
            try {
                $this->do = new Redis();
                $this->do->connect($config['host'], $config['port']);
                if ($config['index'] > 0) {
                    $this->do->select($config['index']);
                }
                if ($config['pass']) {
                    $this->do->auth($config['pass']);
                }
            } catch (Exception $e) {
                LCMS::X($e->getCode(), iconv('gbk', 'utf-8', $e->getMessage()));
            }
        } else {
            LCMS::X(500, "Redis组件未开启");
        }
    }
    /**
     * @description: 防止并发操作锁
     * @param string $key
     * @param int $expire
     * @return bool
     */
    public function lock($key, $expire = 5)
    {
        $is_lock = $this->do->setnx($key, time() + $expire);
        if (!$is_lock) {
            $lock_time = $this->do->get($key);
            if (time() > $lock_time) {
                $this->unlock($key);
                $is_lock = $this->do->setnx($key, time() + $expire);
            }
        }
        return $is_lock ? true : false;
    }
    /**
     * @description: 操作完成后解锁
     * @param string $key
     * @return {*}
     */
    public function unlock($key)
    {
        return $this->do->del($key);
    }
}

<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-10-10 14:20:59
 * @LastEditTime: 2020-12-11 16:54:18
 * @Description:Redis操作类
 * @Copyright 2020 运城市盘石网络科技有限公司
 */
class RDS
{
    public object $do;
    /**
     * [__construct 默认连接 core/config.php 里配置的redis服务]
     * @param string $config [description]
     */
    public function __construct($config = [])
    {
        global $_L;
        $config = $config ? $config : $_L["redis"];
        if (class_exists("Redis")) {
            try {
                $this->do = new Redis();
                $this->do->connect($config['host'], $config['port']);
                if ($config['pass']) {
                    $this->do->auth($config['pass']);
                }
            } catch (\Exception$e) {
                LCMS::X($e->getCode(), iconv('gbk', 'utf-8', $e->getMessage()));
            }
        } else {
            LCMS::X(500, "Redis组件未开启");
        }
    }
    /**
     * [lock 防止并发操作锁]
     * @param  [type]  $key    [description]
     * @param  integer $expire [description]
     * @return [type]          [description]
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
     * [unlock 操作完成后解锁]
     * @param  [type] $key [description]
     * @return [type]      [description]
     */
    public function unlock($key)
    {
        return $this->do->del($key);
    }
}

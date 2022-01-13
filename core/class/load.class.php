<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-10-10 14:20:59
 * @LastEditTime: 2022-01-13 14:27:21
 * @Description:文件加载类
 * @Copyright 2020 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
class LOAD
{
    private static $mclass = [];
    /**
     * @description: 初始化加载
     * @param {*}
     * @return {*}
     */
    public static function init()
    {
        $action = L_ACTION ?: "doindex";
        return self::loadClass(PATH_APP_OWN . L_CLASS, $action);
    }
    /**
     * @description: 加载系统类
     * @param string $cname
     * @param string $action
     * @return {*}
     */
    public static function sys_class($cname, $action = "")
    {
        return self::loadClass(PATH_CORE_CLASS . $cname, $action);
    }
    /**
     * @description: 加载系统方法
     * @param string $fname
     * @return {*}
     */
    public static function sys_func($fname)
    {
        return self::loadFun(PATH_CORE_FUNC . $fname);
    }
    /**
     * @description: 加载系统插件
     * @param string $pname
     * @return {*}
     */
    public static function plugin($pname)
    {
        return self::loadPlugin(PATH_CORE_PLUGIN . $pname);
    }
    /**
     * @description: 加载自有类
     * @param string $cname
     * @param string $action
     * @return {*}
     */
    public static function own_class($cname, $action = "")
    {
        if (in_string($cname, PATH_WEB)) {
            $file = $cname;
        } elseif (in_string($cname, "/")) {
            $file = PATH_APP_NOW . $cname;
        } else {
            $file = PATH_APP_NOW . "include/class/{$cname}";
        }
        return self::loadClass($file, $action);
    }
    /**
     * @description: 加载自有方法
     * @param string $fname
     * @return {*}
     */
    public static function own_func($fname)
    {
        if (in_string($fname, PATH_WEB)) {
            $file = $fname;
        } elseif (in_string($fname, "/")) {
            $file = PATH_APP_NOW . $fname;
        } else {
            $file = PATH_APP_NOW . "include/function/{$fname}";
        }
        return self::loadFun($file);
    }
    /**
     * @description: 加载类
     * @param string $file
     * @param string $action
     * @return {*}
     */
    private static function loadClass($file, $action = "")
    {
        $file = "{$file}.class.php";
        if (is_file($file)) {
            require_once $file;
        } else {
            $file = str_replace(PATH_WEB, '', $file);
            LCMS::X(404, "{$file} 文件不存在");
        }
        if ($action) {
            $cname = end(explode("/", $file));
            $cname = str_replace(".class.php", "", $cname);
            $class = $cname ? new $cname : null;
            if ($action != "new") {
                if (substr($action, 0, 2) != 'do') {
                    LCMS::X(403, "{$action} 方法禁止访问");
                }
                if (method_exists($class, $action)) {
                    call_user_func([$class, $action]);
                } else {
                    LCMS::X(404, "{$action} 方法没有找到");
                }
            }
            return $class;
        }
    }
    /**
     * @description: 加载方法
     * @param string $file
     * @return {*}
     */
    private static function loadFun($file)
    {
        $file = "{$file}.func.php";
        if (is_file($file)) {
            require_once $file;
        } else {
            $file = str_replace(PATH_WEB, '', $file);
            LCMS::X(404, "{$file} 文件不存在");
        }
    }
    /**
     * @description: 加载插件文件
     * @param string $file
     * @return {*}
     */
    private static function loadPlugin($file)
    {
        $file = "{$file}.php";
        if (is_file($file)) {
            require_once $file;
        } else {
            $file = str_replace(PATH_WEB, '', $file);
            LCMS::X(404, "{$file} 文件不存在");
        }
    }
}

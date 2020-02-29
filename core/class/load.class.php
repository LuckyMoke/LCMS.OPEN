<?php
defined('IN_LCMS') or exit('No permission');
class LOAD
{
    private static $mclass = array();
    public static function sys_class($classname, $action = '')
    {
        return self::_load_class(PATH_CORE_CLASS, $classname, $action);
    }
    public static function sys_func($funcname)
    {
        return self::_load_func(PATH_CORE_FUNC, $funcname);
    }
    public static function plugin($pluginname)
    {
        return self::_load_plugin(PATH_CORE_PLUGIN, $pluginname);
    }
    public static function own_class($classname, $action = '')
    {
        if (is_file(PATH_APP_NOW . 'include/class/' . $classname . '.class.php')) {
            return self::_load_class(PATH_APP_NOW . 'include/class/', $classname, $action);
        } else {
            $classdir = self::dir_get($classname);
            return self::_load_class(PATH_APP_NOW . $classdir['dir'], $classdir['file'], $action);
        }
    }
    public static function own_func($funcname)
    {
        if (is_file(PATH_APP_NOW . 'include/function/' . $funcname . '.func.php')) {
            return self::_load_func(PATH_APP_NOW . 'include/function/', $funcname);
        } else {
            $funcdir = self::dir_get($funcname);
            return self::_load_func(PATH_APP_NOW . $funcdir['dir'], $funcdir['file']);
        }
    }
    public static function module($path = '', $modulename = '', $action = '')
    {
        if (!$path) {
            if (!$path) {
                $path = PATH_APP_OWN;
            }
            if (!$modulename) {
                $modulename = L_CLASS;
            }
            if (!$action) {
                $action = L_ACTION;
            }
            if (!$action) {
                $action = 'doindex';
            }
        }
        return self::_load_class($path, $modulename, $action);
    }
    private static function _load_class($path, $classname, $action = '')
    {
        $classname  = str_replace('.class.php', '', $classname);
        $is_myclass = 0;
        if (!@self::$mclass[$classname]) {
            if (is_file($path . $classname . '.class.php')) {
                require_once $path . $classname . '.class.php';
            } else {
                LCMS::X(404, str_replace(PATH_WEB, '', $path) . $classname . '.class.php 文件不存在');
            }
        }
        if ($action) {
            if (@self::$mclass[$classname]) {
                $newclass = self::$mclass[$classname];
            } else {
                if ($is_myclass) {
                    $newclass = new $myclass;
                } else {
                    $newclass = new $classname;
                }
                self::$mclass[$classname] = $newclass;
            }
            if ($action != 'new') {
                if (substr($action, 0, 2) != 'do') {
                    LCMS::X(403, $action . ' 方法禁止访问');
                }
                if (method_exists($newclass, $action)) {
                    call_user_func(array($newclass, $action));
                } else {
                    LCMS::X(404, $action . ' 方法没有找到');
                }
            }
            return $newclass;
        }
    }
    private static function _load_func($path, $funcname)
    {
        $funcname = str_replace('.func.php', '', $funcname);
        if (is_file($path . $funcname . '.func.php')) {
            require_once $path . $funcname . '.func.php';
        } else {
            LCMS::X(404, str_replace(PATH_WEB, '', $path) . $funcname . '.func.php 文件不存在');
        }
    }
    private static function _load_plugin($path, $classname)
    {
        $classname  = str_replace('.php', '', $classname);
        $is_myclass = 0;
        if (!@self::$mclass[$classname]) {
            if (is_file($path . $classname . '.php')) {
                require_once $path . $classname . '.php';
            } else {
                LCMS::X(404, str_replace(PATH_WEB, '', $path) . $classname . '.php 文件不存在');
            }
        }
    }
    private static function dir_get($path)
    {
        $path        = str_replace('\\', '/', $path);
        $paths       = explode('/', $path);
        $dir['file'] = $paths[count($paths) - 1];
        $dir['dir']  = substr($path, 0, strlen($path) - strlen($dir['file']));
        return $dir;
    }
}
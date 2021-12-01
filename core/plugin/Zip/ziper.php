<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-12-13 17:04:20
 * @LastEditTime: 2021-11-29 13:17:37
 * @Description:压缩解压文件
 * @Copyright 2020 运城市盘石网络科技有限公司
 */
class Ziper
{
    protected $ziper;
    protected $isopen;
    /**
     * @description: 初始化
     * @param {*}
     * @return {*}
     */
    public function __construct()
    {
        if (class_exists("ZipArchive")) {
            $this->ziper = new ZipArchive();
        } else {
            LCMS::X(500, "ZipArchive组件未开启");
        }
    }
    /**
     * @description: 压缩文件
     * @param array $fromfiles
     * @param string $zipname
     * @param array|string $jump
     * @return bool
     */
    public function zip($fromfiles, $zipname, $jump = "")
    {
        if ($zipname) {
            $zipname = path_absolute($zipname);
            $zipname = pathinfo($zipname, PATHINFO_EXTENSION) ? $zipname : $zipname . ".zip";
            if (!$this->ziper->open($zipname, ZipArchive::CREATE)) {
                LCMS::X(404, "压缩文件创建失败");
            } else {
                $this->isopen = true;
            }
        }
        $jump = str_replace([
            "\/", "/",
        ], "\/", is_array($jump) ? implode('|', $jump) : $jump);
        foreach ($fromfiles as $fromfile) {
            if (is_array($fromfile)) {
                $fromfile[0] = path_absolute($fromfile[0]);
                if (is_file($fromfile[0])) {
                    $fromfile[1] = $fromfile[1] ?: pathinfo($fromfile[0], PATHINFO_BASENAME);
                    $fromfile[1] = $fromfile[1] ?: str_replace(PATH_WEB, "", $fromfile[0]);
                    $fromfile[1] = ltrim($fromfile[1], '/');
                    $this->ziper->addFile($fromfile[0], $fromfile[1]);
                } elseif (is_dir($fromfile[0])) {
                    $list = traversal_all($fromfile[0], "", $jump);
                    $pre  = str_replace(PATH_WEB, "", $fromfile[0]);
                    $npre = $fromfile[1] ?: $pre;
                    $npre = rtrim(ltrim($npre, '/'), "/") . "/";
                    foreach ($list as $file) {
                        preg_match_all("/^({$jump})/", $file, $match);
                        if ($match && $match[0][0]) {
                            continue;
                        }
                        $nfile = str_replace($pre, "", $file);
                        $this->ziper->addFile(PATH_WEB . $file, $npre . $nfile);
                    }
                }
            }
        }
        return true;
    }
    /**
     * @description: 解压缩
     * @param string $zipname
     * @param string $dir
     * @return bool
     */
    public function unzip($zipname, $dir = "")
    {
        $zipname = path_absolute($zipname);
        if (!is_file($zipname)) {
            LCMS::X(404, "文件不存在");
        }
        if (!$this->ziper->open($zipname)) {
            LCMS::X(404, "打开压缩文件失败");
        } else {
            $this->isopen = true;
        }
        $dir = path_absolute($dir);
        if (!is_dir($dir)) {
            makedir($dir);
        }
        if (!$this->ziper->extractTo($dir)) {
            LCMS::X(500, "文件解压失败");
        }
        return true;
    }
    /**
     * @description: 关闭文件
     * @param {*}
     * @return {*}
     */
    public function __destruct()
    {
        if ($this->isopen) {
            $this->ziper->close();
        }
    }
}

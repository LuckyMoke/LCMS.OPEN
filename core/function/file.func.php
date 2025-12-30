<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-10-10 14:20:59
 * @LastEditTime: 2025-12-24 14:32:30
 * @Description:文件操作方法
 * @Copyright 2020 运城市盘石网络科技有限公司
 */
defined('IN_LCMS') or exit('No permission');
/**
 * @description: 相对路径转绝对路径
 * @param string $path 要转换的路径
 * @return string
 */
function path_absolute($path)
{
    $path = PATH_WEB . str_replace([
        "../", "./", "..\\", ".\\", PATH_WEB,
    ], "", $path);
    $path = in_string($path, [
        "../", "./", "..\\", ".\\",
    ]) ? path_absolute($path) : $path;
    $path = str_replace([
        "\/", "//",
    ], [
        "\\", "/",
    ], $path);
    return is_dir($path) ? path_standard($path) : $path;
}
/**
 * @description: 绝对路径转相对路径
 * @param string $path 要转换的路径
 * @param string $relative 相对路径前缀
 * @return string
 */
function path_relative($path, $relative = "")
{
    $path = str_replace([
        "../", "./", "..\\", ".\\", PATH_WEB,
    ], "", $path);
    if (in_string($path, [
        "../", "./", "..\\", ".\\",
    ])) {
        $path = path_relative($path);
    } else {
        $path = "{$relative}{$path}";
        $path = str_replace([
            "\/", "//",
        ], [
            "\\", "/",
        ], $path);
        return $path;
    }
    return $path;
}
/**
 * @description: 目录结尾加/
 * @param string $path 要转换的路径
 * @return string
 */
function path_standard($path)
{
    $last = substr($path, -1);
    if ($last != "/" && $last != "\\") {
        $path = $path . "/";
    }
    return $path;
}
/**
 * @description: 新建文件夹
 * @param string $dir 要新建的文件夹
 * @return boolean
 */
function makedir($dir)
{
    $dir = path_absolute($dir);
    if (is_dir($dir)) {
        return true;
    } else {
        $dir    = path_relative($dir);
        $dirUrl = PATH_WEB;
        $result = true;
        foreach (explode('/', $dir) as $val) {
            if ($val != null) {
                $dirUrl .= $val . '/';
                if (!is_dir($dirUrl)) {
                    $result = mkdir($dirUrl);
                }
            }
        }
    }
    return $result;
}
/**
 * @description: 复制文件夹
 * @param string $olddir 原文件夹
 * @param string $newdir 复制到的文件夹
 * @param boolean $over 是否覆盖
 * @return boolean
 */
function copydir($olddir, $newdir, $over = true)
{
    $olddir = path_absolute($olddir);
    $newdir = path_absolute($newdir);
    $newdir = path_standard($newdir);
    if (!is_dir($olddir)) {
        return false;
    }
    $files = traversal_one($olddir);
    foreach ($files['file'] as $file) {
        copyfile("{$olddir}{$file}", "{$newdir}{$file}", $over);
    }
    foreach ($files['dir'] as $dir) {
        copydir("{$olddir}{$dir}", "{$newdir}{$dir}", $over);
    }
    return is_dir($newdir);
}
/**
 * @description: 复制文件
 * @param string $oldfile 原文件
 * @param string $newfile 复制后的文件
 * @param boolean $over 是否覆盖
 * @return boolean
 */
function copyfile($oldfile, $newfile, $over = true)
{
    $oldfile = path_absolute($oldfile);
    $newfile = path_absolute($newfile);
    if (!is_file($oldfile)) {
        return false;
    }
    if (is_file($newfile) && !$over) {
        return false;
    }
    makedir(dirname($newfile));
    return copy($oldfile, $newfile);
}
/**
 * @description: 移动文件夹
 * @param string $olddir 原文件夹
 * @param string $newdir 移动到文件夹
 * @param boolean $over 是否覆盖
 * @return boolean
 */
function movedir($olddir, $newdir, $over = true)
{
    $olddir = path_absolute($olddir);
    $newdir = path_absolute($newdir);
    $newdir = path_standard($newdir);
    if (!is_dir($olddir)) {
        return false;
    }
    $files = traversal_one($olddir);
    foreach ($files['file'] as $file) {
        movefile("{$olddir}{$file}", "{$newdir}{$file}", $over);
    }
    foreach ($files['dir'] as $dir) {
        movedir("{$olddir}{$dir}", "{$newdir}{$dir}", $over);
    }
    return rmdir($olddir);
}
/**
 * @description: 移动文件
 * @param string $oldFile 原文件
 * @param string $newfile 移动后的文件
 * @param boolean $over 是否覆盖
 * @return boolean
 */
function movefile($oldfile, $newfile, $over = true)
{
    $oldfile = path_absolute($oldfile);
    $newfile = path_absolute($newfile);
    if (!is_file($oldfile)) {
        return false;
    }
    if (is_file($newfile)) {
        if ($over) {
            delfile($newfile);
        } else {
            return false;
        }
    }
    makedir(dirname($newfile));
    return rename($oldfile, $newfile);
}
/**
 * @description: 删除文件夹
 * @param string $olddir 要删除的文件夹
 * @return boolean
 */
function deldir($olddir)
{
    $olddir = path_absolute($olddir);
    if (!is_dir($olddir)) {
        return false;
    }
    $files = traversal_one($olddir);
    foreach ($files['file'] as $file) {
        delfile("{$olddir}{$file}");
    }
    foreach ($files['dir'] as $dir) {
        deldir("{$olddir}{$dir}");
    }
    return rmdir($olddir);
}
/**
 * @description: 删除文件
 * @param string $file 要删除的文件
 * @return boolean
 */
function delfile($file)
{
    $file = path_absolute($file);
    $file = stristr(PHP_OS, "WIN") ? utf82gbk($file) : $file;
    return is_file($file) ? unlink($file) : false;
}
/**
 * @description: 获取文件的大小
 * @param string $filename 要获取的文件名
 * @param string|null $unit null、GB、MB、KB、B
 * @return string
 */
function getfilesize($filename, $unit = null)
{
    $filename = path_absolute($filename);
    if (is_file($filename)) {
        $filesize = filesize($filename);
        if (!$unit) {
            if ($filesize >= 1073741824) {
                $unit = "GB";
            } elseif ($filesize >= 1048576) {
                $unit = "MB";
            } elseif ($filesize >= 1024) {
                $unit = "KB";
            } else {
                $unit = "B";
            }
            $last = true;
        }
        switch ($unit) {
            case 'GB':
                $filesize = $filesize / 1073741824;
                $filesize = sprintf("%.2f", $filesize);
                break;
            case 'MB':
                $filesize = $filesize / 1048576;
                $filesize = sprintf("%.2f", $filesize);
                break;
            case 'B':
                $filesize = sprintf("%.2f", $filesize);
                break;
            default:
                $filesize = $filesize / 1024;
                $filesize = sprintf("%.2f", $filesize);
                break;
        }
        return $filesize . ($last ? $unit : "");
    }
}
/**
 * @description: 获取文件的后缀名
 * @param string $filename 要获取的文件名
 * @return string
 */
function getfileable($filename)
{
    $filename = path_absolute($filename);
    $lastsite = strrpos($filename, '.');
    $fileable = substr($filename, $lastsite + 1);
    return $fileable;
}
/**
 * @description: 解压ZIP
 * @param string $zipname 要解压的zip压缩文件
 * @param string $dir 解压到目录
 * @return boolean
 */
function unzipfile($zipname, $dir = "")
{
    require_once PATH_CORE_PLUGIN . "Zip/ziper.php";
    $ziper = new Ziper();
    $ziper->unzip($zipname, $dir);
    return true;
}
/**
 * @description: 压缩为ZIP
 * @param array $fromfile 要压缩的文件夹和文件
 * @param string $zipname 压缩后的文件路径需要有后缀
 * @param string $jump 去除的目录名
 * @param boolean $over 是否覆盖（true：覆盖，false：不覆盖）默认覆盖
 * @return boolean
 */
function zipfile($fromfile, $zipname, $jump = "", $over = true)
{
    $zipname = path_absolute($zipname);
    if ($over) {
        delfile($zipname);
    }
    require_once PATH_CORE_PLUGIN . "Zip/ziper.php";
    $ziper = new Ziper();
    $ziper->zip($fromfile, $zipname, $jump);
    return true;
}
/**
 * @description: 验证文件夹是否有写权限
 * @param string $dir 要检测的文件夹
 * @return boolean
 */
function getdirpower($dir)
{
    $dir = path_absolute($dir);
    if (is_dir($dir) && is_writable($dir)) {
        return true;
    }
    return false;
}
/**
 * @description: 验证文件是否有写权限
 * @param string $file 要检测的文件
 * @return boolean
 */
function getfilepower($file)
{
    $file = path_absolute($file);
    if (is_file($file) && is_writable($file)) {
        return true;
    }
}
/**
 * @description: 修改文件夹权限
 * @param string $chdir 要修改的文件夹
 * @param int $power 修改后的文件权限 777、555
 * @return boolean
 */
function chmoddir($chdir, $power)
{
    $chdir = path_absolute($chdir);
    if (!is_dir($chdir) || !chmod($chdir, $power)) {
        return false;
    }
    $files = traversal_one($chdir);
    foreach ($files['file'] as $file) {
        if (!chmodfile("{$chdir}{$file}", $power)) {
            return false;
        }
    }
    foreach ($files['dir'] as $dir) {
        chmoddir("{$chdir}{$dir}", $power);
    }
    return true;
}
/**
 * @description: 修改文件权限
 * @param string $file 要修改的文件
 * @param int $power 修改后的文件权限 777、555
 * @return boolean
 */
function chmodfile($file, $power)
{
    $file = path_absolute($file);
    if (is_file($file)) {
        return chmod($file, $power);
    }
}
/**
 * @description: 遍历指定文件夹下文件和目录，只遍历一层
 * @param string $dir 遍历路径
 * @param string $mime 筛选文件后缀 - 正则 例如：\.(jpg|png)
 * @param string $jump 跳过文件 - 正则
 * @return array
 */
function traversal_one($dir, $mime = null, $jump = null)
{
    $dir = path_absolute($dir);
    if (!is_dir($dir)) {
        return [];
    }
    $iterator = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
    foreach ($iterator as $finfo) {
        $file = $finfo->getFileName();
        if (stristr(PHP_OS, "WIN")) {
            $file = gbk2utf8($file);
        }
        if ($jump && preg_match("/{$jump}/", $file)) {
            continue;
        }
        if ($finfo->isFile()) {
            if ($mime === null) {
                $files['file'][]  = $file;
                $files['orgin'][] = $finfo;
                continue;
            }
            if (preg_match("/{$mime}$/i", $file)) {
                $files['file'][]  = $file;
                $files['orgin'][] = $finfo;
            }
        } else {
            $files['dir'][]   = $file;
            $files['orgin'][] = $finfo;
        }
    }
    return $files ?: [];
}
/**
 * @description: 遍历文件夹下所有文件，多层遍历
 * @param string $dir 遍历路径
 * @param string $mime 筛选文件后缀 - 正则 例如：\.(jpg|png)
 * @param string $jump 跳过文件 - 正则
 * @return array
 */
function traversal_all($dir, $mime = null, $jump = null)
{

    $dir = path_absolute($dir);
    if (!is_dir($dir)) {
        return [];
    }
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
    foreach ($iterator as $finfo) {
        if ($finfo->isFile()) {
            $file = $finfo->getPathname();
            $file = path_relative($file);
            $file = str_replace("\\", "/", $file);
            if (stristr(PHP_OS, "WIN")) {
                $file = gbk2utf8($file);
            }
            if ($jump && preg_match("/{$jump}/", $file)) {
                continue;
            }
            if ($mime === null) {
                $files[] = $file;
                continue;
            }
            if (preg_match("/{$mime}$/i", $file)) {
                $files[] = $file;
            }
        }
    }
    return $files ?: [];
}
/**
 * @description: 读取csv文件
 * @param string $file
 * @param function $success
 * @param function $error
 * @return array|null
 */
function read_csv($file, $success = false, $error = false)
{
    $file = path_absolute($file);
    if (is_file($file)) {
        $fp   = fopen($file, "r");
        $keys = [];
        $i    = 0;
        while (($list = fgetcsv($fp)) !== false) {
            foreach ($list as $index => $li) {
                $list[$index] = gbk2utf8($li);
            }
            if ($i == 0) {
                $list[0] = ltrim($list[0], "\XEF\XBB\XBF");
                $keys    = $list;
            } else {
                if (count($keys) === count($list)) {
                    $csv = array_combine($keys, $list);
                    if ($success) {
                        if ($success($csv) === false) {
                            break;
                        }
                    } else {
                        $csvs[$i - 1] = $csv;
                    }
                } else {
                    $iserror = true;
                }
            }
            $i++;
        }
        fclose($fp);
        $error && $error($iserror ? true : false);
        return $csvs ?: [];
    }
}

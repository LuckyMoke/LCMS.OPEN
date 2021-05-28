<?php
/*
 * @Author: 小小酥很酥
 * @Date: 2020-10-10 14:20:59
 * @LastEditTime: 2021-05-27 19:37:02
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
    $path = str_replace("\/", "\\", $path);
    return is_dir($path) ? path_standard($path) : $path;
}
/**
 * @description: 绝对路径转相对路径
 * @param string $path 要转换的路径
 * @param string $relative 相对路径前缀
 * @return string
 */
function path_relative($path, $relative = "../")
{
    return $relative . str_replace([
        "../", "./", "..\\", ".\\", PATH_WEB,
    ], "", $path);
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
    @clearstatcache();
    if (is_dir($dir)) {
        return true;
    } else {
        $dir    = path_relative($dir, "");
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
 * @param string $oldDir 原文件夹
 * @param string $targetDir 复制后的文件夹名
 * @param boolean $overWrite 是否覆盖原文件夹 默认覆盖
 * @return boolean
 */
function copydir($oldDir, $targetDir, $overWrite = true)
{
    $oldDir = path_absolute($oldDir);
    @clearstatcache();
    if (is_dir($oldDir)) {
        makedir($targetDir);
        $targetDir = path_absolute($targetDir);
        if ($resource = opendir($oldDir)) {
            while (($filename = readdir($resource)) !== false) {
                $filename = gbk2utf8($filename);
                if ($filename == '.' || $filename == '..') {
                    continue;
                } elseif (!is_dir($oldDir . $filename)) {
                    copyfile($oldDir . $filename, $targetDir . $filename, $overWrite);
                } else {
                    copydir($oldDir . $filename, $targetDir . $filename, $overWrite);
                }
            }
            closedir($resource);
            @clearstatcache();
            return is_dir($targetDir);
        }
    }
}
/**
 * @description: 复制文件
 * @param string $oldFile 原文件
 * @param string $targetFile 复制后的文件名
 * @param boolean $overWrite 是否覆盖原文件 默认覆盖
 * @return boolean
 */
function copyfile($oldFile, $targetFile, $overWrite = true)
{
    $oldFile    = path_absolute($oldFile);
    $targetFile = path_absolute($targetFile);
    @clearstatcache();
    if (is_file($oldFile)) {
        if (!is_file($targetFile) || $overWrite) {
            makedir(dirname($targetFile));
            return copy($oldFile, $targetFile);
        }
    }
}
/**
 * @description: 移动文件夹
 * @param string $oldDir 原文件夹
 * @param string $targetDir 移动后的文件夹名
 * @param boolean $overWrite 是否覆盖已有文件夹 默认覆盖
 * @return boolean
 */
function movedir($oldDir, $targetDir, $overWrite = true)
{
    $oldDir = path_absolute($oldDir);
    @clearstatcache();
    if (is_dir($oldDir)) {
        makedir($targetDir);
        $targetDir = path_absolute($targetDir);
        if ($resource = opendir($oldDir)) {
            while (($filename = readdir($resource)) !== false) {
                $filename = gbk2utf8($filename);
                if ($filename == '.' || $filename == '..') {
                    continue;
                } elseif (!is_dir($oldDir . $filename)) {
                    movefile($oldDir . $filename, $targetDir . $filename, $overWrite);
                } else {
                    movedir($oldDir . $filename, $targetDir . $filename, $overWrite);
                }
            }
            closedir($resource);
            return rmdir($oldDir);
        }
    }
}
/**
 * @description: 移动文件
 * @param string $oldFile 原文件
 * @param string $targetFile 移动后的文件名
 * @param boolean $overWrite 是否覆盖已有文件 默认覆盖
 * @return boolean
 */
function movefile($oldFile, $targetFile, $overWrite = true)
{
    $oldFile    = path_absolute($oldFile);
    $targetFile = path_absolute($targetFile);
    @clearstatcache();
    if (is_file($oldFile)) {
        if (is_file($targetFile) && $overWrite == false) {
            return false;
        } else if (is_file($targetFile) && $overWrite == true) {
            delfile($targetFile);
        }
        makedir(dirname($targetFile));
        return rename($oldFile, $targetFile);
    }
}
/**
 * @description: 删除文件夹
 * @param string $fileDir 要删除的文件夹
 * @param boolean $type false：全删，true：只删一层
 * @return boolean
 */
function deldir($fileDir, $type = false)
{
    $fileDir = path_absolute($fileDir);
    @clearstatcache();
    if (is_dir($fileDir)) {
        if ($resource = opendir($fileDir)) {
            while (($filename = readdir($resource)) !== false) {
                $filename = gbk2utf8($filename);
                if ($filename == '.' || $filename == '..') {
                    continue;
                } elseif (!is_dir($fileDir . $filename)) {
                    delfile($fileDir . $filename);
                } else {
                    deldir($fileDir . $filename);
                }
            }
            closedir($resource);
        }
        @clearstatcache();
        return !$type ? rmdir($fileDir) : true;
    }
}
/**
 * @description: 删除文件
 * @param string $fileUrl 要删除的文件
 * @return boolean
 */
function delfile($fileUrl)
{
    $fileUrl = path_absolute($fileUrl);
    $fileUrl = stristr(PHP_OS, "WIN") ? utf82gbk($fileUrl) : $fileUrl;
    @clearstatcache();
    return is_file($fileUrl) ? unlink($fileUrl) : false;
}
/**
 * @description: 获取文件的大小
 * @param string $filename 要获取的文件名
 * @param string|null $unit 返回文件的大小
 * @return string
 */
function getfilesize($filename, $unit = null)
{
    $filename = path_absolute($filename);
    @clearstatcache();
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
 * @param boolean $overWrite 是否覆盖（true：覆盖，false：不覆盖）默认覆盖
 * @return boolean
 */
function zipfile($fromfile, $zipname, $jump = "", $overWrite = true)
{
    $zipname = path_absolute($zipname);
    if ($overWrite) {
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
    @clearstatcache();
    if (is_dir($dir)) {
        $file_hd = @fopen($dir . '/test.txt', 'w');
        $flag    = $file_hd ? true : false;
        @fclose($file_hd);
        @unlink($dir . '/test.txt');
        return $flag;
    }
}
/**
 * @description: 验证文件是否有写权限
 * @param string $file 要检测的文件
 * @return boolean
 */
function getfilepower($file)
{
    $file = path_absolute($file);
    @clearstatcache();
    if (is_file($file) && is_writable($file)) {
        return true;
    }
}
/**
 * @description: 修改文件夹权限
 * @param string $dir 要修改的文件夹
 * @param int $power 修改后的文件权限 777、555
 * @return boolean
 */
function modifydirpower($dir, $power)
{
    $dir = path_absolute($dir);
    $dir = path_standard($dir);
    @clearstatcache();
    if (is_dir($dir) && @chmod($dir, $power)) {
        if ($resource = opendir($dir)) {
            while (($filename = readdir($resource)) !== false) {
                $filename = gbk2utf8($filename);
                if ($filename == '.' || $filename == '..') {
                    continue;
                } elseif (!is_dir($dir . $filename)) {
                    modifyfilepower($dir . $filename, $power);
                } else {
                    modifydirpower($dir . $filename, $power);
                }
            }
            closedir($resource);
        }
        return true;
    }
}
/**
 * @description: 修改文件权限
 * @param string $file 要修改的文件
 * @param int $power 修改后的文件权限 777、555
 * @return boolean
 */
function modifyfilepower($file, $power)
{
    $file = path_absolute($file);
    @clearstatcache();
    if (is_file($file)) {
        return chmod($file, $power);
    }
}
/**
 * @description: 遍历指定文件夹下文件，只遍历一层
 * @param string $jkdir
 * @param string $suffix
 * @param string|null $jump
 * @return array
 */
function traversal_one($jkdir, $suffix = '[A-Za-z]*', $jump = null)
{
    $jkdir = $jkdir == '.' || $jkdir == './' ? "" : $jkdir;
    $jkdir = path_absolute($jkdir);
    if ($resource = opendir($jkdir)) {
        while (($file = readdir($resource)) !== false) {
            $file     = gbk2utf8($file);
            $filename = $jkdir . $file;
            if (is_dir($filename) && $file != '.' && $file != '..' && $file != './..') {
                if ($jump != null) {
                    if (preg_match_all("/^({$jump})/", str_replace($jkdir, '', $filename), $out)) {
                        continue;
                    }
                }
                $filenamearray['dir'][] = str_replace($jkdir, '', $filename);
            } else {
                if ($file != '.' && $file != '..' && $file != './..' && preg_match_all("/\.({$suffix})/i", $filename, $out)) {
                    if (stristr(PHP_OS, "WIN")) {
                        $file = gbk2utf8($file);
                    }
                    $filenamearray['file'][] = $file;
                }
            }
        }
        @closedir($resource);
        return $filenamearray;
    }
}
/**
 * @description: 遍历文件夹下所有文件，多层遍历
 * @param string $jkdir
 * @param string $suffix
 * @param string $jump
 * @param array $filenamearray
 * @return array
 */
function traversal_all($jkdir, $suffix = '[A-Za-z]*', $jump = null, &$filenamearray = array())
{
    $jkdir = $jkdir == '.' || $jkdir == './' ? "" : $jkdir;
    $jkdir = path_absolute($jkdir);
    if ($resource = opendir($jkdir)) {
        while (($file = readdir($resource)) !== false) {
            $file     = gbk2utf8($file);
            $filename = $jkdir . $file;
            if (is_dir($filename) && $file != '.' && $file != '..' && $file != './..') {
                if ($jump != null) {
                    if (preg_match_all("/^({$jump})/", str_replace(PATH_WEB, '', $filename), $out)) {
                        continue;
                    }
                }
                traversal_all($filename, $suffix, $jump, $filenamearray);
            } else {
                if ($file != '.' && $file != '..' && $file != './..' && preg_match_all("/\.({$suffix})/i", $filename, $out)) {
                    if (stristr(PHP_OS, "WIN")) {
                        $filename = gbk2utf8($filename);
                    }
                    $filenamearray[] = str_replace(PATH_WEB, '', $filename);
                }
            }
        }
        @closedir($resource);
        return $filenamearray;
    }
}
/**
 * @description: 读取csv文件
 * @param string $file
 * @return array|null
 */
function read_csv($file)
{
    $file = path_absolute($file);
    if (is_file($file)) {
        $file = fopen($file, "r");
        $keys = [];
        $i    = 0;
        while (($arr = fgetcsv($file)) !== false) {
            if ($i == 0) {
                $keys = $arr;
            } else {
                foreach ($keys as $index => $key) {
                    $csv[$i - 1][$key] = $arr[$index];
                }
            }
            $i++;
        }
        fclose($file);
        return gbk2utf8($csv);
    }
}

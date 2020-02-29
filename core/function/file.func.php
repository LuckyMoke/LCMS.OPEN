<?php
defined('IN_LCMS') or exit('No permission');
/**
 * [path_absolute 相对路径转绝对路径]
 * @param  [type] $path [要转换的路径]
 * @return [type]       [返回转换好的路径]
 */
function path_absolute($path)
{
    if (substr($path, 0, strlen(PATH_WEB)) == PATH_WEB) {
        $path = $path;
    } else {
        $path = PATH_WEB . str_replace(array("../", "./", PATH_WEB), "", $path);
    }
    $path = is_dir($path) ? path_standard($path) : $path;
    return $path;
}
/**
 * [path_relative 绝对路径转相对路径]
 * @param  [type] $path     [要转换的路径]
 * @param  string $relative [相对路径前缀]
 * @return [type]           [返回转换好的路径]
 */
function path_relative($path, $relative = "../")
{
    return $relative . str_replace(array("../", "./", PATH_WEB), "", $path);
}
/**
 * [path_standard 目录结尾加/]
 * @param  [type] $path [要转换的路径]
 * @return [type]       [返回转换好的路径]
 */
function path_standard($path)
{
    if (substr($path, -1, 1) != "/") {
        $path = $path . "/";
    }
    return $path;
}
/**
 * [makedir 新建文件夹]
 * @param  [type] $dir [要新建的文件夹]
 * @return [type]      [文件夹存在则返回真，否侧新建文件夹，并返回是否新建文件夹成功]
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
 * [copydir 复制文件夹]
 * @param  [type]  $oldDir    [原文件夹]
 * @param  [type]  $targetDir [复制后的文件夹名]
 * @param  boolean $overWrite [是否覆盖原文件夹 默认覆盖]
 * @return [type]             [复制成功返回true，否则返回false]
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
 * [copyfile 复制文件]
 * @param  [type]  $oldFile    [原文件]
 * @param  [type]  $targetFile [复制后的文件名]
 * @param  boolean $overWrite  [是否覆盖原文件 默认覆盖]
 * @return [type]              [复制成功返回true，否则返回false]
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
 * [movedir 移动文件夹]
 * @param  [type]  $oldDir    [原文件夹]
 * @param  [type]  $targetDir [移动后的文件夹名]
 * @param  boolean $overWrite [是否覆盖已有文件夹 默认覆盖]
 * @return [type]             [移动成功返回true，否则返回false]
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
 * [movefile 移动文件]
 * @param  [type]  $oldFile    [原文件]
 * @param  [type]  $targetFile [移动后的文件名]
 * @param  boolean $overWrite  [是否覆盖已有文件 默认覆盖]
 * @return [type]              [移动成功返回true，否则返回false]
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
 * [deldir 删除文件夹]
 * @param  [type]  $fileDir [要删除的文件夹]
 * @param  boolean $type    [false：全删，true：只删一层]
 * @return [type]           [删除成功返回true，否则返回false]
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
 * [delfile 删除文件]
 * @param  [type] $fileUrl [要删除的文件]
 * @return [type]          [删除成功返回true，否则返回false]
 */
function delfile($fileUrl)
{
    $fileUrl = path_absolute($fileUrl);
    $fileUrl = stristr(PHP_OS, "WIN") ? utf82gbk($fileUrl) : $fileUrl;
    @clearstatcache();
    return is_file($fileUrl) ? unlink($fileUrl) : false;
}
/**
 * [getfilesize 获取文件的大小]
 * @param  [type] $filename [要获取的文件名]
 * @return [type]           [返回文件的大小]
 */
function getfilesize($filename, $unit = "KB")
{
    $filename = path_absolute($filename);
    @clearstatcache();
    if (is_file($filename)) {
        $filesize = filesize($filename);
        switch ($unit) {
            case 'GB':
                $filesize = $filesize / (1024 * 1024 * 1024);
                $filesize = sprintf("%.2f", $filesize);
                break;
            case 'MB':
                $filesize = $filesize / (1024 * 1024);
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
        return $filesize;
    }
}
/**
 * [getfileable 获取文件的后缀名]
 * @param  [type] $filename [要获取的文件名]
 * @return [type]           [返回文件的后缀名]
 */
function getfileable($filename)
{
    $filename = path_absolute($filename);
    $lastsite = strrpos($filename, '.');
    $fileable = substr($filename, $lastsite + 1);
    return $fileable;
}
/**
 * [unzipfile 解压ZIP]
 * @param  [type] $file        [要解压的zip压缩文件]
 * @param  string $destination [解压后的文件名（默认为解压前的文件名去掉zip后缀）]
 * @return [type]              [解压成功返回true，否则返回false]
 */
function unzipfile($file, $destination = '')
{
    $file = path_absolute($file);
    @clearstatcache();
    if (is_file($file)) {
        $destination = $destination != "" ? $destination : dirname($file);
        $destination = path_absolute($destination);
        require_once PATH_CORE_PLUGIN . "Zip/pclzip.php";
        $PclZip = new PclZip($file);
        $result = $PclZip->extract(
            PCLZIP_OPT_PATH, $destination, PCLZIP_OPT_REPLACE_NEWER
        );
        return $result == 0 ? false : true;
    }
}
/**
 * [zipfile 压缩为ZIP]
 * @param  [type]  $filelist    [要压缩的文件夹和文件]
 * @param  string  $destination [压缩后的文件路径需要有.zip后缀]
 * @param  string  $remove      [去除的目录名]
 * @param  boolean $overWrite   [是否覆盖已有的文件（true：覆盖，false：不覆盖）默认覆盖]
 * @return [type]               [压缩失败返回false]
 */
function zipfile($filelist, $destination, $remove = "", $overWrite = true)
{
    foreach ($filelist as $index => $file) {
        $filelist[$index] = path_absolute($file);
    }
    $destination = path_absolute($destination);
    if (substr($destination, -4) == ".zip") {
        @clearstatcache();
        if (!is_file($destination) || $overWrite) {
            delfile($destination);
            require_once PATH_CORE_PLUGIN . "Zip/pclzip.php";
            $PclZip = new PclZip($destination);
            $result = $PclZip->create(
                $filelist,
                PCLZIP_OPT_REMOVE_PATH, ($remove != "" ? path_absolute($remove) : PATH_WEB)
            );
            return $result == 0 ? false : true;
        }
    }
}
/**
 * [getdirpower 验证文件夹是否有写权限]
 * @param  [type] $dir [要检测的文件夹]
 * @return [type]      [有可写权限返回true，否则返回false]
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
 * [getfilepower 验证文件是否有写权限]
 * @param  [type] $file [要检测的文件]
 * @return [type]       [有可写权限返回true，否则返回false]
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
 * [modifydirpower 修改文件夹权限]
 * @param  [type] $dir   [要修改的文件夹]
 * @param  [type] $power [修改后的文件权限 777、555]
 * @return [type]        [修改成功返回true，否则返回false]
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
 * [modifyfilepower 修改文件权限]
 * @param  [type] $file  [要修改的文件]
 * @param  [type] $power [修改后的文件权限 777、555]
 * @return [type]        [修改成功返回true，否则返回false]
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
 * [traversal_one 遍历指定文件夹下文件，只遍历一层]
 * @param  [type] $jkdir          [description]
 * @param  string $suffix         [description]
 * @param  [type] $jump           [description]
 * @param  array  &$filenamearray [description]
 * @return [type]                 [description]
 */
function traversal_one($jkdir, $suffix = '[A-Za-z]*', $jump = null)
{
    $jkdir = $jkdir == '.' || $jkdir == './' ? "" : $jkdir;
    $jkdir = path_absolute($jkdir);
    if ($resource = opendir($jkdir)) {
        while (($file = readdir($resource)) !== false) {
            $file     = gbk2utf8($file);
            $filename = $jkdir . $file;
            if (@is_dir($filename) && $file != '.' && $file != '..' && $file != './..') {
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
 * [traversal_all 遍历文件夹下所有文件，多层遍历]
 * @param  [type] $jkdir          [description]
 * @param  string $suffix         [description]
 * @param  [type] $jump           [description]
 * @param  array  &$filenamearray [description]
 * @return [type]                 [description]
 */
function traversal_all($jkdir, $suffix = '[A-Za-z]*', $jump = null, &$filenamearray = array())
{
    $jkdir = $jkdir == '.' || $jkdir == './' ? "" : $jkdir;
    $jkdir = path_absolute($jkdir);
    if ($resource = opendir($jkdir)) {
        while (($file = readdir($resource)) !== false) {
            $file     = gbk2utf8($file);
            $filename = $jkdir . $file;
            if (@is_dir($filename) && $file != '.' && $file != '..' && $file != './..') {
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
 * [read_csv 读取csv文件]
 * @param  [type] $file [description]
 * @return [type]       [description]
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

<?php
defined('IN_LCMS') or exit('No permission');
load::sys_class('adminbase');
class gallery extends adminbase
{
    public function __construct()
    {
        global $_L;
        parent::__construct();
        $this->uploadtrue = PATH_UPLOAD . $_L['ROOTID'] . "/images/";
        $this->uploadabs  = "../upload/" . $_L['ROOTID'] . "/images/";
    }
    public function doindex()
    {
        global $_L;
        require LCMS::template("own/gallery");
    }
    public function dodirlist()
    {
        global $_L;
        $list = traversal_one($this->uploadtrue);
        rsort($list['dir'], SORT_NUMERIC);
        $list['total'] = count($list['dir']);
        echo json_encode_ex($list);
    }
    public function dofilelist()
    {
        global $_L;
        $dir  = $this->uploadtrue . $_L['form']['dir'];
        $list = traversal_one($dir);
        rsort($list['file'], SORT_NUMERIC);
        foreach ($list['file'] as $key => $val) {
            $size               = getimagesize($dir . "/" . $val);
            $list['file'][$key] = array(
                "name" => $val,
                "src"  => $this->uploadabs . $_L['form']['dir'] . "/" . $val,
                "size" => $size[0] . "×" . $size[1],
            );
        }
        $list['total'] = count($list['file']);
        echo json_encode_ex($list);
    }
}

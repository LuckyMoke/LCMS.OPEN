<?php
defined('IN_LCMS') or exit('No permission');
load::plugin("PHPMailer/Exception");
load::plugin("PHPMailer/PHPMailer");
load::plugin("PHPMailer/SMTP");
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class EAMIL
{
    public static $lcms = "";
    public static function cfg()
    {
        global $_L;
        $plugin = LCMS::config(array(
            "type" => "sys",
            "name" => "config",
            "cate" => "plugin",
            "lcms" => self::$lcms ? self::$lcms : $_L['ROOTID'],
        ));
        $config = $plugin['email'];
        return $config;
    }
    public static function send($para)
    {
        global $_L;
        $config = self::cfg();
        $config = array(
            "fromname" => $para['fromname'] ? $para['fromname'] : $config['fromname'],
            "from"     => $para['from'] ? $para['from'] : $config['from'],
            "pass"     => $para['pass'] ? $para['pass'] : $config['pass'],
            "smtp"     => $para['smtp'] ? $para['smtp'] : $config['smtp'],
            "ssl"      => $para['ssl'] ? $para['ssl'] : $config['ssl'],
            "port"     => $para['port'] ? $para['port'] : $config['port'],
        );
        $email = new PHPMailer(true);
        $email->setLanguage('zh_cn', PATH_CORE_PLUGIN . 'PHPMailer/language/');
        $email->SMTPDebug = 0; //是否开启DEBUG模式
        $email->isSMTP(); //使用SMTP发送邮件
        $email->Host       = $config['smtp']; //SMTP服务器地址
        $email->SMTPAuth   = true; //SMTP验证
        $email->Username   = $config['from']; //邮箱地址
        $email->Password   = $config['pass']; //邮箱密码
        $email->SMTPSecure = $config['ssl'] ? 'ssl' : 'tls'; //TLS还是SSL
        $email->Port       = $config['port']; //端口号
        $email->Timeout    = 30; //超时设置
        try {
            //邮箱设置
            $email->setFrom($config['from'], $config['fromname']);
            $email->addAddress($para['to'], $para['toname']);
            //附件设置 可多个
            // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');
            // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');
            //邮件内容
            $email->isHTML(true); //以HTML方式发送
            $email->Subject = $para['subject'] ? $para['subject'] : "邮件主题"; //邮件主题
            $email->Body    = $para['body'] ? $para['body'] : "邮件内容"; //邮件内容
            // $email->AltBody = $para['altbody']; //不支持html的邮件显示
            $email->send();
            return array("code" => 1, "msg" => "发送成功");
        } catch (Exception $e) {
            return array("code" => 0, "msg" => $email->ErrorInfo);
        }
    }
}

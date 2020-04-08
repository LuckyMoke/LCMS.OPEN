<?php
defined('IN_LCMS') or exit('No permission');
load::plugin("PHPMailer/Exception");
load::plugin("PHPMailer/PHPMailer");
load::plugin("PHPMailer/SMTP");
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class EMAIL
{
    public static function cfg()
    {
        global $_L;
        $config = LCMS::config([
            "type" => "sys",
            "name" => "config",
            "cate" => "plugin",
        ]);
        return $config['email'];
    }
    public static function send($para)
    {
        global $_L;
        $config = array_merge(self::cfg(), $para);
        $email  = new PHPMailer(true);
        $email->setLanguage('zh_cn', PATH_CORE_PLUGIN . 'PHPMailer/language/');
        $email->SMTPDebug = 0;
        $email->isSMTP();
        $email->Host       = $config['smtp'];
        $email->SMTPAuth   = true;
        $email->Username   = $config['from'];
        $email->Password   = $config['pass'];
        $email->SMTPSecure = $config['ssl'] ? 'ssl' : 'tls';
        $email->Port       = $config['port'];
        $email->Timeout    = 30;
        try {
            $email->setFrom($config['from'], $config['fromname']);
            $email->addAddress($config['to'], $config['toname']);
            if ($config['attachment']) {
                foreach ($config['attachment'] as $key => $val) {
                    $mail->addAttachment($val['path'], $val['title']);
                }
            }
            $email->isHTML(true);
            $email->Subject = $config['subject'] ? $config['subject'] : "邮件主题";
            $email->Body    = $config['body'] ? $config['body'] : "邮件内容";
            $email->send();
            return array("code" => 1, "msg" => "发送成功");
        } catch (Exception $e) {
            return array("code" => 0, "msg" => $email->ErrorInfo);
        }
    }
}

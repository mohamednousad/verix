<?php
namespace Verix\Core\Mail;
use Verix\Core\Support\Env;
final class Mailer {
    public function send(string $to,string $subject,string $html,string $text=''): bool {
        if(!Env::bool('MAIL_ENABLED',false)) return false;
        $root=dirname(__DIR__,2); $path=$root.'/'.trim((string)Env::get('PHPMAILER_PATH','vendor/PHPMailer/src'),'/');
        foreach(['Exception.php','PHPMailer.php','SMTP.php'] as $f){ $file=$path.'/'.$f; if(is_file($file)) require_once $file; }
        if(!class_exists('PHPMailer\\PHPMailer\\PHPMailer')) throw new \RuntimeException('PHPMailer not found.');
        $m=new \PHPMailer\PHPMailer\PHPMailer(true); $m->isSMTP(); $m->Host=(string)Env::get('MAIL_HOST',''); $m->Port=Env::int('MAIL_PORT',587); $m->SMTPAuth=true;
        $m->Username=(string)Env::get('MAIL_USERNAME',''); $m->Password=(string)Env::get('MAIL_PASSWORD',''); $enc=(string)Env::get('MAIL_ENCRYPTION','tls'); if($enc!=='')$m->SMTPSecure=$enc;
        $m->setFrom((string)Env::get('MAIL_FROM_ADDRESS',''),(string)Env::get('MAIL_FROM_NAME','Verix')); $m->addAddress($to); $m->isHTML(true); $m->Subject=$subject; $m->Body=$html; $m->AltBody=$text?:strip_tags($html); $m->send(); return true;
    }
}

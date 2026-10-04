<?php
namespace Verix\Core\Security;
use Verix\Core\Support\Env;
final class Cors {
    public static function apply(): void {
        $allowed=Env::csv('CORS_ALLOWED_ORIGINS',['*']); $origin=$_SERVER['HTTP_ORIGIN'] ?? '';
        $allow=''; if(in_array('*',$allowed,true)) $allow='*'; elseif($origin && in_array($origin,$allowed,true)) $allow=$origin;
        if($allow!=='') header('Access-Control-Allow-Origin: '.$allow);
        header('Vary: Origin');
        header('Access-Control-Allow-Methods: '.implode(', ',Env::csv('CORS_ALLOWED_METHODS',['GET','POST','PUT','PATCH','DELETE','OPTIONS'])));
        header('Access-Control-Allow-Headers: '.implode(', ',Env::csv('CORS_ALLOWED_HEADERS',['Content-Type','Authorization'])));
        if(Env::bool('CORS_ALLOW_CREDENTIALS',false) && $allow!=='*') header('Access-Control-Allow-Credentials: true');
        if(($_SERVER['REQUEST_METHOD']??'GET')==='OPTIONS'){ http_response_code(204); exit; }
    }
}

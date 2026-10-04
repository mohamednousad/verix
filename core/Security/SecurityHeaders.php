<?php
namespace Verix\Core\Security;
use Verix\Core\Support\Env;
final class SecurityHeaders {
    public static function apply(): void {
        if(Env::bool('SECURITY_CONTENT_TYPE_NOSNIFF',true)) header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: '.(string)Env::get('SECURITY_FRAME_OPTIONS','DENY'));
        header('Referrer-Policy: '.(string)Env::get('SECURITY_REFERRER_POLICY','no-referrer'));
        header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
        if(Env::bool('SECURITY_HSTS',false) && (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off')) header('Strict-Transport-Security: max-age='.Env::int('SECURITY_HSTS_MAX_AGE',31536000).'; includeSubDomains');
    }
}

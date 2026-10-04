<?php
namespace Verix\Core\Auth;
use Verix\Core\Support\Env;
final class JWT {
    private static function b64e(string $s): string { return rtrim(strtr(base64_encode($s),'+/','-_'),'='); }
    private static function b64d(string $s): string { $p=strlen($s)%4; if($p)$s.=str_repeat('=',4-$p); return base64_decode(strtr($s,'-_','+/')) ?: ''; }
    public static function encode(array $claims,int $ttl): string {
        $now=time(); $claims+=['iat'=>$now,'exp'=>$now+$ttl,'iss'=>(string)Env::get('JWT_ISSUER','verix'),'jti'=>bin2hex(random_bytes(16))];
        $h=self::b64e(json_encode(['alg'=>'HS256','typ'=>'JWT'],JSON_UNESCAPED_SLASHES));
        $p=self::b64e(json_encode($claims,JSON_UNESCAPED_SLASHES));
        $sig=self::b64e(hash_hmac('sha256',"{$h}.{$p}",(string)Env::get('JWT_SECRET',''),true));
        return "{$h}.{$p}.{$sig}";
    }
    public static function decode(string $jwt): array {
        $parts=explode('.',$jwt); if(count($parts)!==3) throw new \RuntimeException('Invalid token.');
        [$h,$p,$s]=$parts; $expected=self::b64e(hash_hmac('sha256',"{$h}.{$p}",(string)Env::get('JWT_SECRET',''),true));
        if(!hash_equals($expected,$s)) throw new \RuntimeException('Invalid token signature.');
        $payload=json_decode(self::b64d($p),true); if(!is_array($payload)) throw new \RuntimeException('Invalid token payload.');
        if(($payload['exp']??0)<time()) throw new \RuntimeException('Token expired.');
        if(($payload['iss']??'')!==(string)Env::get('JWT_ISSUER','verix')) throw new \RuntimeException('Invalid token issuer.');
        return $payload;
    }
}

<?php
namespace Verix\Core\Security;
use Verix\Core\Support\Env;
final class Encryption {
    private static function key(): string { return hash('sha256',(string)Env::get('APP_KEY',''),true); }
    public static function encrypt(string $plain): string {
        $iv=random_bytes(12); $tag=''; $cipher=openssl_encrypt($plain,'aes-256-gcm',self::key(),OPENSSL_RAW_DATA,$iv,$tag);
        if($cipher===false) throw new \RuntimeException('Encryption failed.');
        return rtrim(strtr(base64_encode($iv.$tag.$cipher),'+/','-_'),'=');
    }
    public static function decrypt(string $encoded): string {
        $raw=base64_decode(strtr($encoded,'-_','+/'),true); if($raw===false||strlen($raw)<28) throw new \RuntimeException('Invalid encrypted value.');
        $iv=substr($raw,0,12);$tag=substr($raw,12,16);$cipher=substr($raw,28);
        $plain=openssl_decrypt($cipher,'aes-256-gcm',self::key(),OPENSSL_RAW_DATA,$iv,$tag); if($plain===false) throw new \RuntimeException('Decryption failed.'); return $plain;
    }
}

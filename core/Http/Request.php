<?php
namespace Verix\Core\Http;
final class Request {
    private array $json=[];
    private array $attributes=[];
    public function __construct(){
        $raw=file_get_contents('php://input') ?: '';
        $decoded=json_decode($raw,true);
        if (is_array($decoded)) $this->json=$decoded;
    }
    public function method(): string { return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET'); }
    public function path(): string {
        $path=parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        return '/' . trim($path,'/');
    }
    public function input(?string $key=null, mixed $default=null): mixed {
        $data=array_merge($_GET,$_POST,$this->json);
        return $key===null ? $data : ($data[$key] ?? $default);
    }
    public function header(string $name, mixed $default=null): mixed {
        $key='HTTP_'.strtoupper(str_replace('-','_',$name));
        if (strtolower($name)==='content-type') $key='CONTENT_TYPE';
        if (strtolower($name)==='authorization' && isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) return $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        return $_SERVER[$key] ?? $default;
    }
    public function bearerToken(): ?string {
        $h=(string)$this->header('Authorization','');
        return preg_match('/^Bearer\s+(.+)$/i',$h,$m) ? trim($m[1]) : null;
    }
    public function ip(): string { return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'; }
    public function file(string $key): ?array { return isset($_FILES[$key]) && is_array($_FILES[$key]) ? $_FILES[$key] : null; }
    public function files(): array { return $_FILES; }
    public function setAttribute(string $key,mixed $value): void { $this->attributes[$key]=$value; }
    public function attribute(string $key,mixed $default=null): mixed { return $this->attributes[$key] ?? $default; }
}

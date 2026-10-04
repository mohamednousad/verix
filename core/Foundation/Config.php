<?php
namespace Verix\Core\Foundation;
use Verix\Core\Support\Env;
final class Config {
    public static function get(string $key, mixed $default=null): mixed { return Env::get($key,$default); }
    public static function bool(string $key, bool $default=false): bool { return Env::bool($key,$default); }
    public static function int(string $key, int $default=0): int { return Env::int($key,$default); }
}

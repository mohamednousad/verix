<?php
namespace Verix\Core\Cache;
final class NullCache implements Cache { public function get(string $key,mixed $default=null): mixed{return $default;} public function put(string $key,mixed $value,int $ttl=300): void{} public function forget(string $key): void{} }

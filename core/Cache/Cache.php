<?php
namespace Verix\Core\Cache;
interface Cache { public function get(string $key,mixed $default=null): mixed; public function put(string $key,mixed $value,int $ttl=300): void; public function forget(string $key): void; }

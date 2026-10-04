<?php
namespace Verix\Core\Foundation;
final class Container {
    private array $bindings=[];
    public function set(string $id, mixed $value): void { $this->bindings[$id]=$value; }
    public function has(string $id): bool { return array_key_exists($id,$this->bindings); }
    public function get(string $id): mixed {
        if (!$this->has($id)) throw new \RuntimeException("Container binding not found: {$id}");
        $v=$this->bindings[$id];
        return is_callable($v) ? $v($this) : $v;
    }
}

<?php
namespace Verix\Core\Support;

final class Env
{
    private static array $values = [];

    public static function load(string $file): void
    {
        if (!is_file($file)) return;
        foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
            [$key, $value] = array_map('trim', explode('=', $line, 2));
            if ($key === '') continue;
            if ((str_starts_with($value, '"') && str_ends_with($value, '"')) || (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                $value = substr($value, 1, -1);
            }
            self::$values[$key] = $value;
            $_ENV[$key] = $value;
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return self::$values[$key] ?? $_ENV[$key] ?? getenv($key) ?: $default;
    }

    public static function bool(string $key, bool $default = false): bool
    {
        $value = strtolower((string)self::get($key, $default ? 'true' : 'false'));
        return in_array($value, ['1','true','yes','on'], true);
    }

    public static function int(string $key, int $default = 0): int
    {
        return (int)self::get($key, $default);
    }

    public static function csv(string $key, array $default = []): array
    {
        $value = trim((string)self::get($key, ''));
        if ($value === '') return $default;
        return array_values(array_filter(array_map('trim', explode(',', $value)), fn($v) => $v !== ''));
    }
}

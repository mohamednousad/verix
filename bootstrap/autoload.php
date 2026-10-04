<?php
declare(strict_types=1);

spl_autoload_register(static function (string $class): void {
    $prefixes = [
        'Verix\\Core\\' => __DIR__ . '/../core/',
        'App\\' => __DIR__ . '/../app/',
    ];
    foreach ($prefixes as $prefix => $baseDir) {
        if (strncmp($class, $prefix, strlen($prefix)) !== 0) continue;
        $relative = substr($class, strlen($prefix));
        $file = $baseDir . str_replace('\\', '/', $relative) . '.php';
        if (is_file($file)) require $file;
        return;
    }
});

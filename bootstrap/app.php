<?php
declare(strict_types=1);
require __DIR__.'/autoload.php';
use Verix\Core\Support\Env; use Verix\Core\Exceptions\Handler; use Verix\Core\Foundation\{Application,Container}; use Verix\Core\Routing\Router; use Verix\Core\Security\{Cors,SecurityHeaders};
Env::load(dirname(__DIR__).'/.env');
date_default_timezone_set((string)Env::get('TIMEZONE','UTC'));
Handler::register(); Cors::apply(); SecurityHeaders::apply();
$container=new Container();$router=new Router();$app=new Application($container,$router);
require dirname(__DIR__).'/routes/api.php';
return $app;

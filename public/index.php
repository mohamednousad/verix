<?php
declare(strict_types=1);
$app=require dirname(__DIR__).'/bootstrap/app.php';
$request=new \Verix\Core\Http\Request();
$app->router->dispatch($request);

<?php
namespace Verix\Core\Routing;
final class Route { public function __construct(public string $method,public string $path,public mixed $handler,public array $middleware=[]){} }

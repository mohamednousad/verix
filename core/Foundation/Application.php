<?php
namespace Verix\Core\Foundation;
use Verix\Core\Routing\Router;
final class Application { public function __construct(public Container $container,public Router $router){} }

<?php
namespace App\Middleware;
use Verix\Core\Http\Request; use Verix\Core\Http\JsonResponse; use Verix\Core\Security\RateLimiter;
final class RateLimitMiddleware { public function handle(Request $r,callable $next): mixed { $key=$r->ip().'|'.$r->path(); if(!RateLimiter::check($key)) JsonResponse::error('Too many requests.',429); return $next($r); } }

<?php
namespace App\Middleware;
use Verix\Core\Auth\Auth; use Verix\Core\Http\Request; use Verix\Core\Http\JsonResponse;
final class AuthMiddleware { public function handle(Request $r,callable $next): mixed { if(!Auth::user($r)) JsonResponse::error('Unauthenticated.',401); return $next($r); } }

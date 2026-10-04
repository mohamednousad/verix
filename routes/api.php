<?php
use App\Controllers\{HealthController,AuthController};
use App\Middleware\{AuthMiddleware,RateLimitMiddleware};
use Verix\Core\Support\Env;
$prefix='/'.trim((string)Env::get('API_PREFIX','/api/v1'),'/');
$rate=new RateLimitMiddleware();$auth=new AuthMiddleware();
$router->get($prefix.'/health',[HealthController::class,'index'],[$rate]);
$router->post($prefix.'/auth/register',[AuthController::class,'register'],[$rate]);
$router->post($prefix.'/auth/login',[AuthController::class,'login'],[$rate]);
$router->post($prefix.'/auth/refresh',[AuthController::class,'refresh'],[$rate]);
$router->post($prefix.'/auth/logout',[AuthController::class,'logout'],[$rate]);
$router->post($prefix.'/auth/password/forgot',[AuthController::class,'forgotPassword'],[$rate]);
$router->post($prefix.'/auth/password/reset',[AuthController::class,'resetPassword'],[$rate]);
$router->get($prefix.'/auth/me',[AuthController::class,'me'],[$rate,$auth]);
$router->post($prefix.'/auth/email/otp',[AuthController::class,'requestEmailOtp'],[$rate,$auth]);
$router->post($prefix.'/auth/email/verify',[AuthController::class,'verifyEmail'],[$rate,$auth]);

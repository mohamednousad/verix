<?php
namespace Verix\Core\Exceptions;
use Verix\Core\Http\JsonResponse;
use Verix\Core\Support\Env;
final class Handler {
    public static function register(): void {
        set_exception_handler(static function(\Throwable $e): never {
            $debug=Env::bool('APP_DEBUG',false);
            JsonResponse::error($debug?$e->getMessage():'Server error.',500,$debug?['type'=>$e::class,'file'=>$e->getFile(),'line'=>$e->getLine()]:null);
        });
        set_error_handler(static function(int $severity,string $message,string $file,int $line): bool {
            if(!(error_reporting() & $severity)) return false; throw new \ErrorException($message,0,$severity,$file,$line);
        });
    }
}

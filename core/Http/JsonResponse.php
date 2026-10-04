<?php
namespace Verix\Core\Http;
final class JsonResponse extends Response {
    public static function success(mixed $data=null,string $message='OK',int $status=200): never {
        self::json(['success'=>true,'message'=>$message,'data'=>$data],$status);
    }
    public static function error(string $message='Error',int $status=400,mixed $errors=null): never {
        $payload=['success'=>false,'message'=>$message];
        if ($errors!==null) $payload['errors']=$errors;
        self::json($payload,$status);
    }
}

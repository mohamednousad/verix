<?php
namespace Verix\Core\Http;
class Response {
    public static function json(array $data,int $status=200,array $headers=[]): never {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        foreach($headers as $k=>$v) header($k.': '.$v);
        echo json_encode($data, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
        exit;
    }
}

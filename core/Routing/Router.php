<?php
namespace Verix\Core\Routing;
use Verix\Core\Http\Request;
use Verix\Core\Http\JsonResponse;
final class Router {
    private array $routes=[];
    public function add(string $method,string $path,mixed $handler,array $middleware=[]): self { $this->routes[]=new Route(strtoupper($method),'/'.trim($path,'/'),$handler,$middleware); return $this; }
    public function get(string $p,mixed $h,array $m=[]): self{return $this->add('GET',$p,$h,$m);} public function post(string $p,mixed $h,array $m=[]): self{return $this->add('POST',$p,$h,$m);} public function put(string $p,mixed $h,array $m=[]): self{return $this->add('PUT',$p,$h,$m);} public function patch(string $p,mixed $h,array $m=[]): self{return $this->add('PATCH',$p,$h,$m);} public function delete(string $p,mixed $h,array $m=[]): self{return $this->add('DELETE',$p,$h,$m);}
    public function dispatch(Request $request): never {
        foreach($this->routes as $route){ if($route->method!==$request->method() || $route->path!==$request->path()) continue;
            $core=fn(Request $r)=>$this->invoke($route->handler,$r); $pipeline=array_reduce(array_reverse($route->middleware),fn($next,$mw)=>fn(Request $r)=>$mw->handle($r,$next),$core); $result=$pipeline($request);
            if(is_array($result)) JsonResponse::success($result); if($result===null) JsonResponse::success(null); JsonResponse::success($result);
        }
        JsonResponse::error('Route not found.',404);
    }
    private function invoke(mixed $handler,Request $r): mixed { if(is_callable($handler)) return $handler($r); if(is_array($handler)&&count($handler)===2){ [$class,$method]=$handler; return (new $class())->{$method}($r); } throw new \RuntimeException('Invalid route handler.'); }
}

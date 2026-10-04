<?php
namespace Verix\Core\Auth;
use Verix\Core\Database\QueryBuilder;
use Verix\Core\Http\Request;
final class Auth {
    public static function user(Request $request): ?array {
        $cached=$request->attribute('auth_user'); if(is_array($cached)) return $cached;
        $token=$request->bearerToken(); if(!$token) return null;
        try { $claims=JWT::decode($token); } catch(\Throwable){ return null; }
        if(($claims['type']??'')!=='access' || empty($claims['sub'])) return null;
        $user=(new QueryBuilder())->first('SELECT id,name,email,email_verified_at,created_at FROM users WHERE id=? LIMIT 1',[(int)$claims['sub']]);
        if($user) $request->setAttribute('auth_user',$user);
        return $user;
    }
    public static function hasPermission(int $userId,string $permission): bool {
        $sql='SELECT 1 FROM permissions p JOIN permission_role pr ON pr.permission_id=p.id JOIN role_user ru ON ru.role_id=pr.role_id WHERE ru.user_id=? AND p.name=? LIMIT 1';
        return (bool)(new QueryBuilder())->first($sql,[$userId,$permission]);
    }
}

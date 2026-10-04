<?php
namespace Verix\Core\Security;
use Verix\Core\Database\QueryBuilder;
use Verix\Core\Support\Env;
final class RateLimiter {
    public static function check(string $key): bool {
        if(!Env::bool('RATE_LIMIT_ENABLED',true)) return true;
        $db=new QueryBuilder(); $window=Env::int('RATE_LIMIT_WINDOW_SECONDS',60); $max=Env::int('RATE_LIMIT_MAX_ATTEMPTS',120); $now=time(); $start=$now-($now%$window);
        $hash=hash('sha256',$key.'|'.$start);
        $row=$db->first('SELECT id,hits FROM verix_rate_limits WHERE bucket_key=? LIMIT 1',[$hash]);
        if($row){ $hits=(int)$row['hits']+1; $db->execute('UPDATE verix_rate_limits SET hits=? WHERE id=?',[$hits,$row['id']]); }
        else { $hits=1; $db->insert('INSERT INTO verix_rate_limits (bucket_key,hits,expires_at) VALUES (?,?,?)',[$hash,$hits,date('Y-m-d H:i:s',$start+$window+5)]); }
        if(random_int(1,100)<=2) $db->execute('DELETE FROM verix_rate_limits WHERE expires_at < NOW()');
        $count=$db->first('SELECT COUNT(*) c FROM verix_rate_limits');
        if((int)($count['c']??0)>Env::int('RATE_LIMIT_MAX_ROWS',10000)) $db->execute('DELETE FROM verix_rate_limits ORDER BY expires_at ASC LIMIT 500');
        return $hits <= $max;
    }
}

<?php
namespace Verix\Core\Cache;
use Verix\Core\Database\QueryBuilder;
use Verix\Core\Support\Env;
final class DatabaseCache implements Cache {
    private QueryBuilder $db; private string $prefix;
    public function __construct(){ $this->db=new QueryBuilder();$this->prefix=(string)Env::get('CACHE_PREFIX','verix_'); }
    private function k(string $k): string{return hash('sha256',$this->prefix.$k);}
    public function get(string $key,mixed $default=null): mixed {
        $r=$this->db->first('SELECT cache_value,expires_at FROM verix_cache WHERE cache_key=? LIMIT 1',[$this->k($key)]);
        if(!$r) return $default; if(strtotime($r['expires_at'])<time()){ $this->forget($key); return $default; }
        $v=json_decode($r['cache_value'],true); return json_last_error()===JSON_ERROR_NONE ? $v : $default;
    }
    public function put(string $key,mixed $value,int $ttl=300): void {
        $k=$this->k($key);$v=json_encode($value,JSON_UNESCAPED_SLASHES);$exp=date('Y-m-d H:i:s',time()+max(1,$ttl));
        $this->db->execute('INSERT INTO verix_cache (cache_key,cache_value,expires_at,created_at) VALUES (?,?,?,NOW()) ON DUPLICATE KEY UPDATE cache_value=VALUES(cache_value),expires_at=VALUES(expires_at)',[$k,$v,$exp]);
        $p=Env::int('CACHE_CLEANUP_PROBABILITY',5); if($p>0 && random_int(1,100)<=$p) $this->cleanup();
    }
    public function forget(string $key): void { $this->db->execute('DELETE FROM verix_cache WHERE cache_key=?',[$this->k($key)]); }
    private function cleanup(): void {
        $this->db->execute('DELETE FROM verix_cache WHERE expires_at < NOW()');
        $r=$this->db->first('SELECT COUNT(*) c FROM verix_cache'); $max=Env::int('CACHE_MAX_ROWS',5000); $count=(int)($r['c']??0);
        if($count>$max){ $remove=$count-$max; $this->db->execute("DELETE FROM verix_cache ORDER BY expires_at ASC LIMIT {$remove}"); }
    }
}

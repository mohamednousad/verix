<?php
namespace Verix\Core\Auth;
use Verix\Core\Database\QueryBuilder;
use Verix\Core\Support\Env;
final class TokenManager {
    private QueryBuilder $db;
    public function __construct(){ $this->db=new QueryBuilder(); }
    public function issue(int $userId,array $extra=[]): array {
        $this->cleanup($userId);
        $access=JWT::encode(['sub'=>$userId,'type'=>'access']+$extra,Env::int('JWT_TTL',3600));
        $plain=bin2hex(random_bytes(32)); $hash=hash('sha256',$plain); $expires=date('Y-m-d H:i:s',time()+Env::int('JWT_REFRESH_TTL',2592000));
        $this->db->insert('INSERT INTO refresh_tokens (user_id, token_hash, expires_at, created_at) VALUES (?,?,?,NOW())',[$userId,$hash,$expires]);
        return ['access_token'=>$access,'refresh_token'=>$plain,'token_type'=>'Bearer','expires_in'=>Env::int('JWT_TTL',3600)];
    }
    public function refresh(string $plain): array {
        $hash=hash('sha256',$plain); $row=$this->db->first('SELECT * FROM refresh_tokens WHERE token_hash=? AND revoked_at IS NULL AND expires_at>NOW() LIMIT 1',[$hash]);
        if(!$row) throw new \RuntimeException('Invalid refresh token.');
        $this->db->execute('UPDATE refresh_tokens SET revoked_at=NOW() WHERE id=?',[$row['id']]);
        return $this->issue((int)$row['user_id']);
    }
    public function revoke(string $plain): void { $this->db->execute('UPDATE refresh_tokens SET revoked_at=NOW() WHERE token_hash=?',[hash('sha256',$plain)]); }
    private function cleanup(int $userId): void {
        $this->db->execute('DELETE FROM refresh_tokens WHERE expires_at < NOW() OR (revoked_at IS NOT NULL AND revoked_at < DATE_SUB(NOW(), INTERVAL 1 DAY))');
        $max=max(1,Env::int('REFRESH_TOKEN_MAX_PER_USER',10));
        $row=$this->db->first('SELECT COUNT(*) c FROM refresh_tokens WHERE user_id=?',[$userId]); $count=(int)($row['c']??0);
        if($count >= $max){ $remove=$count-$max+1; $this->db->execute("DELETE FROM refresh_tokens WHERE user_id=? ORDER BY created_at ASC LIMIT {$remove}",[$userId]); }
    }
}

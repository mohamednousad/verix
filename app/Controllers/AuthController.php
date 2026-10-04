<?php
namespace App\Controllers;
use Verix\Core\Auth\{Auth,PasswordHasher,TokenManager};
use Verix\Core\Database\QueryBuilder;
use Verix\Core\Http\{Request,JsonResponse};
use Verix\Core\Mail\Mailer;
use Verix\Core\Support\Env;
use Verix\Core\Validation\Validator;
final class AuthController {
    private QueryBuilder $db; private TokenManager $tokens;
    public function __construct(){ $this->db=new QueryBuilder();$this->tokens=new TokenManager(); }
    public function register(Request $r): array {
        $data=$r->input(); $min=Env::int('PASSWORD_MIN_LENGTH',8); $errors=Validator::validate($data,['name'=>'required|max:100','email'=>'required|email|max:190','password'=>"required|min:{$min}"]); if($errors) JsonResponse::error('Validation failed.',422,$errors);
        $email=strtolower(trim((string)$data['email']));
        if($this->db->first('SELECT id FROM users WHERE email=? LIMIT 1',[$email])) JsonResponse::error('Email already registered.',409);
        $id=(int)$this->db->insert('INSERT INTO users (name,email,password_hash,created_at,updated_at) VALUES (?,?,?,?,?)',[(string)$data['name'],$email,PasswordHasher::make((string)$data['password']),date('Y-m-d H:i:s'),date('Y-m-d H:i:s')]);
        if(Env::bool('AUTH_REQUIRE_EMAIL_VERIFICATION',false)) $this->sendOtp($id,$email,'email_verify','Verify your email');
        return ['user'=>['id'=>$id,'name'=>$data['name'],'email'=>$email],'tokens'=>$this->tokens->issue($id)];
    }
    public function login(Request $r): array {
        $data=$r->input();$errors=Validator::validate($data,['email'=>'required|email','password'=>'required']);if($errors)JsonResponse::error('Validation failed.',422,$errors);
        $u=$this->db->first('SELECT * FROM users WHERE email=? LIMIT 1',[strtolower(trim((string)$data['email']))]); if(!$u||!PasswordHasher::verify((string)$data['password'],(string)$u['password_hash'])) JsonResponse::error('Invalid credentials.',401);
        if(Env::bool('AUTH_REQUIRE_EMAIL_VERIFICATION',false) && empty($u['email_verified_at'])) JsonResponse::error('Email verification required.',403);
        return ['user'=>['id'=>(int)$u['id'],'name'=>$u['name'],'email'=>$u['email']],'tokens'=>$this->tokens->issue((int)$u['id'])];
    }
    public function refresh(Request $r): array { $token=(string)$r->input('refresh_token',''); if($token==='') JsonResponse::error('Refresh token required.',422); return $this->tokens->refresh($token); }
    public function logout(Request $r): array { $token=(string)$r->input('refresh_token',''); if($token!=='') $this->tokens->revoke($token); return ['logged_out'=>true]; }
    public function me(Request $r): array { return ['user'=>Auth::user($r)]; }
    public function requestEmailOtp(Request $r): array { $u=Auth::user($r); if(!$u)JsonResponse::error('Unauthenticated.',401); $this->sendOtp((int)$u['id'],(string)$u['email'],'email_verify','Verify your email'); return ['sent'=>true]; }
    public function verifyEmail(Request $r): array {
        $u=Auth::user($r);if(!$u)JsonResponse::error('Unauthenticated.',401);$this->verifyOtp((int)$u['id'],'email_verify',(string)$r->input('otp',''));
        $this->db->execute('UPDATE users SET email_verified_at=NOW(),updated_at=NOW() WHERE id=?',[(int)$u['id']]);return ['verified'=>true];
    }
    public function forgotPassword(Request $r): array {
        $email=strtolower(trim((string)$r->input('email',''))); if(!filter_var($email,FILTER_VALIDATE_EMAIL)) JsonResponse::error('Valid email required.',422);
        $u=$this->db->first('SELECT id,email FROM users WHERE email=? LIMIT 1',[$email]);
        if($u) $this->sendOtp((int)$u['id'],$email,'password_reset','Reset your password');
        return ['sent'=>true]; // Same response prevents email-account enumeration.
    }
    public function resetPassword(Request $r): array {
        $email=strtolower(trim((string)$r->input('email',''))); $otp=(string)$r->input('otp',''); $password=(string)$r->input('password',''); $min=Env::int('PASSWORD_MIN_LENGTH',8);
        if(!filter_var($email,FILTER_VALIDATE_EMAIL) || mb_strlen($password)<$min || $otp==='') JsonResponse::error('Invalid reset request.',422);
        $u=$this->db->first('SELECT id FROM users WHERE email=? LIMIT 1',[$email]); if(!$u) JsonResponse::error('Invalid or expired OTP.',422);
        $this->verifyOtp((int)$u['id'],'password_reset',$otp);
        $this->db->execute('UPDATE users SET password_hash=?,updated_at=NOW() WHERE id=?',[PasswordHasher::make($password),(int)$u['id']]);
        $this->db->execute('UPDATE refresh_tokens SET revoked_at=NOW() WHERE user_id=? AND revoked_at IS NULL',[(int)$u['id']]);
        return ['reset'=>true];
    }
    private function verifyOtp(int $userId,string $purpose,string $otp): void {
        $row=$this->db->first('SELECT * FROM otp_codes WHERE user_id=? AND purpose=? AND used_at IS NULL AND expires_at>NOW() ORDER BY id DESC LIMIT 1',[$userId,$purpose]);
        if(!$row || !password_verify($otp,(string)$row['code_hash'])) JsonResponse::error('Invalid or expired OTP.',422);
        $this->db->execute('UPDATE otp_codes SET used_at=NOW() WHERE id=?',[$row['id']]);
    }
    private function sendOtp(int $userId,string $email,string $purpose,string $subject): void {
        $this->cleanupOtp($userId,$purpose);
        $length=max(4,min(8,Env::int('OTP_LENGTH',6)));$min=10**($length-1);$max=(10**$length)-1;$otp=(string)random_int($min,$max);$exp=date('Y-m-d H:i:s',time()+Env::int('OTP_TTL',600));
        $this->db->insert('INSERT INTO otp_codes (user_id,purpose,code_hash,expires_at,created_at) VALUES (?,?,?,?,NOW())',[$userId,$purpose,password_hash($otp,PASSWORD_DEFAULT),$exp]);
        if(Env::bool('MAIL_ENABLED',false)) (new Mailer())->send($email,$subject,'<p>Your verification code is <strong>'.htmlspecialchars($otp,ENT_QUOTES,'UTF-8').'</strong></p>','Your verification code is '.$otp);
    }
    private function cleanupOtp(int $userId,string $purpose): void {
        $this->db->execute('DELETE FROM otp_codes WHERE expires_at < NOW() OR used_at IS NOT NULL');
        $max=max(1,Env::int('OTP_MAX_PER_USER',5)); $row=$this->db->first('SELECT COUNT(*) c FROM otp_codes WHERE user_id=? AND purpose=?',[$userId,$purpose]); $count=(int)($row['c']??0);
        if($count >= $max){ $remove=$count-$max+1; $this->db->execute("DELETE FROM otp_codes WHERE user_id=? AND purpose=? ORDER BY created_at ASC LIMIT {$remove}",[$userId,$purpose]); }
    }
}

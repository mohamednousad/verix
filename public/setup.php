<?php
declare(strict_types=1);
require dirname(__DIR__).'/bootstrap/autoload.php';
use Verix\Core\Support\Env; use Verix\Core\Database\Database;
Env::load(dirname(__DIR__).'/.env');
header('Content-Type: application/json; charset=utf-8');
if(!Env::bool('INSTALL_ENABLED',false)){ http_response_code(403); echo json_encode(['success'=>false,'message'=>'Installer disabled.']); exit; }
if(($_SERVER['REQUEST_METHOD']??'GET')!=='POST'){ echo json_encode(['success'=>false,'message'=>'POST install_key to run setup.']); exit; }
$key=(string)($_POST['install_key']??''); if(!hash_equals((string)Env::get('INSTALL_KEY',''),$key)){ http_response_code(403); echo json_encode(['success'=>false,'message'=>'Invalid install key.']); exit; }
$sql=(string)file_get_contents(dirname(__DIR__).'/database/schema.sql');
try {
    $pdo=Database::connection();
    $statements=array_filter(array_map('trim',preg_split('/;\s*(?:\r?\n|$)/',$sql) ?: []));
    foreach($statements as $statement){ if($statement!=='') $pdo->exec($statement); }
    echo json_encode(['success'=>true,'message'=>'Verix database installed. Now set INSTALL_ENABLED=false in .env.']);
} catch(Throwable $e){
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>Env::bool('APP_DEBUG',false)?$e->getMessage():'Setup failed.']);
}

<?php
namespace Verix\Core\Storage;
use Verix\Core\Support\Env;
final class UploadManager {
    public function store(array $file): array {
        if(!Env::bool('UPLOADS_ENABLED',false)) throw new \RuntimeException('Uploads are disabled.');
        if(($file['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_OK) throw new \RuntimeException('Upload failed.');
        $max=Env::int('UPLOAD_MAX_MB',5)*1024*1024; $size=(int)($file['size']??0);
        if($size<=0 || $size>$max) throw new \RuntimeException('File exceeds allowed size.');
        $finfo=new \finfo(FILEINFO_MIME_TYPE); $mime=$finfo->file((string)$file['tmp_name']) ?: 'application/octet-stream';
        $allowedMimes=Env::csv('UPLOAD_ALLOWED_MIME_TYPES',[]); if(!in_array($mime,$allowedMimes,true)) throw new \RuntimeException('File type is not allowed.');
        $map=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','application/pdf'=>'pdf']; $ext=$map[$mime] ?? strtolower(pathinfo((string)($file['name']??''),PATHINFO_EXTENSION));
        if(!in_array($ext,Env::csv('UPLOAD_ALLOWED_EXTENSIONS',[]),true)) throw new \RuntimeException('File extension is not allowed.');
        $root=dirname(__DIR__,2); $relative=trim((string)Env::get('UPLOAD_PATH','storage/uploads'),'/'); $dir=$root.'/'.$relative;
        if(!is_dir($dir) && !mkdir($dir,0750,true) && !is_dir($dir)) throw new \RuntimeException('Upload directory unavailable.');
        $quota=Env::int('UPLOAD_TOTAL_QUOTA_MB',100)*1024*1024; if($this->directorySize($dir)+$size>$quota) throw new \RuntimeException('Upload storage quota reached.');
        $name=bin2hex(random_bytes(20)).'.'.$ext; $dest=$dir.'/'.$name;
        if(!move_uploaded_file((string)$file['tmp_name'],$dest)) throw new \RuntimeException('Could not save upload.');
        @chmod($dest,0640);
        return ['name'=>$name,'path'=>$relative.'/'.$name,'size'=>$size,'mime'=>$mime];
    }
    public function validateRequestCount(array $files): void {
        $count=0; foreach($files as $f){ if(is_array($f) && isset($f['name'])) $count += is_array($f['name']) ? count($f['name']) : 1; }
        if($count>Env::int('UPLOAD_MAX_FILES_PER_REQUEST',3)) throw new \RuntimeException('Too many files in one request.');
    }
    private function directorySize(string $dir): int {
        $size=0; $it=new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir,\FilesystemIterator::SKIP_DOTS)); foreach($it as $f){ if($f->isFile())$size+=$f->getSize(); } return $size;
    }
}

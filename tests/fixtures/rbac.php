<?php
// Staging-only integration fixture. Never copy this file into the production export.
declare(strict_types=1);
require __DIR__.'/api/_backend.php';
$key=(string)getenv('SHAN_QA_KEY');
if($key==='' || !hash_equals($key,(string)($_SERVER['HTTP_X_QA_KEY'] ?? '')) || strpos(shan_config()['database']['dsn'],'comshan979_rbacqa')===false){http_response_code(404);exit;}
shan_bootstrap_admin();$pdo=shan_db();
foreach(['business','job'] as $type){
    $q=$pdo->prepare('INSERT IGNORE INTO shan_submissions (public_id,form_type,full_name,email,message,resume_stored_name,resume_file_name,resume_mime) VALUES (?,?,?,?,?,?,?,?)');
    $q->execute([$type==='job'?'11111111-1111-4111-8111-111111111111':'22222222-2222-4222-8222-222222222222',$type,'QA '.$type,$type.'@example.invalid','QA fixture',$type==='job'?'fixture.txt':null,$type==='job'?'fixture.txt':null,$type==='job'?'text/plain':null]);
}
$storage=shan_config()['storage_dir'];if(!is_dir($storage)){mkdir($storage,0700,true);}file_put_contents($storage.'/fixture.txt','QA CV fixture');chmod($storage.'/fixture.txt',0600);
header('Content-Type: application/json');echo json_encode(['ready'=>true]);

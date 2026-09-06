<?php
declare(strict_types=1);

ini_set('display_errors', '0');
require_once __DIR__ . '/_common.php';
dashboard_headers();
shan_dashboard_require_auth();
dashboard_require_permission('job.cv');

$publicId = trim((string)($_GET['id'] ?? ''));
if (!preg_match('/^[a-f0-9-]{36}$/', $publicId)) {
    http_response_code(404);
    exit('File not found.');
}

try {
    $statement = shan_db()->prepare("SELECT resume_file_name, resume_stored_name, resume_mime FROM shan_submissions WHERE public_id = :public_id AND form_type='job' AND NOT EXISTS (SELECT 1 FROM shan_submission_trash t WHERE t.submission_id=shan_submissions.id) AND resume_stored_name IS NOT NULL LIMIT 1");
    $statement->execute(['public_id' => $publicId]);
    $file = $statement->fetch();
    $storage = rtrim((string)(shan_config()['storage_dir'] ?? ''), '/');
    $storedName = is_array($file) ? basename((string)$file['resume_stored_name']) : '';
    $path = $storage . '/' . $storedName;
    if ($storedName === '' || !is_file($path) || !is_readable($path)) {
        http_response_code(404);
        exit('File not found.');
    }
    $downloadName = preg_replace('/[^A-Za-z0-9._-]/', '_', (string)$file['resume_file_name']) ?: 'candidate-cv';
    header('Content-Type: ' . ((string)$file['resume_mime'] ?: 'application/octet-stream'));
    header('Content-Length: ' . filesize($path));
    header('Content-Disposition: attachment; filename="' . $downloadName . '"');
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: private, no-store');
    readfile($path);
} catch (Throwable $error) {
    error_log('Shan CV download error: ' . $error->getMessage());
    http_response_code(500);
    exit('The file is temporarily unavailable.');
}

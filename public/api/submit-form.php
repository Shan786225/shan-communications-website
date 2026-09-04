<?php
declare(strict_types=1);

ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');

require_once __DIR__ . '/_backend.php';

const BUSINESS_EMAIL = 'ceo@shancommunication.com';
const JOBS_EMAIL = 'hr@shancommunication.com';
const SENDER_EMAIL = 'support@shancommunication.com';
const MAX_REQUEST_SIZE = 12582912;
const MAX_CV_SIZE = 10485760;

function respond(int $status, bool $success, string $message): void
{
    http_response_code($status);
    echo json_encode(['success' => $success, 'message' => $message], JSON_UNESCAPED_SLASHES);
    exit;
}

function clean_text(string $key, int $maxLength, bool $required = true): string
{
    $value = trim((string)($_POST[$key] ?? ''));
    $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value) ?? '';
    if ($required && $value === '') {
        respond(422, false, 'Please complete every required field.');
    }
    $length = function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
    if ($length > $maxLength) {
        respond(422, false, 'One or more fields are longer than allowed.');
    }
    return $value;
}

function rate_limit(): void
{
    if (getenv('SHAN_FORM_DRY_RUN') === '1') {
        return;
    }
    $ip = (string)($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    $path = sys_get_temp_dir() . '/shan-form-' . hash('sha256', $ip) . '.json';
    $handle = @fopen($path, 'c+');
    if ($handle === false || !flock($handle, LOCK_EX)) {
        return;
    }
    $raw = stream_get_contents($handle);
    $timestamps = json_decode($raw ?: '[]', true);
    if (!is_array($timestamps)) {
        $timestamps = [];
    }
    $cutoff = time() - 600;
    $timestamps = array_values(array_filter($timestamps, static function ($time) use ($cutoff): bool {
        return is_int($time) && $time >= $cutoff;
    }));
    if (count($timestamps) >= 5) {
        flock($handle, LOCK_UN);
        fclose($handle);
        respond(429, false, 'Too many submissions were received. Please wait a few minutes and try again.');
    }
    $timestamps[] = time();
    ftruncate($handle, 0);
    rewind($handle);
    fwrite($handle, json_encode($timestamps));
    fflush($handle);
    flock($handle, LOCK_UN);
    fclose($handle);
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    respond(405, false, 'This endpoint accepts website form submissions only.');
}

$contentLength = (int)($_SERVER['CONTENT_LENGTH'] ?? 0);
if ($contentLength > MAX_REQUEST_SIZE) {
    respond(413, false, 'The submission is too large. The maximum CV size is 10 MB.');
}

$origin = (string)($_SERVER['HTTP_ORIGIN'] ?? '');
if ($origin !== '') {
    $originHost = strtolower((string)parse_url($origin, PHP_URL_HOST));
    $allowedHosts = ['shancommunication.com', 'www.shancommunication.com', 'localhost', '127.0.0.1'];
    if (!in_array($originHost, $allowedHosts, true)) {
        respond(403, false, 'The submission origin could not be verified.');
    }
}

if (trim((string)($_POST['companyWebsite'] ?? '')) !== '') {
    respond(200, true, 'Thank you. Your information has been submitted successfully.');
}

if ((string)($_POST['consent'] ?? '') !== 'accepted') {
    respond(422, false, 'Please confirm the consent statement before submitting.');
}

rate_limit();

$formType = clean_text('formType', 20);
if (!in_array($formType, ['business', 'job'], true)) {
    respond(422, false, 'The form type is invalid.');
}

$name = clean_text('name', 120);
$email = filter_var(clean_text('email', 190), FILTER_VALIDATE_EMAIL);
if ($email === false) {
    respond(422, false, 'Please enter a valid email address.');
}
$phone = clean_text('phone', 60, $formType === 'job');
$message = clean_text('message', 6000);

$fields = [];
$attachment = null;
$resumeUrl = '';
$destination = BUSINESS_EMAIL;
$successMessage = 'Thank you. Your inquiry was submitted successfully. Our team will respond using the contact details provided.';

if ($formType === 'business') {
    $topic = clean_text('topic', 160);
    $subject = 'Website inquiry - ' . $topic . ' - ' . $name;
    $fields = [
        'Form' => 'Business inquiry',
        'Full name' => $name,
        'Email' => $email,
        'Phone' => $phone !== '' ? $phone : 'Not provided',
        'Area of interest' => $topic,
        'Project details' => $message,
    ];
} else {
    $destination = JOBS_EMAIL;
    $role = clean_text('role', 180);
    $experience = clean_text('experience', 100);
    $availability = clean_text('availability', 160);
    $resumeUrl = clean_text('resume', 2000, false);
    if ($resumeUrl !== '') {
        $resumeScheme = strtolower((string)parse_url($resumeUrl, PHP_URL_SCHEME));
        if (filter_var($resumeUrl, FILTER_VALIDATE_URL) === false || !in_array($resumeScheme, ['http', 'https'], true)) {
            respond(422, false, 'Please provide a valid CV link beginning with http:// or https://.');
        }
    }

    if (isset($_FILES['resumeFile']) && (int)$_FILES['resumeFile']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['resumeFile'];
        if ((int)$file['error'] !== UPLOAD_ERR_OK) {
            respond(422, false, 'The CV upload did not complete. Please try again or use a CV link.');
        }
        if ((int)$file['size'] > MAX_CV_SIZE) {
            respond(413, false, 'The CV file must be 10 MB or smaller.');
        }
        $originalName = basename((string)$file['name']);
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowedExtensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
        if (!in_array($extension, $allowedExtensions, true) || !is_uploaded_file((string)$file['tmp_name'])) {
            respond(422, false, 'Use a valid PDF, DOC, DOCX, JPG, JPEG or PNG CV file.');
        }
        if (class_exists('finfo')) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = (string)$finfo->file((string)$file['tmp_name']);
        } elseif (function_exists('mime_content_type')) {
            $mime = (string)mime_content_type((string)$file['tmp_name']);
        } else {
            respond(500, false, 'CV upload verification is temporarily unavailable. Please use a CV link instead.');
        }
        $allowedMime = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/zip',
            'image/jpeg',
            'image/png',
        ];
        if (!in_array($mime, $allowedMime, true)) {
            respond(422, false, 'The uploaded CV file type could not be verified.');
        }
        $safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', $originalName) ?: 'candidate-cv.' . $extension;
        $fileData = file_get_contents((string)$file['tmp_name']);
        if ($fileData === false) {
            respond(500, false, 'The uploaded CV could not be processed. Please try again or use a CV link.');
        }
        $attachment = [
            'name' => $safeName,
            'mime' => $mime,
            'data' => $fileData,
        ];
    }

    if ($attachment === null && $resumeUrl === '') {
        respond(422, false, 'Upload your CV or provide a working CV link to continue.');
    }

    $subject = 'Job application - ' . $role . ' - ' . $name;
    $fields = [
        'Form' => 'Job application',
        'Full name' => $name,
        'Email' => $email,
        'Phone / WhatsApp' => $phone,
        'Role of interest' => $role,
        'Relevant experience' => $experience,
        'Availability / notice period' => $availability,
        'CV link' => $resumeUrl !== '' ? $resumeUrl : 'CV attached',
        'Experience summary' => $message,
    ];
    $successMessage = 'Thank you. Your application was submitted successfully to the Shan Communications hiring team.';
}

$subject = preg_replace('/[\r\n]+/', ' ', $subject) ?? 'Shan Communications website submission';
$publicId = shan_uuid();
$storedName = null;
try {
    if ($attachment !== null) {
        $storedName = shan_store_cv($publicId, $attachment);
    }
    $sheetsConfig = shan_config()['google_sheets'] ?? [];
    $submissionId = shan_store_submission([
        'public_id' => $publicId,
        'form_type' => $formType,
        'full_name' => $name,
        'email' => $email,
        'phone' => $phone !== '' ? $phone : null,
        'topic' => $formType === 'business' ? $topic : null,
        'role_name' => $formType === 'job' ? $role : null,
        'experience' => $formType === 'job' ? $experience : null,
        'availability' => $formType === 'job' ? $availability : null,
        'message' => $message,
        'resume_url' => $resumeUrl !== '' ? $resumeUrl : null,
        'resume_file_name' => $attachment !== null ? (string)$attachment['name'] : null,
        'resume_stored_name' => $storedName,
        'resume_mime' => $attachment !== null ? (string)$attachment['mime'] : null,
        'resume_size' => $attachment !== null ? strlen((string)$attachment['data']) : null,
        'sheets_status' => !empty($sheetsConfig['enabled']) ? 'pending' : 'disabled',
        'source_url' => substr((string)($_SERVER['HTTP_REFERER'] ?? ''), 0, 500),
        'ip_hash' => shan_request_ip_hash(),
        'user_agent' => substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 500),
    ]);
} catch (Throwable $error) {
    if ($storedName !== null) {
        $storage = rtrim((string)(shan_config()['storage_dir'] ?? ''), '/');
        if ($storage !== '') {
            @unlink($storage . '/' . basename($storedName));
        }
    }
    error_log('Shan submission storage error: ' . $error->getMessage());
    respond(503, false, 'The secure submission system is temporarily unavailable. Please try again in a few minutes.');
}

$lines = [
    'A new submission was received from shancommunication.com.',
    'Submission ID: ' . $publicId,
    'Received: ' . gmdate('Y-m-d H:i:s') . ' UTC',
    '',
];
foreach ($fields as $label => $value) {
    $lines[] = $label . ': ' . $value;
}
$body = implode("\r\n", $lines);
$boundary = 'shan_' . bin2hex(random_bytes(12));
$headers = [
    'From: Shan Communications Website <' . SENDER_EMAIL . '>',
    'Reply-To: ' . $email,
    'MIME-Version: 1.0',
];

if ($attachment !== null && is_string($attachment['data'])) {
    $headers[] = 'Content-Type: multipart/mixed; boundary="' . $boundary . '"';
    $mailBody = '--' . $boundary . "\r\n";
    $mailBody .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $mailBody .= "Content-Transfer-Encoding: 8bit\r\n\r\n" . $body . "\r\n";
    $mailBody .= '--' . $boundary . "\r\n";
    $mailBody .= 'Content-Type: ' . $attachment['mime'] . '; name="' . $attachment['name'] . "\"\r\n";
    $mailBody .= "Content-Transfer-Encoding: base64\r\n";
    $mailBody .= 'Content-Disposition: attachment; filename="' . $attachment['name'] . "\"\r\n\r\n";
    $mailBody .= chunk_split(base64_encode($attachment['data'])) . "\r\n";
    $mailBody .= '--' . $boundary . "--\r\n";
} else {
    $headers[] = 'Content-Type: text/plain; charset=UTF-8';
    $mailBody = $body;
}

$sent = getenv('SHAN_FORM_DRY_RUN') === '1' || mail($destination, $subject, $mailBody, implode("\r\n", $headers));
shan_update_delivery($submissionId, $sent ? 'sent' : 'failed', !empty($sheetsConfig['enabled']) ? 'pending' : 'disabled');
try { shan_sync_submission($submissionId); }
catch (Throwable $error) { error_log('Shan Sheets delivery pending for submission ' . $submissionId); }

if (!$sent) {
    respond(202, true, 'Your submission was saved securely. Our team will review it from the dashboard.');
}

respond(200, true, $successMessage);

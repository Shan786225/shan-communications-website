<?php
declare(strict_types=1);

if (realpath((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === __FILE__) {
    http_response_code(404);
    exit;
}

function shan_config(): array
{
    static $config = null;
    if (is_array($config)) {
        return $config;
    }

    $paths = [];
    $explicit = trim((string)getenv('SHAN_BACKEND_CONFIG'));
    if ($explicit !== '') {
        $paths[] = $explicit;
    }
    $accountHome = rtrim((string)($_SERVER['HOME'] ?? getenv('HOME')), '/');
    if ($accountHome !== '') {
        $paths[] = $accountHome . '/shan_config/backend.php';
    }
    $paths[] = dirname(__DIR__, 2) . '/shan_config/backend.php';

    foreach ($paths as $path) {
        if (is_file($path) && is_readable($path)) {
            $loaded = require $path;
            if (is_array($loaded)) {
                $config = $loaded;
                return $config;
            }
        }
    }

    throw new RuntimeException('The protected backend configuration is unavailable.');
}

function shan_db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $database = shan_config()['database'] ?? [];
    $pdo = new PDO(
        (string)($database['dsn'] ?? ''),
        (string)($database['username'] ?? ''),
        (string)($database['password'] ?? ''),
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    $pdo->exec("SET time_zone = '+00:00'");
    $schema = require __DIR__ . '/_schema.php';
    foreach ($schema as $statement) {
        $pdo->exec($statement);
    }

    return $pdo;
}

function shan_uuid(): string
{
    $bytes = random_bytes(16);
    $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
    $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
    $hex = bin2hex($bytes);
    return substr($hex, 0, 8) . '-' . substr($hex, 8, 4) . '-' . substr($hex, 12, 4) . '-' . substr($hex, 16, 4) . '-' . substr($hex, 20);
}

function shan_request_ip_hash(): string
{
    $config = shan_config();
    $ip = (string)($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    return hash_hmac('sha256', $ip, (string)($config['app_key'] ?? 'shan'));
}

function shan_store_cv(string $publicId, array $attachment): string
{
    $storage = rtrim((string)(shan_config()['storage_dir'] ?? ''), '/');
    if ($storage === '') {
        throw new RuntimeException('The protected CV storage path is unavailable.');
    }
    if (!is_dir($storage) && !mkdir($storage, 0700, true) && !is_dir($storage)) {
        throw new RuntimeException('The protected CV storage directory could not be created.');
    }

    $extension = strtolower(pathinfo((string)$attachment['name'], PATHINFO_EXTENSION));
    $allowed = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
    if (!in_array($extension, $allowed, true)) {
        throw new RuntimeException('The CV file extension is invalid.');
    }
    $storedName = $publicId . '.' . $extension;
    $path = $storage . '/' . $storedName;
    if (file_put_contents($path, (string)$attachment['data'], LOCK_EX) === false) {
        throw new RuntimeException('The CV file could not be stored securely.');
    }
    @chmod($path, 0600);
    return $storedName;
}

function shan_store_submission(array $submission): int
{
    $sql = <<<'SQL'
INSERT INTO shan_submissions (
    public_id, form_type, full_name, email, phone, topic, role_name, experience,
    availability, message, resume_url, resume_file_name, resume_stored_name,
    resume_mime, resume_size, sheets_status, source_url, ip_hash, user_agent
) VALUES (
    :public_id, :form_type, :full_name, :email, :phone, :topic, :role_name, :experience,
    :availability, :message, :resume_url, :resume_file_name, :resume_stored_name,
    :resume_mime, :resume_size, :sheets_status, :source_url, :ip_hash, :user_agent
)
SQL;
    $statement = shan_db()->prepare($sql);
    $statement->execute($submission);
    return (int)shan_db()->lastInsertId();
}

function shan_update_delivery(int $id, string $emailStatus, string $sheetsStatus): void
{
    $allowedEmail = ['pending', 'sent', 'failed'];
    $allowedSheets = ['pending', 'synced', 'failed', 'disabled'];
    if (!in_array($emailStatus, $allowedEmail, true) || !in_array($sheetsStatus, $allowedSheets, true)) {
        return;
    }
    $statement = shan_db()->prepare('UPDATE shan_submissions SET email_status = :email_status, sheets_status = :sheets_status WHERE id = :id');
    $statement->execute(['email_status' => $emailStatus, 'sheets_status' => $sheetsStatus, 'id' => $id]);
}

function shan_mirror_submission(array $payload): string
{
    $sheets = shan_config()['google_sheets'] ?? [];
    if (empty($sheets['enabled']) || empty($sheets['webhook_url']) || empty($sheets['secret'])) {
        return 'disabled';
    }

    $payload['secret'] = (string)$sheets['secret'];
    $json = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if (!is_string($json)) {
        return 'failed';
    }

    $response = false;
    if (function_exists('curl_init')) {
        $handle = curl_init((string)$sheets['webhook_url']);
        if ($handle !== false) {
            curl_setopt_array($handle, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $json,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 3,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_TIMEOUT => 18,
            ]);
            $response = curl_exec($handle);
            $status = (int)curl_getinfo($handle, CURLINFO_HTTP_CODE);
            curl_close($handle);
            if ($status < 200 || $status >= 300) {
                $response = false;
            }
        }
    } else {
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\n",
                'content' => $json,
                'timeout' => 18,
                'ignore_errors' => true,
            ],
        ]);
        $response = @file_get_contents((string)$sheets['webhook_url'], false, $context);
    }

    if (!is_string($response)) {
        return 'failed';
    }
    $decoded = json_decode($response, true);
    return is_array($decoded) && ($decoded['success'] ?? false) === true
        && ($decoded['publicId'] ?? '') === ($payload['publicId'] ?? '') ? 'synced' : 'failed';
}

function shan_sync_submission(int $id): string
{
    if (empty(shan_config()['google_sheets']['enabled'])) { return 'disabled'; }
    $pdo = shan_db();
    // Serialize delivery for each record and read the latest saved state.
    $lockName = 'shan_sheet_' . $id;
    $lock = $pdo->prepare('SELECT GET_LOCK(:name, 0)');
    $lock->execute(['name' => $lockName]);
    if ((int)$lock->fetchColumn() !== 1) { return 'pending'; }
    try {
        $query = $pdo->prepare('SELECT * FROM shan_submissions WHERE id = :id LIMIT 1');
        $query->execute(['id' => $id]);
        $row = $query->fetch();
        if (!$row) { return 'failed'; }
        $status = shan_mirror_submission([
            'publicId' => $row['public_id'], 'submittedAtUtc' => $row['created_at'],
            'formType' => $row['form_type'], 'workflowStatus' => $row['workflow_status'],
            'fullName' => $row['full_name'], 'email' => $row['email'], 'phone' => $row['phone'],
            'topic' => $row['topic'], 'role' => $row['role_name'], 'experience' => $row['experience'],
            'availability' => $row['availability'], 'resumeUrl' => $row['resume_url'],
            'resumeFileName' => $row['resume_file_name'], 'message' => $row['message'],
            'emailStatus' => $row['email_status'],
        ]);
        // A newer review saved during delivery must remain queued for retry.
        $update = $pdo->prepare('UPDATE shan_submissions SET sheets_status = :status WHERE id = :id AND workflow_status = :workflow_status');
        $update->execute(['status' => $status, 'id' => $id, 'workflow_status' => $row['workflow_status']]);
        if ($update->rowCount()) { return $status; }
        $query->execute(['id' => $id]);
        $latest = $query->fetch();
        return $latest && $latest['workflow_status'] === $row['workflow_status']
            && $latest['sheets_status'] === $status ? $status : 'pending';
    } finally {
        $release = $pdo->prepare('SELECT RELEASE_LOCK(:name)');
        $release->execute(['name' => $lockName]);
    }
}

function shan_retry_sheets(int $limit = 5): array
{
    if (empty(shan_config()['google_sheets']['enabled'])) { throw new RuntimeException('Google Sheets is not enabled.'); }
    $pdo = shan_db();
    $rows = $pdo->query("SELECT id FROM shan_submissions WHERE sheets_status <> 'synced' ORDER BY updated_at, id LIMIT " . max(1, min(20, $limit)))->fetchAll();
    $synced = 0;
    foreach ($rows as $row) {
        try {
            $status = shan_sync_submission((int)$row['id']);
            if ($status === 'synced') { $synced++; }
            // Stop on a service failure so a manual retry cannot block for minutes.
            elseif ($status === 'failed') { break; }
        } catch (Throwable $error) {
            error_log('Shan Sheets sync failed for submission ' . (int)$row['id']);
            break;
        }
    }
    $remaining = (int)$pdo->query("SELECT COUNT(*) FROM shan_submissions WHERE sheets_status <> 'synced'")->fetchColumn();
    return ['synced' => $synced, 'remaining' => $remaining];
}

function shan_dashboard_base(): string
{
    // A server-only mount override lets the exact release be tested in staging.
    $base = (string)getenv('SHAN_DASHBOARD_BASE');
    return preg_match('~^/dashboard/(?:[a-zA-Z0-9_-]+/)*$~D', $base) ? $base : '/dashboard/';
}

function shan_dashboard_start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $dashboard = shan_config()['dashboard'] ?? [];
    session_name((string)($dashboard['session_name'] ?? 'shan_dashboard'));
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/dashboard/',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    ini_set('session.use_strict_mode', '1');
    session_start();
    if (isset($_SESSION['last_activity']) && time() - (int)$_SESSION['last_activity'] > 1800) {
        $_SESSION = [];
        session_regenerate_id(true);
    }
    $_SESSION['last_activity'] = time();
}

function shan_dashboard_is_authenticated(): bool
{
    return shan_current_user() !== null;
}

function shan_dashboard_csrf(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(24));
    }
    return (string)$_SESSION['csrf'];
}

function shan_dashboard_verify_csrf(string $token): bool
{
    return isset($_SESSION['csrf']) && hash_equals((string)$_SESSION['csrf'], $token);
}

function shan_dashboard_require_auth(bool $allowPasswordChange = false): void
{
    shan_dashboard_start_session();
    if (!shan_dashboard_is_authenticated()) {
        header('Location: ' . shan_dashboard_base());
        exit;
    }
    if (!$allowPasswordChange && !empty(shan_current_user()['must_change_password'])) {
        header('Location: ' . shan_dashboard_base() . 'account.php'); exit;
    }
}

require_once __DIR__ . '/_access.php';

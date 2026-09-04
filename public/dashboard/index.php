<?php
declare(strict_types=1);

ini_set('display_errors', '0');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: no-referrer');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self'; script-src 'none'; form-action 'self'; frame-ancestors 'none'; base-uri 'none'");

require_once dirname(__DIR__) . '/api/_backend.php';

function dashboard_h(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function dashboard_login_attempt_path(): string
{
    $key = hash('sha256', (string)($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
    return sys_get_temp_dir() . '/shan-dashboard-login-' . $key . '.json';
}

function dashboard_login_attempts(): array
{
    $path = dashboard_login_attempt_path();
    $attempts = [];
    if (is_file($path)) {
        $decoded = json_decode((string)file_get_contents($path), true);
        if (is_array($decoded)) {
            $attempts = $decoded;
        }
    }
    $cutoff = time() - 900;
    $attempts = array_values(array_filter($attempts, static function ($time) use ($cutoff): bool {
        return is_int($time) && $time >= $cutoff;
    }));
    return $attempts;
}

function dashboard_login_rate_limited(): bool
{
    return count(dashboard_login_attempts()) >= 8;
}

function dashboard_record_login_failure(): void
{
    $attempts = dashboard_login_attempts();
    $attempts[] = time();
    @file_put_contents(dashboard_login_attempt_path(), json_encode($attempts), LOCK_EX);
}

function dashboard_clear_login_failures(): void
{
    @unlink(dashboard_login_attempt_path());
}

$configurationError = '';
try {
    $config = shan_config();
    shan_dashboard_start_session();
} catch (Throwable $error) {
    error_log('Shan dashboard configuration error: ' . $error->getMessage());
    $config = [];
    $configurationError = 'The dashboard is temporarily unavailable.';
    http_response_code(503);
}

$notice = '';
$errorMessage = $configurationError;
$action = (string)($_POST['action'] ?? '');

if ($configurationError === '' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (!shan_dashboard_verify_csrf((string)($_POST['csrf'] ?? ''))) {
        $errorMessage = 'Your session expired. Please refresh and try again.';
    } elseif ($action === 'login') {
        $dashboard = $config['dashboard'] ?? [];
        $username = strtolower(trim((string)($_POST['username'] ?? '')));
        $expected = strtolower((string)($dashboard['username'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        if (dashboard_login_rate_limited()) {
            $errorMessage = 'Too many login attempts. Please wait 15 minutes and try again.';
            http_response_code(429);
        } elseif ($expected !== '' && hash_equals($expected, $username) && password_verify($password, (string)($dashboard['password_hash'] ?? ''))) {
            dashboard_clear_login_failures();
            session_regenerate_id(true);
            $_SESSION['shan_authenticated'] = true;
            $_SESSION['last_activity'] = time();
            $_SESSION['csrf'] = bin2hex(random_bytes(24));
            header('Location: /dashboard/');
            exit;
        } else {
            dashboard_record_login_failure();
            $errorMessage = 'The email address or password is incorrect.';
            http_response_code(401);
        }
    } elseif ($action === 'logout' && shan_dashboard_is_authenticated()) {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $parameters = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $parameters['path'], $parameters['domain'], $parameters['secure'], $parameters['httponly']);
        }
        session_destroy();
        header('Location: /dashboard/');
        exit;
    } elseif ($action === 'update' && shan_dashboard_is_authenticated()) {
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        $status = (string)($_POST['workflow_status'] ?? '');
        $notes = trim((string)($_POST['admin_notes'] ?? ''));
        $allowedStatuses = ['new', 'in_progress', 'contacted', 'closed'];
        if ($id === false || !in_array($status, $allowedStatuses, true) || strlen($notes) > 4000) {
            $errorMessage = 'The update could not be validated.';
        } else {
            try {
                $statement = shan_db()->prepare('UPDATE shan_submissions SET workflow_status = :status, admin_notes = :notes WHERE id = :id');
                $statement->execute(['status' => $status, 'notes' => $notes !== '' ? $notes : null, 'id' => $id]);
                $notice = 'Submission updated.';
            } catch (Throwable $error) {
                error_log('Shan dashboard update error: ' . $error->getMessage());
                $errorMessage = 'The submission could not be updated.';
            }
        }
    }
}

$authenticated = $configurationError === '' && shan_dashboard_is_authenticated();
if (!$authenticated):
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow,noarchive">
    <title>Dashboard login | Shan Communications</title>
    <link rel="stylesheet" href="/dashboard/dashboard.css">
</head>
<body class="dashboard-login-body">
    <main class="login-shell">
        <section class="login-brand" aria-label="Shan Communications dashboard">
            <img src="/assets/shan-logo-clean.png" alt="Shan Communications">
            <p>Secure operations dashboard</p>
            <h1>Company inquiries and applications in one place.</h1>
            <span>Authorized access only</span>
        </section>
        <section class="login-panel">
            <div>
                <span class="eyebrow">Administration</span>
                <h2>Sign in</h2>
                <p>Use the administrator email address and password.</p>
            </div>
            <?php if ($errorMessage !== ''): ?><p class="alert alert-error" role="alert"><?= dashboard_h($errorMessage) ?></p><?php endif; ?>
            <?php if ($configurationError === ''): ?>
            <form method="post" class="login-form" autocomplete="on">
                <input type="hidden" name="action" value="login">
                <input type="hidden" name="csrf" value="<?= dashboard_h(shan_dashboard_csrf()) ?>">
                <label><span>Email address</span><input type="email" name="username" required autocomplete="username" autofocus></label>
                <label><span>Password</span><input type="password" name="password" required autocomplete="current-password"></label>
                <button type="submit">Sign in securely <span>↗</span></button>
            </form>
            <?php endif; ?>
            <a href="/">← Return to website</a>
        </section>
    </main>
</body>
</html>
<?php
exit;
endif;

$type = (string)($_GET['type'] ?? 'all');
$status = (string)($_GET['status'] ?? 'all');
$search = trim((string)($_GET['q'] ?? ''));
$page = max(1, (int)($_GET['page'] ?? 1));
$allowedTypes = ['all', 'business', 'job'];
$allowedStatuses = ['all', 'new', 'in_progress', 'contacted', 'closed'];
if (!in_array($type, $allowedTypes, true)) {
    $type = 'all';
}
if (!in_array($status, $allowedStatuses, true)) {
    $status = 'all';
}

$where = [];
$parameters = [];
if ($type !== 'all') {
    $where[] = 'form_type = :form_type';
    $parameters['form_type'] = $type;
}
if ($status !== 'all') {
    $where[] = 'workflow_status = :workflow_status';
    $parameters['workflow_status'] = $status;
}
if ($search !== '') {
    $where[] = '(full_name LIKE :search OR email LIKE :search OR phone LIKE :search OR public_id LIKE :search)';
    $parameters['search'] = '%' . $search . '%';
}
$whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';
$perPage = 25;
$offset = ($page - 1) * $perPage;

try {
    $pdo = shan_db();
    $stats = $pdo->query("SELECT COUNT(*) AS total, SUM(form_type = 'business') AS business_total, SUM(form_type = 'job') AS job_total, SUM(workflow_status = 'new') AS new_total, SUM(DATE(created_at) = UTC_DATE()) AS today_total FROM shan_submissions")->fetch();
    $countStatement = $pdo->prepare('SELECT COUNT(*) FROM shan_submissions' . $whereSql);
    $countStatement->execute($parameters);
    $totalRows = (int)$countStatement->fetchColumn();
    $statement = $pdo->prepare('SELECT * FROM shan_submissions' . $whereSql . ' ORDER BY created_at DESC LIMIT ' . $perPage . ' OFFSET ' . $offset);
    $statement->execute($parameters);
    $submissions = $statement->fetchAll();
} catch (Throwable $error) {
    error_log('Shan dashboard database error: ' . $error->getMessage());
    $stats = ['total' => 0, 'business_total' => 0, 'job_total' => 0, 'new_total' => 0, 'today_total' => 0];
    $totalRows = 0;
    $submissions = [];
    $errorMessage = 'The submission database is temporarily unavailable.';
}

$pages = max(1, (int)ceil($totalRows / $perPage));
$sheets = $config['google_sheets'] ?? [];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow,noarchive">
    <title>Operations dashboard | Shan Communications</title>
    <link rel="stylesheet" href="/dashboard/dashboard.css">
</head>
<body>
    <header class="dashboard-header">
        <a class="dashboard-logo" href="/dashboard/"><img src="/assets/shan-logo-clean.png" alt="Shan Communications"><span>Operations dashboard</span></a>
        <nav>
            <?php if (!empty($sheets['enabled']) && !empty($sheets['sheet_url'])): ?><a href="<?= dashboard_h((string)$sheets['sheet_url']) ?>" target="_blank" rel="noopener noreferrer">Open Google Sheet</a><?php endif; ?>
            <a href="/dashboard/export.php">Export CSV</a>
            <a href="/" target="_blank" rel="noopener noreferrer">View website</a>
            <form method="post"><input type="hidden" name="action" value="logout"><input type="hidden" name="csrf" value="<?= dashboard_h(shan_dashboard_csrf()) ?>"><button type="submit" class="button-quiet">Sign out</button></form>
        </nav>
    </header>

    <main class="dashboard-main">
        <section class="dashboard-intro">
            <div><span class="eyebrow">Live operations</span><h1>Website submissions</h1><p>Business inquiries and job applications received through shancommunication.com.</p></div>
            <div class="connection-card"><span class="connection-dot <?= !empty($sheets['enabled']) ? 'is-live' : '' ?>"></span><div><small>Google Sheets</small><strong><?= !empty($sheets['enabled']) ? 'Connected' : 'Not connected' ?></strong></div></div>
        </section>

        <?php if ($notice !== ''): ?><p class="alert alert-success" role="status"><?= dashboard_h($notice) ?></p><?php endif; ?>
        <?php if ($errorMessage !== ''): ?><p class="alert alert-error" role="alert"><?= dashboard_h($errorMessage) ?></p><?php endif; ?>

        <section class="metric-grid" aria-label="Submission summary">
            <article><span>Total</span><strong><?= (int)($stats['total'] ?? 0) ?></strong><small>All submissions</small></article>
            <article><span>New</span><strong><?= (int)($stats['new_total'] ?? 0) ?></strong><small>Awaiting review</small></article>
            <article><span>Business</span><strong><?= (int)($stats['business_total'] ?? 0) ?></strong><small>Inquiries</small></article>
            <article><span>Jobs</span><strong><?= (int)($stats['job_total'] ?? 0) ?></strong><small>Applications</small></article>
            <article><span>Today</span><strong><?= (int)($stats['today_total'] ?? 0) ?></strong><small>UTC</small></article>
        </section>

        <section class="submission-panel">
            <form method="get" class="filters">
                <label><span>Search</span><input type="search" name="q" value="<?= dashboard_h($search) ?>" placeholder="Name, email, phone or ID"></label>
                <label><span>Type</span><select name="type"><option value="all">All submissions</option><option value="business" <?= $type === 'business' ? 'selected' : '' ?>>Business inquiries</option><option value="job" <?= $type === 'job' ? 'selected' : '' ?>>Job applications</option></select></label>
                <label><span>Status</span><select name="status"><option value="all">All statuses</option><option value="new" <?= $status === 'new' ? 'selected' : '' ?>>New</option><option value="in_progress" <?= $status === 'in_progress' ? 'selected' : '' ?>>In progress</option><option value="contacted" <?= $status === 'contacted' ? 'selected' : '' ?>>Contacted</option><option value="closed" <?= $status === 'closed' ? 'selected' : '' ?>>Closed</option></select></label>
                <button type="submit">Apply filters</button>
                <a href="/dashboard/">Reset</a>
            </form>

            <div class="table-wrap">
                <table>
                    <thead><tr><th>Received</th><th>Type</th><th>Contact</th><th>Interest</th><th>Status</th><th>Delivery</th><th>Details</th></tr></thead>
                    <tbody>
                    <?php if (!$submissions): ?>
                        <tr><td colspan="7" class="empty-state"><strong>No submissions found.</strong><span>New website forms will appear here automatically.</span></td></tr>
                    <?php endif; ?>
                    <?php foreach ($submissions as $submission): ?>
                        <tr>
                            <td data-label="Received"><strong><?= dashboard_h(gmdate('M j, Y', strtotime((string)$submission['created_at']))) ?></strong><small><?= dashboard_h(gmdate('H:i', strtotime((string)$submission['created_at']))) ?> UTC</small></td>
                            <td data-label="Type"><span class="type-pill type-<?= dashboard_h((string)$submission['form_type']) ?>"><?= $submission['form_type'] === 'job' ? 'Job' : 'Business' ?></span></td>
                            <td data-label="Contact"><strong><?= dashboard_h((string)$submission['full_name']) ?></strong><a href="mailto:<?= dashboard_h((string)$submission['email']) ?>"><?= dashboard_h((string)$submission['email']) ?></a><?php if (!empty($submission['phone'])): ?><a href="tel:<?= dashboard_h((string)$submission['phone']) ?>"><?= dashboard_h((string)$submission['phone']) ?></a><?php endif; ?></td>
                            <td data-label="Interest"><strong><?= dashboard_h((string)($submission['form_type'] === 'job' ? $submission['role_name'] : $submission['topic'])) ?></strong><?php if (!empty($submission['experience'])): ?><small><?= dashboard_h((string)$submission['experience']) ?></small><?php endif; ?></td>
                            <td data-label="Status"><span class="status status-<?= dashboard_h((string)$submission['workflow_status']) ?>"><?= dashboard_h(ucwords(str_replace('_', ' ', (string)$submission['workflow_status']))) ?></span></td>
                            <td data-label="Delivery"><small>Email: <?= dashboard_h((string)$submission['email_status']) ?></small><small>Sheet: <?= dashboard_h((string)$submission['sheets_status']) ?></small></td>
                            <td data-label="Details"><details><summary>Review</summary><div class="submission-detail"><div class="detail-head"><span>ID</span><code><?= dashboard_h((string)$submission['public_id']) ?></code></div><dl><?php if (!empty($submission['availability'])): ?><div><dt>Availability</dt><dd><?= dashboard_h((string)$submission['availability']) ?></dd></div><?php endif; ?><?php if (!empty($submission['resume_stored_name'])): ?><div><dt>CV</dt><dd><a href="/dashboard/download.php?id=<?= rawurlencode((string)$submission['public_id']) ?>">Download <?= dashboard_h((string)$submission['resume_file_name']) ?></a></dd></div><?php elseif (!empty($submission['resume_url'])): ?><div><dt>CV</dt><dd><a href="<?= dashboard_h((string)$submission['resume_url']) ?>" target="_blank" rel="noopener noreferrer">Open CV link</a></dd></div><?php endif; ?><div><dt>Message</dt><dd><?= nl2br(dashboard_h((string)$submission['message'])) ?></dd></div></dl><form method="post" class="update-form"><input type="hidden" name="action" value="update"><input type="hidden" name="csrf" value="<?= dashboard_h(shan_dashboard_csrf()) ?>"><input type="hidden" name="id" value="<?= (int)$submission['id'] ?>"><label><span>Workflow status</span><select name="workflow_status"><option value="new" <?= $submission['workflow_status'] === 'new' ? 'selected' : '' ?>>New</option><option value="in_progress" <?= $submission['workflow_status'] === 'in_progress' ? 'selected' : '' ?>>In progress</option><option value="contacted" <?= $submission['workflow_status'] === 'contacted' ? 'selected' : '' ?>>Contacted</option><option value="closed" <?= $submission['workflow_status'] === 'closed' ? 'selected' : '' ?>>Closed</option></select></label><label><span>Internal notes</span><textarea name="admin_notes" rows="3" maxlength="4000"><?= dashboard_h((string)$submission['admin_notes']) ?></textarea></label><button type="submit">Save update</button></form></div></details></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($pages > 1): ?><nav class="pagination" aria-label="Dashboard pages"><?php for ($number = 1; $number <= $pages; $number++): ?><a class="<?= $number === $page ? 'is-current' : '' ?>" href="?q=<?= rawurlencode($search) ?>&amp;type=<?= rawurlencode($type) ?>&amp;status=<?= rawurlencode($status) ?>&amp;page=<?= $number ?>"><?= $number ?></a><?php endfor; ?></nav><?php endif; ?>
        </section>
    </main>
</body>
</html>

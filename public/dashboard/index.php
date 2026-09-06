<?php
declare(strict_types=1);

require_once __DIR__ . '/_common.php';
dashboard_headers();

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
$filters = dashboard_filters($_GET);

if ($configurationError === '' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (!shan_dashboard_verify_csrf((string)($_POST['csrf'] ?? ''))) {
        http_response_code(403);
        $errorMessage = 'Your session expired. Please refresh and try again.';
    } elseif ($action === 'login') {
        $dashboard = $config['dashboard'] ?? [];
        $username = strtolower(trim((string)($_POST['username'] ?? '')));
        $expected = strtolower((string)($dashboard['username'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        if (dashboard_login_rate_limited()) {
            $errorMessage = 'Too many login attempts. Please wait 15 minutes and try again.';
            http_response_code(429);
        } elseif (shan_login_user($username, $password)) {
            dashboard_clear_login_failures();
            header('Location: ' . shan_dashboard_base());
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
        header('Location: ' . shan_dashboard_base());
        exit;
    } elseif ($action === 'sync' && shan_dashboard_is_authenticated()) {
        dashboard_require_permission('sheets.manage');
        try {
            $result = shan_retry_sheets(5);
            dashboard_flash($result['synced'] . ' submission(s) synced. ' . $result['remaining'] . ' still waiting.', $result['remaining'] ? 'warning' : 'success');
            header('Location: ' . dashboard_url($filters), true, 303);
            exit;
        } catch (Throwable $error) {
            error_log('Shan Sheets retry error: ' . $error->getMessage());
            $errorMessage = 'Google Sheets could not be reached. Your submissions are saved; please retry shortly.';
        }
    }
}

$authenticated = $configurationError === '' && shan_dashboard_is_authenticated();
if (!$authenticated):
?>
<?php dashboard_head('Dashboard login'); ?>
<body class="dashboard-login-body">
    <main class="login-shell">
        <section class="login-brand" aria-label="Shan Communications dashboard">
            <img src="/assets/shan-logo-clean.png" alt="Shan Communications">
            <p>Secure operations dashboard</p>
            <h1>Your next conversation starts here.</h1>
            <span>Authorized access only</span>
        </section>
        <section class="login-panel">
            <div>
                <span class="eyebrow">Administration</span>
                <h2>Welcome back.</h2>
                <p>Sign in to review business inquiries and job applications.</p>
            </div>
            <?php if ($errorMessage !== ''): ?><p class="alert alert-error" role="alert"><?= dashboard_h($errorMessage) ?></p><?php endif; ?>
            <?php if ($configurationError === ''): ?>
            <form method="post" class="login-form" autocomplete="on">
                <input type="hidden" name="action" value="login">
                <input type="hidden" name="csrf" value="<?= dashboard_h(shan_dashboard_csrf()) ?>">
                <label><span>Email address</span><input type="email" name="username" required autocomplete="username" autofocus value="<?= dashboard_h($_POST['username'] ?? '') ?>"></label>
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

shan_dashboard_require_auth();
[$whereSql, $parameters] = dashboard_query($filters, dashboard_types());
$type = $filters['type'];
$status = $filters['status'];
$search = $filters['q'];
$perPage = 25;
$page = $filters['page'];
$stats = ['total' => 0, 'business_total' => 0, 'job_total' => 0, 'new_total' => 0, 'today_total' => 0, 'unsynced' => 0];

try {
    $pdo = shan_db();
    [$scope] = dashboard_query(dashboard_filters([]), dashboard_types());
    $stats = $pdo->query("SELECT COUNT(*) AS total, SUM(form_type = 'business') AS business_total, SUM(form_type = 'job') AS job_total, SUM(workflow_status = 'new') AS new_total, SUM(DATE(DATE_ADD(created_at, INTERVAL 5 HOUR)) = DATE(DATE_ADD(UTC_TIMESTAMP(), INTERVAL 5 HOUR))) AS today_total, SUM(sheets_status <> 'synced') AS unsynced FROM shan_submissions" . $scope)->fetch();
    $countStatement = $pdo->prepare('SELECT COUNT(*) FROM shan_submissions' . $whereSql);
    $countStatement->execute($parameters);
    $totalRows = (int)$countStatement->fetchColumn();
    $pages = max(1, (int)ceil($totalRows / $perPage));
    $page = min($page, $pages);
    $filters['page'] = $page;
    $offset = ($page - 1) * $perPage;
    $statement = $pdo->prepare('SELECT * FROM shan_submissions' . $whereSql . ' ORDER BY created_at DESC LIMIT ' . $perPage . ' OFFSET ' . $offset);
    $statement->execute($parameters);
    $submissions = $statement->fetchAll();
} catch (Throwable $error) {
    error_log('Shan dashboard database error: ' . $error->getMessage());
    $totalRows = 0;
    $submissions = [];
    $errorMessage = 'We could not load these results. Please try again; your saved submissions are safe.';
}

$pages = max(1, (int)ceil($totalRows / $perPage));
$sheets = $config['google_sheets'] ?? [];
?>
<?php dashboard_head('Operations dashboard'); ?>
<body>
    <?php dashboard_header($config); ?>
    <main class="dashboard-main" id="dashboard-content">
        <section class="dashboard-intro">
            <div><span class="eyebrow">Operations dashboard</span><h1>Website submissions</h1><p>Review inquiries, manage applications, and keep the next step clear.</p></div>
            <div class="inline-actions"><?php if(dashboard_types('delete')):?><a class="button button-secondary" href="<?= dashboard_h(shan_dashboard_base()) ?>trash.php">Trash</a><?php endif;?><?php if(dashboard_types('export')):?><a class="button button-secondary" href="<?= dashboard_h(dashboard_url($filters, '/dashboard/export.php')) ?>">Export permitted results ↓</a><?php endif;?></div>
        </section>

        <?php if ($notice !== ''): ?><p class="alert alert-success" role="status"><?= dashboard_h($notice) ?></p><?php endif; ?>
        <?php if ($errorMessage !== ''): ?><p class="alert alert-error" role="alert"><?= dashboard_h($errorMessage) ?></p><?php endif; ?>
        <?php dashboard_notice(); ?>

        <section class="metric-grid" aria-label="Submission summary">
            <article><span>Total</span><strong><?= (int)($stats['total'] ?? 0) ?></strong><small>All submissions</small></article>
            <article><span>New</span><strong><?= (int)($stats['new_total'] ?? 0) ?></strong><small>Awaiting review</small></article>
            <article><span>Business</span><strong><?= (int)($stats['business_total'] ?? 0) ?></strong><small>Inquiries</small></article>
            <article><span>Jobs</span><strong><?= (int)($stats['job_total'] ?? 0) ?></strong><small>Applications</small></article>
            <article><span>Today</span><strong><?= (int)($stats['today_total'] ?? 0) ?></strong><small>Pakistan time</small></article>
        </section>

        <?php if(shan_can('sheets.manage')): ?><section class="sync-strip" aria-label="Google Sheets delivery">
            <div><span class="connection-dot <?= !empty($sheets['enabled']) && !(int)$stats['unsynced'] ? 'is-live' : '' ?>"></span><span><strong>Google Sheets</strong><small><?= empty($sheets['enabled']) ? 'Not connected' : ((int)$stats['unsynced'] ? (int)$stats['unsynced'] . ' waiting to sync' : ((int)$stats['total'] ? 'All submissions synced' : 'Ready for submissions')) ?></small></span></div>
            <?php if (!empty($sheets['enabled']) && (int)$stats['unsynced'] > 0): ?><form method="post" action="<?= dashboard_h(dashboard_url($filters)) ?>"><input type="hidden" name="csrf" value="<?= dashboard_h(shan_dashboard_csrf()) ?>"><input type="hidden" name="action" value="sync"><button class="button-secondary" type="submit">Retry pending sync</button></form><?php endif; ?>
        </section><?php endif; ?>

        <section class="submission-panel">
            <form method="get" class="filters">
                <label class="search-field"><span>Search submissions</span><input type="search" name="q" maxlength="200" value="<?= dashboard_h($search) ?>" placeholder="Name, email, phone, role or service"></label>
                <label><span>Type</span><select name="type"><option value="all">All submissions</option><option value="business" <?= $type === 'business' ? 'selected' : '' ?>>Business inquiries</option><option value="job" <?= $type === 'job' ? 'selected' : '' ?>>Job applications</option></select></label>
                <label><span>Status</span><select name="status"><option value="all">All statuses</option><option value="new" <?= $status === 'new' ? 'selected' : '' ?>>New</option><option value="in_progress" <?= $status === 'in_progress' ? 'selected' : '' ?>>In progress</option><option value="contacted" <?= $status === 'contacted' ? 'selected' : '' ?>>Contacted</option><option value="closed" <?= $status === 'closed' ? 'selected' : '' ?>>Closed</option></select></label>
                <button type="submit">Search & filter</button>
                <a href="<?= dashboard_h(shan_dashboard_base()) ?>">Reset</a>
            </form>
            <div class="results-heading"><h2><?= $totalRows ?> <?= $totalRows === 1 ? 'submission' : 'submissions' ?></h2><span>Newest first · Times in PKT (UTC+5)</span></div>

            <div class="table-wrap">
                <table>
                    <thead><tr><th scope="col">Contact / received</th><th scope="col">Type</th><th scope="col">Interest</th><th scope="col">Status</th><th scope="col">Delivery</th><th scope="col">Review</th></tr></thead>
                    <tbody>
                    <?php if (!$submissions): ?>
                        <tr><td colspan="6" class="empty-state"><strong><?= $errorMessage !== '' ? 'Results unavailable' : 'No matching submissions' ?></strong><span><?= $search !== '' || $type !== 'all' || $status !== 'all' ? 'Try a different search or clear your filters.' : 'New business inquiries and job applications will appear here.' ?></span><a href="<?= dashboard_h(shan_dashboard_base()) ?>">Clear filters</a></td></tr>
                    <?php endif; ?>
                    <?php foreach ($submissions as $submission): ?>
                        <tr>
                            <td class="contact-cell" data-label="Contact"><strong><?= dashboard_h($submission['full_name']) ?></strong><a href="mailto:<?= dashboard_h($submission['email']) ?>"><?= dashboard_h($submission['email']) ?></a><small><?= dashboard_h(dashboard_date($submission['created_at'])) ?></small></td>
                            <td data-label="Type"><span class="type-pill type-<?= dashboard_h((string)$submission['form_type']) ?>"><?= $submission['form_type'] === 'job' ? 'Job' : 'Business' ?></span></td>
                            <td data-label="Interest"><strong><?= dashboard_h((string)($submission['form_type'] === 'job' ? $submission['role_name'] : $submission['topic'])) ?></strong><?php if (!empty($submission['experience'])): ?><small><?= dashboard_h((string)$submission['experience']) ?></small><?php endif; ?></td>
                            <td data-label="Status"><span class="status status-<?= dashboard_h((string)$submission['workflow_status']) ?>"><?= dashboard_h(ucwords(str_replace('_', ' ', (string)$submission['workflow_status']))) ?></span></td>
                            <td data-label="Delivery"><small>Email: <?= dashboard_h(dashboard_delivery($submission['email_status'])) ?></small><small>Sheet: <?= dashboard_h(dashboard_delivery($submission['sheets_status'])) ?></small></td>
                            <td class="review-cell"><a class="button button-secondary" aria-label="Review <?= dashboard_h($submission['full_name']) ?> <?= $submission['form_type'] === 'job' ? 'application' : 'inquiry' ?>" href="<?= dashboard_h(dashboard_url(array_merge($filters, ['id' => $submission['public_id']]), '/dashboard/review.php')) ?>">Review →</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($pages > 1): ?><nav class="pagination" aria-label="Submission pages"><?php if ($page > 1): ?><a href="<?= dashboard_h(dashboard_url(array_merge($filters, ['page' => $page - 1]))) ?>">← Previous</a><?php endif; ?><span>Page <?= $page ?> of <?= $pages ?></span><?php if ($page < $pages): ?><a href="<?= dashboard_h(dashboard_url(array_merge($filters, ['page' => $page + 1]))) ?>">Next →</a><?php endif; ?></nav><?php endif; ?>
        </section>
    </main>
</body>
</html>

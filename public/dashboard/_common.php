<?php
declare(strict_types=1);

if (realpath((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === __FILE__) {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__) . '/api/_backend.php';

function dashboard_headers(): void
{
    ini_set('display_errors', '0');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: no-referrer');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('X-Robots-Tag: noindex, nofollow, noarchive');
    header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self'; script-src 'none'; form-action 'self'; frame-ancestors 'none'; base-uri 'none'");
}

function dashboard_h($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function dashboard_statuses(): array
{
    return ['new' => 'New', 'in_progress' => 'In progress', 'contacted' => 'Contacted', 'closed' => 'Closed'];
}

function dashboard_filters(array $input): array
{
    $text = static function (string $key) use ($input): string {
        return isset($input[$key]) && is_string($input[$key]) ? trim($input[$key]) : '';
    };
    $type = $text('type');
    $status = $text('status');
    return [
        'q' => function_exists('mb_substr') ? mb_substr($text('q'), 0, 200) : substr($text('q'), 0, 200),
        'type' => in_array($type, ['business', 'job'], true) ? $type : 'all',
        'status' => isset(dashboard_statuses()[$status]) ? $status : 'all',
        'page' => max(1, (int)(filter_var($text('page'), FILTER_VALIDATE_INT) ?: 1)),
    ];
}

function dashboard_query(array $filters): array
{
    $where = [];
    $parameters = [];
    foreach (['type' => 'form_type', 'status' => 'workflow_status'] as $key => $column) {
        if ($filters[$key] !== 'all') {
            $where[] = $column . ' = :' . $column;
            $parameters[$column] = $filters[$key];
        }
    }
    if ($filters['q'] !== '') {
        $clauses = [];
        // Native PDO prepares require a unique placeholder for every occurrence.
        // Treat LIKE metacharacters literally so searches for '_' or '%' stay precise.
        $pattern = '%' . strtr($filters['q'], ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
        foreach (['full_name', 'email', 'phone', 'public_id', 'role_name', 'topic'] as $column) {
            $clauses[] = $column . " LIKE :search_" . $column . " ESCAPE '!'";
            $parameters['search_' . $column] = $pattern;
        }
        $where[] = '(' . implode(' OR ', $clauses) . ')';
    }
    return [$where ? ' WHERE ' . implode(' AND ', $where) : '', $parameters];
}

function dashboard_url(array $filters, string $path = '/dashboard/'): string
{
    $path = shan_dashboard_base() . substr($path, strlen('/dashboard/'));
    return $path . '?' . http_build_query($filters, '', '&', PHP_QUERY_RFC3986);
}

function dashboard_date(string $date, string $format = 'j M Y, g:i a'): string
{
    return (new DateTimeImmutable($date, new DateTimeZone('UTC')))
        ->setTimezone(new DateTimeZone('Asia/Karachi'))->format($format);
}

function dashboard_flash(string $message, string $type = 'success'): void
{
    $_SESSION['dashboard_flash'] = ['message' => $message, 'type' => $type];
}

function dashboard_notice(): void
{
    $flash = $_SESSION['dashboard_flash'] ?? null;
    unset($_SESSION['dashboard_flash']);
    if (is_array($flash)) {
        $class = $flash['type'] === 'success' ? 'alert-success' : 'alert-warning';
        echo '<p class="alert ' . $class . '" role="status">' . dashboard_h($flash['message']) . '</p>';
    }
}

function dashboard_head(string $title): void
{
    ?>
    <!doctype html><html lang="en"><head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow,noarchive">
    <title><?= dashboard_h($title) ?> | Shan Communications</title>
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="<?= dashboard_h(shan_dashboard_base()) ?>dashboard.css?v=20260905-2">
    </head>
    <?php
}

function dashboard_header(array $config): void
{
    $sheets = $config['google_sheets'] ?? [];
    ?>
    <a class="skip-link" href="#dashboard-content">Skip to content</a>
    <header class="dashboard-header">
        <a class="dashboard-logo" href="<?= dashboard_h(shan_dashboard_base()) ?>"><img src="/assets/shan-logo-clean.png" alt="Shan Communications" width="112" height="72"><span>Operations <small>Business & recruitment</small></span></a>
        <nav aria-label="Dashboard navigation">
            <a href="<?= dashboard_h(shan_dashboard_base()) ?>">Submissions</a>
            <?php if (!empty($sheets['enabled']) && !empty($sheets['sheet_url'])): ?><a href="<?= dashboard_h($sheets['sheet_url']) ?>" target="_blank" rel="noopener noreferrer">Google Sheet ↗</a><?php endif; ?>
            <a href="/" target="_blank" rel="noopener noreferrer">Website ↗</a>
            <form method="post" action="<?= dashboard_h(shan_dashboard_base()) ?>"><input type="hidden" name="action" value="logout"><input type="hidden" name="csrf" value="<?= dashboard_h(shan_dashboard_csrf()) ?>"><button class="button-quiet" type="submit">Sign out</button></form>
        </nav>
    </header>
    <?php
}

function dashboard_delivery(string $status): string
{
    return ['sent' => 'Sent', 'synced' => 'Synced', 'failed' => 'Needs retry', 'pending' => 'Pending', 'disabled' => 'Not connected'][$status] ?? 'Pending';
}

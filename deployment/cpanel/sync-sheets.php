<?php
declare(strict_types=1);
// Install outside public_html; run every five minutes with cPanel Cron Jobs.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require '/home/comshan979/public_html/api/_backend.php';
try {
    $result = shan_retry_sheets(10);
    if ($result['remaining'] > 0) { error_log('Shan Sheets: ' . $result['remaining'] . ' submission(s) awaiting delivery.'); }
} catch (Throwable $error) {
    error_log('Shan Sheets scheduled retry could not complete.');
    exit(1);
}

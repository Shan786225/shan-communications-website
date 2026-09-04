<?php
declare(strict_types=1);

ini_set('display_errors', '0');
require_once dirname(__DIR__) . '/api/_backend.php';
shan_dashboard_require_auth();

function dashboard_csv_value($value): string
{
    $text = (string)$value;
    return preg_match('/^[=+\-@]/', $text) ? "'" . $text : $text;
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="shan-submissions-' . gmdate('Y-m-d') . '.csv"');
header('Cache-Control: private, no-store');

$output = fopen('php://output', 'wb');
if ($output === false) {
    http_response_code(500);
    exit;
}
fwrite($output, "\xEF\xBB\xBF");
fputcsv($output, ['ID', 'Received UTC', 'Type', 'Status', 'Full name', 'Email', 'Phone', 'Area or role', 'Experience', 'Availability', 'CV URL', 'CV file', 'Message', 'Email delivery', 'Google Sheets', 'Internal notes']);

try {
    $statement = shan_db()->query('SELECT * FROM shan_submissions ORDER BY created_at DESC');
    while ($row = $statement->fetch()) {
        fputcsv($output, array_map('dashboard_csv_value', [
            $row['public_id'], $row['created_at'], $row['form_type'], $row['workflow_status'],
            $row['full_name'], $row['email'], $row['phone'], $row['form_type'] === 'job' ? $row['role_name'] : $row['topic'],
            $row['experience'], $row['availability'], $row['resume_url'], $row['resume_file_name'],
            $row['message'], $row['email_status'], $row['sheets_status'], $row['admin_notes'],
        ]));
    }
} catch (Throwable $error) {
    error_log('Shan dashboard export error: ' . $error->getMessage());
}
fclose($output);

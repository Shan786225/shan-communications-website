<?php
declare(strict_types=1);
require __DIR__ . '/../public/dashboard/_common.php';
function check(bool $condition, string $label): void {
    if (!$condition) { throw new RuntimeException($label); }
    echo 'PASS ' . $label . PHP_EOL;
}
$pdo = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$pdo->exec('CREATE TABLE shan_submissions (full_name TEXT,email TEXT,phone TEXT,public_id TEXT,role_name TEXT,topic TEXT,form_type TEXT,workflow_status TEXT)');
$insert = $pdo->prepare('INSERT INTO shan_submissions VALUES (?,?,?,?,?,?,?,?)');
$insert->execute(['Test Business','business@example.test','03100930000','business-id',null,'Customer Experience','business','new']);
$insert->execute(['Test Applicant','applicant@example.test','03100900000','job-id','Customer operations',null,'job','in_progress']);
$insert->execute(['100%_Exact','literal@example.invalid',null,'literal-id',null,'Medical Billing','business','new']);
$cases = [
    [['q' => 'Test'],2], [['q' => 'Test','type' => 'job'],1],
    [['q' => 'applicant@example.test'],1], [['q' => '03100930000'],1],
    [['q' => 'Customer'],2], [['q' => 'Billing','type' => 'job'],0],
    [['q' => 'Test','status' => 'in_progress'],1], [['q' => '%_'],1],
    [['q' => "' OR 1=1 --"],0], [['q' => 'no match'],0],
];
foreach ($cases as [$input,$expected]) {
    [$sql,$params] = dashboard_query(dashboard_filters($input));
    preg_match_all('/:([a-z_]+)/', $sql, $matches);
    check(count($matches[1]) === count(array_unique($matches[1])), 'unique native PDO placeholders: ' . json_encode($input));
    $statement = $pdo->prepare('SELECT COUNT(*) FROM shan_submissions' . $sql);
    $statement->execute($params);
    check((int)$statement->fetchColumn() === $expected, 'search and filters: ' . json_encode($input));
}
check(dashboard_filters(['q'=>['invalid'], 'type'=>'invalid', 'page'=>'-99']) === ['q'=>'','type'=>'all','status'=>'all','page'=>1], 'malformed filters normalized');
check(dashboard_date('2026-09-04 19:18:00','Y-m-d H:i') === '2026-09-05 00:18', 'Pakistan timezone day boundary');

<?php
declare(strict_types=1);
if (realpath((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === __FILE__) { http_response_code(404); exit; }

// Roles are readable presets; the saved grant list is authoritative for staff.
function shan_permission_catalog(): array
{
    return [
        'business.view'=>'View inquiries', 'business.edit'=>'Edit details & notes', 'business.status'=>'Update status',
        'business.delete'=>'Move to Trash & restore', 'business.export'=>'Export inquiries',
        'job.view'=>'View applications', 'job.edit'=>'Edit details & notes', 'job.status'=>'Update status',
        'job.delete'=>'Move to Trash & restore', 'job.export'=>'Export applications', 'job.cv'=>'Open & download CVs',
        'messages'=>'Send & receive staff messages',
    ];
}
function shan_role_presets(): array
{
    return [
        'admin'=>['label'=>'Administrator', 'grants'=>array_keys(shan_permission_catalog())],
        'sub_admin'=>['label'=>'Sub-admin (full access)', 'grants'=>array_keys(shan_permission_catalog())],
        'hr'=>['label'=>'HR', 'grants'=>['job.view','job.edit','job.status','job.cv','messages']],
        'receptionist'=>['label'=>'Receptionist', 'grants'=>['business.view','business.status','messages']],
        'custom'=>['label'=>'Custom access', 'grants'=>['messages']],
    ];
}
function shan_is_admin(array $user): bool { return in_array($user['role'] ?? '', ['admin','sub_admin'], true); }
function shan_user_can(array $user, string $permission): bool
{
    if (empty($user['active']) || !empty($user['must_change_password'])) { return false; }
    if (shan_is_admin($user)) { return isset(shan_permission_catalog()[$permission]) || in_array($permission,['users.manage','sheets.manage','audit.view'],true); }
    $grants = json_decode((string)($user['permissions'] ?? '[]'), true);
    if (!is_array($grants) || !isset(shan_permission_catalog()[$permission]) || !in_array($permission,$grants,true)) { return false; }
    $parts = explode('.', $permission);
    return count($parts) === 1 || in_array($parts[0].'.view',$grants,true);
}
function shan_bootstrap_admin(): void
{
    $pdo = shan_db();
    if ($pdo->query("SELECT COUNT(*) FROM shan_access_meta WHERE name='admin_imported'")->fetchColumn()) { return; }
    $pdo->beginTransaction();
    try {
        $pdo->exec("INSERT IGNORE INTO shan_access_meta(name) VALUES('admin_imported')");
        // INSERT serializes concurrent first logins. Never re-import after password changes.
        if (!$pdo->query('SELECT COUNT(*) FROM shan_users')->fetchColumn()) {
            $old = shan_config()['dashboard'] ?? [];
            if (empty($old['username']) || empty($old['password_hash'])) { throw new RuntimeException('Initial administrator is not configured.'); }
            $q=$pdo->prepare("INSERT INTO shan_users (display_name,email,password_hash,role,permissions,active) VALUES (?,?,?,'admin','[]',1)");
            $q->execute(['Shan Khan',strtolower($old['username']),$old['password_hash']]);
        }
        $pdo->commit();
    } catch(Throwable $e) { $pdo->rollBack(); throw $e; }
}
function shan_current_user(): ?array
{
    if (empty($_SESSION['user_id']) || empty($_SESSION['session_version'])) { return null; }
    static $cache=[];
    $key=$_SESSION['user_id'].':'.$_SESSION['session_version'];
    if(array_key_exists($key,$cache)){return $cache[$key];}
    $q=shan_db()->prepare('SELECT * FROM shan_users WHERE id=? AND active=1 AND session_version=?');
    $q->execute([(int)$_SESSION['user_id'],(int)$_SESSION['session_version']]);
    return $cache[$key]=$q->fetch() ?: null;
}
function shan_can(string $permission): bool { return shan_user_can(shan_current_user() ?? [],$permission); }
function shan_login_user(string $email, string $password): bool
{
    shan_bootstrap_admin();
    $q=shan_db()->prepare('SELECT * FROM shan_users WHERE email=? LIMIT 1'); $q->execute([$email]); $user=$q->fetch();
    // Fixed dummy hash avoids a fast path revealing nonexistent accounts.
    $hash=$user['password_hash'] ?? '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.';
    if (!password_verify($password,$hash) || !$user || !$user['active']) { return false; }
    session_regenerate_id(true);
    $_SESSION=['user_id'=>(int)$user['id'],'session_version'=>(int)$user['session_version'],'last_activity'=>time(),'csrf'=>bin2hex(random_bytes(24))];
    return true;
}
function shan_audit(string $action, string $target, ?PDO $pdo=null): void
{
    $q=($pdo ?? shan_db())->prepare('INSERT INTO shan_audit (actor_id,action,target) VALUES (?,?,?)');
    $q->execute([(int)($_SESSION['user_id'] ?? 0),$action,$target]);
}
function shan_password_valid(string $password): bool { return strlen($password)>=12 && strlen($password)<=72 && strpos($password,"\0")===false; }
function shan_verify_own_password(string $password): bool
{
    $attempts=array_values(array_filter($_SESSION['password_failures'] ?? [],static function($t){return $t>time()-900;}));
    if(count($attempts)>=8){return false;}
    $user=shan_current_user();$ok=$user && password_verify($password,$user['password_hash']);
    if(!$ok){$attempts[]=time();}$_SESSION['password_failures']=$ok?[]:$attempts;return $ok;
}

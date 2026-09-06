<?php
declare(strict_types=1);
if (realpath((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === __FILE__) { http_response_code(404); exit; }

function dashboard_save_user(array $input): int
{
    if (!shan_can('users.manage')) { throw new DomainException('Administrator access is required.'); }
    if (!shan_verify_own_password((string)($input['admin_password'] ?? ''))) { throw new DomainException('Enter your current administrator password to authorize this change.'); }
    $id=(int)($input['id'] ?? 0);
    $name=dashboard_text($input,'display_name'); $email=strtolower(dashboard_text($input,'email')); $role=dashboard_text($input,'role');
    $password=is_string($input['new_password'] ?? null) ? $input['new_password'] : '';
    $active=isset($input['active']) ? 1 : 0;
    if ($name==='' || strlen($name)>120 || strlen($email)>190 || !filter_var($email,FILTER_VALIDATE_EMAIL) || !isset(shan_role_presets()[$role])) { throw new DomainException('Enter a name, valid email address and role.'); }
    if ((!$id || $password!=='') && !shan_password_valid($password)) { throw new DomainException('Use a password of 12–72 bytes (at least 12 characters for English text).'); }
    if ($password!=='' && $password!==($input['confirm_password'] ?? '')) { throw new DomainException('The new passwords do not match.'); }
    $grants=is_array($input['permissions'] ?? null) ? array_values(array_intersect(array_keys(shan_permission_catalog()),$input['permissions'])) : [];
    foreach (['job','business'] as $type) {
        if (!in_array($type.'.view',$grants,true)) { $grants=array_values(array_filter($grants,static function($p) use($type) { return strpos($p,$type.'.')!==0; })); }
    }
    $pdo=shan_db(); $pdo->beginTransaction();
    try {
        // Serialize all account administration, including concurrent last-admin changes.
        $users=$pdo->query('SELECT * FROM shan_users ORDER BY id FOR UPDATE')->fetchAll();
        $target=null; $actor=null; $otherAdmins=0;
        foreach($users as $u) {
            if ((int)$u['id']===$id) { $target=$u; }
            if ((int)$u['id']===(int)$_SESSION['user_id']) { $actor=$u; }
            if ((int)$u['id']!==$id && $u['active'] && shan_is_admin($u)) { $otherAdmins++; }
        }
        if (!$actor || !$actor['active'] || !shan_is_admin($actor) || (int)$actor['session_version']!==(int)$_SESSION['session_version']) { throw new DomainException('Your access changed. Sign in again.'); }
        if ($id && !$target) { throw new DomainException('Account not found.'); }
        $adminRole=in_array($role,['admin','sub_admin'],true);
        if ($id===(int)$actor['id'] && (!$active || !$adminRole || $password!=='')) { throw new DomainException('Keep your own administrator access active. Change your password under My account.'); }
        if ($target && shan_is_admin($target) && (!$active || !$adminRole) && !$otherAdmins) { throw new DomainException('At least one active administrator must remain.'); }
        if ($target && (int)($input['version'] ?? 0)!==(int)$target['session_version']) { throw new DomainException('This account changed in another session. Reload before saving.'); }
        if ($id) {
            $hash=$password!=='' ? password_hash($password,PASSWORD_DEFAULT) : $target['password_hash'];
            $q=$pdo->prepare('UPDATE shan_users SET display_name=?,email=?,role=?,permissions=?,active=?,password_hash=?,must_change_password=?,session_version=session_version+1 WHERE id=?');
            $q->execute([$name,$email,$role,json_encode($grants),$active,$hash,$password!=='' ? 1 : $target['must_change_password'],$id]);
        } else {
            $q=$pdo->prepare('INSERT INTO shan_users (display_name,email,role,permissions,active,password_hash,must_change_password) VALUES (?,?,?,?,?,?,1)');
            $q->execute([$name,$email,$role,json_encode($grants),$active,password_hash($password,PASSWORD_DEFAULT)]); $id=(int)$pdo->lastInsertId();
        }
        shan_audit($target ? ($password!=='' ? 'user.password_reset' : 'user.access_updated') : 'user.created',(string)$id,$pdo);
        $pdo->commit();
        if ($id===(int)$_SESSION['user_id']) { $_SESSION['session_version']++; }
        return $id;
    } catch(Throwable $e) { if($pdo->inTransaction()){$pdo->rollBack();} throw $e; }
}

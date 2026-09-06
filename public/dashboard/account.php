<?php
declare(strict_types=1);
require_once __DIR__.'/_common.php';dashboard_headers();shan_dashboard_require_auth(true);
$user=shan_current_user();$error='';
if(($_SERVER['REQUEST_METHOD'] ?? '')==='POST'){
    dashboard_post_csrf();$old=is_string($_POST['current_password'] ?? null)?$_POST['current_password']:'';$new=is_string($_POST['new_password'] ?? null)?$_POST['new_password']:'';
    if(!shan_verify_own_password($old)){$error='Current password not accepted. After eight failed attempts, wait 15 minutes.';}
    elseif(!shan_password_valid($new)){$error='Use a password of 12–72 bytes (at least 12 characters for English text).';}
    elseif($new!==($_POST['confirm_password'] ?? '')){$error='The new passwords do not match.';}
    elseif(password_verify($new,$user['password_hash'])){$error='Choose a different password from your current one.';}
    else{
        $pdo=shan_db();$pdo->beginTransaction();
        try{
            $q=$pdo->prepare('UPDATE shan_users SET password_hash=?,must_change_password=0,session_version=session_version+1 WHERE id=? AND session_version=? AND active=1');
            $q->execute([password_hash($new,PASSWORD_DEFAULT),$user['id'],$user['session_version']]);
            if($q->rowCount()!==1){throw new DomainException('Your account changed. Sign in again before changing your password.');}
            shan_audit('user.password_changed',(string)$user['id'],$pdo);$pdo->commit();
            session_regenerate_id(true);$_SESSION['session_version']++;$_SESSION['csrf']=bin2hex(random_bytes(24));dashboard_flash('Password changed. Other sessions have been signed out.');
            header('Location: '.shan_dashboard_base().'account.php',true,303);exit;
        }catch(Throwable $e){if($pdo->inTransaction()){$pdo->rollBack();}$error=$e instanceof DomainException?$e->getMessage():'The password could not be changed. Please try again.';}
    }
    http_response_code(422);
}
dashboard_head('My account');
?><body><?php dashboard_header(shan_config());?><main class="dashboard-main account-main" id="dashboard-content"><section class="dashboard-intro"><div><span class="eyebrow">My account</span><h1><?= dashboard_h($user['display_name']) ?></h1><p><?= dashboard_h($user['email']) ?></p></div></section><?php dashboard_notice(); ?>
<?php if($user['must_change_password']):?><p class="alert alert-warning">Replace your temporary password before continuing to the dashboard.</p><?php endif;?><?php if($error):?><p class="alert alert-error" role="alert"><?= dashboard_h($error) ?></p><?php endif;?>
<section class="review-card"><div class="card-heading"><h2>Change password</h2></div><form method="post" class="update-form"><input type="hidden" name="csrf" value="<?= dashboard_h(shan_dashboard_csrf()) ?>"><label><span>Current password</span><input type="password" name="current_password" required autocomplete="current-password"></label><label><span>New password</span><input type="password" name="new_password" required minlength="12" maxlength="72" autocomplete="new-password"></label><label><span>Confirm new password</span><input type="password" name="confirm_password" required minlength="12" maxlength="72" autocomplete="new-password"></label><p class="field-hint">Use 12–72 characters. Changing your password signs out other sessions.</p><button>Change password</button></form></section></main></body></html>

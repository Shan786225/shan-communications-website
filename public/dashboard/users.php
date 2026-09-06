<?php
declare(strict_types=1);
require_once __DIR__.'/_common.php'; require_once __DIR__.'/_users.php';
dashboard_headers(); shan_dashboard_require_auth(); dashboard_require_permission('users.manage');
$error=''; $id=max(0,(int)($_GET['id'] ?? 0)); $presets=shan_role_presets();
$preset=dashboard_text($_GET,'preset'); if(!isset($presets[$preset])){$preset='hr';}
$editing=['id'=>0,'display_name'=>'','email'=>'','role'=>$preset,'permissions'=>json_encode($presets[$preset]['grants']),'active'=>1,'session_version'=>0];
if($id){$q=shan_db()->prepare('SELECT * FROM shan_users WHERE id=?');$q->execute([$id]);$editing=$q->fetch();if(!$editing){dashboard_deny(404,'Account not found.');}}
if(($_SERVER['REQUEST_METHOD'] ?? '')==='POST') {
    dashboard_post_csrf();
    try {
        $saved=dashboard_save_user($_POST); dashboard_flash('Account saved. Access changes take effect immediately. New or reset passwords must be changed at next sign-in.');
        header('Location: '.shan_dashboard_base().'users.php?id='.$saved,true,303);exit;
    } catch(DomainException $e){$error=$e->getMessage();http_response_code(422);}
    catch(PDOException $e){$error=$e->getCode()==='23000' ? 'An account already uses this email address.' : 'The account could not be saved. Please try again.';http_response_code(422);error_log('Shan account save: '.$e->getCode());}
    foreach(['display_name','email','role'] as $key){$editing[$key]=dashboard_text($_POST,$key);}
    $editing['permissions']=json_encode(is_array($_POST['permissions'] ?? null)?$_POST['permissions']:[]);$editing['active']=isset($_POST['active'])?1:0;
}
$users=shan_db()->query('SELECT id,display_name,email,role,active,must_change_password FROM shan_users ORDER BY active DESC,display_name')->fetchAll();
$grants=json_decode($editing['permissions'],true) ?: [];
dashboard_head('Users & access');
?>
<body><?php dashboard_header(shan_config()); ?><main class="dashboard-main" id="dashboard-content">
<section class="dashboard-intro"><div><span class="eyebrow">Administration</span><h1>Users & access</h1><p>Create staff accounts and choose exactly what each person can do.</p></div><a class="button button-secondary" href="<?= dashboard_h(shan_dashboard_base()) ?>audit.php">Activity log</a></section>
<?php dashboard_notice(); ?><?php if($error): ?><p class="alert alert-error" role="alert"><?= dashboard_h($error) ?></p><?php endif; ?>
<div class="access-layout">
<section class="review-card"><div class="card-heading"><h2>Team accounts</h2><a class="button button-secondary" href="<?= dashboard_h(shan_dashboard_base()) ?>users.php?new=1">Add user +</a></div>
<div class="user-list"><?php foreach($users as $u): ?><a class="user-item <?= $id===(int)$u['id']?'is-selected':'' ?>" href="?id=<?= (int)$u['id'] ?>"><span><strong><?= dashboard_h($u['display_name']) ?></strong><small><?= dashboard_h($u['email']) ?></small><small><?= dashboard_h($presets[$u['role']]['label'] ?? $u['role']) ?></small></span><span class="type-pill"><?= $u['active']?'Active':'Disabled' ?></span></a><?php endforeach; ?></div></section>
<section class="review-card"><div class="card-heading"><h2><?= $id?'Edit account':'Create account' ?></h2></div>
<?php if(!$id): ?><form method="get" class="preset-form"><label><span>Start with a role</span><select name="preset"><?php foreach($presets as $key=>$p):?><option value="<?= $key ?>" <?= $preset===$key?'selected':'' ?>><?= dashboard_h($p['label']) ?></option><?php endforeach;?></select></label><button class="button-secondary">Load preset</button></form><?php endif; ?>
<form method="post" class="update-form" autocomplete="off">
<input type="hidden" name="csrf" value="<?= dashboard_h(shan_dashboard_csrf()) ?>"><input type="hidden" name="id" value="<?= (int)$editing['id'] ?>"><input type="hidden" name="version" value="<?= (int)$editing['session_version'] ?>">
<div class="form-columns"><label><span>Full name</span><input name="display_name" required maxlength="120" value="<?= dashboard_h($editing['display_name']) ?>"></label><label><span>Login email</span><input type="email" name="email" required maxlength="190" value="<?= dashboard_h($editing['email']) ?>"></label></div>
<label><span>Account role</span><select name="role"><?php foreach($presets as $key=>$p):?><option value="<?= $key ?>" <?= $editing['role']===$key?'selected':'' ?>><?= dashboard_h($p['label']) ?></option><?php endforeach;?></select></label>
<p class="field-hint">Administrator and Sub-admin have full access, including user management. For HR, Receptionist and Custom access, the checkboxes below determine access; changing the role label alone does not change them.</p>
<div class="permission-groups"><?php foreach(['business'=>'Business inquiries','job'=>'Job applications','messages'=>'Staff messages'] as $group=>$label): ?><fieldset><legend><?= $label ?></legend><?php foreach(shan_permission_catalog() as $key=>$label): if($key!==$group && strpos($key,$group.'.')!==0){continue;} ?><label class="check-option"><input type="checkbox" name="permissions[]" value="<?= $key ?>" <?= in_array($key,$grants,true)?'checked':'' ?>><span><?= $label ?></span></label><?php endforeach;?></fieldset><?php endforeach;?></div>
<p class="field-hint">View access is required for every action in that section. Staff cannot open the shared Google Sheet or change users. Passwords and private conversations are never exported.</p>
<label class="check-option"><input type="checkbox" name="active" value="1" <?= $editing['active']?'checked':'' ?>><span>Account active — uncheck to block sign-in immediately</span></label>
<?php if((int)$editing['id']!==(int)$_SESSION['user_id']): ?><div class="form-columns"><label><span><?= $id?'Reset password (optional)':'Temporary password' ?></span><input type="password" name="new_password" minlength="12" maxlength="72" <?= $id?'':'required' ?> autocomplete="new-password"></label><label><span>Confirm temporary password</span><input type="password" name="confirm_password" minlength="12" maxlength="72" <?= $id?'':'required' ?> autocomplete="new-password"></label></div><p class="field-hint">Use 12–72 characters. Share the temporary password privately; the user must replace it when signing in.</p><?php else: ?><a href="<?= dashboard_h(shan_dashboard_base()) ?>account.php">Change your password in My account →</a><?php endif; ?>
<label><span>Your current admin password</span><input type="password" name="admin_password" required autocomplete="current-password"></label><button type="submit"><?= $id?'Save account & access':'Create account' ?></button>
</form></section></div></main></body></html>

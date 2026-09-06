<?php
declare(strict_types=1);
require_once __DIR__.'/_common.php';dashboard_headers();shan_dashboard_require_auth();if(!dashboard_types('delete')){dashboard_deny();}
if(($_SERVER['REQUEST_METHOD'] ?? '')==='POST'){
    dashboard_post_csrf();$id=dashboard_text($_POST,'id');$action=dashboard_text($_POST,'action');
    if(!in_array($action,['trash','restore'],true)){dashboard_deny(422,'Invalid action.');}
    $q=shan_db()->prepare('SELECT id,form_type FROM shan_submissions WHERE public_id=?');$q->execute([$id]);$row=$q->fetch();
    if(!$row || !shan_can($row['form_type'].'.delete')){dashboard_deny(404,'Submission not available.');}
    $pdo=shan_db();$pdo->beginTransaction();try{
        $q=$pdo->prepare('SELECT id FROM shan_submissions WHERE id=? FOR UPDATE');$q->execute([$row['id']]);
        $q=$pdo->prepare($action==='trash'?'INSERT IGNORE INTO shan_submission_trash (submission_id,deleted_by) VALUES (?,?)':'DELETE FROM shan_submission_trash WHERE submission_id=?');$q->execute($action==='trash'?[$row['id'],$_SESSION['user_id']]:[$row['id']]);
        shan_audit('submission.'.$action,$id,$pdo);$pdo->commit();
        dashboard_flash($action==='trash'?'Moved to Trash. You can restore it here.':'Submission restored.');header('Location: '.shan_dashboard_base().'trash.php',true,303);exit;
    }catch(Throwable $e){if($pdo->inTransaction()){$pdo->rollBack();}dashboard_deny(503,'The change could not be saved. Please try again.');}
}
$filters=dashboard_filters($_GET);[$where,$params]=dashboard_query($filters,dashboard_types('delete'),true);$page=$filters['page'];
$q=shan_db()->prepare('SELECT * FROM shan_submissions'.$where.' ORDER BY created_at DESC LIMIT 25 OFFSET '.(($page-1)*25));$q->execute($params);$rows=$q->fetchAll();dashboard_head('Trash');
?><body><?php dashboard_header(shan_config());?><main class="dashboard-main" id="dashboard-content"><section class="dashboard-intro"><div><span class="eyebrow">Recoverable deletion</span><h1>Trash</h1><p>Removed from dashboard results and exports. Google Sheet copies and previously sent emails are retained.</p></div><a class="button button-secondary" href="<?= dashboard_h(shan_dashboard_base()) ?>">Back to submissions</a></section><?php dashboard_notice();?><section class="review-card"><div class="user-list"><?php foreach($rows as $r):?><article class="user-item"><span><strong><?= dashboard_h($r['full_name']) ?></strong><small><?= dashboard_h($r['email']) ?> · <?= $r['form_type']==='job'?'Job':'Business' ?></small></span><form method="post"><input type="hidden" name="csrf" value="<?= dashboard_h(shan_dashboard_csrf()) ?>"><input type="hidden" name="id" value="<?= dashboard_h($r['public_id']) ?>"><input type="hidden" name="action" value="restore"><button class="button-secondary">Restore</button></form></article><?php endforeach;?><?php if(!$rows):?><p>Trash is empty.</p><?php endif;?></div><nav class="pagination"><?php if($page>1):?><a href="?page=<?= $page-1 ?>">← Previous</a><?php endif;?><?php if(count($rows)===25):?><a href="?page=<?= $page+1 ?>">Next →</a><?php endif;?></nav></section></main></body></html>

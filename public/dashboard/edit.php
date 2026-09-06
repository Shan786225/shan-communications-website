<?php
declare(strict_types=1);
require_once __DIR__.'/_common.php';dashboard_headers();shan_dashboard_require_auth();
$id=dashboard_text($_GET,'id');$q=shan_db()->prepare('SELECT * FROM shan_submissions WHERE public_id=? AND NOT EXISTS (SELECT 1 FROM shan_submission_trash t WHERE t.submission_id=shan_submissions.id)');$q->execute([$id]);$row=$q->fetch();
if(!$row || !shan_can($row['form_type'].'.view')){dashboard_deny(404,'Submission not available.');}dashboard_require_permission($row['form_type'].'.edit');
$fields=['full_name'=>['Full name',120],'email'=>['Email address',190],'phone'=>['Phone',60]];
$fields+=$row['form_type']==='job'?['role_name'=>['Role of interest',180],'experience'=>['Experience',100],'availability'=>['Availability',160]]:['topic'=>['Area of interest',160]];
$fields+=['message'=>['Submitted message',10000]];$draft=$row;$error='';
if(($_SERVER['REQUEST_METHOD'] ?? '')==='POST'){
    dashboard_post_csrf();foreach($fields as $key=>[$label,$max]){$draft[$key]=dashboard_text($_POST,$key);if(strlen($draft[$key])>$max){$error=$label.' is too long.';}}
    if($draft['full_name']==='' || !filter_var($draft['email'],FILTER_VALIDATE_EMAIL)){$error='Enter a full name and valid email address.';}
    if(!$error){
        $pdo=shan_db();$pdo->beginTransaction();try{
            $q=$pdo->prepare('SELECT * FROM shan_submissions WHERE id=? FOR UPDATE');$q->execute([$row['id']]);$latest=$q->fetch();
            if(!hash_equals(hash('sha256',json_encode($latest)),dashboard_text($_POST,'revision'))){throw new DomainException('This submission changed. Reload before saving.');}
            $q=$pdo->prepare('UPDATE shan_submissions SET '.implode('=?,',array_keys($fields)).'=?,sheets_status=? WHERE id=?');
            $values=[];foreach($fields as $key=>$unused){$values[]=$draft[$key];}$values[]=!empty(shan_config()['google_sheets']['enabled'])?'pending':'disabled';$values[]=$row['id'];$q->execute($values);
            shan_audit('submission.details_updated',$id,$pdo);$pdo->commit();
            // The existing queue mirrors the edit without delaying the staff member.
            dashboard_flash('Details saved. Google Sheets will sync automatically.');header('Location: '.shan_dashboard_base().'review.php?id='.rawurlencode($id),true,303);exit;
        }catch(Throwable $e){if($pdo->inTransaction()){$pdo->rollBack();}$error=$e instanceof DomainException?$e->getMessage():'Details could not be saved. Please try again.';}
    }http_response_code(422);
}
dashboard_head('Edit submission');
?><body><?php dashboard_header(shan_config());?><main class="dashboard-main account-main" id="dashboard-content"><a class="back-link" href="review.php?id=<?= rawurlencode($id) ?>">← Back to submission</a><section class="dashboard-intro"><h1>Edit details</h1></section><?php if($error):?><p class="alert alert-error" role="alert"><?= dashboard_h($error) ?></p><?php endif;?><section class="review-card"><form method="post" class="update-form"><input type="hidden" name="csrf" value="<?= dashboard_h(shan_dashboard_csrf()) ?>"><input type="hidden" name="revision" value="<?= hash('sha256',json_encode($row)) ?>"><?php foreach($fields as $key=>[$label,$max]):?><label><span><?= $label ?></span><?php if($key==='message'):?><textarea name="<?= $key ?>" maxlength="<?= $max ?>" rows="5"><?= dashboard_h($draft[$key]) ?></textarea><?php else:?><input type="<?= $key==='email'?'email':'text' ?>" name="<?= $key ?>" maxlength="<?= $max ?>" <?= in_array($key,['full_name','email'],true)?'required':'' ?> value="<?= dashboard_h($draft[$key]) ?>"><?php endif;?></label><?php endforeach;?><button>Save details</button></form></section></main></body></html>

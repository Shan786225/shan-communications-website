<?php
declare(strict_types=1);
require_once __DIR__.'/_common.php';dashboard_headers();shan_dashboard_require_auth();dashboard_require_permission('messages');
$me=(int)$_SESSION['user_id'];$peerId=max(0,(int)($_GET['user'] ?? 0));$before=max(0,(int)($_GET['before'] ?? 0));$error='';$draft='';
$q=shan_db()->prepare('SELECT id,display_name,role,active,permissions,must_change_password FROM shan_users WHERE id<>? ORDER BY display_name');$q->execute([$me]);$people=$q->fetchAll();$peer=null;
foreach($people as $p){if((int)$p['id']===$peerId){$peer=$p;}}
if($peerId && !$peer){dashboard_deny(404,'This staff account was not found.');}
$canSend=$peer && shan_user_can($peer,'messages');
if(($_SERVER['REQUEST_METHOD'] ?? '')==='POST'){
    dashboard_post_csrf();$draft=dashboard_text($_POST,'message');$nonce=dashboard_text($_POST,'nonce');
    if(!$canSend){dashboard_deny(403,'This account is not currently available for messaging.');}
    if($draft==='' || strlen($draft)>16000 || (function_exists('mb_strlen')?mb_strlen($draft):strlen($draft))>4000 || !preg_match('/^[a-f0-9]{48}$/D',$nonce)){$error='Enter a message of up to 4,000 characters.';http_response_code(422);}
    else{
        $pdo=shan_db();$pdo->beginTransaction();
        try{
            // Lock both accounts in stable order, rechecking access after any concurrent admin change.
            $q=$pdo->prepare('SELECT * FROM shan_users WHERE id IN (?,?) ORDER BY id FOR UPDATE');$q->execute([$me,$peerId]);$locked=$q->fetchAll();$allowed=0;
            foreach($locked as $u){if(shan_user_can($u,'messages') && ((int)$u['id']!==$me || (int)$u['session_version']===(int)$_SESSION['session_version'])){$allowed++;}}
            if($allowed!==2){throw new DomainException('Account access changed. Refresh before sending.');}
            $q=$pdo->prepare('SELECT COUNT(*) FROM shan_messages WHERE sender_id=? AND created_at>DATE_SUB(UTC_TIMESTAMP(),INTERVAL 1 MINUTE)');$q->execute([$me]);
            if((int)$q->fetchColumn()>=20){throw new DomainException('Please wait a minute before sending more messages.');}
            $q=$pdo->prepare('INSERT INTO shan_messages (sender_id,recipient_id,body,nonce) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE id=id');$q->execute([$me,$peerId,$draft,$nonce]);$pdo->commit();
            header('Location: '.shan_dashboard_base().'messages.php?user='.$peerId.'#latest',true,303);exit;
        }catch(Throwable $e){if($pdo->inTransaction()){$pdo->rollBack();}$error=$e instanceof DomainException?$e->getMessage():'Message could not be sent. Your draft is below; try again.';http_response_code(422);}
    }
}
$q=shan_db()->prepare('SELECT sender_id,COUNT(*) AS unread FROM shan_messages WHERE recipient_id=? AND read_at IS NULL GROUP BY sender_id');$q->execute([$me]);$unread=array_column($q->fetchAll(),'unread','sender_id');
$rows=[];
if($peer){
    $sql='SELECT * FROM shan_messages WHERE ((sender_id=? AND recipient_id=?) OR (sender_id=? AND recipient_id=?))'.($before?' AND id<?':'').' ORDER BY id DESC LIMIT 50';
    $params=[$me,$peerId,$peerId,$me];if($before){$params[]=$before;}$q=shan_db()->prepare($sql);$q->execute($params);$rows=array_reverse($q->fetchAll());
    if($rows){$q=shan_db()->prepare('UPDATE shan_messages SET read_at=UTC_TIMESTAMP() WHERE recipient_id=? AND sender_id=? AND read_at IS NULL AND id>=? AND id<=?');$q->execute([$me,$peerId,$rows[0]['id'],end($rows)['id']]);}
}
dashboard_head('Staff messages');
?><body><?php dashboard_header(shan_config());?><main class="dashboard-main" id="dashboard-content"><section class="dashboard-intro"><div><span class="eyebrow">Team communication</span><h1>Messages</h1><p>Private, one-to-one conversations inside your dashboard.</p></div><a class="button button-secondary" href="?<?= $peer?'user='.$peerId:'' ?>">Refresh messages</a></section>
<div class="chat-layout"><aside class="review-card"><h2>Team</h2><div class="user-list"><?php foreach($people as $p): if(!$p['active'] && (int)$p['id']!==$peerId){continue;} ?><a class="user-item <?= (int)$p['id']===$peerId?'is-selected':'' ?>" href="?user=<?= (int)$p['id'] ?>"><span><strong><?= dashboard_h($p['display_name']) ?></strong><small><?= dashboard_h(shan_role_presets()[$p['role']]['label'] ?? $p['role']) ?></small></span><?php if(!empty($unread[$p['id']])):?><span class="unread-badge"><?= (int)$unread[$p['id']] ?></span><?php endif;?></a><?php endforeach;?><?php if(!$people):?><p class="field-hint">Create another staff account to start a conversation.</p><?php endif;?></div></aside>
<section class="review-card chat-panel"><?php if($peer):?><div class="card-heading"><h2><?= dashboard_h($peer['display_name']) ?></h2><small>Only you and this person can view this conversation.</small></div>
<?php if(count($rows)===50):?><a class="back-link" href="?user=<?= $peerId ?>&before=<?= (int)$rows[0]['id'] ?>">← Older messages</a><?php endif;?>
<div class="message-thread" aria-label="Conversation"><?php foreach($rows as $m):?><article class="chat-bubble <?= (int)$m['sender_id']===$me?'from-me':'' ?>"><div><?= nl2br(dashboard_h($m['body'])) ?></div><small><?= (int)$m['sender_id']===$me?'You':'Received' ?> · <?= dashboard_h(dashboard_date($m['created_at'])) ?> PKT<?= (int)$m['sender_id']===$me?($m['read_at']?' · Read':' · Sent'):'' ?></small></article><?php endforeach;?><?php if(!$rows):?><p class="empty-conversation">No messages yet. Start a conversation below.</p><?php endif;?></div><div id="latest"></div>
<?php if($error):?><p class="alert alert-error" role="alert"><?= dashboard_h($error) ?></p><?php endif;?>
<?php if($canSend):?><form method="post" class="update-form message-compose"><input type="hidden" name="csrf" value="<?= dashboard_h(shan_dashboard_csrf()) ?>"><input type="hidden" name="nonce" value="<?= dashboard_h($nonce ?? bin2hex(random_bytes(24))) ?>"><label><span>Message to <?= dashboard_h($peer['display_name']) ?></span><textarea name="message" rows="3" maxlength="4000" required placeholder="Write your message…"><?= dashboard_h($draft) ?></textarea></label><button>Send message →</button></form><?php else:?><p class="alert alert-warning">This account cannot currently receive new messages.</p><?php endif;?>
<?php else:?><div class="empty-conversation"><h2>Select a teammate</h2><p>Choose an account to read or send messages.</p></div><?php endif;?></section></div></main></body></html>

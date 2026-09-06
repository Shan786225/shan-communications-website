<?php
declare(strict_types=1);
require_once __DIR__ . '/_common.php';
dashboard_headers();
shan_dashboard_require_auth();
$config = shan_config();
$filters = dashboard_filters($_GET);
$publicId = is_string($_GET['id'] ?? null) ? $_GET['id'] : '';
$backUrl = dashboard_url($filters);
$reviewUrl = dashboard_url(array_merge($filters, ['id' => $publicId]), '/dashboard/review.php');
$errorMessage = '';
$submission = null;

try {
    $statement = shan_db()->prepare('SELECT * FROM shan_submissions WHERE public_id = :id AND NOT EXISTS (SELECT 1 FROM shan_submission_trash t WHERE t.submission_id=shan_submissions.id) LIMIT 1');
    $statement->execute(['id' => $publicId]);
    $submission = $statement->fetch() ?: null;
} catch (Throwable $error) {
    error_log('Shan review load error: ' . $error->getMessage());
    $errorMessage = 'We could not load this submission. Please try again.';
    http_response_code(503);
}

if ($submission && !shan_can($submission['form_type'].'.view')) { dashboard_deny(404,'This submission is not available to your account.'); }
$canEdit=$submission && shan_can($submission['form_type'].'.edit');
$canStatus=$submission && shan_can($submission['form_type'].'.status');

$draftStatus = $submission['workflow_status'] ?? 'new';
$draftNotes = $submission['admin_notes'] ?? '';
if ($submission && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if(!$canEdit && !$canStatus){dashboard_deny();}
    if((isset($_POST['workflow_status']) && !$canStatus && $_POST['workflow_status']!==$draftStatus) || (isset($_POST['admin_notes']) && !$canEdit && $_POST['admin_notes']!==$draftNotes)){dashboard_deny();}
    $draftStatus = $canStatus && is_string($_POST['workflow_status'] ?? null) ? $_POST['workflow_status'] : $draftStatus;
    $draftNotes = $canEdit && is_string($_POST['admin_notes'] ?? null) ? trim($_POST['admin_notes']) : $draftNotes;
    $notesLength = function_exists('mb_strlen') ? mb_strlen($draftNotes) : strlen($draftNotes);
    if (!shan_dashboard_verify_csrf((string)($_POST['csrf'] ?? ''))) {
        http_response_code(403);
        $errorMessage = 'Your session expired. Refresh this page before saving again.';
    } elseif (!isset(dashboard_statuses()[$draftStatus]) || $notesLength > 4000) {
        http_response_code(422);
        $errorMessage = 'Choose a valid status and keep internal notes within 4,000 characters.';
    } else {
        try {
            $pdo = shan_db();
            $pdo->beginTransaction();
            $locked = $pdo->prepare('SELECT * FROM shan_submissions WHERE id = :id FOR UPDATE');
            $locked->execute(['id' => $submission['id']]);
            if (!hash_equals(hash('sha256',json_encode($locked->fetch())),dashboard_text($_POST,'revision'))) {
                $pdo->rollBack();
                $errorMessage = 'This submission changed in another session. Your draft is still below. Reload the latest version before saving.';
                http_response_code(409);
            } else {
                $statement = $pdo->prepare("UPDATE shan_submissions SET workflow_status = :status, admin_notes = :notes, sheets_status = :sheets_status WHERE id = :id");
                $statement->execute([
                    'status' => $draftStatus, 'notes' => $draftNotes !== '' ? $draftNotes : null,
                    'sheets_status' => !empty($config['google_sheets']['enabled']) ? 'pending' : 'disabled', 'id' => $submission['id'],
                ]);
                shan_audit('submission.review_updated',$publicId,$pdo);
                $pdo->commit();
                // A sync failure must never turn a successfully saved review into an error.
                $sync = 'failed';
                try { $sync = shan_sync_submission((int)$submission['id']); }
                catch (Throwable $error) { error_log('Shan review sync error: ' . $error->getMessage()); }
                dashboard_flash($sync === 'synced' ? 'Changes saved. Google Sheets is up to date.' : 'Changes saved. Google Sheets will retry automatically.', $sync === 'synced' ? 'success' : 'warning');
                header('Location: ' . $reviewUrl, true, 303);
                exit;
            }
        } catch (Throwable $error) {
            if (isset($pdo) && $pdo->inTransaction()) { $pdo->rollBack(); }
            error_log('Shan review update error: ' . $error->getMessage());
            $errorMessage = 'Your changes could not be saved. Your draft is still below; please try again.';
            http_response_code(503);
        }
    }
}
if (!$submission && $errorMessage === '') {
    http_response_code(404);
    $errorMessage = 'This submission could not be found.';
}
dashboard_head($submission ? 'Review ' . $submission['full_name'] : 'Submission unavailable');
?>
<body>
<?php dashboard_header($config); ?>
<main class="dashboard-main review-main" id="dashboard-content">
    <a class="back-link" href="<?= dashboard_h($backUrl) ?>">← Back to results</a>
    <?php dashboard_notice(); ?>
    <?php if ($errorMessage !== ''): ?><p class="alert alert-error" role="alert"><?= dashboard_h($errorMessage) ?></p><?php endif; ?>
    <?php if ($submission): $isJob = $submission['form_type'] === 'job'; ?>
    <div class="review-intro">
        <div><span class="eyebrow"><?= $isJob ? 'Job application' : 'Business inquiry' ?></span><h1><?= dashboard_h($submission['full_name']) ?></h1><p>Received <?= dashboard_h(dashboard_date($submission['created_at'])) ?> PKT</p></div>
        <span class="status status-<?= dashboard_h($submission['workflow_status']) ?>"><?= dashboard_h(dashboard_statuses()[$submission['workflow_status']]) ?></span>
    </div>
    <div class="review-grid">
        <section class="review-card contact-summary">
            <div class="card-heading"><span class="section-number">01</span><h2><?= $isJob ? 'Applicant details' : 'Contact details' ?></h2></div>
            <dl class="detail-grid">
                <div><dt>Email address</dt><dd><a href="mailto:<?= dashboard_h($submission['email']) ?>"><?= dashboard_h($submission['email']) ?></a></dd></div>
                <div><dt>Phone / WhatsApp</dt><dd><?php if ($submission['phone']): ?><a href="tel:<?= dashboard_h($submission['phone']) ?>"><?= dashboard_h($submission['phone']) ?></a><?php else: ?>Not provided<?php endif; ?></dd></div>
                <div class="detail-wide"><dt><?= $isJob ? 'Role of interest' : 'Area of interest' ?></dt><dd><?= dashboard_h($isJob ? $submission['role_name'] : $submission['topic']) ?></dd></div>
                <?php if ($isJob): ?><div><dt>Relevant experience</dt><dd><?= dashboard_h($submission['experience'] ?: 'Not provided') ?></dd></div><div><dt>Availability</dt><dd><?= dashboard_h($submission['availability'] ?: 'Not provided') ?></dd></div><?php endif; ?>
            </dl>
            <?php if ($isJob && shan_can('job.cv')): ?><div class="cv-card"><div><strong>Curriculum vitae</strong><span><?= dashboard_h($submission['resume_file_name'] ?: ($submission['resume_url'] ? 'Submitted as a link' : 'No CV provided')) ?></span></div><?php if ($submission['resume_stored_name']): ?><a class="button button-secondary" href="<?= dashboard_h(shan_dashboard_base()) ?>download.php?id=<?= rawurlencode($publicId) ?>">Download CV ↓</a><?php elseif ($submission['resume_url']): ?><a class="button button-secondary" href="<?= dashboard_h($submission['resume_url']) ?>" target="_blank" rel="noopener noreferrer">Open CV ↗</a><?php endif; ?></div><?php endif; ?>
            <?php if($canEdit):?><p><a class="button button-secondary" href="<?= dashboard_h(shan_dashboard_base()) ?>edit.php?id=<?= rawurlencode($publicId) ?>">Edit submission details</a></p><?php endif;?>
        </section>
        <section class="review-card workflow-card">
            <div class="card-heading"><span class="section-number">02</span><h2>Manage submission</h2></div>
            <form method="post" action="<?= dashboard_h($reviewUrl) ?>" class="update-form">
                <input type="hidden" name="csrf" value="<?= dashboard_h(shan_dashboard_csrf()) ?>">
                <input type="hidden" name="updated_at" value="<?= dashboard_h($submission['updated_at']) ?>">
                <input type="hidden" name="revision" value="<?= hash('sha256',json_encode($submission)) ?>">
                <fieldset <?= $canStatus?'':'disabled' ?>><legend>Workflow status</legend><div class="status-options">
                    <?php foreach (dashboard_statuses() as $value => $label): ?><label class="status-option"><input type="radio" name="workflow_status" value="<?= $value ?>" <?= $draftStatus === $value ? 'checked' : '' ?> required><span><?= $label ?></span></label><?php endforeach; ?>
                </div></fieldset>
                <label><span>Internal notes</span><textarea name="admin_notes" rows="5" maxlength="4000" <?= $canEdit?'':'readonly' ?> placeholder="Record the next step, follow-up, or team notes."><?= dashboard_h($draftNotes) ?></textarea></label>
                <p class="field-hint">Notes stay private in this dashboard. Status changes also update Google Sheets.</p>
                <?php if($canEdit || $canStatus):?><button type="submit">Save changes</button><?php else:?><p class="field-hint">Your access to this submission is view-only.</p><?php endif;?>
            </form>
        </section>
        <section class="review-card message-card">
            <div class="card-heading"><span class="section-number">03</span><h2><?= $isJob ? 'Experience summary' : 'Project details' ?></h2></div>
            <div class="message-content"><?= nl2br(dashboard_h($submission['message'])) ?></div>
        </section>
        <section class="review-card receipt-card">
            <h2>Delivery & reference</h2>
            <dl class="detail-grid">
                <div><dt>Email notification</dt><dd><?= dashboard_h(dashboard_delivery($submission['email_status'])) ?></dd></div>
                <div><dt>Google Sheets</dt><dd><?= dashboard_h(dashboard_delivery($submission['sheets_status'])) ?></dd></div>
                <div class="detail-wide"><dt>Submission ID</dt><dd class="reference-id"><?= dashboard_h($publicId) ?></dd></div>
            </dl>
            <?php if(shan_can($submission['form_type'].'.delete')):?><details class="trash-confirm"><summary>Move this submission to Trash</summary><p>It can be restored. Existing Google Sheet copies and sent emails are retained.</p><form method="post" action="<?= dashboard_h(shan_dashboard_base()) ?>trash.php"><input type="hidden" name="csrf" value="<?= dashboard_h(shan_dashboard_csrf()) ?>"><input type="hidden" name="id" value="<?= dashboard_h($publicId) ?>"><input type="hidden" name="action" value="trash"><button class="button-danger">Confirm move to Trash</button></form></details><?php endif;?>
        </section>
    </div>
    <?php endif; ?>
</main>
</body></html>

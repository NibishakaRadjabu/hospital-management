<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_permission('work_logs.create');
$categories = work_log_categories();

$pageTitle = 'Record work';
require __DIR__ . '/../includes/header.php';
?>
<div class="page-heading">
    <div><h2>Record completed work</h2><p>Keep a clear operational record of work you completed.</p></div>
    <a class="btn btn-outline-secondary" href="/hospital%20management/work-log/index.php">Back to work log</a>
</div>
<form class="content-card form-card mt-0" method="post" action="/hospital%20management/work-log/save.php">
    <?= csrf_field() ?>
    <div class="form-section-title">Work entry</div>
    <div class="row g-3">
        <div class="col-md-4"><label class="form-label" for="work_date">Work date <span class="text-danger">*</span></label><input class="form-control" id="work_date" name="work_date" type="date" max="<?= date('Y-m-d') ?>" value="<?= e((string) ($_POST['work_date'] ?? date('Y-m-d'))) ?>" required></div>
        <div class="col-md-8"><label class="form-label" for="category">Category <span class="text-danger">*</span></label><select class="form-select" id="category" name="category" required><option value="">Select a category</option><?php foreach ($categories as $key => $label): ?><option value="<?= e($key) ?>" <?= ($_POST['category'] ?? '') === $key ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></div>
        <div class="col-12"><label class="form-label" for="summary">Work summary <span class="text-danger">*</span></label><input class="form-control" id="summary" name="summary" maxlength="160" required value="<?= e((string) ($_POST['summary'] ?? '')) ?>" placeholder="Briefly describe the work completed"></div>
        <div class="col-12"><label class="form-label" for="details">Work details <span class="text-danger">*</span></label><textarea class="form-control" id="details" name="details" rows="5" maxlength="5000" required><?= e((string) ($_POST['details'] ?? '')) ?></textarea><div class="form-text">Describe the work and outcome. Do not include unnecessary patient-identifying or confidential clinical details.</div></div>
        <div class="col-md-4"><label class="form-label" for="duration_minutes">Time spent (minutes)</label><input class="form-control" id="duration_minutes" name="duration_minutes" type="number" min="1" max="1440" value="<?= e((string) ($_POST['duration_minutes'] ?? '')) ?>"><div class="form-text">Optional; maximum 24 hours.</div></div>
    </div>
    <div class="d-flex justify-content-end gap-2 mt-4"><a class="btn btn-outline-secondary" href="/hospital%20management/work-log/index.php">Cancel</a><button class="btn btn-primary" type="submit">Save work entry</button></div>
</form>
<?php require __DIR__ . '/../includes/footer.php'; ?>

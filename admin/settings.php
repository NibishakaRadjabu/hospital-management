<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_permission('settings.manage');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim($_POST['hospital_name'] ?? '');
    if ($name !== '') {
        $pdo->prepare(
            "INSERT INTO hospital_settings (setting_key, setting_value)
             VALUES ('hospital_name', :val)
             ON DUPLICATE KEY UPDATE setting_value = :val"
        )->execute(['val' => $name]);
        record_audit('update', 'hospital_settings', 'hospital_name', 'Hospital name changed to: ' . $name);
        set_flash('success', 'Hospital name updated.');
    } else {
        set_flash('danger', 'Hospital name cannot be empty.');
    }
    redirect('/hospital%20management/admin/settings.php');
}

$currentName = get_hospital_name();
$pageTitle = 'Hospital Settings';
require __DIR__ . '/../includes/header.php';
?>
<div class="page-heading">
    <div><h2>Hospital Settings</h2><p>Configure global hospital information.</p></div>
</div>
<div class="row">
    <div class="col-12 col-md-6">
        <section class="content-card mt-0">
            <div class="card-heading"><h3>Hospital name</h3></div>
            <form method="post" class="form-card">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label" for="hospital_name">Display name</label>
                    <input class="form-control" id="hospital_name" name="hospital_name"
                           type="text" maxlength="120" required
                           value="<?= e($currentName) ?>">
                    <div class="form-text">Shown in the sidebar, browser tab, and printed documents.</div>
                </div>
                <button class="btn btn-primary" type="submit">Save</button>
            </form>
        </section>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>

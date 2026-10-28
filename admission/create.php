<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_permission('admissions.create');

$patients = $pdo->query('SELECT id, patient_no, first_name, last_name FROM patients ORDER BY last_name, first_name')->fetchAll();
$beds = $pdo->query(
    "SELECT b.id, b.bed_number, w.name AS ward_name
     FROM beds b JOIN wards w ON w.id = b.ward_id
     WHERE b.status = 'available' ORDER BY w.name, b.bed_number"
)->fetchAll();

$pageTitle = 'Admit patient';
require __DIR__ . '/../includes/header.php';
?>
<div class="page-heading">
    <div><h2>New admission</h2><p>Select a patient and an available bed to start an inpatient stay.</p></div>
    <a class="btn btn-outline-secondary" href="/hospital%20management/admission/index.php">Cancel</a>
</div>
<?php if ($patients === []): ?>
    <div class="alert alert-warning">Register a patient before creating an admission. <a href="/hospital%20management/patient/form.php">Register patient</a></div>
<?php elseif ($beds === []): ?>
    <div class="alert alert-warning">There are no available beds. Set up ward beds or discharge an existing patient before continuing.</div>
<?php else: ?>
    <form class="content-card form-card mt-0" method="post" action="/hospital%20management/admission/save.php">
        <?= csrf_field() ?>
        <div class="form-section-title">Admission details</div>
        <div class="row g-3">
            <div class="col-12"><label class="form-label" for="patient_id">Patient <span class="text-danger">*</span></label>
                <select class="form-select" id="patient_id" name="patient_id" required>
                    <option value="">Select a patient</option>
                    <?php foreach ($patients as $patient): ?>
                        <option value="<?= (int) $patient['id'] ?>" <?= (string) ($_GET['patient_id'] ?? '') === (string) $patient['id'] ? 'selected' : '' ?>><?= e($patient['patient_no'] . ' · ' . $patient['first_name'] . ' ' . $patient['last_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12"><label class="form-label" for="bed_id">Available bed <span class="text-danger">*</span></label>
                <select class="form-select" id="bed_id" name="bed_id" required>
                    <option value="">Select a bed</option>
                    <?php foreach ($beds as $bed): ?>
                        <option value="<?= (int) $bed['id'] ?>"><?= e($bed['ward_name'] . ' · ' . $bed['bed_number']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12"><label class="form-label" for="reason">Admission reason <span class="text-danger">*</span></label><textarea class="form-control" id="reason" name="reason" rows="4" maxlength="4000" required></textarea></div>
        </div>
        <div class="d-flex justify-content-end gap-2 mt-4"><a class="btn btn-outline-secondary" href="/hospital%20management/admission/index.php">Cancel</a><button class="btn btn-primary" type="submit">Confirm admission</button></div>
    </form>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>

<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_permission('patient_services.create');
$categories = patient_service_categories();
$patientNo = trim((string) ($_GET['patient_no'] ?? ''));

$pageTitle = 'Record patient service';
require __DIR__ . '/../includes/header.php';
?>
<div class="page-heading">
    <div><h2>Record patient service</h2><p>Document a service you personally provided to a patient.</p></div>
    <a class="btn btn-outline-secondary" href="/hospital%20management/patient-services/index.php">Back to services</a>
</div>
<div class="alert alert-info py-2">Patient services are part of the patient record. Only enter accurate information needed for care.</div>
<form class="content-card form-card mt-0" method="post" action="/hospital%20management/patient-services/save.php">
    <?= csrf_field() ?>
    <div class="form-section-title">Service details</div>
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label" for="patient_no">Patient number <span class="text-danger">*</span></label><input class="form-control" id="patient_no" name="patient_no" maxlength="30" required value="<?= e($patientNo) ?>" placeholder="PT-YYMMDD-XXXXXXXXXX"><div class="form-text">Use the patient number shown on the patient card.</div></div>
        <div class="col-md-6"><label class="form-label" for="service_date">Service date <span class="text-danger">*</span></label><input class="form-control" id="service_date" name="service_date" type="date" max="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>" required></div>
        <div class="col-md-6"><label class="form-label" for="category">Service type <span class="text-danger">*</span></label><select class="form-select" id="category" name="category" required><option value="">Select type</option><?php foreach ($categories as $key => $label): ?><option value="<?= e($key) ?>"><?= e($label) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-6"><label class="form-label" for="service_name">Service provided <span class="text-danger">*</span></label><input class="form-control" id="service_name" name="service_name" maxlength="160" required placeholder="e.g. Wound dressing"></div>
        <div class="col-12"><label class="form-label" for="details">Service notes <span class="text-danger">*</span></label><textarea class="form-control" id="details" name="details" rows="5" maxlength="5000" required placeholder="Record the service and relevant outcome. Do not include unrelated information."></textarea></div>
        <div class="col-md-4"><label class="form-label" for="duration_minutes">Time spent (minutes)</label><input class="form-control" id="duration_minutes" name="duration_minutes" type="number" min="1" max="1440"><div class="form-text">Optional; maximum 24 hours.</div></div>
    </div>
    <div class="d-flex justify-content-end gap-2 mt-4"><a class="btn btn-outline-secondary" href="/hospital%20management/patient-services/index.php">Cancel</a><button class="btn btn-primary" type="submit">Save service record</button></div>
</form>
<?php require __DIR__ . '/../includes/footer.php'; ?>

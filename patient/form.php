<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_login();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($id === false || ($id !== null && $id < 1)) {
    http_response_code(400);
    exit('A valid patient ID is required.');
}
require_permission($id === null ? 'patients.create' : 'patients.update');
$patient = [
    'id' => null, 'patient_no' => '', 'first_name' => '', 'last_name' => '',
    'date_of_birth' => '', 'sex' => 'unspecified', 'phone' => '', 'email' => '',
    'address' => '', 'blood_group' => '', 'emergency_contact_name' => '',
    'emergency_contact_phone' => '', 'notes' => '',
];

if ($id !== null && $id !== false) {
    $statement = $pdo->prepare('SELECT * FROM patients WHERE id = :id');
    $statement->execute(['id' => $id]);
    $existingPatient = $statement->fetch();
    if (!$existingPatient) {
        http_response_code(404);
        exit('Patient not found.');
    }
    $patient = $existingPatient;
}

$pageTitle = $patient['id'] === null ? 'Register patient' : 'Edit patient';
require __DIR__ . '/../includes/header.php';
?>
<div class="page-heading">
    <div><h2><?= e($pageTitle) ?></h2><p>Patient information is confidential. Only enter details required for care.</p></div>
    <a class="btn btn-outline-secondary" href="<?= $patient['id'] === null ? '/hospital%20management/patient/index.php' : '/hospital%20management/patient/show.php?id=' . (int) $patient['id'] ?>">Cancel</a>
</div>
<form class="content-card form-card mt-0" method="post" action="/hospital%20management/patient/save.php">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= e((string) ($patient['id'] ?? '')) ?>">
    <div class="form-section-title">Personal details</div>
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label" for="first_name">First name <span class="text-danger">*</span></label><input class="form-control" id="first_name" name="first_name" maxlength="80" required value="<?= e($patient['first_name']) ?>"></div>
        <div class="col-md-6"><label class="form-label" for="last_name">Last name <span class="text-danger">*</span></label><input class="form-control" id="last_name" name="last_name" maxlength="80" required value="<?= e($patient['last_name']) ?>"></div>
        <div class="col-md-4"><label class="form-label" for="date_of_birth">Date of birth</label><input class="form-control" id="date_of_birth" name="date_of_birth" type="date" max="<?= date('Y-m-d') ?>" value="<?= e($patient['date_of_birth']) ?>"></div>
        <div class="col-md-4"><label class="form-label" for="sex">Sex</label><select class="form-select" id="sex" name="sex"><?php foreach (['unspecified' => 'Prefer not to say', 'female' => 'Female', 'male' => 'Male', 'other' => 'Other'] as $value => $label): ?><option value="<?= e($value) ?>" <?= $patient['sex'] === $value ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-4"><label class="form-label" for="blood_group">Blood group</label><select class="form-select" id="blood_group" name="blood_group"><option value="">Not recorded</option><?php foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $group): ?><option value="<?= e($group) ?>" <?= $patient['blood_group'] === $group ? 'selected' : '' ?>><?= e($group) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-6"><label class="form-label" for="phone">Phone</label><input class="form-control" id="phone" name="phone" maxlength="40" value="<?= e($patient['phone']) ?>"></div>
        <div class="col-md-6"><label class="form-label" for="email">Email</label><input class="form-control" id="email" name="email" type="email" maxlength="190" value="<?= e($patient['email']) ?>"></div>
        <div class="col-12"><label class="form-label" for="address">Address</label><input class="form-control" id="address" name="address" maxlength="255" value="<?= e($patient['address']) ?>"></div>
    </div>
    <div class="form-section-title mt-4">Emergency contact</div>
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label" for="emergency_contact_name">Contact name</label><input class="form-control" id="emergency_contact_name" name="emergency_contact_name" maxlength="160" value="<?= e($patient['emergency_contact_name']) ?>"></div>
        <div class="col-md-6"><label class="form-label" for="emergency_contact_phone">Contact phone</label><input class="form-control" id="emergency_contact_phone" name="emergency_contact_phone" maxlength="40" value="<?= e($patient['emergency_contact_phone']) ?>"></div>
        <div class="col-12"><label class="form-label" for="notes">Registration notes</label><textarea class="form-control" id="notes" name="notes" rows="3"><?= e($patient['notes']) ?></textarea></div>
    </div>
    <div class="d-flex justify-content-end gap-2 mt-4"><a class="btn btn-outline-secondary" href="<?= $patient['id'] === null ? '/hospital%20management/patient/index.php' : '/hospital%20management/patient/show.php?id=' . (int) $patient['id'] ?>">Cancel</a><button class="btn btn-primary" type="submit"><?= $patient['id'] === null ? 'Register patient' : 'Save changes' ?></button></div>
</form>
<?php require __DIR__ . '/../includes/footer.php'; ?>

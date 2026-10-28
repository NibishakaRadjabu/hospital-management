<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_permission('patients.view');

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    http_response_code(400);
    exit('A valid patient ID is required.');
}
$statement = $pdo->prepare('SELECT * FROM patients WHERE id = :id');
$statement->execute(['id' => $id]);
$patient = $statement->fetch();
if (!$patient) {
    http_response_code(404);
    exit('Patient not found.');
}
if (current_user()['role_key'] === 'doctor') {
    $access = $pdo->prepare(
        'SELECT d.id FROM doctors d
         WHERE d.user_id = :user_id
           AND (EXISTS (SELECT 1 FROM appointments a WHERE a.patient_id = :appointment_patient AND a.doctor_id = d.id)
                OR EXISTS (SELECT 1 FROM visits v WHERE v.patient_id = :visit_patient AND v.doctor_id = d.id))'
    );
    $access->execute([
        'user_id' => current_user()['id'],
        'appointment_patient' => $id,
        'visit_patient' => $id,
    ]);
    if (!$access->fetch()) { http_response_code(403); exit('You can only view patients assigned to your care.'); }
}

$visits = [];
if (has_permission('patients.history')) {
    $visitsQuery = $pdo->prepare('SELECT visited_at, chief_complaint, diagnosis, notes FROM visits WHERE patient_id = :id ORDER BY visited_at DESC LIMIT 20');
    $visitsQuery->execute(['id' => $id]);
    $visits = $visitsQuery->fetchAll();
}
$admissions = [];
if (has_permission('admissions.view')) {
    $admissionsQuery = $pdo->prepare(
        'SELECT a.admitted_at, a.discharged_at, a.status, a.reason, w.name AS ward_name, b.bed_number
         FROM admissions a JOIN beds b ON b.id = a.bed_id JOIN wards w ON w.id = b.ward_id
         WHERE a.patient_id = :id ORDER BY a.admitted_at DESC LIMIT 20'
    );
    $admissionsQuery->execute(['id' => $id]);
    $admissions = $admissionsQuery->fetchAll();
}
$patientServices = [];
if (has_permission('patients.history') || has_permission('patient_services.view_all') || has_permission('patient_services.view_own')) {
    $serviceFilters = ['ps.patient_id = :patient_id'];
    $serviceParams = ['patient_id' => $id];
    if (!has_permission('patients.history') && !has_permission('patient_services.view_all')) {
        $serviceFilters[] = 'ps.provided_by = :current_user_id';
        $serviceParams['current_user_id'] = current_user()['id'];
    }
    $servicesQuery = $pdo->prepare(
        'SELECT ps.service_date, ps.category, ps.service_name, ps.details, ps.duration_minutes,
                u.full_name AS staff_name
         FROM patient_services ps JOIN users u ON u.id = ps.provided_by
         WHERE ' . implode(' AND ', $serviceFilters) . '
         ORDER BY ps.service_date DESC, ps.created_at DESC'
    );
    $servicesQuery->execute($serviceParams);
    $patientServices = $servicesQuery->fetchAll();
}

$pageTitle = 'Patient record';
require __DIR__ . '/../includes/header.php';
?>
<div class="page-heading">
    <div><h2><?= e($patient['first_name'] . ' ' . $patient['last_name']) ?></h2><p><?= e($patient['patient_no']) ?> · Registered <?= e(format_date($patient['created_at'])) ?></p></div>
    <div class="d-flex gap-2"><a class="btn btn-outline-secondary" href="/hospital%20management/patient/index.php">Back to patients</a><?php if (has_permission('patient_services.create')): ?><a class="btn btn-outline-secondary" href="/hospital%20management/patient-services/create.php?patient_no=<?= rawurlencode($patient['patient_no']) ?>">Record service</a><?php endif; ?><?php if (has_permission('patients.update')): ?><a class="btn btn-primary" href="/hospital%20management/patient/form.php?id=<?= (int) $patient['id'] ?>">Edit patient</a><?php endif; ?></div>
</div>
<section class="content-card mt-0">
    <div class="card-heading"><h3>Patient information</h3></div>
    <div class="detail-grid">
        <div class="detail-item"><span>Full name</span><strong><?= e($patient['first_name'] . ' ' . $patient['last_name']) ?></strong></div>
        <div class="detail-item"><span>Patient ID</span><strong><?= e($patient['patient_no']) ?></strong></div>
        <div class="detail-item"><span>Date of birth</span><strong><?= e(format_date($patient['date_of_birth'])) ?></strong></div>
        <div class="detail-item"><span>Sex</span><strong><?= e(ucfirst($patient['sex'])) ?></strong></div>
        <div class="detail-item"><span>Phone</span><strong><?= e($patient['phone'] ?: '—') ?></strong></div>
        <div class="detail-item"><span>Email</span><strong><?= e($patient['email'] ?: '—') ?></strong></div>
        <div class="detail-item"><span>Blood group</span><strong><?= e($patient['blood_group'] ?: 'Not recorded') ?></strong></div>
        <div class="detail-item"><span>Address</span><strong><?= e($patient['address'] ?: '—') ?></strong></div>
        <div class="detail-item"><span>Emergency contact</span><strong><?= e($patient['emergency_contact_name'] ?: '—') ?></strong></div>
        <div class="detail-item"><span>Emergency phone</span><strong><?= e($patient['emergency_contact_phone'] ?: '—') ?></strong></div>
        <?php if ($patient['notes']): ?><div class="detail-item"><span>Registration notes</span><strong><?= nl2br(e($patient['notes'])) ?></strong></div><?php endif; ?>
    </div>
</section>
<?php if (has_permission('patients.history') || has_permission('patient_services.view_all') || has_permission('patient_services.view_own')): ?>
<section class="content-card">
    <div class="card-heading"><h3>Services provided</h3><div class="d-flex gap-2"><button class="btn btn-sm btn-outline-secondary" type="button" data-print-document="patient-services-print">Print services</button><?php if (has_permission('patient_services.create')): ?><a href="/hospital%20management/patient-services/create.php?patient_no=<?= rawurlencode($patient['patient_no']) ?>">Record service</a><?php endif; ?></div></div>
    <div class="table-responsive"><table class="table"><thead><tr><th>Date</th><th>Service</th><th>Details</th><th>Provided by</th><th>Time</th></tr></thead><tbody>
    <?php if ($patientServices === []): ?><tr><td class="empty-state" colspan="5">No service records available to your account.</td></tr><?php else: foreach ($patientServices as $service): ?>
        <tr><td><?= e(format_date($service['service_date'])) ?></td><td><span class="badge-neutral"><?= e(patient_service_categories()[$service['category']] ?? $service['category']) ?></span><br><span class="patient-name"><?= e($service['service_name']) ?></span></td><td><?= nl2br(e($service['details'])) ?></td><td><?= e($service['staff_name']) ?></td><td><?= $service['duration_minutes'] === null ? '—' : e((string) $service['duration_minutes'] . ' min') ?></td></tr>
    <?php endforeach; endif; ?>
    </tbody></table></div>
</section>
<?php endif; ?>
<?php if (has_permission('patients.history')): ?>
<section class="content-card">
    <div class="card-heading"><h3>Visit history</h3></div>
    <div class="table-responsive"><table class="table"><thead><tr><th>Date</th><th>Chief complaint</th><th>Diagnosis</th><th>Notes</th></tr></thead><tbody>
    <?php if ($visits === []): ?><tr><td class="empty-state" colspan="4">No visits recorded yet.</td></tr><?php else: foreach ($visits as $visit): ?>
        <tr><td><?= e(date('M j, Y · g:i a', strtotime($visit['visited_at']))) ?></td><td><?= e($visit['chief_complaint'] ?: '—') ?></td><td><?= e($visit['diagnosis'] ?: '—') ?></td><td><?= e($visit['notes'] ?: '—') ?></td></tr>
    <?php endforeach; endif; ?>
    </tbody></table></div>
</section>
<?php endif; ?>
<?php if (has_permission('admissions.view')): ?>
<section class="content-card">
    <div class="card-heading"><h3>Admission history</h3></div>
    <div class="table-responsive"><table class="table"><thead><tr><th>Ward / bed</th><th>Reason</th><th>Admitted</th><th>Discharged</th><th>Status</th></tr></thead><tbody>
    <?php if ($admissions === []): ?><tr><td class="empty-state" colspan="5">No admissions recorded yet.</td></tr><?php else: foreach ($admissions as $admission): ?>
        <tr><td><?= e($admission['ward_name'] . ' · ' . $admission['bed_number']) ?></td><td><?= e($admission['reason']) ?></td><td><?= e(date('M j, Y · g:i a', strtotime($admission['admitted_at']))) ?></td><td><?= e($admission['discharged_at'] ? date('M j, Y · g:i a', strtotime($admission['discharged_at'])) : '—') ?></td><td><span class="<?= $admission['status'] === 'admitted' ? 'badge-soft' : 'badge-neutral' ?>"><?= e(ucfirst($admission['status'])) ?></span></td></tr>
    <?php endforeach; endif; ?>
    </tbody></table></div>
</section>
<?php endif; ?>
<?php if (has_permission('patients.history') || has_permission('patient_services.view_all') || has_permission('patient_services.view_own')): ?>
<section id="patient-services-print" class="print-document" hidden>
    <div class="print-document-header"><div><strong>KivuCare</strong><div>Hospital management</div></div><h2>Patient service record</h2></div>
    <dl class="print-document-meta">
        <div><dt>Patient</dt><dd><?= e($patient['first_name'] . ' ' . $patient['last_name']) ?></dd></div>
        <div><dt>Patient number</dt><dd><?= e($patient['patient_no']) ?></dd></div>
        <div><dt>Printed on</dt><dd><?= e(format_date(date('Y-m-d'))) ?></dd></div>
    </dl>
    <table class="table"><thead><tr><th>Date</th><th>Service</th><th>Details</th><th>Provided by</th><th>Duration</th></tr></thead><tbody>
    <?php if ($patientServices === []): ?><tr><td colspan="5">No service records available to your account.</td></tr>
    <?php else: foreach ($patientServices as $service): ?>
        <tr><td><?= e(format_date($service['service_date'])) ?></td><td><?= e(patient_service_categories()[$service['category']] ?? $service['category']) ?><br><strong><?= e($service['service_name']) ?></strong></td><td><?= nl2br(e($service['details'])) ?></td><td><?= e($service['staff_name']) ?></td><td><?= $service['duration_minutes'] === null ? '—' : e((string) $service['duration_minutes'] . ' min') ?></td></tr>
    <?php endforeach; endif; ?>
    </tbody></table>
</section>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>

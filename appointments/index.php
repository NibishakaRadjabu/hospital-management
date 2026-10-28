<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_permission('appointments.view');
$user = current_user();
$doctorId = null;
if ($user['role_key'] === 'doctor') {
    $doctorQuery = $pdo->prepare('SELECT id FROM doctors WHERE user_id = :user_id');
    $doctorQuery->execute(['user_id' => $user['id']]);
    $doctorId = $doctorQuery->fetchColumn();
    if (!$doctorId) { http_response_code(403); exit('Your doctor profile is not configured.'); }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_permission('appointments.manage');
    verify_csrf();
    $action = (string) ($_POST['action'] ?? '');
    if ($action === 'create') {
        $patientId = filter_var($_POST['patient_id'] ?? '', FILTER_VALIDATE_INT);
        $doctorInput = $doctorId ?: filter_var($_POST['doctor_id'] ?? '', FILTER_VALIDATE_INT);
        $scheduledAt = trim((string) ($_POST['scheduled_at'] ?? ''));
        $reason = trim((string) ($_POST['reason'] ?? ''));
        $parsed = DateTime::createFromFormat('!Y-m-d\TH:i', $scheduledAt);
        if (!$patientId || !$doctorInput || !$parsed || $parsed->format('Y-m-d\TH:i') !== $scheduledAt || strlen($reason) > 255) {
            set_flash('danger', 'Choose a patient, clinician, valid appointment time, and a reason under 255 characters.');
            redirect('/hospital%20management/appointments/index.php');
        }
        $validPatient = $pdo->prepare('SELECT id FROM patients WHERE id = :id');
        $validPatient->execute(['id' => $patientId]);
        $validDoctor = $pdo->prepare('SELECT id FROM doctors WHERE id = :id');
        $validDoctor->execute(['id' => $doctorInput]);
        if (!$validPatient->fetch() || !$validDoctor->fetch()) {
            set_flash('danger', 'Choose a registered patient and clinician.');
            redirect('/hospital%20management/appointments/index.php');
        }
        $stmt = $pdo->prepare('INSERT INTO appointments (patient_id, doctor_id, scheduled_at, reason) VALUES (:patient_id, :doctor_id, :scheduled_at, :reason)');
        $stmt->execute(['patient_id' => $patientId, 'doctor_id' => $doctorInput, 'scheduled_at' => $parsed->format('Y-m-d H:i:s'), 'reason' => $reason ?: null]);
        $id = (string) $pdo->lastInsertId();
        record_audit('create', 'appointment', $id, 'Appointment scheduled');
        set_flash('success', 'Appointment scheduled.');
        redirect('/hospital%20management/appointments/index.php');
    }
    if ($action === 'status') {
        $id = filter_var($_POST['appointment_id'] ?? '', FILTER_VALIDATE_INT);
        $status = (string) ($_POST['status'] ?? '');
        if (!$id || !in_array($status, ['checked_in', 'cancelled', 'no_show'], true)) {
            http_response_code(400); exit('Invalid appointment update.');
        }
        $sql = 'UPDATE appointments SET status = :status WHERE id = :id AND status = \'scheduled\'';
        $params = ['status' => $status, 'id' => $id];
        if ($doctorId) { $sql .= ' AND doctor_id = :doctor_id'; $params['doctor_id'] = $doctorId; }
        $stmt = $pdo->prepare($sql); $stmt->execute($params);
        if ($stmt->rowCount()) record_audit('update', 'appointment', (string) $id, 'Appointment status changed to ' . $status);
        set_flash($stmt->rowCount() ? 'success' : 'warning', $stmt->rowCount() ? 'Appointment updated.' : 'The appointment was not found or is no longer scheduled.');
        redirect('/hospital%20management/appointments/index.php');
    }
    http_response_code(400); exit('Unknown appointment action.');
}

$conditions = [];
$params = [];
if ($doctorId) { $conditions[] = 'a.doctor_id = :doctor_id'; $params['doctor_id'] = $doctorId; }
$where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
$stmt = $pdo->prepare("SELECT a.id, a.patient_id, a.scheduled_at, a.status, a.reason, p.patient_no, p.first_name, p.last_name, u.full_name AS doctor_name FROM appointments a JOIN patients p ON p.id = a.patient_id LEFT JOIN doctors d ON d.id = a.doctor_id LEFT JOIN users u ON u.id = d.user_id {$where} ORDER BY a.scheduled_at DESC LIMIT 200");
$stmt->execute($params); $appointments = $stmt->fetchAll();
$patients = has_permission('appointments.manage') ? $pdo->query('SELECT id, patient_no, first_name, last_name FROM patients ORDER BY last_name, first_name LIMIT 1000')->fetchAll() : [];
$doctors = has_permission('appointments.manage') && !$doctorId ? $pdo->query("SELECT d.id, u.full_name FROM doctors d JOIN users u ON u.id=d.user_id WHERE u.is_active=1 ORDER BY u.full_name")->fetchAll() : [];
$pageTitle = 'Appointments'; require __DIR__ . '/../includes/header.php';
?>
<div class="page-heading"><div><h2>Appointments</h2><p>Schedule outpatient visits, check patients in, and follow today's queue.</p></div></div>
<?php if (has_permission('appointments.manage')): ?><section class="content-card form-card mt-0"><div class="card-heading"><h3>Book appointment</h3></div><form method="post" class="row g-3"><input type="hidden" name="action" value="create"><?= csrf_field() ?>
<div class="col-md-4"><label class="form-label">Patient</label><select class="form-select" name="patient_id" required><option value="">Select patient</option><?php foreach ($patients as $patient): ?><option value="<?= (int)$patient['id'] ?>"><?= e($patient['patient_no'] . ' · ' . $patient['last_name'] . ', ' . $patient['first_name']) ?></option><?php endforeach; ?></select></div>
<?php if ($doctorId): ?><input type="hidden" name="doctor_id" value="<?= (int)$doctorId ?>"><?php else: ?><div class="col-md-3"><label class="form-label">Clinician</label><select class="form-select" name="doctor_id" required><option value="">Select clinician</option><?php foreach ($doctors as $doctor): ?><option value="<?= (int)$doctor['id'] ?>"><?= e($doctor['full_name']) ?></option><?php endforeach; ?></select></div><?php endif; ?>
<div class="col-md-3"><label class="form-label">Date and time</label><input class="form-control" type="datetime-local" name="scheduled_at" required></div><div class="col-md"><label class="form-label">Reason</label><input class="form-control" name="reason" maxlength="255"></div><div class="col-12"><button class="btn btn-primary">Schedule appointment</button></div></form></section><?php endif; ?>
<section class="content-card"><div class="table-responsive"><table class="table"><thead><tr><th>Date</th><th>Patient</th><th>Clinician</th><th>Reason</th><th>Status</th><th>Queue action</th></tr></thead><tbody><?php if (!$appointments): ?><tr><td class="empty-state" colspan="6">No appointments found.</td></tr><?php else: foreach ($appointments as $appointment): ?><tr><td><?= e(date('M j, Y g:i a', strtotime($appointment['scheduled_at']))) ?></td><td><a href="/hospital%20management/patient/show.php?id=<?= (int)$appointment['patient_id'] ?>"><?= e($appointment['last_name'] . ', ' . $appointment['first_name']) ?></a><br><span class="subtle"><?= e($appointment['patient_no']) ?></span></td><td><?= e($appointment['doctor_name'] ?? 'Unassigned') ?></td><td><?= e($appointment['reason'] ?: '—') ?></td><td><?= e(ucwords(str_replace('_', ' ', $appointment['status']))) ?></td><td><?php if (has_permission('appointments.manage') && $appointment['status'] === 'scheduled'): ?><form method="post" class="d-flex gap-1"><input type="hidden" name="action" value="status"><input type="hidden" name="appointment_id" value="<?= (int)$appointment['id'] ?>"><?= csrf_field() ?><button class="btn btn-sm btn-primary" name="status" value="checked_in">Check in</button><button class="btn btn-sm btn-outline-secondary" name="status" value="no_show">No show</button><button class="btn btn-sm btn-outline-danger" name="status" value="cancelled">Cancel</button></form><?php endif; ?></td></tr><?php endforeach; endif; ?></tbody></table></div></section>
<?php require __DIR__ . '/../includes/footer.php'; ?>

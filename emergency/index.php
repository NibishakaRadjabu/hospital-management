<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_permission('triage.view');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_permission('triage.manage');
    verify_csrf();

    $patientId = filter_var($_POST['patient_id'] ?? '', FILTER_VALIDATE_INT) ?: null;
    $patientName = trim((string) ($_POST['patient_name'] ?? ''));
    $acuity = (string) ($_POST['acuity'] ?? '');
    $complaint = trim((string) ($_POST['chief_complaint'] ?? ''));
    $notes = trim((string) ($_POST['notes'] ?? ''));
    $allowedAcuities = ['resuscitation', 'emergency', 'urgent', 'less_urgent', 'non_urgent'];
    $vitals = [];
    $vitalFields = [
        'temperature_c' => ['temperature', 50, true],
        'pulse_bpm' => ['pulse', 300, false],
        'respiratory_rate' => ['respiratory_rate', 100, false],
        'systolic_bp' => ['systolic', 400, false],
        'diastolic_bp' => ['diastolic', 300, false],
        'oxygen_saturation' => ['oxygen', 100, false],
    ];
    foreach ($vitalFields as $column => [$field, $max, $decimal]) {
        $raw = trim((string) ($_POST[$field] ?? ''));
        $value = $raw === '' ? null : filter_var($raw, $decimal ? FILTER_VALIDATE_FLOAT : FILTER_VALIDATE_INT);
        if ($raw !== '' && ($value === false || $value < 0 || $value > $max)) {
            set_flash('danger', 'Check the vital sign values.');
            redirect('/hospital%20management/emergency/index.php');
        }
        $vitals[$column] = $value;
    }

    if ((!$patientId && $patientName === '') || strlen($patientName) > 160
        || !in_array($acuity, $allowedAcuities, true) || $complaint === ''
        || strlen($complaint) > 500 || strlen($notes) > 5000) {
        set_flash('danger', 'Choose a registered patient or enter an unregistered patient name, acuity, and chief complaint.');
        redirect('/hospital%20management/emergency/index.php');
    }
    if ($patientId) {
        $check = $pdo->prepare('SELECT id FROM patients WHERE id = :id');
        $check->execute(['id' => $patientId]);
        if (!$check->fetch()) {
            set_flash('danger', 'The selected patient could not be found.');
            redirect('/hospital%20management/emergency/index.php');
        }
    }

    $insert = $pdo->prepare(
        'INSERT INTO emergency_triage
            (patient_id, patient_name, assessed_by, acuity, chief_complaint, temperature_c,
             pulse_bpm, respiratory_rate, systolic_bp, diastolic_bp, oxygen_saturation, notes)
         VALUES
            (:patient_id, :patient_name, :assessed_by, :acuity, :complaint, :temperature,
             :pulse, :respiratory_rate, :systolic, :diastolic, :oxygen, :notes)'
    );
    $insert->execute([
        'patient_id' => $patientId,
        'patient_name' => $patientName ?: null,
        'assessed_by' => current_user()['id'],
        'acuity' => $acuity,
        'complaint' => $complaint,
        'temperature' => $vitals['temperature_c'],
        'pulse' => $vitals['pulse_bpm'],
        'respiratory_rate' => $vitals['respiratory_rate'],
        'systolic' => $vitals['systolic_bp'],
        'diastolic' => $vitals['diastolic_bp'],
        'oxygen' => $vitals['oxygen_saturation'],
        'notes' => $notes ?: null,
    ]);
    $id = (string) $pdo->lastInsertId();
    record_audit('create', 'emergency_triage', $id, 'Emergency triage assessment recorded');
    notify_staff('triage_recorded', 'emergency_triage', $id, current_user()['full_name'] . ' recorded an emergency triage assessment.');
    set_flash('success', 'Triage assessment recorded. Provide immediate care for life-threatening conditions.');
    redirect('/hospital%20management/emergency/index.php');
}

$patients = has_permission('triage.manage')
    ? $pdo->query('SELECT id, patient_no, first_name, last_name FROM patients ORDER BY last_name, first_name LIMIT 1000')->fetchAll()
    : [];
$rows = $pdo->query(
    'SELECT t.*, p.patient_no, p.first_name, p.last_name, u.full_name AS assessor
     FROM emergency_triage t
     LEFT JOIN patients p ON p.id = t.patient_id
     JOIN users u ON u.id = t.assessed_by
     ORDER BY t.assessed_at DESC LIMIT 200'
)->fetchAll();

$pageTitle = 'Emergency Triage';
require __DIR__ . '/../includes/header.php';
?>
<div class="page-heading"><div><h2>Emergency triage</h2><p>Assess urgency and record initial observations. Immediate emergency care takes priority over administrative steps.</p></div></div>
<?php if (has_permission('triage.manage')): ?>
<section class="content-card form-card mt-0">
    <div class="card-heading"><h3>Record assessment</h3></div>
    <div class="alert alert-warning py-2">If registration is not complete, enter the patient's name and continue with triage.</div>
    <form method="post" class="row g-3">
        <?= csrf_field() ?>
        <div class="col-md-4"><label class="form-label" for="patient_id">Registered patient</label><select class="form-select" id="patient_id" name="patient_id"><option value="">Not registered / unknown</option><?php foreach ($patients as $patient): ?><option value="<?= (int) $patient['id'] ?>"><?= e($patient['patient_no'] . ' · ' . $patient['last_name'] . ', ' . $patient['first_name']) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-4"><label class="form-label" for="patient_name">Patient name if unregistered</label><input class="form-control" id="patient_name" name="patient_name" maxlength="160"></div>
        <div class="col-md-4"><label class="form-label" for="acuity">Acuity</label><select class="form-select" id="acuity" name="acuity" required><?php foreach (['resuscitation'=>'Resuscitation','emergency'=>'Emergency','urgent'=>'Urgent','less_urgent'=>'Less urgent','non_urgent'=>'Non-urgent'] as $key => $label): ?><option value="<?= e($key) ?>"><?= e($label) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-8"><label class="form-label" for="chief_complaint">Chief complaint</label><input class="form-control" id="chief_complaint" name="chief_complaint" maxlength="500" required></div>
        <div class="col-md-2"><label class="form-label">Temperature °C</label><input class="form-control" type="number" name="temperature" min="0" max="50" step="0.1"></div>
        <div class="col-md-2"><label class="form-label">Pulse / min</label><input class="form-control" type="number" name="pulse" min="0" max="300"></div>
        <div class="col-md-2"><label class="form-label">Resp. / min</label><input class="form-control" type="number" name="respiratory_rate" min="0" max="100"></div>
        <div class="col-md-3"><label class="form-label">Blood pressure</label><div class="input-group"><input class="form-control" name="systolic" type="number" min="0" max="400" placeholder="SYS"><span class="input-group-text">/</span><input class="form-control" name="diastolic" type="number" min="0" max="300" placeholder="DIA"></div></div>
        <div class="col-md-2"><label class="form-label">Oxygen %</label><input class="form-control" type="number" name="oxygen" min="0" max="100"></div>
        <div class="col-md-5"><label class="form-label" for="notes">Notes</label><textarea class="form-control" id="notes" name="notes" maxlength="5000" rows="2"></textarea></div>
        <div class="col-12"><button class="btn btn-primary">Save triage</button></div>
    </form>
</section>
<?php endif; ?>
<section class="content-card"><div class="table-responsive"><table class="table"><thead><tr><th>Arrival</th><th>Patient</th><th>Priority</th><th>Complaint</th><th>Vital signs</th><th>Assessed by</th></tr></thead><tbody>
<?php if (!$rows): ?><tr><td class="empty-state" colspan="6">No triage assessments recorded.</td></tr><?php else: foreach ($rows as $row): ?>
<tr><td><?= e(date('M j, Y g:i a', strtotime($row['assessed_at']))) ?></td><td><?php if ($row['patient_id']): ?><a href="/hospital%20management/patient/show.php?id=<?= (int) $row['patient_id'] ?>"><?= e($row['last_name'] . ', ' . $row['first_name']) ?></a><br><span class="subtle"><?= e($row['patient_no']) ?></span><?php else: ?><?= e($row['patient_name'] ?: 'Unknown patient') ?> <span class="badge-neutral">Unregistered</span><?php endif; ?></td><td><span class="badge-soft"><?= e(ucwords(str_replace('_', ' ', $row['acuity']))) ?></span></td><td><?= e($row['chief_complaint']) ?></td><td><?= e('Temp ' . ($row['temperature_c'] ?? '—') . '°C · Pulse ' . ($row['pulse_bpm'] ?? '—') . ' · BP ' . ($row['systolic_bp'] ?? '—') . '/' . ($row['diastolic_bp'] ?? '—') . ' · SpO₂ ' . ($row['oxygen_saturation'] ?? '—') . '%') ?></td><td><?= e($row['assessor']) ?></td></tr>
<?php endforeach; endif; ?></tbody></table></div></section>
<?php require __DIR__ . '/../includes/footer.php'; ?>

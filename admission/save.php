<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_permission('admissions.create');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}
verify_csrf();

$patientId = filter_var($_POST['patient_id'] ?? '', FILTER_VALIDATE_INT);
$bedId = filter_var($_POST['bed_id'] ?? '', FILTER_VALIDATE_INT);
$reason = trim((string) ($_POST['reason'] ?? ''));
if (!$patientId || !$bedId || $reason === '' || strlen($reason) > 4000) {
    set_flash('danger', 'Choose a patient, an available bed, and provide an admission reason.');
    redirect('/hospital%20management/admission/create.php');
}

try {
    $pdo->beginTransaction();
    $bed = $pdo->prepare('SELECT status FROM beds WHERE id = :id FOR UPDATE');
    $bed->execute(['id' => $bedId]);
    $bedStatus = $bed->fetchColumn();
    if ($bedStatus !== 'available') {
        $pdo->rollBack();
        set_flash('warning', 'That bed is no longer available. Select another bed.');
        redirect('/hospital%20management/admission/create.php');
    }

    $patient = $pdo->prepare('SELECT id FROM patients WHERE id = :id');
    $patient->execute(['id' => $patientId]);
    if (!$patient->fetch()) {
        $pdo->rollBack();
        set_flash('danger', 'The selected patient could not be found.');
        redirect('/hospital%20management/admission/create.php');
    }

    $insert = $pdo->prepare('INSERT INTO admissions (patient_id, bed_id, admitted_by, reason) VALUES (:patient_id, :bed_id, :admitted_by, :reason)');
    $insert->execute([
        'patient_id' => $patientId,
        'bed_id' => $bedId,
        'admitted_by' => current_user()['id'],
        'reason' => $reason,
    ]);
    $update = $pdo->prepare("UPDATE beds SET status = 'occupied' WHERE id = :id AND status = 'available'");
    $update->execute(['id' => $bedId]);
    if ($update->rowCount() !== 1) {
        throw new RuntimeException('Bed availability changed while the admission was being saved.');
    }
    record_audit('create', 'admission', (string) $pdo->lastInsertId(), 'Patient admitted and bed assigned');
    $pdo->commit();
    set_flash('success', 'Patient admitted and bed assigned.');
    redirect('/hospital%20management/admission/index.php');
} catch (PDOException $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    if ($exception->getCode() === '23000') {
        set_flash('warning', 'This patient already has an active admission or the selected bed was just assigned.');
        redirect('/hospital%20management/admission/create.php');
    }
    error_log('Admission creation failed: ' . $exception->getMessage());
    set_flash('danger', 'The admission could not be saved. Please try again.');
    redirect('/hospital%20management/admission/create.php');
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Admission creation failed: ' . $exception->getMessage());
    set_flash('danger', 'The admission could not be saved. Please try again.');
    redirect('/hospital%20management/admission/create.php');
}

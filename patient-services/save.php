<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_permission('patient_services.create');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}
verify_csrf();

$patientNo = strtoupper(trim((string) ($_POST['patient_no'] ?? '')));
$serviceDate = trim((string) ($_POST['service_date'] ?? ''));
$category = (string) ($_POST['category'] ?? '');
$serviceName = trim((string) ($_POST['service_name'] ?? ''));
$details = trim((string) ($_POST['details'] ?? ''));
$durationInput = trim((string) ($_POST['duration_minutes'] ?? ''));
$duration = $durationInput === '' ? null : filter_var($durationInput, FILTER_VALIDATE_INT);
$date = DateTime::createFromFormat('!Y-m-d', $serviceDate);

if ($patientNo === '' || strlen($patientNo) > 30
    || !$date || $date->format('Y-m-d') !== $serviceDate || $date > new DateTime('today')
    || !isset(patient_service_categories()[$category])
    || $serviceName === '' || strlen($serviceName) > 160
    || $details === '' || strlen($details) > 5000
    || ($durationInput !== '' && ($duration === false || $duration < 1 || $duration > 1440))) {
    set_flash('danger', 'Check the patient number, date, service type, service name, notes, and optional duration.');
    redirect('/hospital%20management/patient-services/create.php');
}

$patientQuery = $pdo->prepare('SELECT id FROM patients WHERE patient_no = :patient_no');
$patientQuery->execute(['patient_no' => $patientNo]);
$patientId = $patientQuery->fetchColumn();
if ($patientId === false) {
    set_flash('danger', 'No patient was found with that patient number.');
    redirect('/hospital%20management/patient-services/create.php');
}
if (current_user()['role_key'] === 'doctor') {
    $assigned = $pdo->prepare(
        'SELECT d.id FROM doctors d
         WHERE d.user_id = :user_id
           AND (EXISTS (SELECT 1 FROM appointments a WHERE a.patient_id = :appointment_patient AND a.doctor_id = d.id)
                OR EXISTS (SELECT 1 FROM visits v WHERE v.patient_id = :visit_patient AND v.doctor_id = d.id))'
    );
    $assigned->execute([
        'user_id' => current_user()['id'],
        'appointment_patient' => $patientId,
        'visit_patient' => $patientId,
    ]);
    if (!$assigned->fetch()) {
        set_flash('danger', 'You can only record care services for patients assigned to you.');
        redirect('/hospital%20management/patient-services/create.php');
    }
}

try {
    $pdo->beginTransaction();
    $insert = $pdo->prepare(
        'INSERT INTO patient_services
            (patient_id, provided_by, service_date, category, service_name, details, duration_minutes)
         VALUES
            (:patient_id, :provided_by, :service_date, :category, :service_name, :details, :duration_minutes)'
    );
    $insert->execute([
        'patient_id' => $patientId,
        'provided_by' => current_user()['id'],
        'service_date' => $serviceDate,
        'category' => $category,
        'service_name' => $serviceName,
        'details' => $details,
        'duration_minutes' => $duration,
    ]);
    $serviceId = (string) $pdo->lastInsertId();
    record_audit('create', 'patient_service', $serviceId, 'Patient service recorded');
    $pdo->commit();
    set_flash('success', 'Patient service recorded.');
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Patient service save failed: ' . $exception->getMessage());
    set_flash('danger', 'The patient service could not be saved. Please try again.');
}
redirect('/hospital%20management/patient-services/index.php');

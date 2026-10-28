<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}
verify_csrf();

$rawId = trim((string) ($_POST['id'] ?? ''));
$id = $rawId === '' ? null : filter_var($rawId, FILTER_VALIDATE_INT);
require_permission($id === null ? 'patients.create' : 'patients.update');
$firstName = trim((string) ($_POST['first_name'] ?? ''));
$lastName = trim((string) ($_POST['last_name'] ?? ''));
$dob = trim((string) ($_POST['date_of_birth'] ?? ''));
$sex = (string) ($_POST['sex'] ?? 'unspecified');
$email = strtolower(trim((string) ($_POST['email'] ?? '')));
$validSexes = ['female', 'male', 'other', 'unspecified'];

if ($id === false || ($id !== null && $id < 1) || $firstName === '' || $lastName === '' || strlen($firstName) > 80 || strlen($lastName) > 80 || !in_array($sex, $validSexes, true)) {
    set_flash('danger', 'Enter valid patient name and personal details.');
    redirect('/hospital%20management/patient/form.php' . ($id ? '?id=' . (int) $id : ''));
}
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    set_flash('danger', 'Enter a valid email address.');
    redirect('/hospital%20management/patient/form.php' . ($id ? '?id=' . (int) $id : ''));
}
if ($dob !== '') {
    $date = DateTime::createFromFormat('!Y-m-d', $dob);
    if (!$date || $date->format('Y-m-d') !== $dob || $date > new DateTime('today')) {
        set_flash('danger', 'Enter a valid date of birth that is not in the future.');
        redirect('/hospital%20management/patient/form.php' . ($id ? '?id=' . (int) $id : ''));
    }
}

$values = [
    'first_name' => $firstName,
    'last_name' => $lastName,
    'date_of_birth' => $dob !== '' ? $dob : null,
    'sex' => $sex,
    'phone' => trim((string) ($_POST['phone'] ?? '')) ?: null,
    'email' => $email !== '' ? $email : null,
    'address' => trim((string) ($_POST['address'] ?? '')) ?: null,
    'blood_group' => trim((string) ($_POST['blood_group'] ?? '')) ?: null,
    'emergency_contact_name' => trim((string) ($_POST['emergency_contact_name'] ?? '')) ?: null,
    'emergency_contact_phone' => trim((string) ($_POST['emergency_contact_phone'] ?? '')) ?: null,
    'notes' => trim((string) ($_POST['notes'] ?? '')) ?: null,
];

try {
    if ($id !== null) {
        $pdo->beginTransaction();
        $values['id'] = $id;
        $statement = $pdo->prepare(
            'UPDATE patients SET first_name = :first_name, last_name = :last_name, date_of_birth = :date_of_birth,
             sex = :sex, phone = :phone, email = :email, address = :address, blood_group = :blood_group,
             emergency_contact_name = :emergency_contact_name, emergency_contact_phone = :emergency_contact_phone,
             notes = :notes WHERE id = :id'
        );
        $statement->execute($values);
        if ($statement->rowCount() === 0) {
            $check = $pdo->prepare('SELECT id FROM patients WHERE id = :id');
            $check->execute(['id' => $id]);
            if (!$check->fetch()) {
                $pdo->rollBack();
                http_response_code(404);
                exit('Patient not found.');
            }
        }
        record_audit('update', 'patient', (string) $id, 'Patient profile updated');
        $pdo->commit();
        set_flash('success', 'Patient details updated.');
        redirect('/hospital%20management/patient/show.php?id=' . $id);
    }

    $pdo->beginTransaction();
    $patientNo = 'PT-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(5)));
    $values['patient_no'] = $patientNo;
    $values['created_by'] = current_user()['id'];
    $statement = $pdo->prepare(
        'INSERT INTO patients (patient_no, first_name, last_name, date_of_birth, sex, phone, email, address,
         blood_group, emergency_contact_name, emergency_contact_phone, notes, created_by)
         VALUES (:patient_no, :first_name, :last_name, :date_of_birth, :sex, :phone, :email, :address,
         :blood_group, :emergency_contact_name, :emergency_contact_phone, :notes, :created_by)'
    );
    $statement->execute($values);
    $patientId = (int) $pdo->lastInsertId();
    record_audit('create', 'patient', (string) $patientId, 'Patient registered');
    $pdo->commit();
    set_flash('success', 'Patient registered with ID ' . $patientNo . '.');
    redirect('/hospital%20management/patient/show.php?id=' . $patientId);
} catch (PDOException $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Patient save failed: ' . $exception->getMessage());
    set_flash('danger', 'Patient details could not be saved. Check that the information is valid and try again.');
    redirect('/hospital%20management/patient/form.php' . ($id ? '?id=' . (int) $id : ''));
}

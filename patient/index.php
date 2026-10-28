<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_permission('patients.view');

$search = trim((string) ($_GET['q'] ?? ''));
$conditions = [];
$params = [];
if (current_user()['role_key'] === 'doctor') {
    $doctor = $pdo->prepare('SELECT id FROM doctors WHERE user_id = :user_id');
    $doctor->execute(['user_id' => current_user()['id']]);
    $doctorId = $doctor->fetchColumn();
    if (!$doctorId) { http_response_code(403); exit('Your doctor profile is not configured.'); }
    $conditions[] = '(EXISTS (SELECT 1 FROM appointments a WHERE a.patient_id = p.id AND a.doctor_id = :doctor_appointment)
                      OR EXISTS (SELECT 1 FROM visits v WHERE v.patient_id = p.id AND v.doctor_id = :doctor_visit))';
    $params['doctor_appointment'] = $doctorId;
    $params['doctor_visit'] = $doctorId;
}
if ($search !== '') {
    $conditions[] = '(p.patient_no LIKE :search OR p.first_name LIKE :search OR p.last_name LIKE :search OR p.phone LIKE :search OR p.email LIKE :search)';
    $params['search'] = '%' . $search . '%';
}
$sql = 'SELECT p.id, p.patient_no, p.first_name, p.last_name, p.date_of_birth, p.sex, p.phone, p.created_at FROM patients p';
$sql .= $conditions ? ' WHERE ' . implode(' AND ', $conditions) : '';
$sql .= ' ORDER BY p.created_at DESC LIMIT 100';
$statement = $pdo->prepare($sql);
$statement->execute($params);
$patients = $statement->fetchAll();

$pageTitle = 'Patients';
require __DIR__ . '/../includes/header.php';
?>
<div class="page-heading">
    <div><h2>Patient directory</h2><p>Search and manage patient registrations and visit history.</p></div>
    <?php if (has_permission('patients.create')): ?><a class="btn btn-primary" href="/hospital%20management/patient/form.php">+ Register patient</a><?php endif; ?>
</div>
<section class="content-card mt-0">
    <div class="card-heading">
        <h3><?= number_format(count($patients)) ?> patient<?= count($patients) === 1 ? '' : 's' ?><?= $search !== '' ? ' found' : ' recently registered' ?></h3>
        <form class="search-box" method="get" action="/hospital%20management/patient/index.php">
            <input class="form-control" type="search" name="q" placeholder="Search name, patient ID, phone..." value="<?= e($search) ?>" aria-label="Search patients">
        </form>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead><tr><th>Patient</th><th>Patient ID</th><th>Date of birth</th><th>Sex</th><th>Phone</th><th>Registered</th><th></th></tr></thead>
            <tbody>
            <?php if ($patients === []): ?>
                <tr><td class="empty-state" colspan="7"><?= $search === '' ? 'No patients have been registered yet.' : 'No patients matched your search.' ?></td></tr>
            <?php else: foreach ($patients as $patient): ?>
                <tr>
                    <td><a class="patient-name" href="/hospital%20management/patient/show.php?id=<?= (int) $patient['id'] ?>"><?= e($patient['first_name'] . ' ' . $patient['last_name']) ?></a></td>
                    <td><?= e($patient['patient_no']) ?></td>
                    <td><?= e(format_date($patient['date_of_birth'])) ?></td>
                    <td><?= e(ucfirst($patient['sex'])) ?></td>
                    <td><?= e($patient['phone'] ?: '—') ?></td>
                    <td><?= e(format_date($patient['created_at'])) ?></td>
                    <td><a class="btn btn-sm btn-outline-secondary" href="/hospital%20management/patient/show.php?id=<?= (int) $patient['id'] ?>">View</a><?php if (has_permission('patients.update')): ?> <a class="btn btn-sm btn-outline-secondary" href="/hospital%20management/patient/form.php?id=<?= (int) $patient['id'] ?>">Edit</a><?php endif; ?></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>

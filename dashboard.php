<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_login();

$role = current_user()['role_key'];
$cards = [];
$revenue = null;
$recentAdmissions = [];

if (in_array($role, ['admin', 'super_admin', 'hospital_admin'], true)) {
    $cards = [
        ['Total patients', (int) $pdo->query('SELECT COUNT(*) FROM patients')->fetchColumn(), 'Registered in the system', '♙'],
        ['Medical staff', (int) $pdo->query("SELECT COUNT(*) FROM users u JOIN roles r ON r.id = u.role_id WHERE r.role_key = 'doctor' AND u.is_active = 1")->fetchColumn(), 'Active doctors', '✚'],
        ["Today's appointments", (int) $pdo->query("SELECT COUNT(*) FROM appointments WHERE DATE(scheduled_at) = CURDATE() AND status <> 'cancelled'")->fetchColumn(), 'Scheduled for today', '▦'],
        ['Current admissions', (int) $pdo->query("SELECT COUNT(*) FROM admissions WHERE status = 'admitted'")->fetchColumn(), 'Patients in a ward', '▤'],
    ];
    $revenue = (float) $pdo->query('SELECT COALESCE(SUM(amount), 0) FROM payments WHERE DATE(paid_at) = CURDATE()')->fetchColumn();
} elseif ($role === 'doctor') {
    $statement = $pdo->prepare(
        "SELECT COUNT(*) FROM appointments a JOIN doctors d ON d.id = a.doctor_id
         WHERE d.user_id = :user_id AND DATE(a.scheduled_at) = CURDATE() AND a.status <> 'cancelled'"
    );
    $statement->execute(['user_id' => current_user()['id']]);
    $cards[] = ["Today's patients", (int) $statement->fetchColumn(), 'Appointments on your schedule', '♙'];

    $statement = $pdo->prepare(
        'SELECT COUNT(*) FROM visits v JOIN doctors d ON d.id = v.doctor_id
         WHERE d.user_id = :user_id AND DATE(v.visited_at) = CURDATE()'
    );
    $statement->execute(['user_id' => current_user()['id']]);
    $cards[] = ['Visits completed today', (int) $statement->fetchColumn(), 'Consultations recorded', '✚'];

    $statement = $pdo->prepare(
        'SELECT COUNT(*) FROM prescriptions p JOIN doctors d ON d.id = p.doctor_id WHERE d.user_id = :user_id'
    );
    $statement->execute(['user_id' => current_user()['id']]);
    $cards[] = ['Prescriptions', (int) $statement->fetchColumn(), 'Issued by you', '▤'];
} elseif ($role === 'nurse') {
    $cards = [
        ['Current admissions', (int) $pdo->query("SELECT COUNT(*) FROM admissions WHERE status = 'admitted'")->fetchColumn(), 'Patients in a ward', '▤'],
        ['Available beds', (int) $pdo->query("SELECT COUNT(*) FROM beds WHERE status = 'available'")->fetchColumn(), 'Ready for assignment', '＋'],
        ["Today's admissions", (int) $pdo->query("SELECT COUNT(*) FROM admissions WHERE DATE(admitted_at) = CURDATE() AND status = 'admitted'")->fetchColumn(), 'Admitted today', '▦'],
    ];
} elseif ($role === 'receptionist') {
    $cards = [
        ["Today's registrations", (int) $pdo->query('SELECT COUNT(*) FROM patients WHERE DATE(created_at) = CURDATE()')->fetchColumn(), 'Patients registered today', '♙'],
        ["Today's appointments", (int) $pdo->query("SELECT COUNT(*) FROM appointments WHERE DATE(scheduled_at) = CURDATE() AND status <> 'cancelled'")->fetchColumn(), 'Scheduled for today', '▦'],
        ['Current admissions', (int) $pdo->query("SELECT COUNT(*) FROM admissions WHERE status = 'admitted'")->fetchColumn(), 'Patients in a ward', '▤'],
        ['Available beds', (int) $pdo->query("SELECT COUNT(*) FROM beds WHERE status = 'available'")->fetchColumn(), 'Ready for assignment', '＋'],
    ];
} elseif ($role === 'pharmacist') {
    $cards = [
        ['Low-stock medicines', (int) $pdo->query('SELECT COUNT(*) FROM medicines WHERE quantity <= reorder_level')->fetchColumn(), 'At or below reorder level', '⚑'],
        ['Medicines in catalogue', (int) $pdo->query('SELECT COUNT(*) FROM medicines')->fetchColumn(), 'Products tracked', '▤'],
    ];
} elseif ($role === 'laboratory_technician') {
    $cards = [
        ['Tests in progress', (int) $pdo->query("SELECT COUNT(*) FROM lab_tests WHERE status IN ('requested', 'in_progress')")->fetchColumn(), 'Awaiting or being processed', '⚗'],
        ['Results recorded today', (int) $pdo->query("SELECT COUNT(*) FROM lab_results r JOIN lab_tests t ON t.id = r.lab_test_id WHERE t.status = 'completed' AND DATE(r.recorded_at) = CURDATE()")->fetchColumn(), 'Tests completed today', '✓'],
    ];
} elseif (has_permission('financial_reports.view')) {
    $revenue = (float) $pdo->query('SELECT COALESCE(SUM(amount), 0) FROM payments WHERE DATE(paid_at) = CURDATE()')->fetchColumn();
    $outstandingCount = (int) $pdo->query("SELECT COUNT(*) FROM invoices WHERE status IN ('unpaid', 'partial')")->fetchColumn();
    $outstandingAmount = (float) $pdo->query(
        "SELECT COALESCE(SUM(i.total_amount - COALESCE((SELECT SUM(p.amount) FROM payments p WHERE p.invoice_id = i.id), 0)), 0)
         FROM invoices i WHERE i.status IN ('unpaid', 'partial')"
    )->fetchColumn();
    $cards = [
        ["Today's collections", number_format($revenue, 2), 'Payments received today', '＄'],
        ['Outstanding invoices', number_format($outstandingCount), 'Awaiting full payment', '▤'],
        ['Outstanding balance', number_format($outstandingAmount, 2), 'Amount in billing currency', '＄'],
    ];
} elseif ($role === 'radiologist') {
    $cards = [
        ['Radiology access', 'Ready', 'Radiology permissions are assigned; request/report screens are pending.', '⚗'],
    ];
} elseif ($role === 'store_keeper') {
    $cards = [
        ['Inventory access', 'Ready', 'Inventory permissions are assigned; stock workflows are pending.', '▤'],
    ];
} elseif ($role === 'hr') {
    $cards = [
        ['HR access', 'Ready', 'Employee permissions are assigned; HR workflows are pending.', '♙'],
    ];
} elseif ($role === 'ambulance_driver') {
    $cards = [
        ['Transport access', 'Ready', 'Emergency transport permissions are assigned; ambulance workflows are pending.', '＋'],
    ];
} elseif ($role === 'patient_portal') {
    $cards = [
        ['Patient portal', 'Ready', 'Patient-only permissions are assigned; portal pages and account linking are pending.', '♙'],
    ];
}

$canViewAdmissions = has_permission('admissions.view');
if ($canViewAdmissions) {
    $recentAdmissions = $pdo->query(
        "SELECT p.patient_no, p.first_name, p.last_name, w.name AS ward_name, b.bed_number, a.admitted_at
         FROM admissions a
         JOIN patients p ON p.id = a.patient_id
         JOIN beds b ON b.id = a.bed_id
         JOIN wards w ON w.id = b.ward_id
         WHERE a.status = 'admitted'
         ORDER BY a.admitted_at DESC LIMIT 6"
    )->fetchAll();
}

$pageTitle = 'Dashboard';
require __DIR__ . '/includes/header.php';
?>
<div class="page-heading">
    <div><h2>Good <?= (int) date('G') < 12 ? 'morning' : ((int) date('G') < 17 ? 'afternoon' : 'evening') ?>, <?= e(explode(' ', current_user()['full_name'])[0]) ?></h2><p>Your <?= e(strtolower(current_user()['role_name'])) ?> overview for <?= date('l, F j, Y') ?>.</p></div>
    <?php if (has_permission('patients.create')): ?>
        <a class="btn btn-primary" href="/hospital%20management/patient/form.php">+ Register patient</a>
    <?php endif; ?>
</div>
<div class="row g-3">
    <?php foreach ($cards as [$label, $value, $note, $icon]): ?>
        <div class="col-6 col-xl-3"><article class="stat-card"><div class="d-flex justify-content-between align-items-start"><span class="stat-label"><?= e($label) ?></span><span class="stat-icon"><?= e($icon) ?></span></div><div class="stat-value"><?= e((string) $value) ?></div><div class="stat-note"><?= e($note) ?></div></article></div>
    <?php endforeach; ?>
</div>
<div class="row g-3 mt-1">
    <?php if ($canViewAdmissions): ?>
        <div class="col-12 col-xl-8">
            <section class="content-card mt-0">
                <div class="card-heading"><h3>Recent admissions</h3><a href="/hospital%20management/admission/index.php">View all</a></div>
                <div class="table-responsive">
                    <table class="table">
                        <thead><tr><th>Patient</th><th>Ward / bed</th><th>Admitted</th><th>Status</th></tr></thead>
                        <tbody>
                        <?php if ($recentAdmissions === []): ?>
                            <tr><td class="empty-state" colspan="4">No active admissions yet.</td></tr>
                        <?php else: foreach ($recentAdmissions as $admission): ?>
                            <tr>
                                <td><span class="patient-name"><?= e($admission['first_name'] . ' ' . $admission['last_name']) ?></span><br><span class="subtle"><?= e($admission['patient_no']) ?></span></td>
                                <td><?= e($admission['ward_name']) ?> <span class="subtle">· <?= e($admission['bed_number']) ?></span></td>
                                <td><?= e(date('M j, Y · g:i a', strtotime($admission['admitted_at']))) ?></td>
                                <td><span class="badge-soft">Admitted</span></td>
                            </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    <?php endif; ?>
    <div class="<?= $canViewAdmissions ? 'col-12 col-xl-4' : 'col-12 col-xl-6' ?>">
        <?php if ($revenue !== null && has_permission('billing.overview')): ?>
            <section class="content-card mt-0">
                <div class="card-heading"><h3>Today's collections</h3></div>
                <div class="p-4"><div class="stat-label">Payments received today</div><div class="stat-value"><?= number_format($revenue, 2) ?></div><div class="stat-note">Amount shown in the currency used when billing.</div></div>
            </section>
        <?php endif; ?>
        <section class="content-card <?= $revenue !== null && has_permission('billing.overview') ? '' : 'mt-0' ?>">
            <div class="card-heading"><h3>Quick actions</h3></div>
            <div class="d-grid gap-2 p-3">
                <?php if (has_permission('patients.create')): ?>
                    <a class="btn btn-outline-secondary text-start" href="/hospital%20management/patient/form.php">＋ &nbsp; Register a patient</a>
                <?php endif; ?>
                <?php if (has_permission('patients.view')): ?>
                    <a class="btn btn-outline-secondary text-start" href="/hospital%20management/patient/index.php">⌕ &nbsp; Find a patient</a>
                <?php endif; ?>
                <?php if (has_permission('admissions.create')): ?>
                    <a class="btn btn-outline-secondary text-start" href="/hospital%20management/admission/create.php">▤ &nbsp; Admit a patient</a>
                <?php else: ?>
                    <?php if (has_permission('admissions.view')): ?><a class="btn btn-outline-secondary text-start" href="/hospital%20management/admission/index.php">▤ &nbsp; View admissions</a><?php endif; ?>
                <?php endif; ?>
                <?php if (has_permission('appointments.view')): ?><a class="btn btn-outline-secondary text-start" href="/hospital%20management/appointments/index.php">Appointments and check-in</a><?php endif; ?>
                <?php if (has_permission('visits.view')): ?><a class="btn btn-outline-secondary text-start" href="/hospital%20management/consultations/index.php">Consultation queue</a><?php endif; ?>
                <?php if (has_permission('triage.view')): ?><a class="btn btn-outline-secondary text-start" href="/hospital%20management/emergency/index.php">Emergency triage</a><?php endif; ?>
                <?php if (has_permission('lab.requests.view') || has_permission('lab.results.view')): ?><a class="btn btn-outline-secondary text-start" href="/hospital%20management/laboratory/index.php">Laboratory requests and results</a><?php endif; ?>
                <?php if (has_permission('radiology.requests.view') || has_permission('radiology.results.manage')): ?><a class="btn btn-outline-secondary text-start" href="/hospital%20management/radiology/index.php">Radiology requests and reports</a><?php endif; ?>
                <?php if (has_permission('prescriptions.create') || has_permission('pharmacy.dispense')): ?><a class="btn btn-outline-secondary text-start" href="/hospital%20management/pharmacy/index.php">Prescriptions and dispensing</a><?php endif; ?>
                <?php if (has_permission('nursing_notes.manage')): ?><a class="btn btn-outline-secondary text-start" href="/hospital%20management/nursing/index.php">Nursing observations</a><?php endif; ?>
                <?php if (has_permission('billing.overview') || has_permission('billing.view') || has_permission('payments.manage') || has_permission('invoices.manage')): ?><a class="btn btn-outline-secondary text-start" href="/hospital%20management/billing/index.php">Invoices and payments</a><?php endif; ?>
                <?php if (has_permission('ambulance.manage') || has_permission('ambulance.requests.create') || has_permission('emergency_requests.view')): ?><a class="btn btn-outline-secondary text-start" href="/hospital%20management/ambulance/index.php">Ambulance dispatch and requests</a><?php endif; ?>
                <?php if (has_permission('attendance.manage') || has_permission('payroll.manage') || has_permission('employees.manage')): ?><a class="btn btn-outline-secondary text-start" href="/hospital%20management/hr/index.php">Staff attendance and payroll</a><?php endif; ?>
                <?php if (has_permission('inventory.view')): ?><a class="btn btn-outline-secondary text-start" href="/hospital%20management/inventory/index.php">Inventory and low-stock items</a><?php endif; ?>
                <?php if (has_permission('inventory.view')): ?><a class="btn btn-outline-secondary text-start" href="/hospital%20management/inventory/equipment.php">Equipment maintenance schedule</a><?php endif; ?>
                <?php if (has_permission('reports.view') || has_permission('financial_reports.view')): ?><a class="btn btn-outline-secondary text-start" href="/hospital%20management/reports/index.php">Hospital reports</a><?php endif; ?>
                <?php if (has_permission('users.view')): ?><a class="btn btn-outline-secondary text-start" href="/hospital%20management/admin/users.php">♧ &nbsp; Manage staff accounts</a><?php endif; ?>
                <?php if (has_permission('roles.manage')): ?><a class="btn btn-outline-secondary text-start" href="/hospital%20management/admin/roles.php">⚙ &nbsp; Roles & permissions</a><?php endif; ?>
                <?php if ($cards === []): ?><p class="subtle mb-0">This role is configured. Its workflow screens will appear here as the module is implemented.</p><?php endif; ?>
            </div>
        </section>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>

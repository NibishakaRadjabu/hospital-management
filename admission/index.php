<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_permission('admissions.view');

$admissions = $pdo->query(
    'SELECT a.id, a.admitted_at, a.discharged_at, a.status, a.reason,
            p.id AS patient_id, p.patient_no, p.first_name, p.last_name,
            w.name AS ward_name, b.bed_number, ds.diagnosis, ds.summary, ds.medication_plan, ds.follow_up
     FROM admissions a
     JOIN patients p ON p.id = a.patient_id
     JOIN beds b ON b.id = a.bed_id
     JOIN wards w ON w.id = b.ward_id
     LEFT JOIN discharge_summaries ds ON ds.admission_id = a.id
     ORDER BY CASE WHEN a.status = \'admitted\' THEN 0 ELSE 1 END, a.admitted_at DESC
     LIMIT 100'
)->fetchAll();

$pageTitle = 'Admissions';
require __DIR__ . '/../includes/header.php';
?>
<div class="page-heading">
    <div><h2>Inpatient admissions</h2><p>Manage ward placement and discharge for admitted patients.</p></div>
    <a class="btn btn-primary" href="/hospital%20management/admission/create.php">+ Admit patient</a>
</div>
<section class="content-card mt-0">
    <div class="card-heading"><h3>Admissions <span class="subtle">(latest 100)</span></h3></div>
    <div class="table-responsive">
        <table class="table"><thead><tr><th>Patient</th><th>Ward / bed</th><th>Reason</th><th>Admitted</th><th>Status</th><th>Action</th></tr></thead><tbody>
        <?php if ($admissions === []): ?>
            <tr><td class="empty-state" colspan="6">No admissions have been recorded yet.</td></tr>
        <?php else: foreach ($admissions as $admission): ?>
            <tr>
                <td><a class="patient-name" href="/hospital%20management/patient/show.php?id=<?= (int) $admission['patient_id'] ?>"><?= e($admission['first_name'] . ' ' . $admission['last_name']) ?></a><br><span class="subtle"><?= e($admission['patient_no']) ?></span></td>
                <td><?= e($admission['ward_name']) ?><br><span class="subtle"><?= e($admission['bed_number']) ?></span></td>
                <td><?= e($admission['reason']) ?><?php if ($admission['summary'] && (has_permission('patients.history') || has_permission('visits.view') || has_permission('nursing_notes.manage'))): ?><details class="mt-2"><summary>Discharge summary</summary><p class="mb-1"><?= nl2br(e($admission['summary'])) ?></p><?php if ($admission['diagnosis']): ?><strong>Diagnosis:</strong> <?= nl2br(e($admission['diagnosis'])) ?><br><?php endif; ?><?php if ($admission['medication_plan']): ?><strong>Medication:</strong> <?= nl2br(e($admission['medication_plan'])) ?><br><?php endif; ?><?php if ($admission['follow_up']): ?><strong>Follow-up:</strong> <?= nl2br(e($admission['follow_up'])) ?><?php endif; ?></details><?php endif; ?></td>
                <td><?= e(date('M j, Y · g:i a', strtotime($admission['admitted_at']))) ?></td>
                <td><span class="<?= $admission['status'] === 'admitted' ? 'badge-soft' : 'badge-neutral' ?>"><?= e(ucfirst($admission['status'])) ?></span></td>
                <td>
                    <?php if ($admission['status'] === 'admitted' && has_permission('admissions.discharge')): ?>
                        <a class="btn btn-sm btn-outline-secondary" href="/hospital%20management/admission/discharge.php?id=<?= (int) $admission['id'] ?>">Prepare discharge</a>
                    <?php else: ?><span class="subtle"><?= e(format_date($admission['discharged_at'])) ?></span><?php endif; ?>
                </td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody></table>
    </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>

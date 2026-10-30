<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
require_permission('visits.view');
$user = current_user(); $doctorId = null;
if ($user['role_key'] === 'doctor') { $q=$pdo->prepare('SELECT id FROM doctors WHERE user_id=:id'); $q->execute(['id'=>$user['id']]); $doctorId=$q->fetchColumn(); if (!$doctorId) { http_response_code(403); exit('Your doctor profile is not configured.'); } }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_permission('visits.manage'); verify_csrf();
    $patientId=filter_var($_POST['patient_id']??'',FILTER_VALIDATE_INT); $appointmentId=filter_var($_POST['appointment_id']??'',FILTER_VALIDATE_INT);
    $complaint=trim((string)($_POST['chief_complaint']??'')); $diagnosis=trim((string)($_POST['diagnosis']??'')); $notes=trim((string)($_POST['notes']??''));
    if (!$patientId || strlen($complaint)>5000 || strlen($diagnosis)>5000 || strlen($notes)>10000) { set_flash('danger','Select a patient and keep clinical notes within the allowed lengths.'); redirect('/hospital%20management/consultations/index.php'); }
    $pdo->beginTransaction();
    try {
        $doctorForVisit=$doctorId;
        if ($appointmentId) {
            $q=$pdo->prepare('SELECT patient_id, doctor_id, status FROM appointments WHERE id=:id FOR UPDATE'); $q->execute(['id'=>$appointmentId]); $a=$q->fetch();
            if (!$a || (int)$a['patient_id']!==$patientId || ($doctorId && (int)$a['doctor_id']!==(int)$doctorId) || !in_array($a['status'],['checked_in','scheduled'],true)) throw new RuntimeException('Appointment does not match this patient or is no longer active.');
            $doctorForVisit=$a['doctor_id'];
        }
        $q=$pdo->prepare('INSERT INTO visits (patient_id, doctor_id, appointment_id, chief_complaint, diagnosis, notes) VALUES (:patient_id,:doctor_id,:appointment_id,:complaint,:diagnosis,:notes)');
        $q->execute(['patient_id'=>$patientId,'doctor_id'=>$doctorForVisit,'appointment_id'=>$appointmentId?:null,'complaint'=>$complaint?:null,'diagnosis'=>$diagnosis?:null,'notes'=>$notes?:null]);
        $id=(string)$pdo->lastInsertId();
        if ($appointmentId) $pdo->prepare("UPDATE appointments SET status='completed' WHERE id=:id")->execute(['id'=>$appointmentId]);
        record_audit('create','visit',$id,'Consultation recorded'); notify_staff('consultation_recorded','visit',$id,current_user()['full_name'].' recorded a consultation.'); $pdo->commit(); set_flash('success','Consultation recorded.');
    } catch (Throwable $e) { if($pdo->inTransaction())$pdo->rollBack(); set_flash('danger',$e instanceof RuntimeException?$e->getMessage():'Consultation could not be saved.'); }
    redirect('/hospital%20management/consultations/index.php');
}
$where=''; $params=[]; if($doctorId){$where='WHERE v.doctor_id=:doctor_id';$params['doctor_id']=$doctorId;}
$q=$pdo->prepare("SELECT v.id,v.patient_id,v.appointment_id,v.visited_at,v.chief_complaint,v.diagnosis,v.notes,p.patient_no,p.first_name,p.last_name,u.full_name AS doctor_name FROM visits v JOIN patients p ON p.id=v.patient_id LEFT JOIN doctors d ON d.id=v.doctor_id LEFT JOIN users u ON u.id=d.user_id {$where} ORDER BY v.visited_at DESC LIMIT 200"); $q->execute($params); $visits=$q->fetchAll();
$patients=has_permission('visits.manage')?$pdo->query('SELECT id,patient_no,first_name,last_name FROM patients ORDER BY last_name,first_name LIMIT 1000')->fetchAll():[];
$appointments=has_permission('visits.manage')?$pdo->query("SELECT id,patient_id,scheduled_at FROM appointments WHERE status IN ('checked_in','scheduled') ORDER BY scheduled_at DESC LIMIT 100")->fetchAll():[];
$pageTitle='Consultations'; require __DIR__.'/../includes/header.php'; ?>
<div class="page-heading"><div><h2>Consultations</h2><p>Record the medical consultation, diagnosis, and follow-up notes.</p></div></div>
<?php if(has_permission('visits.manage')): ?><section class="content-card form-card mt-0"><div class="card-heading"><h3>Record consultation</h3></div><form method="post" class="row g-3"><?= csrf_field() ?>
<div class="col-md-6"><label class="form-label">Patient</label><select class="form-select" name="patient_id" required><option value="">Select patient</option><?php foreach($patients as $p): ?><option value="<?= (int)$p['id'] ?>"><?= e($p['patient_no'].' · '.$p['last_name'].', '.$p['first_name']) ?></option><?php endforeach; ?></select></div>
<div class="col-md-6"><label class="form-label">Checked-in appointment (optional)</label><select class="form-select" name="appointment_id"><option value="">Walk-in / no linked appointment</option><?php foreach($appointments as $a): ?><option value="<?= (int)$a['id'] ?>">#<?= (int)$a['id'] ?> · <?= e(date('M j, g:i a',strtotime($a['scheduled_at']))) ?></option><?php endforeach; ?></select></div>
<div class="col-md-6"><label class="form-label">Chief complaint</label><textarea class="form-control" name="chief_complaint" rows="2" maxlength="5000"></textarea></div><div class="col-md-6"><label class="form-label">Diagnosis</label><textarea class="form-control" name="diagnosis" rows="2" maxlength="5000"></textarea></div><div class="col-12"><label class="form-label">Clinical notes and plan</label><textarea class="form-control" name="notes" rows="3" maxlength="10000"></textarea></div><div class="col-12"><button class="btn btn-primary">Save consultation</button></div></form></section><?php endif; ?>
<section class="content-card"><div class="table-responsive"><table class="table"><thead><tr><th>Time</th><th>Patient</th><th>Clinician</th><th>Complaint</th><th>Diagnosis</th><th>Notes</th></tr></thead><tbody><?php if(!$visits): ?><tr><td class="empty-state" colspan="6">No consultations recorded.</td></tr><?php else: foreach($visits as $v): ?><tr><td><?= e(date('M j, Y g:i a',strtotime($v['visited_at']))) ?></td><td><a href="/hospital%20management/patient/show.php?id=<?= (int)$v['patient_id'] ?>"><?= e($v['last_name'].', '.$v['first_name']) ?></a><br><span class="subtle"><?= e($v['patient_no']) ?></span></td><td><?= e($v['doctor_name']??'Unassigned') ?></td><td><?= nl2br(e($v['chief_complaint']?:'—')) ?></td><td><?= nl2br(e($v['diagnosis']?:'—')) ?></td><td><?= nl2br(e($v['notes']?:'—')) ?></td></tr><?php endforeach; endif; ?></tbody></table></div></section>
<?php require __DIR__.'/../includes/footer.php'; ?>

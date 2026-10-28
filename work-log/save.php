<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_permission('work_logs.create');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}
verify_csrf();

$workDate = trim((string) ($_POST['work_date'] ?? ''));
$category = (string) ($_POST['category'] ?? '');
$summary = trim((string) ($_POST['summary'] ?? ''));
$details = trim((string) ($_POST['details'] ?? ''));
$durationInput = trim((string) ($_POST['duration_minutes'] ?? ''));
$categories = work_log_categories();
$date = DateTime::createFromFormat('!Y-m-d', $workDate);
$duration = $durationInput === '' ? null : filter_var($durationInput, FILTER_VALIDATE_INT);

if (!$date || $date->format('Y-m-d') !== $workDate || $date > new DateTime('today')
    || !isset($categories[$category])
    || $summary === '' || strlen($summary) > 160
    || $details === '' || strlen($details) > 5000
    || ($durationInput !== '' && ($duration === false || $duration < 1 || $duration > 1440))) {
    set_flash('danger', 'Check the work date, category, summary, details, and optional duration, then try again.');
    redirect('/hospital%20management/work-log/create.php');
}

try {
    $pdo->beginTransaction();
    $statement = $pdo->prepare(
        'INSERT INTO work_logs (user_id, work_date, category, summary, details, duration_minutes)
         VALUES (:user_id, :work_date, :category, :summary, :details, :duration_minutes)'
    );
    $statement->execute([
        'user_id' => current_user()['id'],
        'work_date' => $workDate,
        'category' => $category,
        'summary' => $summary,
        'details' => $details,
        'duration_minutes' => $duration,
    ]);
    $id = (string) $pdo->lastInsertId();
    record_audit('create', 'work_log', $id, 'Work log entry recorded');
    $pdo->commit();
    set_flash('success', 'Your work entry has been recorded.');
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Work log save failed: ' . $exception->getMessage());
    set_flash('danger', 'The work entry could not be saved. Please try again.');
}
redirect('/hospital%20management/work-log/index.php');

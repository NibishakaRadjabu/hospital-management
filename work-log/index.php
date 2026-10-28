<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_login();
if (!has_permission('work_logs.view_own') && !has_permission('work_logs.view_all')) {
    http_response_code(403);
    exit('You do not have permission to access work logs.');
}

$canViewAll = has_permission('work_logs.view_all');
$categories = work_log_categories();
$dateFrom = trim((string) ($_GET['date_from'] ?? ''));
$dateTo = trim((string) ($_GET['date_to'] ?? ''));
$category = (string) ($_GET['category'] ?? '');
$selectedUser = filter_var($_GET['user_id'] ?? '', FILTER_VALIDATE_INT);
$filters = [];
$params = [];

foreach (['date_from' => $dateFrom, 'date_to' => $dateTo] as $field => $dateValue) {
    if ($dateValue !== '') {
        $parsed = DateTime::createFromFormat('!Y-m-d', $dateValue);
        if (!$parsed || $parsed->format('Y-m-d') !== $dateValue) {
            http_response_code(400);
            exit('Invalid work log date filter.');
        }
        $filters[] = 'wl.work_date ' . ($field === 'date_from' ? '>=' : '<=') . ' :' . $field;
        $params[$field] = $dateValue;
    }
}
if ($category !== '') {
    if (!isset($categories[$category])) {
        http_response_code(400);
        exit('Invalid work log category filter.');
    }
    $filters[] = 'wl.category = :category';
    $params['category'] = $category;
}
if (!$canViewAll) {
    $filters[] = 'wl.user_id = :current_user_id';
    $params['current_user_id'] = current_user()['id'];
} elseif ($selectedUser !== false && $selectedUser !== null && $selectedUser > 0) {
    $filters[] = 'wl.user_id = :selected_user_id';
    $params['selected_user_id'] = $selectedUser;
}

$where = $filters === [] ? '' : 'WHERE ' . implode(' AND ', $filters);
$query = $pdo->prepare(
    "SELECT wl.id, wl.user_id, wl.work_date, wl.category, wl.summary, wl.details,
            wl.duration_minutes, wl.created_at, u.full_name, r.name AS role_name
     FROM work_logs wl
     JOIN users u ON u.id = wl.user_id
     JOIN roles r ON r.id = u.role_id
     {$where}
     ORDER BY wl.work_date DESC, wl.created_at DESC
     LIMIT 200"
);
$query->execute($params);
$workLogs = $query->fetchAll();

$staff = [];
if ($canViewAll) {
    $staff = $pdo->query(
        'SELECT DISTINCT u.id, u.full_name
         FROM users u JOIN work_logs wl ON wl.user_id = u.id
         ORDER BY u.full_name'
    )->fetchAll();
}

$pageTitle = $canViewAll ? 'Work logs' : 'My work log';
require __DIR__ . '/../includes/header.php';
?>
<div class="page-heading">
    <div><h2><?= e($pageTitle) ?></h2><p>Record completed work and review work entries. Entries are read-only after submission.</p></div>
    <?php if (has_permission('work_logs.create')): ?><a class="btn btn-primary" href="/hospital%20management/work-log/create.php">+ Record work</a><?php endif; ?>
</div>
<?php if (has_permission('work_logs.create')): ?>
    <div class="alert alert-info py-2">Use a short operational summary. Do not include unnecessary patient-identifying or confidential clinical details.</div>
<?php endif; ?>
<section class="content-card mt-0">
    <form class="card-heading row g-2 align-items-end" method="get" action="/hospital%20management/work-log/index.php">
        <div class="col-6 col-md-2"><label class="form-label mb-1" for="date_from">From</label><input class="form-control" id="date_from" name="date_from" type="date" value="<?= e($dateFrom) ?>"></div>
        <div class="col-6 col-md-2"><label class="form-label mb-1" for="date_to">To</label><input class="form-control" id="date_to" name="date_to" type="date" value="<?= e($dateTo) ?>"></div>
        <div class="col-6 col-md-3"><label class="form-label mb-1" for="category">Category</label><select class="form-select" id="category" name="category"><option value="">All categories</option><?php foreach ($categories as $key => $label): ?><option value="<?= e($key) ?>" <?= $category === $key ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></div>
        <?php if ($canViewAll): ?><div class="col-6 col-md-3"><label class="form-label mb-1" for="user_id">Staff member</label><select class="form-select" id="user_id" name="user_id"><option value="">All users</option><?php foreach ($staff as $member): ?><option value="<?= (int) $member['id'] ?>" <?= (string) $selectedUser === (string) $member['id'] ? 'selected' : '' ?>><?= e($member['full_name']) ?></option><?php endforeach; ?></select></div><?php endif; ?>
        <div class="col-12 col-md-auto"><button class="btn btn-outline-secondary" type="submit">Filter</button> <a class="btn btn-outline-secondary" href="/hospital%20management/work-log/index.php">Clear</a></div>
    </form>
    <div class="table-responsive">
        <table class="table">
            <thead><tr><?php if ($canViewAll): ?><th>Staff member</th><?php endif; ?><th>Work date</th><th>Category</th><th>Work summary</th><th>Details</th><th>Time</th><th>Recorded at</th></tr></thead>
            <tbody>
            <?php if ($workLogs === []): ?>
                <tr><td class="empty-state" colspan="<?= $canViewAll ? 7 : 6 ?>">No work logs match these filters.</td></tr>
            <?php else: foreach ($workLogs as $entry): ?>
                <tr>
                    <?php if ($canViewAll): ?><td><span class="patient-name"><?= e($entry['full_name']) ?></span><br><span class="subtle"><?= e($entry['role_name']) ?></span></td><?php endif; ?>
                    <td><?= e(format_date($entry['work_date'])) ?></td>
                    <td><span class="badge-neutral"><?= e($categories[$entry['category']] ?? $entry['category']) ?></span></td>
                    <td class="patient-name"><?= e($entry['summary']) ?></td>
                    <td><?= nl2br(e($entry['details'])) ?></td>
                    <td><?= $entry['duration_minutes'] === null ? '—' : e((string) $entry['duration_minutes'] . ' min') ?></td>
                    <td><?= e(date('M j, Y · g:i a', strtotime($entry['created_at']))) ?></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    <?php if (count($workLogs) === 200): ?><div class="p-3 subtle">Showing the latest 200 matching entries. Narrow the date filter to see older records.</div><?php endif; ?>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>

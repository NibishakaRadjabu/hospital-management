<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_login();
if (!has_permission('patient_services.view_own') && !has_permission('patient_services.view_all')) {
    http_response_code(403);
    exit('You do not have permission to access patient service records.');
}

$canViewAll = has_permission('patient_services.view_all');
$categories = patient_service_categories();
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
            exit('Invalid patient service date filter.');
        }
        $filters[] = 'ps.service_date ' . ($field === 'date_from' ? '>=' : '<=') . ' :' . $field;
        $params[$field] = $dateValue;
    }
}
if ($category !== '') {
    if (!isset($categories[$category])) {
        http_response_code(400);
        exit('Invalid patient service category filter.');
    }
    $filters[] = 'ps.category = :category';
    $params['category'] = $category;
}
if (!$canViewAll) {
    $filters[] = 'ps.provided_by = :current_user_id';
    $params['current_user_id'] = current_user()['id'];
} elseif ($selectedUser !== false && $selectedUser !== null && $selectedUser > 0) {
    $filters[] = 'ps.provided_by = :selected_user_id';
    $params['selected_user_id'] = $selectedUser;
}

$where = $filters === [] ? '' : 'WHERE ' . implode(' AND ', $filters);
$query = $pdo->prepare(
    "SELECT ps.id, ps.patient_id, ps.provided_by, ps.service_date, ps.category,
            ps.service_name, ps.details, ps.duration_minutes, ps.created_at,
            p.patient_no, p.first_name, p.last_name, u.full_name AS staff_name,
            r.name AS role_name
     FROM patient_services ps
     JOIN patients p ON p.id = ps.patient_id
     JOIN users u ON u.id = ps.provided_by
     JOIN roles r ON r.id = u.role_id
     {$where}
     ORDER BY ps.service_date DESC, ps.created_at DESC
     LIMIT 200"
);
$query->execute($params);
$services = $query->fetchAll();

$staff = [];
if ($canViewAll) {
    $staff = $pdo->query(
        'SELECT DISTINCT u.id, u.full_name
         FROM users u JOIN patient_services ps ON ps.provided_by = u.id
         ORDER BY u.full_name'
    )->fetchAll();
}

$pageTitle = $canViewAll ? 'Patient services' : 'My patient services';
require __DIR__ . '/../includes/header.php';
?>
<div class="page-heading">
    <div><h2><?= e($pageTitle) ?></h2><p>Record and review services delivered to patients. Saved records are read-only.</p></div>
    <?php if (has_permission('patient_services.create')): ?><a class="btn btn-primary" href="/hospital%20management/patient-services/create.php">+ Record service</a><?php endif; ?>
</div>
<section class="content-card mt-0">
    <form class="card-heading row g-2 align-items-end" method="get" action="/hospital%20management/patient-services/index.php">
        <div class="col-6 col-md-2"><label class="form-label mb-1" for="date_from">From</label><input class="form-control" id="date_from" name="date_from" type="date" value="<?= e($dateFrom) ?>"></div>
        <div class="col-6 col-md-2"><label class="form-label mb-1" for="date_to">To</label><input class="form-control" id="date_to" name="date_to" type="date" value="<?= e($dateTo) ?>"></div>
        <div class="col-6 col-md-3"><label class="form-label mb-1" for="category">Service type</label><select class="form-select" id="category" name="category"><option value="">All service types</option><?php foreach ($categories as $key => $label): ?><option value="<?= e($key) ?>" <?= $category === $key ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></div>
        <?php if ($canViewAll): ?><div class="col-6 col-md-3"><label class="form-label mb-1" for="user_id">Staff member</label><select class="form-select" id="user_id" name="user_id"><option value="">All staff</option><?php foreach ($staff as $member): ?><option value="<?= (int) $member['id'] ?>" <?= (string) $selectedUser === (string) $member['id'] ? 'selected' : '' ?>><?= e($member['full_name']) ?></option><?php endforeach; ?></select></div><?php endif; ?>
        <div class="col-12 col-md-auto"><button class="btn btn-outline-secondary" type="submit">Filter</button> <a class="btn btn-outline-secondary" href="/hospital%20management/patient-services/index.php">Clear</a></div>
    </form>
    <div class="table-responsive">
        <table class="table">
            <thead><tr><?php if ($canViewAll): ?><th>Recorded by</th><?php endif; ?><th>Patient</th><th>Date</th><th>Service</th><th>Details</th><th>Time</th></tr></thead>
            <tbody>
            <?php if ($services === []): ?>
                <tr><td class="empty-state" colspan="<?= $canViewAll ? 6 : 5 ?>">No patient service records match these filters.</td></tr>
            <?php else: foreach ($services as $service): ?>
                <tr>
                    <?php if ($canViewAll): ?><td><span class="patient-name"><?= e($service['staff_name']) ?></span><br><span class="subtle"><?= e($service['role_name']) ?></span></td><?php endif; ?>
                    <td><a class="patient-name" href="/hospital%20management/patient/show.php?id=<?= (int) $service['patient_id'] ?>"><?= e($service['first_name'] . ' ' . $service['last_name']) ?></a><br><span class="subtle"><?= e($service['patient_no']) ?></span></td>
                    <td><?= e(format_date($service['service_date'])) ?></td>
                    <td><span class="badge-neutral"><?= e($categories[$service['category']] ?? $service['category']) ?></span><br><span class="patient-name"><?= e($service['service_name']) ?></span></td>
                    <td><?= nl2br(e($service['details'])) ?></td>
                    <td><?= $service['duration_minutes'] === null ? '—' : e((string) $service['duration_minutes'] . ' min') ?></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    <?php if (count($services) === 200): ?><div class="p-3 subtle">Showing the latest 200 matching records. Narrow the date filter to see older services.</div><?php endif; ?>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>

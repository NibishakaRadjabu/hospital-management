<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_permission('departments.manage');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);
    $name = trim((string) ($_POST['name'] ?? ''));
    $description = trim((string) ($_POST['description'] ?? ''));
    if ($name === '' || strlen($name) > 120 || strlen($description) > 255 || ($id !== false && $id !== null && $id < 1)) {
        set_flash('danger', 'Enter a department name up to 120 characters and a description up to 255 characters.');
        redirect('/hospital%20management/admin/departments.php');
    }

    try {
        $pdo->beginTransaction();
        if ($id === false || $id === null || $id === 0) {
            $statement = $pdo->prepare('INSERT INTO departments (name, description) VALUES (:name, :description)');
            $statement->execute(['name' => $name, 'description' => $description ?: null]);
            $id = (int) $pdo->lastInsertId();
            record_audit('create', 'department', (string) $id, 'Hospital department created');
            $pdo->commit();
            set_flash('success', 'Department created.');
        } else {
            $statement = $pdo->prepare('UPDATE departments SET name = :name, description = :description WHERE id = :id');
            $statement->execute(['name' => $name, 'description' => $description ?: null, 'id' => $id]);
            if ($statement->rowCount() === 0) {
                $exists = $pdo->prepare('SELECT id FROM departments WHERE id = :id');
                $exists->execute(['id' => $id]);
                if (!$exists->fetch()) {
                    $pdo->rollBack();
                    http_response_code(404);
                    exit('Department not found.');
                }
            }
            record_audit('update', 'department', (string) $id, 'Hospital department updated');
            $pdo->commit();
            set_flash('success', 'Department updated.');
        }
    } catch (PDOException $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        if ($exception->getCode() === '23000') {
            set_flash('danger', 'A department with that name already exists.');
        } else {
            error_log('Department update failed: ' . $exception->getMessage());
            set_flash('danger', 'The department could not be saved.');
        }
    }
    redirect('/hospital%20management/admin/departments.php');
}

$departments = $pdo->query('SELECT id, name, description FROM departments ORDER BY name')->fetchAll();
$pageTitle = 'Departments';
require __DIR__ . '/../includes/header.php';
?>
<div class="page-heading"><div><h2>Hospital departments</h2><p>Maintain the departments used across hospital services.</p></div></div>
<section class="content-card form-card mt-0 mb-4">
    <form method="post" action="/hospital%20management/admin/departments.php" class="row g-3 align-items-end">
        <?= csrf_field() ?><input type="hidden" name="id" value="0">
        <div class="col-md-4"><label class="form-label" for="new-name">Department name</label><input class="form-control" id="new-name" name="name" maxlength="120" required></div>
        <div class="col-md-6"><label class="form-label" for="new-description">Description</label><input class="form-control" id="new-description" name="description" maxlength="255"></div>
        <div class="col-md-2"><button class="btn btn-primary w-100" type="submit">Add</button></div>
    </form>
</section>
<section class="content-card mt-0">
    <div class="card-heading"><h3>Departments <span class="subtle">(<?= count($departments) ?>)</span></h3></div>
    <div class="table-responsive"><table class="table"><thead><tr><th>Name</th><th>Description</th><th>Update</th></tr></thead><tbody>
        <?php foreach ($departments as $department): ?>
            <tr>
                <td colspan="3">
                    <form id="department-<?= (int) $department['id'] ?>" method="post" action="/hospital%20management/admin/departments.php">
                        <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $department['id'] ?>">
                    </form>
                    <div class="row g-2">
                        <div class="col-md-4"><input class="form-control" form="department-<?= (int) $department['id'] ?>" name="name" maxlength="120" required value="<?= e($department['name']) ?>" aria-label="Department name"></div>
                        <div class="col-md-6"><input class="form-control" form="department-<?= (int) $department['id'] ?>" name="description" maxlength="255" value="<?= e($department['description']) ?>" aria-label="Department description"></div>
                        <div class="col-md-2"><button class="btn btn-sm btn-outline-secondary" form="department-<?= (int) $department['id'] ?>" type="submit">Save</button></div>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody></table></div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>

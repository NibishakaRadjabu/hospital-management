<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_permission('roles.manage');

$systemOwner = in_array(current_user()['role_key'], ['super_admin', 'admin'], true);
if (!$systemOwner) {
    http_response_code(403);
    exit('Only a system owner can change role permissions.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'create') {
        $roleKey = strtolower(trim((string) ($_POST['role_key'] ?? '')));
        $name = trim((string) ($_POST['name'] ?? ''));
        if (!preg_match('/^[a-z][a-z0-9_]{2,39}$/', $roleKey) || $name === '' || strlen($name) > 80) {
            set_flash('danger', 'Enter a role key using 3–40 lowercase letters, numbers, or underscores, and a role name up to 80 characters.');
            redirect('/hospital%20management/admin/roles.php');
        }

        try {
            $pdo->beginTransaction();
            $insert = $pdo->prepare('INSERT INTO roles (role_key, name) VALUES (:role_key, :name)');
            $insert->execute(['role_key' => $roleKey, 'name' => $name]);
            record_audit('create', 'role', (string) $pdo->lastInsertId(), 'Custom role created');
            $pdo->commit();
            set_flash('success', 'Role created. Configure its permissions below.');
        } catch (PDOException $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            if ($exception->getCode() === '23000') {
                set_flash('danger', 'That role key already exists.');
            } else {
                error_log('Role creation failed: ' . $exception->getMessage());
                set_flash('danger', 'The role could not be created.');
            }
        }
        redirect('/hospital%20management/admin/roles.php');
    }

    if ($action === 'save_permissions') {
        $roleId = filter_var($_POST['role_id'] ?? '', FILTER_VALIDATE_INT);
        $roleQuery = $pdo->prepare('SELECT id, role_key FROM roles WHERE id = :id');
        $roleQuery->execute(['id' => $roleId]);
        $targetRole = $roleQuery->fetch();
        if (!$roleId || !$targetRole || in_array($targetRole['role_key'], ['admin', 'super_admin'], true)) {
            set_flash('danger', 'The selected role cannot be edited.');
            redirect('/hospital%20management/admin/roles.php');
        }

        $submitted = $_POST['permission_keys'] ?? [];
        if (!is_array($submitted)) {
            set_flash('danger', 'Invalid permission selection.');
            redirect('/hospital%20management/admin/roles.php');
        }
        $submitted = array_values(array_unique(array_filter($submitted, 'is_string')));
        $restrictedSystemPermissions = ['roles.manage', 'settings.manage', 'database.backup', 'audit.view'];
        if (!in_array($targetRole['role_key'], ['admin', 'super_admin'], true)
            && array_intersect($submitted, $restrictedSystemPermissions) !== []) {
            set_flash('danger', 'System-owner permissions cannot be assigned to this role.');
            redirect('/hospital%20management/admin/roles.php');
        }
        $permissions = $pdo->query('SELECT id, permission_key FROM permissions')->fetchAll();
        $permissionIds = [];
        foreach ($permissions as $permission) {
            if (in_array($permission['permission_key'], $submitted, true)) {
                $permissionIds[] = (int) $permission['id'];
            }
        }
        if (count($permissionIds) !== count($submitted)) {
            set_flash('danger', 'The selected permissions contain an unknown value.');
            redirect('/hospital%20management/admin/roles.php');
        }

        try {
            $pdo->beginTransaction();
            $pdo->prepare('DELETE FROM role_permissions WHERE role_id = :role_id')->execute(['role_id' => $roleId]);
            $grant = $pdo->prepare('INSERT INTO role_permissions (role_id, permission_id) VALUES (:role_id, :permission_id)');
            foreach ($permissionIds as $permissionId) {
                $grant->execute(['role_id' => $roleId, 'permission_id' => $permissionId]);
            }
            record_audit('update_permissions', 'role', (string) $roleId, 'Role permissions updated');
            $pdo->commit();
            set_flash('success', 'Permissions updated for ' . $targetRole['role_key'] . '.');
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Role permissions update failed: ' . $exception->getMessage());
            set_flash('danger', 'Role permissions could not be saved.');
        }
        redirect('/hospital%20management/admin/roles.php');
    }

    http_response_code(400);
    exit('Unknown role management action.');
}

$roles = $pdo->query('SELECT id, role_key, name FROM roles ORDER BY name')->fetchAll();
$permissions = $pdo->query('SELECT id, permission_key, name, description, category FROM permissions ORDER BY category, name')->fetchAll();
$rolePermissionRows = $pdo->query('SELECT role_id, permission_id FROM role_permissions')->fetchAll();
$grants = [];
foreach ($rolePermissionRows as $grant) {
    $grants[(int) $grant['role_id']][(int) $grant['permission_id']] = true;
}
$permissionGroups = [];
foreach ($permissions as $permission) {
    $permissionGroups[$permission['category']][] = $permission;
}

$pageTitle = 'Roles & permissions';
require __DIR__ . '/../includes/header.php';
?>
<div class="page-heading">
    <div><h2>Roles & permissions</h2><p>Configure server-enforced access for each role. System-owner roles are protected from permission changes.</p></div>
</div>
<section class="content-card form-card mt-0 mb-4">
    <form method="post" action="/hospital%20management/admin/roles.php" class="row g-3 align-items-end">
        <?= csrf_field() ?><input type="hidden" name="action" value="create">
        <div class="col-md-4"><label class="form-label" for="role_key">Role key</label><input class="form-control" id="role_key" name="role_key" maxlength="40" pattern="[a-z][a-z0-9_]{2,39}" placeholder="ward_coordinator" required></div>
        <div class="col-md-5"><label class="form-label" for="name">Role name</label><input class="form-control" id="name" name="name" maxlength="80" placeholder="Ward Coordinator" required></div>
        <div class="col-md-3"><button class="btn btn-primary w-100" type="submit">Create role</button></div>
    </form>
</section>
<div class="d-grid gap-3">
    <?php foreach ($roles as $role): ?>
        <?php $protectedRole = in_array($role['role_key'], ['admin', 'super_admin'], true); ?>
        <details class="content-card role-card">
            <summary class="card-heading role-summary">
                <span><strong><?= e($role['name']) ?></strong><span class="subtle ms-2"><?= e($role['role_key']) ?></span></span>
                <span class="badge-neutral"><?= $protectedRole ? 'Protected system role' : count($grants[(int) $role['id']] ?? []) . ' permissions' ?></span>
            </summary>
            <?php if ($protectedRole): ?>
                <div class="p-3 subtle">This role always retains full system access.</div>
            <?php else: ?>
                <form method="post" action="/hospital%20management/admin/roles.php" class="p-3">
                    <?= csrf_field() ?><input type="hidden" name="action" value="save_permissions"><input type="hidden" name="role_id" value="<?= (int) $role['id'] ?>">
                    <?php foreach ($permissionGroups as $category => $items): ?>
                        <div class="form-section-title mt-3"><?= e($category) ?></div>
                        <div class="row g-2">
                            <?php foreach ($items as $permission): ?>
                                <div class="col-12 col-md-6 col-xl-4">
                                    <?php $restrictedPermission = !$protectedRole && in_array($permission['permission_key'], ['roles.manage', 'settings.manage', 'database.backup', 'audit.view'], true); ?>
                                    <label class="permission-option">
                                        <input class="form-check-input" type="checkbox" name="permission_keys[]" value="<?= e($permission['permission_key']) ?>" <?= isset($grants[(int) $role['id']][(int) $permission['id']]) ? 'checked' : '' ?> <?= $restrictedPermission ? 'disabled' : '' ?>>
                                        <span><strong><?= e($permission['name']) ?></strong><small><?= e($permission['description']) ?></small></span>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                    <div class="d-flex justify-content-end mt-4"><button class="btn btn-primary" type="submit">Save permissions</button></div>
                </form>
            <?php endif; ?>
        </details>
    <?php endforeach; ?>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>

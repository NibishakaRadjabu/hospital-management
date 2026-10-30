<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_permission('users.view');
$canManageUsers = has_permission('users.manage');
$canManagePrivilegedRoles = has_permission('roles.manage');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_permission('users.manage');
    verify_csrf();
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'create') {
        $name = trim((string) ($_POST['full_name'] ?? ''));
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');
        $roleId = filter_var($_POST['role_id'] ?? '', FILTER_VALIDATE_INT);
        $patientId = filter_var($_POST['patient_id'] ?? '', FILTER_VALIDATE_INT);

        if ($name === '' || strlen($name) > 160 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190 || strlen($password) < 12 || !$roleId) {
            set_flash('danger', 'Enter a name, valid email, role, and password of at least 12 characters.');
            redirect('/hospital%20management/admin/users.php');
        }

        try {
            $role = $pdo->prepare('SELECT role_key FROM roles WHERE id = :id');
            $role->execute(['id' => $roleId]);
            $roleKey = $role->fetchColumn();
            if ($roleKey === false || (!$canManagePrivilegedRoles && in_array($roleKey, ['admin', 'super_admin', 'hospital_admin', 'patient_portal'], true))) {
                set_flash('danger', 'Choose a valid staff role.');
                redirect('/hospital%20management/admin/users.php');
            }
            if ($roleKey === 'patient_portal' && (!$canManagePrivilegedRoles || !$patientId || $patientId < 1)) {
                set_flash('danger', 'A system owner must link each portal account to exactly one registered patient.');
                redirect('/hospital%20management/admin/users.php');
            }

            $pdo->beginTransaction();
            $insert = $pdo->prepare('INSERT INTO users (role_id, full_name, email, password_hash, payment_method, payment_details) VALUES (:role_id, :full_name, :email, :password_hash, :payment_method, :payment_details)');
            $insert->execute([
                'role_id' => $roleId,
                'full_name' => $name,
                'email' => $email,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'payment_method' => trim((string) ($_POST['payment_method'] ?? '')) ?: null,
                'payment_details' => trim((string) ($_POST['payment_details'] ?? '')) ?: null,
            ]);
            $userId = (int) $pdo->lastInsertId();
            if ($roleKey === 'doctor') {
                $pdo->prepare('INSERT INTO doctors (user_id) VALUES (:user_id)')
                    ->execute(['user_id' => $userId]);
            } elseif ($roleKey === 'patient_portal') {
                $link = $pdo->prepare('INSERT INTO patient_portal_accounts (user_id, patient_id) VALUES (:user_id, :patient_id)');
                $link->execute(['user_id' => $userId, 'patient_id' => $patientId]);
            }
            record_audit('create', 'user', (string) $userId, $roleKey === 'patient_portal' ? 'Patient portal account created' : 'Staff account created');
            $pdo->commit();
            set_flash('success', 'Staff account created. Share the initial password with the staff member securely.');
        } catch (PDOException $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            if ($exception->getCode() === '23000') {
                set_flash('danger', 'That email is already in use, or the selected role is invalid.');
            } else {
                error_log('Staff account creation failed: ' . $exception->getMessage());
                set_flash('danger', 'The staff account could not be created.');
            }
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Staff account creation failed: ' . $exception->getMessage());
            set_flash('danger', 'The staff account could not be created.');
        }
        redirect('/hospital%20management/admin/users.php');
    }

    if ($action === 'toggle') {
        $userId = filter_var($_POST['user_id'] ?? '', FILTER_VALIDATE_INT);
        if (!$userId || $userId < 1 || $userId === (int) current_user()['id']) {
            set_flash('danger', 'You cannot deactivate your own account.');
            redirect('/hospital%20management/admin/users.php');
        }

        $target = $pdo->prepare('SELECT u.id, u.is_active, r.role_key FROM users u JOIN roles r ON r.id = u.role_id WHERE u.id = :id');
        $target->execute(['id' => $userId]);
        $account = $target->fetch();
        if (!$account) {
            set_flash('danger', 'The selected account could not be found.');
            redirect('/hospital%20management/admin/users.php');
        }

        if (!$canManagePrivilegedRoles && in_array($account['role_key'], ['admin', 'super_admin', 'hospital_admin'], true)) {
            set_flash('danger', 'You do not have permission to change a system or hospital administrator account.');
            redirect('/hospital%20management/admin/users.php');
        }

        if ((int) $account['is_active'] === 1 && $account['role_key'] === 'admin') {
            $activeAdmins = (int) $pdo->query("SELECT COUNT(*) FROM users u JOIN roles r ON r.id = u.role_id WHERE r.role_key = 'admin' AND u.is_active = 1")->fetchColumn();
            if ($activeAdmins <= 1) {
                set_flash('warning', 'The last active administrator cannot be deactivated.');
                redirect('/hospital%20management/admin/users.php');
            }
            if ((int) $account['is_active'] === 1 && $account['role_key'] === 'super_admin') {
                $activeOwners = (int) $pdo->query("SELECT COUNT(*) FROM users u JOIN roles r ON r.id = u.role_id WHERE r.role_key = 'super_admin' AND u.is_active = 1")->fetchColumn();
                if ($activeOwners <= 1) {
                    set_flash('warning', 'The last active system owner cannot be deactivated.');
                    redirect('/hospital%20management/admin/users.php');
                }
            }
        }

        $update = $pdo->prepare('UPDATE users SET is_active = :is_active WHERE id = :id');
        $update->execute(['is_active' => (int) $account['is_active'] === 1 ? 0 : 1, 'id' => $userId]);
        record_audit((int) $account['is_active'] === 1 ? 'deactivate' : 'activate', 'user', (string) $userId, 'Staff account status changed');
        set_flash('success', (int) $account['is_active'] === 1 ? 'Staff account deactivated.' : 'Staff account activated.');
        redirect('/hospital%20management/admin/users.php');
    }

    http_response_code(400);
    exit('Unknown staff account action.');
}

$roleSql = 'SELECT id, role_key, name FROM roles';
if (!$canManagePrivilegedRoles) {
    $roleSql .= " WHERE role_key NOT IN ('admin', 'super_admin', 'hospital_admin', 'patient_portal')";
}
$roles = $pdo->query($roleSql . ' ORDER BY name')->fetchAll();
$patients = $canManagePrivilegedRoles
    ? $pdo->query('SELECT id, patient_no, first_name, last_name FROM patients ORDER BY last_name, first_name')->fetchAll()
    : [];
$users = $pdo->query(
    'SELECT u.id, u.full_name, u.email, u.is_active, u.last_login_at, u.payment_method, u.payment_details, r.name AS role_name, r.role_key
     FROM users u JOIN roles r ON r.id = u.role_id
     ORDER BY u.created_at DESC'
)->fetchAll();

$pageTitle = 'Staff accounts';
require __DIR__ . '/../includes/header.php';
?>
<div class="page-heading">
    <div><h2>Staff accounts</h2><p>Create role-based logins and deactivate accounts that should no longer access the system.</p></div>
</div>
<div class="row g-3">
    <div class="col-12 col-xl-4">
        <form class="content-card form-card mt-0" method="post" action="/hospital%20management/admin/users.php">
            <?= csrf_field() ?><input type="hidden" name="action" value="create">
            <div class="form-section-title">Add staff member</div>
            <?php if ($canManageUsers): ?>
                <div class="mb-3"><label class="form-label" for="full_name">Full name <span class="text-danger">*</span></label><input class="form-control" id="full_name" name="full_name" maxlength="160" required></div>
                <div class="mb-3"><label class="form-label" for="email">Work email <span class="text-danger">*</span></label><input class="form-control" id="email" name="email" type="email" maxlength="190" required></div>
                <div class="mb-3"><label class="form-label" for="role_id">Role <span class="text-danger">*</span></label><select class="form-select" id="role_id" name="role_id" required><option value="">Select role</option><?php foreach ($roles as $role): ?><option value="<?= (int) $role['id'] ?>"><?= e($role['name']) ?></option><?php endforeach; ?></select></div>
                <?php if ($canManagePrivilegedRoles): ?><div class="mb-3"><label class="form-label" for="patient_id">Linked patient (patient portal only)</label><select class="form-select" id="patient_id" name="patient_id"><option value="">Not a patient portal account</option><?php foreach ($patients as $patient): ?><option value="<?= (int) $patient['id'] ?>"><?= e($patient['patient_no'] . ' · ' . $patient['first_name'] . ' ' . $patient['last_name']) ?></option><?php endforeach; ?></select></div><?php endif; ?>
                <div class="mb-3"><label class="form-label" for="password">Initial password <span class="text-danger">*</span></label><input class="form-control" id="password" name="password" type="password" minlength="12" autocomplete="new-password" required><div class="form-text">At least 12 characters. Share it securely.</div></div>
                <div class="form-section-title">Payment method</div>
                <div class="mb-3"><label class="form-label" for="payment_method">Payment method</label><select class="form-select" id="payment_method" name="payment_method"><option value="">Not specified</option><option value="bank_transfer">Bank transfer</option><option value="mobile_money">Mobile money</option><option value="paypal">PayPal</option><option value="other">Other</option></select></div>
                <div class="mb-3"><label class="form-label" for="payment_details">Payment details</label><input class="form-control" id="payment_details" name="payment_details" maxlength="255" placeholder="Account number, phone, or PayPal email"><div class="form-text">Bank account, mobile money number, PayPal email, or other reference.</div></div>
                <button class="btn btn-primary w-100" type="submit">Create account</button>
            <?php else: ?>
                <p class="subtle mb-0">You can review staff accounts but do not have permission to change them.</p>
            <?php endif; ?>
        </form>
    </div>
    <div class="col-12 col-xl-8">
        <section class="content-card mt-0">
            <div class="card-heading"><h3>Team access <span class="subtle">(<?= count($users) ?>)</span></h3></div>
            <div class="table-responsive"><table class="table"><thead><tr><th>Staff member</th><th>Role</th><th>Payment method</th><th>Last sign in</th><th>Status</th><th></th></tr></thead><tbody>
            <?php foreach ($users as $account): ?>
                <tr>
                    <td><span class="patient-name"><?= e($account['full_name']) ?></span><br><span class="subtle"><?= e($account['email']) ?></span></td>
                    <td><?= e($account['role_name']) ?></td>
                    <td><?php if ($account['payment_method']): ?><span class="badge-neutral"><?= e(ucwords(str_replace('_', ' ', $account['payment_method']))) ?></span><?php if ($account['payment_details']): ?><br><span class="subtle"><?= e($account['payment_details']) ?></span><?php endif; ?><?php else: ?><span class="subtle">—</span><?php endif; ?></td>
                    <td><?= $account['last_login_at'] ? e(date('M j, Y · g:i a', strtotime($account['last_login_at']))) : 'Never' ?></td>
                    <td><span class="<?= (int) $account['is_active'] === 1 ? 'badge-soft' : 'badge-neutral' ?>"><?= (int) $account['is_active'] === 1 ? 'Active' : 'Inactive' ?></span></td>
                    <td>
                        <?php if ($canManageUsers && (int) $account['id'] !== (int) current_user()['id']): ?>
                            <form method="post" action="/hospital%20management/admin/users.php">
                                <?= csrf_field() ?><input type="hidden" name="action" value="toggle"><input type="hidden" name="user_id" value="<?= (int) $account['id'] ?>">
                                <button class="btn btn-sm btn-outline-secondary" type="submit"><?= (int) $account['is_active'] === 1 ? 'Deactivate' : 'Activate' ?></button>
                            </form>
                        <?php else: ?><span class="subtle">You</span><?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody></table></div>
        </section>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>

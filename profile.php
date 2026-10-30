<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_login();

$userId = (int) current_user()['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'details') {
        $name  = trim((string) ($_POST['full_name'] ?? ''));
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));

        if ($name === '' || strlen($name) > 160 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) {
            set_flash('danger', 'Enter a valid name and email address.');
            redirect('/hospital%20management/profile.php');
        }

        try {
            $pdo->prepare('UPDATE users SET full_name = :name, email = :email WHERE id = :id')
                ->execute(['name' => $name, 'email' => $email, 'id' => $userId]);
            $_SESSION['user']['full_name'] = $name;
            record_audit('update', 'user', (string) $userId, 'Profile details updated');
            set_flash('success', 'Profile updated.');
        } catch (PDOException $e) {
            set_flash('danger', $e->getCode() === '23000' ? 'That email is already in use.' : 'Could not update profile.');
        }
        redirect('/hospital%20management/profile.php');
    }

    if ($action === 'password') {
        $current = (string) ($_POST['current_password'] ?? '');
        $new     = (string) ($_POST['new_password'] ?? '');
        $confirm = (string) ($_POST['confirm_password'] ?? '');

        if (strlen($new) < 12 || $new !== $confirm) {
            set_flash('danger', 'New password must be at least 12 characters and both fields must match.');
            redirect('/hospital%20management/profile.php');
        }

        $row = $pdo->prepare('SELECT password_hash FROM users WHERE id = :id');
        $row->execute(['id' => $userId]);
        $hash = $row->fetchColumn();

        if (!$hash || !password_verify($current, $hash)) {
            set_flash('danger', 'Current password is incorrect.');
            redirect('/hospital%20management/profile.php');
        }

        $pdo->prepare('UPDATE users SET password_hash = :hash WHERE id = :id')
            ->execute(['hash' => password_hash($new, PASSWORD_DEFAULT), 'id' => $userId]);
        record_audit('password_change', 'user', (string) $userId, 'User changed own password');
        set_flash('success', 'Password changed.');
        redirect('/hospital%20management/profile.php');
    }

    http_response_code(400);
    exit('Unknown action.');
}

$me = $pdo->prepare('SELECT full_name, email, last_login_at FROM users WHERE id = :id');
$me->execute(['id' => $userId]);
$me = $me->fetch();

$pageTitle = 'My profile';
require __DIR__ . '/includes/header.php';
?>
<div class="page-heading">
    <div><h2>My profile</h2><p>Update your display name, email address, or password.</p></div>
</div>
<div class="row g-3">
    <div class="col-12 col-md-6">
        <section class="content-card mt-0">
            <div class="card-heading"><h3>Account details</h3></div>
            <form method="post" class="form-card">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="details">
                <div class="mb-3">
                    <label class="form-label" for="full_name">Full name <span class="text-danger">*</span></label>
                    <input class="form-control" id="full_name" name="full_name" maxlength="160" required value="<?= e($me['full_name']) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="email">Email address <span class="text-danger">*</span></label>
                    <input class="form-control" id="email" name="email" type="email" maxlength="190" required value="<?= e($me['email']) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Role</label>
                    <input class="form-control" type="text" disabled value="<?= e(current_user()['role_name'] ?? '') ?>">
                </div>
                <?php if ($me['last_login_at']): ?>
                    <p class="subtle mb-3">Last sign in: <?= e(date('M j, Y · g:i a', strtotime($me['last_login_at']))) ?></p>
                <?php endif; ?>
                <button class="btn btn-primary" type="submit">Save details</button>
            </form>
        </section>
    </div>
    <div class="col-12 col-md-6">
        <section class="content-card mt-0">
            <div class="card-heading"><h3>Change password</h3></div>
            <form method="post" class="form-card">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="password">
                <div class="mb-3">
                    <label class="form-label" for="current_password">Current password <span class="text-danger">*</span></label>
                    <input class="form-control" id="current_password" name="current_password" type="password" autocomplete="current-password" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="new_password">New password <span class="text-danger">*</span></label>
                    <input class="form-control" id="new_password" name="new_password" type="password" minlength="12" autocomplete="new-password" required>
                    <div class="form-text">At least 12 characters.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="confirm_password">Confirm new password <span class="text-danger">*</span></label>
                    <input class="form-control" id="confirm_password" name="confirm_password" type="password" minlength="12" autocomplete="new-password" required>
                </div>
                <button class="btn btn-primary" type="submit">Change password</button>
            </form>
        </section>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>

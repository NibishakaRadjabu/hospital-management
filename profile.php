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

    if ($action === 'payment') {
        $method  = trim((string) ($_POST['payment_method'] ?? ''));
        $details = trim((string) ($_POST['payment_details'] ?? ''));
        $allowed = ['', 'bank_transfer', 'mobile_money', 'paypal', 'other'];
        if (!in_array($method, $allowed, true) || strlen($details) > 255) {
            set_flash('danger', 'Check the payment method and details.');
            redirect('/hospital%20management/profile.php');
        }
        $pdo->prepare('UPDATE users SET payment_method = :method, payment_details = :details WHERE id = :id')
            ->execute(['method' => $method ?: null, 'details' => $details ?: null, 'id' => $userId]);
        record_audit('update', 'user', (string) $userId, 'Payment method updated');
        set_flash('success', 'Payment method saved.');
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

    if ($action === 'delete') {
        $current = (string) ($_POST['current_password'] ?? '');
        $row = $pdo->prepare('SELECT password_hash FROM users WHERE id = :id');
        $row->execute(['id' => $userId]);
        $hash = $row->fetchColumn();
        if (!$hash || !password_verify($current, $hash)) {
            set_flash('danger', 'Current password is incorrect.');
            redirect('/hospital%20management/profile.php');
        }
        $roleKey = current_user()['role_key'] ?? '';
        if (in_array($roleKey, ['super_admin', 'admin'], true)) {
            $count = (int) $pdo->query("SELECT COUNT(*) FROM users u JOIN roles r ON r.id=u.role_id WHERE r.role_key='{$roleKey}' AND u.is_active=1")->fetchColumn();
            if ($count <= 1) {
                set_flash('danger', 'You are the last active administrator. Transfer the role before deleting your account.');
                redirect('/hospital%20management/profile.php');
            }
        }
        $pdo->prepare('DELETE FROM users WHERE id = :id')->execute(['id' => $userId]);
        record_audit('delete', 'user', (string) $userId, 'User deleted own account');
        $_SESSION = [];
        session_regenerate_id(true);
        redirect('/hospital%20management/login.php');
    }

    http_response_code(400);
    exit('Unknown action.');
}

$me = $pdo->prepare('SELECT full_name, email, last_login_at, payment_method, payment_details FROM users WHERE id = :id');
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
<div class="row g-3 mt-1">
    <div class="col-12 col-md-6">
        <section class="content-card mt-0">
            <div class="card-heading"><h3>Payment method</h3></div>
            <form method="post" class="form-card">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="payment">
                <div class="mb-3">
                    <label class="form-label" for="payment_method">Payment method</label>
                    <select class="form-select" id="payment_method" name="payment_method">
                        <option value="" <?= !$me['payment_method'] ? 'selected' : '' ?>>Not specified</option>
                        <option value="bank_transfer" <?= $me['payment_method'] === 'bank_transfer' ? 'selected' : '' ?>>Bank transfer</option>
                        <option value="mobile_money" <?= $me['payment_method'] === 'mobile_money' ? 'selected' : '' ?>>Mobile money</option>
                        <option value="paypal" <?= $me['payment_method'] === 'paypal' ? 'selected' : '' ?>>PayPal</option>
                        <option value="other" <?= $me['payment_method'] === 'other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="payment_details">Payment details</label>
                    <input class="form-control" id="payment_details" name="payment_details" maxlength="255"
                           placeholder="Account number, phone, or PayPal email"
                           value="<?= e($me['payment_details'] ?? '') ?>">
                    <div class="form-text">Bank account number, mobile money number, PayPal email, or other reference used to pay you.</div>
                </div>
                <button class="btn btn-primary" type="submit">Save payment method</button>
            </form>
        </section>
    </div>
</div>
<div class="row g-3 mt-1">
    <div class="col-12 col-md-6">
        <section class="content-card mt-0">
            <div class="card-heading"><h3>Delete account</h3></div>
            <form method="post" class="form-card" onsubmit="return confirm('Permanently delete your account? This cannot be undone.')">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete">
                <div class="mb-3">
                    <label class="form-label" for="delete_password">Confirm your password <span class="text-danger">*</span></label>
                    <input class="form-control" id="delete_password" name="current_password" type="password" autocomplete="current-password" required>
                </div>
                <button class="btn btn-danger" type="submit">Delete my account</button>
            </form>
        </section>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>

<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

if ((int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn() > 0) {
    http_response_code(403);
    exit('Setup is disabled because an account already exists.');
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim((string) ($_POST['full_name'] ?? ''));
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 12) {
        $error = 'Enter a name, a valid email address, and a password of at least 12 characters.';
    } else {
        $pdo->beginTransaction();
        try {
            $roleId = $pdo->query("SELECT id FROM roles WHERE role_key = 'super_admin'")->fetchColumn();
            if ($roleId === false) {
                throw new RuntimeException('The Super Admin role is missing. Import database/schema.sql first.');
            }
            $insert = $pdo->prepare('INSERT INTO users (role_id, full_name, email, password_hash) VALUES (:role_id, :name, :email, :password_hash)');
            $insert->execute([
                'role_id' => $roleId,
                'name' => $name,
                'email' => $email,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            ]);
            $userId = (int) $pdo->lastInsertId();
            $pdo->exec("INSERT INTO departments (name, description) VALUES ('General Medicine', 'General outpatient services') ON DUPLICATE KEY UPDATE name = VALUES(name)");
            $pdo->exec("INSERT INTO wards (name, ward_type) VALUES ('General Ward', 'General') ON DUPLICATE KEY UPDATE name = VALUES(name)");
            $wardId = (int) $pdo->query("SELECT id FROM wards WHERE name = 'General Ward'")->fetchColumn();
            $bedCountQuery = $pdo->prepare('SELECT COUNT(*) FROM beds WHERE ward_id = :ward_id');
            $bedCountQuery->execute(['ward_id' => $wardId]);
            if ((int) $bedCountQuery->fetchColumn() === 0) {
                $addBed = $pdo->prepare('INSERT INTO beds (ward_id, bed_number) VALUES (:ward_id, :bed_number)');
                for ($number = 1; $number <= 10; $number++) {
                    $addBed->execute(['ward_id' => $wardId, 'bed_number' => sprintf('GW-%02d', $number)]);
                }
            }
            record_audit('create', 'user', (string) $userId, 'Initial system owner account created');
            $pdo->commit();
            set_flash('success', 'Super Admin account created. You can now sign in.');
            redirect('/hospital%20management/login.php');
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            if ($exception instanceof PDOException && $exception->getCode() === '23000') {
                $error = 'That email address is already in use.';
            } else {
                error_log('Initial setup failed: ' . $exception->getMessage());
                $error = 'Setup could not be completed. Check the PHP error log and database schema.';
            }
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Initial setup | KivuCare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/hospital%20management/assets/css/app.css" rel="stylesheet">
</head>
<body class="auth-page">
<main class="auth-card">
    <?= render_language_switcher() ?>
    <a class="brand" href="/hospital%20management/"><span class="brand-mark">K</span><span>KivuCare<small>HOSPITAL SYSTEM</small></span></a>
    <h1>Create administrator</h1>
    <p class="lead">This one-time setup is available only before the first account is created.</p>
    <?php if ($error !== null): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
    <form method="post" action="/hospital%20management/setup.php">
        <?= csrf_field() ?>
        <div class="mb-3"><label class="form-label" for="full_name">Full name</label><input class="form-control" id="full_name" name="full_name" required value="<?= e((string) ($_POST['full_name'] ?? '')) ?>"></div>
        <div class="mb-3"><label class="form-label" for="email">Work email</label><input class="form-control" id="email" name="email" type="email" required value="<?= e((string) ($_POST['email'] ?? '')) ?>"></div>
        <div class="mb-4"><label class="form-label" for="password">Password</label><input class="form-control" id="password" name="password" type="password" minlength="12" autocomplete="new-password" required><div class="form-text">Use at least 12 characters.</div></div>
        <button class="btn btn-primary w-100" type="submit">Create administrator</button>
    </form>
    <div class="auth-footer"><a href="/hospital%20management/login.php">Back to sign in</a></div>
</main>
</body>
</html>

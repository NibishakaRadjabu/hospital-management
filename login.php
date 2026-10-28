<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

if (!empty($_SESSION['user'])) {
    redirect('/hospital%20management/dashboard.php');
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');

    $statement = $pdo->prepare(
        'SELECT u.id, u.full_name, u.email, u.password_hash, r.id AS role_id, r.role_key, r.name AS role_name
         FROM users u JOIN roles r ON r.id = u.role_id
         WHERE u.email = :email AND u.is_active = 1 LIMIT 1'
    );
    $statement->execute(['email' => $email]);
    $user = $statement->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        unset($user['password_hash']);
        $_SESSION['user'] = $user;
        $pdo->prepare('UPDATE users SET last_login_at = NOW() WHERE id = :id')->execute(['id' => $user['id']]);
        record_audit('login', 'user', (string) $user['id'], 'Successful sign-in');
        redirect('/hospital%20management/dashboard.php');
    }

    $error = 'The email or password you entered is incorrect.';
}

$flash = take_flash();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in | hospital management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/hospital%20management/assets/css/app.css" rel="stylesheet">
</head>
<body class="auth-page">
<main class="auth-card">
    <?= render_language_switcher() ?>
    <a class="brand" href="/hospital%20management/"><span class="brand-mark">H</span><span>Hospital Management<small>HOSPITAL SYSTEM</small></span></a>
    <h1>Welcome back</h1>
    <p class="lead">Sign in to your hospital workspace.</p>
    <?php if ($flash !== null): ?><div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div><?php endif; ?>
    <?php if ($error !== null): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
    <form method="post" action="/hospital%20management/login.php">
        <?= csrf_field() ?>
        <div class="mb-3">
            <label class="form-label" for="email">Work email</label>
            <input class="form-control" id="email" name="email" type="email" autocomplete="username" required autofocus value="<?= e((string) ($_POST['email'] ?? '')) ?>">
        </div>
        <div class="mb-4">
            <label class="form-label" for="password">Password</label>
            <input class="form-control" id="password" name="password" type="password" autocomplete="current-password" required>
        </div>
        <button class="btn btn-primary w-100" type="submit">Sign in</button>
    </form>
    <div class="auth-footer">First time setting up? <a href="/hospital%20management/setup.php">Create the administrator account</a></div>
</main>
</body>
</html>

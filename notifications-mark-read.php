<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_login();
verify_csrf();

$uid    = (int) current_user()['id'];
$filter = trim((string) ($_POST['action_filter'] ?? ''));
$redirect = trim((string) ($_POST['redirect'] ?? ''));

// Whitelist redirect to internal paths only
$allowed = '/hospital%20management/';
if ($redirect === '' || !str_starts_with($redirect, $allowed)) {
    $redirect = '/hospital%20management/dashboard.php';
}

if ($filter !== '') {
    $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = :uid AND action = :action AND is_read = 0')
        ->execute(['uid' => $uid, 'action' => $filter]);
} else {
    $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = :uid AND is_read = 0')
        ->execute(['uid' => $uid]);
}

redirect($redirect);

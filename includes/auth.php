<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

function require_login(): void
{
    if (empty($_SESSION['user'])) {
        set_flash('warning', 'Please sign in to continue.');
        redirect('/hospital%20management/login.php');
    }

    global $pdo;
    $statement = $pdo->prepare(
        'SELECT u.is_active, r.id AS role_id, r.role_key, r.name AS role_name
         FROM users u JOIN roles r ON r.id = u.role_id WHERE u.id = :id'
    );
    $statement->execute(['id' => $_SESSION['user']['id']]);
    $account = $statement->fetch();
    if (!$account || (int) $account['is_active'] !== 1) {
        $_SESSION = [];
        session_regenerate_id(true);
        set_flash('warning', 'Your account is inactive or no longer available.');
        redirect('/hospital%20management/login.php');
    }

    $_SESSION['user']['role_id'] = (int) $account['role_id'];
    $_SESSION['user']['role_key'] = $account['role_key'];
    $_SESSION['user']['role_name'] = $account['role_name'];
}

function require_roles(array $roles): void
{
    require_login();

    if (!in_array($_SESSION['user']['role_key'] ?? '', $roles, true)) {
        http_response_code(403);
        exit('You do not have permission to access this page.');
    }
}

function has_permission(string $permissionKey): bool
{
    static $permissionsByRole = [];
    $roleId = (int) (current_user()['role_id'] ?? 0);
    if ($roleId < 1) {
        return false;
    }

    if (!isset($permissionsByRole[$roleId])) {
        global $pdo;
        $statement = $pdo->prepare(
            'SELECT p.permission_key
             FROM role_permissions rp
             JOIN permissions p ON p.id = rp.permission_id
             WHERE rp.role_id = :role_id'
        );
        $statement->execute(['role_id' => $roleId]);
        $permissionsByRole[$roleId] = array_fill_keys(
            array_column($statement->fetchAll(), 'permission_key'),
            true
        );
    }

    return isset($permissionsByRole[$roleId][$permissionKey]);
}

function require_permission(string $permissionKey): void
{
    require_login();

    if (!has_permission($permissionKey)) {
        http_response_code(403);
        exit('You do not have permission to access this page.');
    }
}

function current_user(): array
{
    return $_SESSION['user'] ?? [];
}

<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

if (!empty($_SESSION['user'])) {
    redirect('/hospital%20management/dashboard.php');
}

redirect('/hospital%20management/login.php');

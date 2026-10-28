<?php
$pageTitle = $pageTitle ?? 'Dashboard';
$flash = take_flash();
$user = current_user();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> | KivuCare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/hospital%20management/assets/css/app.css" rel="stylesheet">
</head>
<body>
<div class="app-shell">
    <button class="mobile-menu-toggle" type="button" aria-controls="workspace-menu" aria-expanded="false">
        <span aria-hidden="true">&#9776;</span><span>Menu</span>
    </button>
    <aside class="sidebar" id="workspace-menu" aria-label="Workspace navigation">
        <a class="brand" href="/hospital%20management/dashboard.php"><span class="brand-mark">K</span><span>KivuCare<small>HOSPITAL SYSTEM</small></span></a>
        <div class="nav-label">WORKSPACE</div>
        <nav class="nav flex-column">
            <a class="nav-link" href="/hospital%20management/dashboard.php"><span>▦</span> Dashboard</a>
            <?php if (has_permission('work_logs.view_own')): ?>
                <a class="nav-link" href="/hospital%20management/work-log/index.php"><span>✓</span> My work log</a>
            <?php endif; ?>
            <?php if (has_permission('patient_services.view_own') || has_permission('patient_services.view_all')): ?>
                <a class="nav-link" href="/hospital%20management/patient-services/index.php"><span>✚</span> Patient services</a>
            <?php endif; ?>
            <?php if (has_permission('patients.view')): ?>
                <a class="nav-link" href="/hospital%20management/patient/index.php"><span>♙</span> Patients</a>
            <?php endif; ?>
            <?php if (current_user()['role_key'] === 'patient_portal' && (has_permission('appointments.self_book') || has_permission('prescriptions.own_view') || has_permission('lab.results.own_view') || has_permission('radiology.results.own_view') || has_permission('invoices.own_view'))): ?>
                <a class="nav-link" href="/hospital%20management/portal/index.php"><span>Me</span> Patient portal</a>
            <?php endif; ?>
            <?php if (has_permission('admissions.view')): ?>
                <a class="nav-link" href="/hospital%20management/admission/index.php"><span>▤</span> Admissions</a>
            <?php endif; ?>
            <?php if (has_permission('triage.view')): ?>
                <a class="nav-link" href="/hospital%20management/emergency/index.php"><span>!</span> Emergency triage</a>
            <?php endif; ?>
            <?php if (has_permission('appointments.view')): ?>
                <a class="nav-link" href="/hospital%20management/appointments/index.php"><span>A</span> Appointments</a>
            <?php endif; ?>
            <?php if (has_permission('visits.view')): ?>
                <a class="nav-link" href="/hospital%20management/consultations/index.php"><span>C</span> Consultations</a>
            <?php endif; ?>
            <?php if (has_permission('lab.requests.view') || has_permission('lab.results.view')): ?>
                <a class="nav-link" href="/hospital%20management/laboratory/index.php"><span>L</span> Laboratory</a>
            <?php endif; ?>
            <?php if (has_permission('radiology.requests.view') || has_permission('radiology.results.manage')): ?>
                <a class="nav-link" href="/hospital%20management/radiology/index.php"><span>R</span> Radiology</a>
            <?php endif; ?>
            <?php if (has_permission('prescriptions.create') || has_permission('prescriptions.view') || has_permission('pharmacy.dispense')): ?>
                <a class="nav-link" href="/hospital%20management/pharmacy/index.php"><span>P</span> Pharmacy</a>
            <?php endif; ?>
            <?php if (has_permission('nursing_notes.manage')): ?>
                <a class="nav-link" href="/hospital%20management/nursing/index.php"><span>N</span> Nursing / Wards</a>
            <?php endif; ?>
            <?php if (has_permission('ambulance.manage') || has_permission('ambulance.requests.create') || has_permission('emergency_requests.view')): ?>
                <a class="nav-link" href="/hospital%20management/ambulance/index.php"><span>A</span> Ambulance</a>
            <?php endif; ?>
            <?php if (has_permission('billing.overview') || has_permission('billing.view') || has_permission('invoices.manage') || has_permission('payments.manage') || has_permission('financial_reports.view')): ?>
                <a class="nav-link" href="/hospital%20management/billing/index.php"><span>$</span> Billing</a>
            <?php endif; ?>
            <?php if (has_permission('reports.view') || has_permission('financial_reports.view') || has_permission('inventory.reports') || has_permission('pharmacy.reports')): ?>
                <a class="nav-link" href="/hospital%20management/reports/index.php"><span>R</span> Reports</a>
            <?php endif; ?>
            <?php if (has_permission('inventory.view')): ?>
                <a class="nav-link" href="/hospital%20management/inventory/index.php"><span>I</span> Inventory</a>
            <?php endif; ?>
            <?php if (has_permission('purchase_orders.manage') || has_permission('suppliers.manage')): ?>
                <a class="nav-link" href="/hospital%20management/inventory/purchases.php"><span>P</span> Procurement</a>
            <?php endif; ?>
            <?php if (has_permission('inventory.view')): ?>
                <a class="nav-link" href="/hospital%20management/inventory/equipment.php"><span>E</span> Equipment maintenance</a>
            <?php endif; ?>
            <?php if (has_permission('attendance.manage') || has_permission('payroll.manage') || has_permission('employees.manage')): ?>
                <a class="nav-link" href="/hospital%20management/hr/index.php"><span>H</span> Human Resources</a>
            <?php endif; ?>
            <?php if (has_permission('users.view')): ?>
                <a class="nav-link" href="/hospital%20management/admin/users.php"><span>♧</span> Staff accounts</a>
            <?php endif; ?>
            <?php if (has_permission('roles.manage')): ?>
                <a class="nav-link" href="/hospital%20management/admin/roles.php"><span>⚙</span> Roles & permissions</a>
            <?php endif; ?>
            <?php if (has_permission('departments.manage')): ?>
                <a class="nav-link" href="/hospital%20management/admin/departments.php"><span>▤</span> Departments</a>
            <?php endif; ?>
            <?php if (has_permission('audit.view')): ?>
                <a class="nav-link" href="/hospital%20management/admin/audit.php"><span>◷</span> Audit activity</a>
            <?php endif; ?>
        </nav>
        <div class="sidebar-bottom">
            <div class="sidebar-note">Care that connects.<br><span>One patient at a time.</span></div>
            <div class="sidebar-version">Hospital management · v1.0</div>
        </div>
    </aside>
    <main class="main-content">
        <header class="topbar">
            <div class="topbar-heading">
                <div>
                    <div class="eyebrow">HOSPITAL OPERATIONS</div>
                    <h1><?= e($pageTitle) ?></h1>
                </div>
            </div>
            <div class="user-menu">
                <?= render_language_switcher() ?>
                <button class="btn btn-sm btn-outline-secondary print-page no-print" type="button" onclick="window.print()">Print</button>
                <div class="avatar"><?= e(strtoupper(substr($user['full_name'] ?? 'U', 0, 1))) ?></div>
                <div class="user-detail"><strong><?= e($user['full_name'] ?? '') ?></strong><span><?= e($user['role_name'] ?? '') ?></span></div>
                <form method="post" action="/hospital%20management/logout.php" class="ms-2">
                    <?= csrf_field() ?>
                    <button class="btn btn-sm btn-outline-secondary" type="submit">Sign out</button>
                </form>
            </div>
        </header>
        <section class="page-body">
            <?php if ($flash !== null): ?>
                <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert">
                    <?= e($flash['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

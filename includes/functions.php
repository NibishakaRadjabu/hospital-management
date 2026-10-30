<?php
declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        http_response_code(419);
        exit('Your session expired. Go back, refresh the page, and try again.');
    }
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function take_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);

    return is_array($flash) ? $flash : null;
}

function format_date(?string $value): string
{
    if ($value === null || $value === '') {
        return '—';
    }

    return date('M j, Y', strtotime($value));
}

function record_audit(string $action, string $entityType, ?string $entityId = null, ?string $details = null): void
{
    global $pdo;
    $actorId = $_SESSION['user']['id'] ?? null;
    $statement = $pdo->prepare(
        'INSERT INTO audit_logs (actor_user_id, action, entity_type, entity_id, details, ip_address)
         VALUES (:actor_user_id, :action, :entity_type, :entity_id, :details, :ip_address)'
    );
    $statement->execute([
        'actor_user_id' => $actorId,
        'action' => $action,
        'entity_type' => $entityType,
        'entity_id' => $entityId,
        'details' => $details,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
    ]);
}

function work_log_categories(): array
{
    return [
        'clinical_care' => 'Clinical care',
        'patient_support' => 'Patient support',
        'administration' => 'Administration',
        'laboratory' => 'Laboratory',
        'radiology' => 'Radiology',
        'pharmacy' => 'Pharmacy',
        'billing' => 'Billing',
        'inventory' => 'Inventory',
        'maintenance' => 'Maintenance',
        'training' => 'Training',
        'other' => 'Other',
    ];
}

function get_hospital_name(): string
{
    global $pdo;
    static $name = null;
    if ($name === null) {
        try {
            $name = (string) $pdo->query(
                "SELECT setting_value FROM hospital_settings WHERE setting_key = 'hospital_name'"
            )->fetchColumn();
        } catch (\Throwable) {
            $name = 'KivuCare';
        }
        if ($name === '' || $name === false) {
            $name = 'KivuCare';
        }
    }
    return $name;
}

function get_notification_counts(): array
{
    global $pdo;
    $userId = $_SESSION['user']['id'] ?? null;
    if (!$userId) {
        return ['total' => 0, 'by_action' => []];
    }
    try {
        $stmt = $pdo->prepare(
            'SELECT action, COUNT(*) AS cnt FROM notifications
             WHERE user_id = :uid AND is_read = 0 GROUP BY action'
        );
        $stmt->execute(['uid' => $userId]);
        $rows = $stmt->fetchAll();
        $byAction = [];
        $total = 0;
        foreach ($rows as $row) {
            $byAction[$row['action']] = (int) $row['cnt'];
            $total += (int) $row['cnt'];
        }
        return ['total' => $total, 'by_action' => $byAction];
    } catch (\Throwable) {
        return ['total' => 0, 'by_action' => []];
    }
}

function notify(int $userId, string $action, string $entityType, ?string $entityId, string $message): void
{
    global $pdo;
    try {
        $pdo->prepare(
            'INSERT INTO notifications (user_id, action, entity_type, entity_id, message)
             VALUES (:uid, :action, :entity_type, :entity_id, :message)'
        )->execute([
            'uid' => $userId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'message' => $message,
        ]);
    } catch (\Throwable) {
        // non-fatal
    }
}

function notify_staff(string $action, string $entityType, ?string $entityId, string $message): void
{
    global $pdo;
    $actorId = (int) ($_SESSION['user']['id'] ?? 0);
    try {
        $stmt = $pdo->prepare(
            "SELECT u.id FROM users u
             JOIN roles r ON r.id = u.role_id
             WHERE u.is_active = 1
               AND r.role_key <> 'patient_portal'
               AND u.id <> :actor"
        );
        $stmt->execute(['actor' => $actorId]);
        $insert = $pdo->prepare(
            'INSERT INTO notifications (user_id, action, entity_type, entity_id, message)
             VALUES (:uid, :action, :entity_type, :entity_id, :message)'
        );
        foreach ($stmt->fetchAll() as $row) {
            $insert->execute([
                'uid'         => $row['id'],
                'action'      => $action,
                'entity_type' => $entityType,
                'entity_id'   => $entityId,
                'message'     => $message,
            ]);
        }
    } catch (\Throwable) {
        // non-fatal
    }
}

function patient_service_categories(): array
{
    return [
        'consultation' => 'Consultation',
        'nursing_care' => 'Nursing care',
        'medication' => 'Medication / pharmacy',
        'laboratory' => 'Laboratory',
        'radiology' => 'Radiology',
        'admission_care' => 'Admission / ward care',
        'emergency_care' => 'Emergency care',
        'billing' => 'Billing service',
        'transport' => 'Ambulance / transport',
        'other' => 'Other',
    ];
}

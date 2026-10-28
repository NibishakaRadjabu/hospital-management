<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_permission('audit.view');

$logs = $pdo->query(
    'SELECT l.action, l.entity_type, l.entity_id, l.details, l.ip_address, l.created_at, u.full_name
     FROM audit_logs l LEFT JOIN users u ON u.id = l.actor_user_id
     ORDER BY l.created_at DESC LIMIT 250'
)->fetchAll();
$pageTitle = 'Audit activity';
require __DIR__ . '/../includes/header.php';
?>
<div class="page-heading"><div><h2>Audit activity</h2><p>Recent sign-ins and sensitive record changes, newest first.</p></div></div>
<section class="content-card mt-0">
    <div class="card-heading"><h3>Latest activity <span class="subtle">(up to 250 events)</span></h3></div>
    <div class="table-responsive"><table class="table"><thead><tr><th>When</th><th>Actor</th><th>Action</th><th>Record</th><th>Details</th><th>IP address</th></tr></thead><tbody>
        <?php if ($logs === []): ?><tr><td class="empty-state" colspan="6">No audit activity has been recorded yet.</td></tr><?php else: foreach ($logs as $log): ?>
            <tr><td><?= e(date('M j, Y · g:i:s a', strtotime($log['created_at']))) ?></td><td><?= e($log['full_name'] ?: 'Deleted account') ?></td><td><?= e($log['action']) ?></td><td><?= e($log['entity_type'] . ($log['entity_id'] ? ' · ' . $log['entity_id'] : '')) ?></td><td><?= e($log['details'] ?: '—') ?></td><td><?= e($log['ip_address'] ?: '—') ?></td></tr>
        <?php endforeach; endif; ?>
    </tbody></table></div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>

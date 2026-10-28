<?php if ($canViewDetail): ?>
<section class="content-card no-print">
    <div class="card-heading"><h3>Print invoices and receipts</h3></div>
    <div class="table-responsive"><table class="table">
        <thead><tr><th>Document</th><th>Patient</th><th>Amount</th><th>Action</th></tr></thead>
        <tbody>
        <?php foreach ($invoices as $invoice): ?>
            <tr>
                <td><?= e($invoice['invoice_no']) ?> · Invoice</td>
                <td><?= e($invoice['first_name'] . ' ' . $invoice['last_name']) ?> <span class="subtle"><?= e($invoice['patient_no']) ?></span></td>
                <td><?= number_format((float) $invoice['total_amount'], 2) ?></td>
                <td><button class="btn btn-sm btn-outline-secondary" type="button" data-print-document="invoice-print-<?= (int) $invoice['id'] ?>">Print invoice</button></td>
            </tr>
            <?php if (has_permission('receipts.issue')):
                $receiptQuery = $pdo->prepare('SELECT id, amount, method, reference, paid_at FROM payments WHERE invoice_id = :id ORDER BY paid_at DESC');
                $receiptQuery->execute(['id' => $invoice['id']]);
                foreach ($receiptQuery->fetchAll() as $receipt): ?>
                    <tr>
                        <td>Receipt REC-<?= (int) $receipt['id'] ?> · <?= e($invoice['invoice_no']) ?></td>
                        <td><?= e($invoice['first_name'] . ' ' . $invoice['last_name']) ?> <span class="subtle"><?= e($invoice['patient_no']) ?></span></td>
                        <td><?= number_format((float) $receipt['amount'], 2) ?></td>
                        <td><button class="btn btn-sm btn-outline-secondary" type="button" data-print-document="receipt-print-<?= (int) $receipt['id'] ?>">Print receipt</button></td>
                    </tr>
                <?php endforeach; endif; ?>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</section>

<?php foreach ($invoices as $invoice): $balance = max(0, (float) $invoice['total_amount'] - (float) $invoice['paid']); ?>
    <section id="invoice-print-<?= (int) $invoice['id'] ?>" class="print-document" hidden>
        <div class="print-document-header"><div><strong>KivuCare</strong><div>Hospital management</div></div><h2>Invoice</h2></div>
        <dl class="print-document-meta">
            <div><dt>Invoice number</dt><dd><?= e($invoice['invoice_no']) ?></dd></div>
            <div><dt>Invoice date</dt><dd><?= e(format_date($invoice['created_at'])) ?></dd></div>
            <div><dt>Patient</dt><dd><?= e($invoice['first_name'] . ' ' . $invoice['last_name']) ?></dd></div>
            <div><dt>Patient number</dt><dd><?= e($invoice['patient_no']) ?></dd></div>
            <div><dt>Status</dt><dd><?= e(ucfirst($invoice['status'])) ?></dd></div>
        </dl>
        <div class="print-document-total">Invoice total: <?= number_format((float) $invoice['total_amount'], 2) ?></div>
        <dl class="print-document-meta">
            <div><dt>Paid</dt><dd><?= number_format((float) $invoice['paid'], 2) ?></dd></div>
            <div><dt>Balance</dt><dd><?= number_format($balance, 2) ?></dd></div>
        </dl>
    </section>
    <?php if (has_permission('receipts.issue')):
        $receiptQuery = $pdo->prepare('SELECT id, amount, method, reference, paid_at FROM payments WHERE invoice_id = :id ORDER BY paid_at DESC');
        $receiptQuery->execute(['id' => $invoice['id']]);
        foreach ($receiptQuery->fetchAll() as $receipt): ?>
            <section id="receipt-print-<?= (int) $receipt['id'] ?>" class="print-document" hidden>
                <div class="print-document-header"><div><strong>KivuCare</strong><div>Hospital management</div></div><h2>Payment receipt</h2></div>
                <dl class="print-document-meta">
                    <div><dt>Receipt number</dt><dd>REC-<?= (int) $receipt['id'] ?></dd></div>
                    <div><dt>Date paid</dt><dd><?= e(date('M j, Y g:i a', strtotime($receipt['paid_at']))) ?></dd></div>
                    <div><dt>Patient</dt><dd><?= e($invoice['first_name'] . ' ' . $invoice['last_name']) ?></dd></div>
                    <div><dt>Patient number</dt><dd><?= e($invoice['patient_no']) ?></dd></div>
                    <div><dt>Invoice number</dt><dd><?= e($invoice['invoice_no']) ?></dd></div>
                    <div><dt>Payment method</dt><dd><?= e(ucwords(str_replace('_', ' ', $receipt['method']))) ?></dd></div>
                    <?php if ($receipt['reference']): ?><div><dt>Reference</dt><dd><?= e($receipt['reference']) ?></dd></div><?php endif; ?>
                </dl>
                <div class="print-document-total">Amount received: <?= number_format((float) $receipt['amount'], 2) ?></div>
            </section>
        <?php endforeach; endif; ?>
<?php endforeach; ?>
<?php endif; ?>

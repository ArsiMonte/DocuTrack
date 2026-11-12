<?php
// admin/dashboard.php - Registrar Administration Dashboard
$pageTitle = "Admin Dashboard";
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin();
$user = currentUser();

// Summary stats
$stats = $pdo->query("
    SELECT 
        COUNT(*) AS total,
        SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) AS pending,
        SUM(CASE WHEN status = 'Payment Verification' THEN 1 ELSE 0 END) AS verification,
        SUM(CASE WHEN status = 'Processing' THEN 1 ELSE 0 END) AS processing,
        SUM(CASE WHEN status = 'Ready for Pickup' THEN 1 ELSE 0 END) AS ready,
        SUM(CASE WHEN status = 'Released' THEN 1 ELSE 0 END) AS released
    FROM document_requests
")->fetch();

// Recent 10 requests
$recentStmt = $pdo->query("
    SELECT r.*, d.name AS document_name, u.full_name AS student_name, u.student_id
    FROM document_requests r
    JOIN document_types d ON r.document_type_id = d.id
    JOIN users u ON r.user_id = u.id
    ORDER BY r.created_at DESC
    LIMIT 10
");
$recentRequests = $recentStmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.75rem; color: var(--dark); font-weight: 700;">Registrar Admin Dashboard</h1>
        <p style="color: var(--muted); font-size: 0.9rem;">Overview of student document requests, verification queue, and releases.</p>
    </div>
    <div style="display: flex; gap: 0.5rem;">
        <a href="/DocuTrack/admin/requests.php" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-file-invoice"></i> Manage All Requests
        </a>
        <a href="/DocuTrack/admin/documents.php" class="btn btn-secondary btn-sm">
            <i class="fa-solid fa-folder-gear"></i> Document Catalog
        </a>
    </div>
</div>

<!-- Metrics Overview -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
    <div class="card" style="padding: 1.25rem;">
        <div style="font-size: 0.8rem; color: var(--muted); font-weight: 600; text-transform: uppercase;">Total Requests</div>
        <div style="font-size: 1.85rem; font-weight: 700; color: var(--dark);"><?= (int)($stats['total'] ?? 0) ?></div>
    </div>
    <div class="card" style="padding: 1.25rem; border-left: 4px solid #f59e0b;">
        <div style="font-size: 0.8rem; color: #b45309; font-weight: 600; text-transform: uppercase;">Pending</div>
        <div style="font-size: 1.85rem; font-weight: 700; color: #b45309;"><?= (int)($stats['pending'] ?? 0) ?></div>
    </div>
    <div class="card" style="padding: 1.25rem; border-left: 4px solid #6366f1;">
        <div style="font-size: 0.8rem; color: #4338ca; font-weight: 600; text-transform: uppercase;">Verification</div>
        <div style="font-size: 1.85rem; font-weight: 700; color: #4338ca;"><?= (int)($stats['verification'] ?? 0) ?></div>
    </div>
    <div class="card" style="padding: 1.25rem; border-left: 4px solid var(--info);">
        <div style="font-size: 0.8rem; color: var(--info); font-weight: 600; text-transform: uppercase;">Processing</div>
        <div style="font-size: 1.85rem; font-weight: 700; color: var(--info);"><?= (int)($stats['processing'] ?? 0) ?></div>
    </div>
    <div class="card" style="padding: 1.25rem; border-left: 4px solid var(--success);">
        <div style="font-size: 0.8rem; color: var(--success); font-weight: 600; text-transform: uppercase;">Ready for Pickup</div>
        <div style="font-size: 1.85rem; font-weight: 700; color: var(--success);"><?= (int)($stats['ready'] ?? 0) ?></div>
    </div>
    <div class="card" style="padding: 1.25rem; border-left: 4px solid var(--muted);">
        <div style="font-size: 0.8rem; color: var(--muted); font-weight: 600; text-transform: uppercase;">Released</div>
        <div style="font-size: 1.85rem; font-weight: 700; color: var(--muted);"><?= (int)($stats['released'] ?? 0) ?></div>
    </div>
</div>

<!-- Recent Requests -->
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
        <h3 class="card-title" style="margin-bottom: 0;"><i class="fa-solid fa-clock-rotate-left"></i> Recent Submissions</h3>
        <a href="/DocuTrack/admin/requests.php" style="font-size: 0.875rem; color: var(--primary); font-weight: 600; text-decoration: none;">View All &rarr;</a>
    </div>

    <?php if (empty($recentRequests)): ?>
        <p style="color: var(--muted); font-size: 0.9rem;">No requests logged in the system yet.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Tracking Reference</th>
                        <th>Student</th>
                        <th>Document</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentRequests as $r): ?>
                        <?php
                            $statusBadge = 'badge-pending';
                            if ($r['status'] === 'Payment Verification') $statusBadge = 'badge-verification';
                            if ($r['status'] === 'Processing') $statusBadge = 'badge-processing';
                            if ($r['status'] === 'Ready for Pickup') $statusBadge = 'badge-ready';
                            if ($r['status'] === 'Released') $statusBadge = 'badge-released';
                            if ($r['status'] === 'Rejected') $statusBadge = 'badge-rejected';
                        ?>
                        <tr>
                            <td>
                                <strong style="color: var(--primary); font-family: monospace;"><?= htmlspecialchars($r['tracking_code']) ?></strong>
                            </td>
                            <td>
                                <div><strong><?= htmlspecialchars($r['student_name']) ?></strong></div>
                                <div style="font-size: 0.78rem; color: var(--muted);"><?= htmlspecialchars($r['student_id'] ?? '') ?></div>
                            </td>
                            <td><?= htmlspecialchars($r['document_name']) ?> (<?= $r['copies'] ?>)</td>
                            <td>₱<?= number_format($r['total_amount'], 2) ?></td>
                            <td><span class="badge <?= $statusBadge ?>"><?= htmlspecialchars($r['status']) ?></span></td>
                            <td><?= date('M d, Y', strtotime($r['created_at'])) ?></td>
                            <td>
                                <a href="/DocuTrack/admin/requests.php?edit=<?= $r['id'] ?>" class="btn btn-sm btn-primary">
                                    <i class="fa-solid fa-pen-to-square"></i> Review
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

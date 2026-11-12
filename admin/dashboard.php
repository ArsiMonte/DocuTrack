<?php
// admin/dashboard.php — Registrar Administration Dashboard
$pageTitle = "Registrar Dashboard";
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

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 2px; color: var(--gold-dark); font-weight: 700;">Office of the Registrar</span>
        <h1 style="font-family: var(--font-serif); font-size: 2.1rem; color: var(--burgundy-dark); font-weight: 700; margin-top: 0.15rem;">
            Administrative Overview
        </h1>
        <p style="color: var(--muted); font-size: 0.925rem;">
            Real-time queue monitoring, verification backlog, and credential release management.
        </p>
    </div>
    <div style="display: flex; gap: 0.65rem;">
        <a href="/DocuTrack/admin/requests.php" class="btn btn-gold btn-sm">
            <i class="fa-solid fa-list-check"></i> Manage All Requests
        </a>
        <a href="/DocuTrack/admin/documents.php" class="btn btn-secondary btn-sm">
            <i class="fa-solid fa-layer-group"></i> Document Offerings
        </a>
    </div>
</div>

<!-- Metrics Overview Cards with Luxury Trims -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1.25rem; margin-bottom: 2.25rem;">
    <div class="card" style="padding: 1.4rem; border-top: 3.5px solid var(--burgundy-dark);">
        <div style="font-size: 0.72rem; color: var(--gold-dark); font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">Total Requests</div>
        <div style="font-family: var(--font-display); font-size: 2.2rem; font-weight: 700; color: var(--burgundy-dark);"><?= (int)($stats['total'] ?? 0) ?></div>
    </div>
    <div class="card" style="padding: 1.4rem; border-top: 3.5px solid var(--gold-bright);">
        <div style="font-size: 0.72rem; color: #92690a; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">Pending</div>
        <div style="font-family: var(--font-display); font-size: 2.2rem; font-weight: 700; color: #92690a;"><?= (int)($stats['pending'] ?? 0) ?></div>
    </div>
    <div class="card" style="padding: 1.4rem; border-top: 3.5px solid #7b2c5e;">
        <div style="font-size: 0.72rem; color: #7b2c5e; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">Verification</div>
        <div style="font-family: var(--font-display); font-size: 2.2rem; font-weight: 700; color: #7b2c5e;"><?= (int)($stats['verification'] ?? 0) ?></div>
    </div>
    <div class="card" style="padding: 1.4rem; border-top: 3.5px solid var(--burgundy-primary);">
        <div style="font-size: 0.72rem; color: var(--burgundy-rich); font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">Processing</div>
        <div style="font-family: var(--font-display); font-size: 2.2rem; font-weight: 700; color: var(--burgundy-rich);"><?= (int)($stats['processing'] ?? 0) ?></div>
    </div>
    <div class="card" style="padding: 1.4rem; border-top: 3.5px solid #166534;">
        <div style="font-size: 0.72rem; color: #166534; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">Ready for Pickup</div>
        <div style="font-family: var(--font-display); font-size: 2.2rem; font-weight: 700; color: #166534;"><?= (int)($stats['ready'] ?? 0) ?></div>
    </div>
    <div class="card" style="padding: 1.4rem; border-top: 3.5px solid #4b5563;">
        <div style="font-size: 0.72rem; color: #4b5563; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">Released</div>
        <div style="font-family: var(--font-display); font-size: 2.2rem; font-weight: 700; color: #4b5563;"><?= (int)($stats['released'] ?? 0) ?></div>
    </div>
</div>

<!-- Recent Submissions Table -->
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.35rem;">
        <h3 class="card-title" style="margin-bottom: 0;">
            <i class="fa-solid fa-clock-rotate-left"></i> Recent Applications Queue
        </h3>
        <a href="/DocuTrack/admin/requests.php" style="font-size: 0.88rem; color: var(--burgundy-primary); font-weight: 700; text-decoration: none;">
            View All Requests &rarr;
        </a>
    </div>

    <?php if (empty($recentRequests)): ?>
        <p style="color: var(--muted); font-size: 0.9rem; text-align: center; padding: 2.5rem 0;">No requests recorded in the system.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Tracking Reference</th>
                        <th>Student Name & ID</th>
                        <th>Document</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Filing Date</th>
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
                                <strong style="color: var(--burgundy-primary); font-family: monospace; letter-spacing: 0.5px;"><?= htmlspecialchars($r['tracking_code']) ?></strong>
                            </td>
                            <td>
                                <div style="font-weight: 700; color: var(--dark);"><?= htmlspecialchars($r['student_name']) ?></div>
                                <div style="font-size: 0.78rem; color: var(--muted);"><?= htmlspecialchars($r['student_id'] ?? 'N/A') ?></div>
                            </td>
                            <td>
                                <strong style="font-family: var(--font-serif);"><?= htmlspecialchars($r['document_name']) ?></strong>
                                <span style="font-size: 0.8rem; color: var(--muted);">(<?= $r['copies'] ?>x)</span>
                            </td>
                            <td><strong>₱<?= number_format($r['total_amount'], 2) ?></strong></td>
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

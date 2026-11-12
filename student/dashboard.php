<?php
// student/dashboard.php — Student Portal
$pageTitle = "Student Portal";
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireStudent();
$user = currentUser();

// Summary metrics
$statsStmt = $pdo->prepare("
    SELECT 
        COUNT(*) AS total_count,
        SUM(CASE WHEN status IN ('Pending', 'Payment Verification', 'Processing') THEN 1 ELSE 0 END) AS active_count,
        SUM(CASE WHEN status = 'Ready for Pickup' THEN 1 ELSE 0 END) AS ready_count,
        SUM(CASE WHEN status = 'Released' THEN 1 ELSE 0 END) AS released_count
    FROM document_requests
    WHERE user_id = ?
");
$statsStmt->execute([$user['id']]);
$stats = $statsStmt->fetch();

// Fetch requests
$reqStmt = $pdo->prepare("
    SELECT r.*, d.name AS document_name, d.processing_days
    FROM document_requests r
    JOIN document_types d ON r.document_type_id = d.id
    WHERE r.user_id = ?
    ORDER BY r.created_at DESC
");
$reqStmt->execute([$user['id']]);
$requests = $reqStmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 2px; color: var(--gold-dark); font-weight: 700;">Student Services</span>
        <h1 style="font-family: var(--font-serif); font-size: 2rem; color: var(--burgundy-dark); font-weight: 700; margin-top: 0.15rem;">
            Academic Records Portal
        </h1>
        <p style="color: var(--muted); font-size: 0.925rem;">
            Signed in as: <strong><?= htmlspecialchars($user['name']) ?></strong> (<?= htmlspecialchars($user['student_id'] ?? 'Student') ?>)
        </p>
    </div>
    <div>
        <a href="/DocuTrack/student/new_request.php" class="btn btn-gold" style="padding: 0.75rem 1.6rem;">
            <i class="fa-solid fa-file-circle-plus"></i> Request New Document
        </a>
    </div>
</div>

<!-- Stat Cards with Gold & Burgundy Accents -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 1.25rem; margin-bottom: 2.25rem;">
    <div class="card" style="padding: 1.5rem; border-top: 3.5px solid var(--burgundy-primary);">
        <span style="font-size: 0.75rem; color: var(--gold-dark); font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">Total Requests</span>
        <h2 style="font-family: var(--font-display); font-size: 2.25rem; color: var(--burgundy-dark); margin-top: 0.35rem;"><?= (int)($stats['total_count'] ?? 0) ?></h2>
    </div>

    <div class="card" style="padding: 1.5rem; border-top: 3.5px solid var(--gold-bright);">
        <span style="font-size: 0.75rem; color: var(--gold-dark); font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">In Progress</span>
        <h2 style="font-family: var(--font-display); font-size: 2.25rem; color: var(--gold-dark); margin-top: 0.35rem;"><?= (int)($stats['active_count'] ?? 0) ?></h2>
    </div>

    <div class="card" style="padding: 1.5rem; border-top: 3.5px solid var(--status-ready);">
        <span style="font-size: 0.75rem; color: #15803d; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">Ready for Pickup</span>
        <h2 style="font-family: var(--font-display); font-size: 2.25rem; color: #15803d; margin-top: 0.35rem;"><?= (int)($stats['ready_count'] ?? 0) ?></h2>
    </div>

    <div class="card" style="padding: 1.5rem; border-top: 3.5px solid #6b7280;">
        <span style="font-size: 0.75rem; color: #4b5563; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">Released / Claimed</span>
        <h2 style="font-family: var(--font-display); font-size: 2.25rem; color: #374151; margin-top: 0.35rem;"><?= (int)($stats['released_count'] ?? 0) ?></h2>
    </div>
</div>

<!-- Requests Table Card -->
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <h3 class="card-title" style="margin-bottom: 0;">
            <i class="fa-solid fa-scroll"></i> My Document Applications
        </h3>
        <div>
            <input type="text" id="tableSearch" class="form-control" placeholder="Search my records..." style="width: 230px; font-size: 0.88rem;">
        </div>
    </div>

    <?php if (empty($requests)): ?>
        <div style="text-align: center; padding: 3.5rem 1rem; color: var(--muted); background: var(--bg-silk); border-radius: 12px; border: 1px dashed var(--border-gold);">
            <div style="width: 60px; height: 60px; border-radius: 50%; background: #ffffff; color: var(--gold-primary); display: inline-flex; align-items: center; justify-content: center; font-size: 1.75rem; margin-bottom: 1rem; border: 1px solid var(--border-gold);">
                <i class="fa-solid fa-scroll"></i>
            </div>
            <h4 style="font-family: var(--font-serif); font-size: 1.25rem; color: var(--burgundy-dark); margin-bottom: 0.35rem;">No Applications Filed</h4>
            <p style="font-size: 0.9rem; margin-bottom: 1.5rem;">You do not have any active or past document requests.</p>
            <a href="/DocuTrack/student/new_request.php" class="btn btn-gold btn-sm">Request Your First Document</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Tracking Reference</th>
                        <th>Document Requested</th>
                        <th>Purpose</th>
                        <th>Amount</th>
                        <th>Filing Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requests as $r): ?>
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
                                <strong style="color: var(--burgundy-primary); font-family: monospace; font-size: 1rem; letter-spacing: 0.5px;"><?= htmlspecialchars($r['tracking_code']) ?></strong>
                            </td>
                            <td>
                                <strong style="color: var(--dark); font-family: var(--font-serif);"><?= htmlspecialchars($r['document_name']) ?></strong>
                                <div style="font-size: 0.78rem; color: var(--muted);"><?= $r['copies'] ?> copy(ies)</div>
                            </td>
                            <td><?= htmlspecialchars($r['purpose']) ?></td>
                            <td><strong>₱<?= number_format($r['total_amount'], 2) ?></strong></td>
                            <td><?= date('M d, Y', strtotime($r['created_at'])) ?></td>
                            <td>
                                <span class="badge <?= $statusBadge ?>"><?= htmlspecialchars($r['status']) ?></span>
                            </td>
                            <td>
                                <a href="/DocuTrack/track.php?code=<?= urlencode($r['tracking_code']) ?>" class="btn btn-sm btn-secondary" title="View live timeline">
                                    <i class="fa-solid fa-compass"></i> Track
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

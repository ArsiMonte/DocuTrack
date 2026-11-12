<?php
// student/dashboard.php - Student Portal
$pageTitle = "Student Dashboard";
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireStudent();
$user = currentUser();

// Fetch summary metrics for this student
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

// Fetch list of requests
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

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.75rem; color: var(--dark); font-weight: 700;">Student Portal</h1>
        <p style="color: var(--muted); font-size: 0.9rem;">Welcome back, <strong><?= htmlspecialchars($user['name']) ?></strong> (<?= htmlspecialchars($user['student_id']) ?>)</p>
    </div>
    <div>
        <a href="/DocuTrack/student/new_request.php" class="btn btn-primary">
            <i class="fa-solid fa-plus-circle"></i> Request Document
        </a>
    </div>
</div>

<!-- Quick Stat Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
    <div class="card" style="padding: 1.25rem;">
        <span style="font-size: 0.8rem; color: var(--muted); font-weight: 600; text-transform: uppercase;">Total Requests</span>
        <h2 style="font-size: 2rem; color: var(--dark); margin-top: 0.25rem;"><?= (int)($stats['total_count'] ?? 0) ?></h2>
    </div>

    <div class="card" style="padding: 1.25rem; border-left: 4px solid var(--info);">
        <span style="font-size: 0.8rem; color: var(--muted); font-weight: 600; text-transform: uppercase;">In Progress</span>
        <h2 style="font-size: 2rem; color: var(--info); margin-top: 0.25rem;"><?= (int)($stats['active_count'] ?? 0) ?></h2>
    </div>

    <div class="card" style="padding: 1.25rem; border-left: 4px solid var(--success);">
        <span style="font-size: 0.8rem; color: var(--muted); font-weight: 600; text-transform: uppercase;">Ready for Pickup</span>
        <h2 style="font-size: 2rem; color: var(--success); margin-top: 0.25rem;"><?= (int)($stats['ready_count'] ?? 0) ?></h2>
    </div>

    <div class="card" style="padding: 1.25rem; border-left: 4px solid var(--muted);">
        <span style="font-size: 0.8rem; color: var(--muted); font-weight: 600; text-transform: uppercase;">Released / Completed</span>
        <h2 style="font-size: 2rem; color: var(--dark); margin-top: 0.25rem;"><?= (int)($stats['released_count'] ?? 0) ?></h2>
    </div>
</div>

<!-- Requests Table Card -->
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
        <h3 class="card-title" style="margin-bottom: 0;"><i class="fa-solid fa-list-check"></i> My Document Requests</h3>
        <div>
            <input type="text" id="tableSearch" class="form-control" placeholder="Search my requests..." style="width: 220px; font-size: 0.85rem;">
        </div>
    </div>

    <?php if (empty($requests)): ?>
        <div style="text-align: center; padding: 3rem 1rem; color: var(--muted);">
            <i class="fa-regular fa-folder-open" style="font-size: 3rem; margin-bottom: 1rem; color: #cbd5e1;"></i>
            <h4>No requests found</h4>
            <p style="font-size: 0.9rem; margin-bottom: 1.25rem;">You have not requested any registrar documents yet.</p>
            <a href="/DocuTrack/student/new_request.php" class="btn btn-primary btn-sm">Request Your First Document</a>
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
                        <th>Date Submitted</th>
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
                                <strong style="color: var(--primary); font-family: monospace; font-size: 1rem;"><?= htmlspecialchars($r['tracking_code']) ?></strong>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($r['document_name']) ?></strong>
                                <div style="font-size: 0.78rem; color: var(--muted);"><?= $r['copies'] ?> copy(ies)</div>
                            </td>
                            <td><?= htmlspecialchars($r['purpose']) ?></td>
                            <td>₱<?= number_format($r['total_amount'], 2) ?></td>
                            <td><?= date('M d, Y', strtotime($r['created_at'])) ?></td>
                            <td>
                                <span class="badge <?= $statusBadge ?>"><?= htmlspecialchars($r['status']) ?></span>
                            </td>
                            <td>
                                <a href="/DocuTrack/track.php?code=<?= urlencode($r['tracking_code']) ?>" class="btn btn-sm btn-secondary" title="View live timeline tracking">
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i> Track
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

<?php
// admin/requests.php - Manage and Process Document Requests
$pageTitle = "Manage Requests";
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin();
$user = currentUser();

$message = '';
$error = '';

// Handle status update POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $requestId      = (int)($_POST['request_id'] ?? 0);
    $newStatus      = trim($_POST['status'] ?? '');
    $remarks        = trim($_POST['remarks'] ?? '');
    $targetRelease  = !empty($_POST['target_release_date']) ? $_POST['target_release_date'] : null;

    $validStatuses = ['Pending', 'Payment Verification', 'Processing', 'Ready for Pickup', 'Released', 'Rejected'];

    if ($requestId <= 0 || !in_array($newStatus, $validStatuses)) {
        $error = "Invalid request or status selected.";
    } else {
        try {
            $pdo->beginTransaction();

            $upd = $pdo->prepare("
                UPDATE document_requests
                SET status = ?, admin_remarks = ?, target_release_date = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $upd->execute([$newStatus, $remarks, $targetRelease, $requestId]);

            // Add audit log
            $hist = $pdo->prepare("
                INSERT INTO status_history (request_id, status, remarks, changed_by)
                VALUES (?, ?, ?, ?)
            ");
            $hist->execute([$requestId, $newStatus, $remarks, $user['id']]);

            $pdo->commit();
            $message = "Request status successfully updated to \"$newStatus\".";
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Failed to update status: " . $e->getMessage();
        }
    }
}

// Filter parameters
$statusFilter = $_GET['status'] ?? 'all';
$searchQuery  = trim($_GET['search'] ?? '');
$editId       = (int)($_GET['edit'] ?? 0);

$editRecord = null;
if ($editId > 0) {
    $editStmt = $pdo->prepare("
        SELECT r.*, d.name AS document_name, u.full_name AS student_name, u.student_id, u.email, u.contact_number, u.course_section
        FROM document_requests r
        JOIN document_types d ON r.document_type_id = d.id
        JOIN users u ON r.user_id = u.id
        WHERE r.id = ?
        LIMIT 1
    ");
    $editStmt->execute([$editId]);
    $editRecord = $editStmt->fetch();
}

// Build query
$whereClauses = [];
$params = [];

if ($statusFilter !== 'all' && !empty($statusFilter)) {
    $whereClauses[] = "r.status = ?";
    $params[] = $statusFilter;
}

if (!empty($searchQuery)) {
    $whereClauses[] = "(r.tracking_code LIKE ? OR u.student_id LIKE ? OR u.full_name LIKE ? OR d.name LIKE ?)";
    $like = "%$searchQuery%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$whereSql = !empty($whereClauses) ? "WHERE " . implode(" AND ", $whereClauses) : "";

$stmt = $pdo->prepare("
    SELECT r.*, d.name AS document_name, u.full_name AS student_name, u.student_id
    FROM document_requests r
    JOIN document_types d ON r.document_type_id = d.id
    JOIN users u ON r.user_id = u.id
    $whereSql
    ORDER BY r.created_at DESC
");
$stmt->execute($params);
$requests = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.75rem; color: var(--dark); font-weight: 700;">Document Requests Queue</h1>
        <p style="color: var(--muted); font-size: 0.9rem;">Filter, review, verify, and update real-time status of student applications.</p>
    </div>
    <div>
        <a href="/DocuTrack/admin/dashboard.php" class="btn btn-secondary btn-sm">
            <i class="fa-solid fa-arrow-left"></i> Dashboard
        </a>
    </div>
</div>

<?php if (!empty($message)): ?>
    <div style="background: #ecfdf5; color: #065f46; padding: 0.85rem 1rem; border-radius: 8px; margin-bottom: 1.25rem;">
        <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div style="background: #fee2e2; color: #991b1b; padding: 0.85rem 1rem; border-radius: 8px; margin-bottom: 1.25rem;">
        <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<!-- Review/Update Form Modal/Box if Edit is clicked -->
<?php if ($editRecord): ?>
    <div class="card" style="border: 2px solid var(--primary); background: #f8fafc; margin-bottom: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h3 class="card-title" style="margin-bottom: 0;">
                <i class="fa-solid fa-clipboard-check" style="color: var(--primary);"></i> Review Request: 
                <span style="font-family: monospace; color: var(--primary);"><?= htmlspecialchars($editRecord['tracking_code']) ?></span>
            </h3>
            <a href="/DocuTrack/admin/requests.php" class="btn btn-secondary btn-sm"><i class="fa-solid fa-xmark"></i> Close</a>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; background: #fff; padding: 1.25rem; border-radius: 8px; border: 1px solid var(--border); margin-bottom: 1.5rem;">
            <div>
                <span style="font-size: 0.8rem; color: var(--muted);">Student</span>
                <div style="font-weight: 600;"><?= htmlspecialchars($editRecord['student_name']) ?> (<?= htmlspecialchars($editRecord['student_id'] ?? '') ?>)</div>
                <div style="font-size: 0.8rem; color: var(--muted);"><?= htmlspecialchars($editRecord['course_section'] ?? '') ?></div>
                <div style="font-size: 0.8rem; color: var(--muted);"><?= htmlspecialchars($editRecord['contact_number'] ?? '') ?> &bull; <?= htmlspecialchars($editRecord['email']) ?></div>
            </div>
            <div>
                <span style="font-size: 0.8rem; color: var(--muted);">Requested Document</span>
                <div style="font-weight: 600;"><?= htmlspecialchars($editRecord['document_name']) ?></div>
                <div style="font-size: 0.85rem;"><?= $editRecord['copies'] ?> copy(ies) &bull; <strong>₱<?= number_format($editRecord['total_amount'], 2) ?></strong></div>
            </div>
            <div>
                <span style="font-size: 0.8rem; color: var(--muted);">Purpose of Request</span>
                <div style="font-weight: 600;"><?= htmlspecialchars($editRecord['purpose']) ?></div>
            </div>
            <div>
                <span style="font-size: 0.8rem; color: var(--muted);">Uploaded Attachment</span>
                <div>
                    <?php if (!empty($editRecord['payment_proof'])): ?>
                        <a href="/DocuTrack/uploads/<?= htmlspecialchars($editRecord['payment_proof']) ?>" target="_blank" class="btn btn-sm btn-outline" style="margin-top: 0.25rem;">
                            <i class="fa-solid fa-paperclip"></i> View Attachment
                        </a>
                    <?php else: ?>
                        <span style="color: var(--muted); font-size: 0.85rem;">None uploaded</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <form action="/DocuTrack/admin/requests.php" method="POST">
            <input type="hidden" name="action" value="update_status">
            <input type="hidden" name="request_id" value="<?= $editRecord['id'] ?>">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="status">Update Status *</label>
                    <select name="status" id="status" class="form-select" required>
                        <?php
                        $options = ['Pending', 'Payment Verification', 'Processing', 'Ready for Pickup', 'Released', 'Rejected'];
                        foreach ($options as $opt):
                        ?>
                            <option value="<?= $opt ?>" <?= $editRecord['status'] === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="target_release_date">Target Release Date</label>
                    <input type="date" id="target_release_date" name="target_release_date" class="form-control" value="<?= htmlspecialchars($editRecord['target_release_date'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="remarks">Admin Remarks / Instructions for Student</label>
                <textarea name="remarks" id="remarks" rows="2" class="form-control" placeholder="e.g. Verified payment. Document is currently being printed and signed by Registrar."><?= htmlspecialchars($editRecord['admin_remarks'] ?? '') ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-floppy-disk"></i> Save & Log Status Update
            </button>
        </form>
    </div>
<?php endif; ?>

<!-- Search & Filter Controls -->
<div class="card" style="padding: 1rem 1.5rem; margin-bottom: 1.5rem;">
    <form action="/DocuTrack/admin/requests.php" method="GET" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 200px;">
            <input type="text" name="search" value="<?= htmlspecialchars($searchQuery) ?>" class="form-control" placeholder="Search tracking code, student ID, name...">
        </div>

        <div>
            <select name="status" class="form-select" style="min-width: 170px;">
                <option value="all" <?= $statusFilter === 'all' ? 'selected' : '' ?>>All Statuses</option>
                <option value="Pending" <?= $statusFilter === 'Pending' ? 'selected' : '' ?>>Pending</option>
                <option value="Payment Verification" <?= $statusFilter === 'Payment Verification' ? 'selected' : '' ?>>Payment Verification</option>
                <option value="Processing" <?= $statusFilter === 'Processing' ? 'selected' : '' ?>>Processing</option>
                <option value="Ready for Pickup" <?= $statusFilter === 'Ready for Pickup' ? 'selected' : '' ?>>Ready for Pickup</option>
                <option value="Released" <?= $statusFilter === 'Released' ? 'selected' : '' ?>>Released</option>
                <option value="Rejected" <?= $statusFilter === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-filter"></i> Apply</button>
        <?php if (!empty($searchQuery) || $statusFilter !== 'all'): ?>
            <a href="/DocuTrack/admin/requests.php" class="btn btn-secondary">Reset</a>
        <?php endif; ?>
    </form>
</div>

<!-- Requests Table -->
<div class="card">
    <?php if (empty($requests)): ?>
        <p style="text-align: center; color: var(--muted); padding: 2rem 0;">No document requests match your criteria.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Tracking Reference</th>
                        <th>Student</th>
                        <th>Document Requested</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Target Release</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requests as $r): ?>
                        <?php
                            $badgeClass = 'badge-pending';
                            if ($r['status'] === 'Payment Verification') $badgeClass = 'badge-verification';
                            if ($r['status'] === 'Processing') $badgeClass = 'badge-processing';
                            if ($r['status'] === 'Ready for Pickup') $badgeClass = 'badge-ready';
                            if ($r['status'] === 'Released') $badgeClass = 'badge-released';
                            if ($r['status'] === 'Rejected') $badgeClass = 'badge-rejected';
                        ?>
                        <tr>
                            <td>
                                <strong style="color: var(--primary); font-family: monospace; font-size: 0.95rem;"><?= htmlspecialchars($r['tracking_code']) ?></strong>
                                <div style="font-size: 0.75rem; color: var(--muted);"><?= date('M d, Y', strtotime($r['created_at'])) ?></div>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($r['student_name']) ?></strong>
                                <div style="font-size: 0.78rem; color: var(--muted);"><?= htmlspecialchars($r['student_id'] ?? 'N/A') ?></div>
                            </td>
                            <td>
                                <?= htmlspecialchars($r['document_name']) ?>
                                <span style="font-size: 0.8rem; color: var(--muted);">(<?= $r['copies'] ?>x)</span>
                            </td>
                            <td>₱<?= number_format($r['total_amount'], 2) ?></td>
                            <td><span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($r['status']) ?></span></td>
                            <td>
                                <?= $r['target_release_date'] ? date('M d, Y', strtotime($r['target_release_date'])) : '<span style="color:var(--muted); font-size:0.8rem;">Unset</span>' ?>
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.35rem;">
                                    <a href="/DocuTrack/admin/requests.php?edit=<?= $r['id'] ?>" class="btn btn-sm btn-primary" title="Update status">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <a href="/DocuTrack/track.php?code=<?= urlencode($r['tracking_code']) ?>" class="btn btn-sm btn-secondary" target="_blank" title="Preview public tracking">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

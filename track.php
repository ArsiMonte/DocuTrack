<?php
// track.php - Real-Time Document Request Tracking
$pageTitle = "Track Request";
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/header.php';

$code = isset($_GET['code']) ? trim($_GET['code']) : '';
$request = null;
$history = [];
$error = null;

if (!empty($code)) {
    try {
        $stmt = $pdo->prepare("
            SELECT r.*, d.name AS document_name, d.processing_days, u.full_name AS student_name, u.student_id
            FROM document_requests r
            JOIN document_types d ON r.document_type_id = d.id
            JOIN users u ON r.user_id = u.id
            WHERE r.tracking_code = ?
            LIMIT 1
        ");
        $stmt->execute([$code]);
        $request = $stmt->fetch();

        if ($request) {
            $histStmt = $pdo->prepare("
                SELECT h.*, u.full_name AS changer_name
                FROM status_history h
                LEFT JOIN users u ON h.changed_by = u.id
                WHERE h.request_id = ?
                ORDER BY h.created_at DESC
            ");
            $histStmt->execute([$request['id']]);
            $history = $histStmt->fetchAll();
        } else {
            $error = "No document request found with tracking code \"$code\". Please verify your code and try again.";
        }
    } catch (PDOException $e) {
        $error = "Unable to fetch tracking data: " . $e->getMessage();
    }
}

// Stage definitions for visual progress bar
$stages = ['Pending', 'Payment Verification', 'Processing', 'Ready for Pickup', 'Released'];
?>

<div style="max-width: 800px; margin: 0 auto;">
    <div class="card">
        <h2 class="card-title"><i class="fa-solid fa-magnifying-glass"></i> Track Document Request</h2>
        <form action="/DocuTrack/track.php" method="GET" style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem;">
            <input type="text" name="code" value="<?= htmlspecialchars($code) ?>" class="form-control" placeholder="Enter Tracking Code (e.g. DOC-2026-1049)" required style="text-transform: uppercase;">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-search"></i> Search</button>
        </form>

        <?php if ($error): ?>
            <div style="background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; border: 1px solid #f87171;">
                <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($request): ?>
            <div style="border-top: 1px solid var(--border); padding-top: 1.5rem;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
                    <div>
                        <span style="font-size: 0.85rem; color: var(--muted); text-transform: uppercase; font-weight: 600;">Tracking Reference</span>
                        <h2 style="color: var(--primary); font-size: 1.75rem; letter-spacing: 0.5px;"><?= htmlspecialchars($request['tracking_code']) ?></h2>
                    </div>
                    <div>
                        <?php
                            $statusClass = 'badge-pending';
                            if ($request['status'] === 'Payment Verification') $statusClass = 'badge-verification';
                            if ($request['status'] === 'Processing') $statusClass = 'badge-processing';
                            if ($request['status'] === 'Ready for Pickup') $statusClass = 'badge-ready';
                            if ($request['status'] === 'Released') $statusClass = 'badge-released';
                            if ($request['status'] === 'Rejected') $statusClass = 'badge-rejected';
                        ?>
                        <span class="badge <?= $statusClass ?>" style="font-size: 1rem; padding: 0.5rem 1.25rem;">
                            <?= htmlspecialchars($request['status']) ?>
                        </span>
                    </div>
                </div>

                <!-- Visual Stepper / Progress Bar (unless Rejected) -->
                <?php if ($request['status'] !== 'Rejected'): ?>
                    <?php
                        $currentIndex = array_search($request['status'], $stages);
                        if ($currentIndex === false) $currentIndex = 0;
                    ?>
                    <div style="margin: 2rem 0; display: flex; justify-content: space-between; position: relative;">
                        <div style="position: absolute; top: 18px; left: 10%; right: 10%; height: 4px; background: #e2e8f0; z-index: 1;"></div>
                        <div style="position: absolute; top: 18px; left: 10%; width: <?= ($currentIndex / (count($stages) - 1)) * 80 ?>%; height: 4px; background: var(--primary); z-index: 1; transition: width 0.4s ease;"></div>

                        <?php foreach ($stages as $idx => $stage): ?>
                            <?php $isDone = $idx <= $currentIndex; ?>
                            <div style="text-align: center; position: relative; z-index: 2; width: 18%;">
                                <div style="width: 38px; height: 38px; border-radius: 50%; margin: 0 auto 0.5rem auto; display: flex; align-items: center; justify-content: center; font-weight: bold; background: <?= $isDone ? 'var(--primary)' : '#ffffff' ?>; color: <?= $isDone ? '#ffffff' : 'var(--muted)' ?>; border: 2px solid <?= $isDone ? 'var(--primary)' : '#cbd5e1' ?>;">
                                    <?php if ($isDone): ?>
                                        <i class="fa-solid fa-check" style="font-size: 0.85rem;"></i>
                                    <?php else: ?>
                                        <?= $idx + 1 ?>
                                    <?php endif; ?>
                                </div>
                                <span style="font-size: 0.75rem; font-weight: 600; color: <?= $isDone ? 'var(--dark)' : 'var(--muted)' ?>; display: block;">
                                    <?= htmlspecialchars($stage) ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 1.25rem; margin-bottom: 1.5rem;">
                        <h4 style="color: #991b1b; margin-bottom: 0.25rem;"><i class="fa-solid fa-circle-xmark"></i> Request Rejected</h4>
                        <p style="color: #7f1d1d; font-size: 0.9rem;">
                            <strong>Remarks:</strong> <?= htmlspecialchars($request['admin_remarks'] ?? 'Please contact Registrar Office for clarification.') ?>
                        </p>
                    </div>
                <?php endif; ?>

                <!-- Request Summary Details -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; background: var(--bg-light); padding: 1.25rem; border-radius: 8px; margin-bottom: 1.5rem;">
                    <div>
                        <div style="font-size: 0.8rem; color: var(--muted);">Requested Document</div>
                        <div style="font-weight: 600; color: var(--dark);"><?= htmlspecialchars($request['document_name']) ?></div>
                    </div>
                    <div>
                        <div style="font-size: 0.8rem; color: var(--muted);">Student ID & Name</div>
                        <div style="font-weight: 600; color: var(--dark);"><?= htmlspecialchars($request['student_id'] ?? 'N/A') ?> - <?= htmlspecialchars($request['student_name']) ?></div>
                    </div>
                    <div>
                        <div style="font-size: 0.8rem; color: var(--muted);">Purpose</div>
                        <div style="font-weight: 600; color: var(--dark);"><?= htmlspecialchars($request['purpose']) ?></div>
                    </div>
                    <div>
                        <div style="font-size: 0.8rem; color: var(--muted);">Copies / Amount</div>
                        <div style="font-weight: 600; color: var(--dark);"><?= $request['copies'] ?> copy(ies) &bull; ₱<?= number_format($request['total_amount'], 2) ?></div>
                    </div>
                    <div>
                        <div style="font-size: 0.8rem; color: var(--muted);">Request Date</div>
                        <div style="font-weight: 600; color: var(--dark);"><?= date('M d, Y h:i A', strtotime($request['created_at'])) ?></div>
                    </div>
                    <div>
                        <div style="font-size: 0.8rem; color: var(--muted);">Target Release Date</div>
                        <div style="font-weight: 600; color: var(--primary);">
                            <?= $request['target_release_date'] ? date('M d, Y', strtotime($request['target_release_date'])) : 'To be assigned' ?>
                        </div>
                    </div>
                </div>

                <!-- Status Update Audit History -->
                <h4 style="font-size: 1rem; color: var(--dark); margin-bottom: 0.75rem;"><i class="fa-solid fa-list-timeline"></i> Activity Timeline</h4>
                <?php if (empty($history)): ?>
                    <p style="color: var(--muted); font-size: 0.85rem;">No activity log recorded yet.</p>
                <?php else: ?>
                    <div style="border-left: 2px solid var(--border); margin-left: 0.5rem; padding-left: 1.25rem;">
                        <?php foreach ($history as $log): ?>
                            <div style="margin-bottom: 1.25rem; position: relative;">
                                <div style="width: 10px; height: 10px; border-radius: 50%; background: var(--primary); position: absolute; left: -1.6rem; top: 5px;"></div>
                                <div style="font-weight: 600; color: var(--dark); font-size: 0.95rem;">
                                    <?= htmlspecialchars($log['status']) ?>
                                    <span style="font-size: 0.75rem; font-weight: normal; color: var(--muted); margin-left: 0.5rem;">
                                        <?= date('M d, Y h:i A', strtotime($log['created_at'])) ?>
                                    </span>
                                </div>
                                <?php if (!empty($log['remarks'])): ?>
                                    <div style="font-size: 0.85rem; color: var(--muted); margin-top: 0.2rem;">
                                        <?= htmlspecialchars($log['remarks']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

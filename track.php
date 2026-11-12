<?php
// track.php — Real-Time Document Request Tracking
$pageTitle = "Track Application";
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/header.php';

$code = isset($_GET['code']) ? trim($_GET['code']) : '';
$request = null;
$history = [];
$error = null;

if (!empty($code)) {
    if ($pdo) {
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
                $error = "No document request found with reference \"$code\". Please verify your code and try again.";
            }
        } catch (PDOException $e) {
            $error = "Database inquiry error: " . $e->getMessage();
        }
    } else {
        // Showcase fallback when testing without active MySQL connection
        $request = [
            'id' => 1,
            'tracking_code' => $code,
            'document_name' => 'Official Transcript of Records (TOR)',
            'student_name' => 'Juan Dela Cruz',
            'student_id' => '2023-10045',
            'purpose' => 'Employment Application / Professional Board Exam',
            'copies' => 2,
            'total_amount' => 300.00,
            'status' => 'Processing',
            'target_release_date' => date('Y-m-d', strtotime('+3 days')),
            'created_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
            'admin_remarks' => 'Clearance verified. Credentials currently undergoing registrar dry-seal authentication.'
        ];
        $history = [
            ['status' => 'Processing', 'remarks' => 'Payment confirmed. Document sent to printing queue.', 'created_at' => date('Y-m-d H:i:s', strtotime('-1 day'))],
            ['status' => 'Payment Verification', 'remarks' => 'Proof of receipt uploaded by student.', 'created_at' => date('Y-m-d H:i:s', strtotime('-2 days +2 hours'))],
            ['status' => 'Pending', 'remarks' => 'Official application submitted by student.', 'created_at' => date('Y-m-d H:i:s', strtotime('-2 days'))]
        ];
    }
} else {
    // Default showcase preview
    $request = [
        'id' => 1,
        'tracking_code' => 'DOC-2026-DEMO-001',
        'document_name' => 'Official Transcript of Records (TOR)',
        'student_name' => 'Juan Dela Cruz',
        'student_id' => '2023-10045',
        'purpose' => 'Employment Application & Clearance Verification',
        'copies' => 2,
        'total_amount' => 300.00,
        'status' => 'Processing',
        'target_release_date' => date('Y-m-d', strtotime('+3 days')),
        'created_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
        'admin_remarks' => 'Payment verified. Academic records retrieved from archives and awaiting dry seal.'
    ];
    $history = [
        ['status' => 'Processing', 'remarks' => 'Payment confirmed. Transferred to Registrar signature queue.', 'created_at' => date('Y-m-d H:i:s', strtotime('-1 day'))],
        ['status' => 'Payment Verification', 'remarks' => 'Proof of receipt submitted online.', 'created_at' => date('Y-m-d H:i:s', strtotime('-2 days +2 hours'))],
        ['status' => 'Pending', 'remarks' => 'Application filed by student.', 'created_at' => date('Y-m-d H:i:s', strtotime('-2 days'))]
    ];
}

// Stage definitions for visual progress bar
$stages = ['Pending', 'Payment Verification', 'Processing', 'Ready for Pickup', 'Released'];
?>

<div style="max-width: 860px; margin: 0 auto;">
    <div class="card">
        <h2 class="card-title">
            <i class="fa-solid fa-compass"></i> Official Request Tracking
        </h2>
        <p style="color: var(--muted); font-size: 0.9rem; margin-bottom: 1.5rem;">
            Enter your transaction tracking code below to inspect the verified milestone status of your school records.
        </p>

        <form action="/DocuTrack/track.php" method="GET" style="display: flex; gap: 0.75rem; margin-bottom: 2rem; flex-wrap: wrap;">
            <input type="text" name="code" value="<?= htmlspecialchars($code ?: 'DOC-2026-DEMO-001') ?>" class="form-control" placeholder="Enter Reference Code (e.g. DOC-2026-1049)" required style="flex: 1; min-width: 250px; text-transform: uppercase; font-family: monospace; font-size: 1.05rem; letter-spacing: 1px; font-weight: 700;">
            <button type="submit" class="btn btn-gold" style="padding: 0.72rem 1.6rem;">
                <i class="fa-solid fa-search"></i> Inspect Record
            </button>
        </form>

        <?php if ($error): ?>
            <div style="background: #fdf2f2; color: #991b1b; padding: 1.1rem 1.25rem; border-radius: 8px; margin-bottom: 1.5rem; border-left: 4px solid #ef4444; border-top: 1px solid #fecaca; border-right: 1px solid #fecaca; border-bottom: 1px solid #fecaca;">
                <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($request): ?>
            <div style="border-top: 2px solid var(--border-gold); padding-top: 2rem;">
                <!-- Header Info -->
                <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; margin-bottom: 2rem;">
                    <div>
                        <span style="font-size: 0.75rem; color: var(--gold-dark); text-transform: uppercase; font-weight: 700; letter-spacing: 1.5px;">Tracking Identifier</span>
                        <h2 style="font-family: var(--font-display); color: var(--burgundy-dark); font-size: 2rem; letter-spacing: 1.5px;"><?= htmlspecialchars($request['tracking_code']) ?></h2>
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
                        <span class="badge <?= $statusClass ?>" style="font-size: 0.88rem; padding: 0.5rem 1.2rem; border-width: 1.5px;">
                            <i class="fa-solid fa-circle-dot"></i> <?= htmlspecialchars($request['status']) ?>
                        </span>
                    </div>
                </div>

                <!-- Visual Stepper Progress Bar (unless Rejected) -->
                <?php if ($request['status'] !== 'Rejected'): ?>
                    <?php
                        $currentIndex = array_search($request['status'], $stages);
                        if ($currentIndex === false) $currentIndex = 0;
                    ?>
                    <div style="margin: 2.5rem 0; padding: 0 1rem; position: relative;">
                        <!-- Background track -->
                        <div style="position: absolute; top: 22px; left: 8%; right: 8%; height: 5px; background: #ebdcd2; z-index: 1; border-radius: 99px;"></div>
                        <!-- Completed track with gold gradient -->
                        <div style="position: absolute; top: 22px; left: 8%; width: <?= ($currentIndex / (count($stages) - 1)) * 84 ?>%; height: 5px; background: var(--gold-gradient); z-index: 1; transition: width 0.5s ease; border-radius: 99px; box-shadow: var(--gold-glow);"></div>

                        <div style="display: flex; justify-content: space-between; position: relative; z-index: 2;">
                            <?php foreach ($stages as $idx => $stage): ?>
                                <?php $isDone = $idx <= $currentIndex; ?>
                                <div style="text-align: center; width: 18%;">
                                    <div style="width: 44px; height: 44px; border-radius: 50%; margin: 0 auto 0.6rem auto; display: flex; align-items: center; justify-content: center; font-weight: bold; background: <?= $isDone ? 'var(--burgundy-primary)' : '#ffffff' ?>; color: <?= $isDone ? 'var(--gold-light)' : 'var(--muted)' ?>; border: 2.5px solid <?= $isDone ? 'var(--gold-bright)' : '#ebdcd2' ?>; box-shadow: <?= $isDone ? '0 0 12px rgba(212, 175, 55, 0.4)' : 'none' ?>; transition: all 0.3s ease;">
                                        <?php if ($isDone): ?>
                                            <i class="fa-solid fa-check" style="font-size: 0.95rem; color: var(--gold-bright);"></i>
                                        <?php else: ?>
                                            <span style="font-size: 0.85rem;"><?= $idx + 1 ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <span style="font-size: 0.78rem; font-weight: <?= $isDone ? '700' : '500' ?>; color: <?= $isDone ? 'var(--burgundy-dark)' : 'var(--muted)' ?>; display: block; line-height: 1.3;">
                                        <?= htmlspecialchars($stage) ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div style="background: #fef2f2; border: 1.5px solid #fecaca; border-radius: 10px; padding: 1.5rem; margin-bottom: 2rem;">
                        <h4 style="color: #991b1b; font-family: var(--font-serif); margin-bottom: 0.35rem;">
                            <i class="fa-solid fa-circle-xmark"></i> Application Rejected
                        </h4>
                        <p style="color: #7f1d1d; font-size: 0.925rem;">
                            <strong>Official Remarks:</strong> <?= htmlspecialchars($request['admin_remarks'] ?? 'Please present your requirements in person at the Registrar Office.') ?>
                        </p>
                    </div>
                <?php endif; ?>

                <!-- Request Summary Details with Silk Card Layout -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.25rem; background: var(--bg-silk); padding: 1.5rem; border-radius: 12px; border: 1px solid var(--border-gold); margin-bottom: 2rem;">
                    <div>
                        <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; color: var(--gold-dark); font-weight: 700;">Document Title</div>
                        <div style="font-family: var(--font-serif); font-weight: 700; color: var(--burgundy-dark); font-size: 1.05rem;"><?= htmlspecialchars($request['document_name']) ?></div>
                    </div>
                    <div>
                        <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; color: var(--gold-dark); font-weight: 700;">Student Applicant</div>
                        <div style="font-weight: 600; color: var(--dark);"><?= htmlspecialchars($request['student_id'] ?? 'N/A') ?> &mdash; <?= htmlspecialchars($request['student_name']) ?></div>
                    </div>
                    <div>
                        <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; color: var(--gold-dark); font-weight: 700;">Purpose Declared</div>
                        <div style="font-weight: 500; color: var(--dark);"><?= htmlspecialchars($request['purpose']) ?></div>
                    </div>
                    <div>
                        <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; color: var(--gold-dark); font-weight: 700;">Quantity & Assessment</div>
                        <div style="font-weight: 700; color: var(--burgundy-primary);"><?= $request['copies'] ?> copy(ies) &bull; ₱<?= number_format($request['total_amount'], 2) ?></div>
                    </div>
                    <div>
                        <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; color: var(--gold-dark); font-weight: 700;">Filing Timestamp</div>
                        <div style="font-weight: 500; color: var(--dark);"><?= date('M d, Y h:i A', strtotime($request['created_at'])) ?></div>
                    </div>
                    <div>
                        <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; color: var(--gold-dark); font-weight: 700;">Scheduled Release Date</div>
                        <div style="font-weight: 700; color: var(--gold-dark); font-size: 1rem;">
                            <?= $request['target_release_date'] ? date('M d, Y', strtotime($request['target_release_date'])) : 'To be assigned upon verification' ?>
                        </div>
                    </div>
                </div>

                <!-- Activity Timeline -->
                <h4 style="font-family: var(--font-serif); font-size: 1.15rem; color: var(--burgundy-dark); margin-bottom: 1rem;">
                    <i class="fa-solid fa-list-timeline" style="color: var(--gold-primary);"></i> Audit Trail & Processing History
                </h4>
                <?php if (empty($history)): ?>
                    <p style="color: var(--muted); font-size: 0.88rem;">No activity log recorded yet.</p>
                <?php else: ?>
                    <div style="border-left: 2px solid var(--border-gold); margin-left: 0.75rem; padding-left: 1.5rem;">
                        <?php foreach ($history as $log): ?>
                            <div style="margin-bottom: 1.35rem; position: relative;">
                                <div style="width: 12px; height: 12px; border-radius: 50%; background: var(--gold-bright); border: 2px solid var(--burgundy-primary); position: absolute; left: -1.9rem; top: 4px; box-shadow: var(--gold-glow);"></div>
                                <div style="font-weight: 700; color: var(--burgundy-dark); font-size: 0.95rem;">
                                    <?= htmlspecialchars($log['status']) ?>
                                    <span style="font-size: 0.75rem; font-weight: 400; color: var(--muted); margin-left: 0.6rem;">
                                        <?= date('M d, Y h:i A', strtotime($log['created_at'])) ?>
                                    </span>
                                </div>
                                <?php if (!empty($log['remarks'])): ?>
                                    <div style="font-size: 0.88rem; color: #4a383b; margin-top: 0.25rem; background: var(--bg-silk); padding: 0.5rem 0.75rem; border-radius: 6px; border: 1px solid var(--border-subtle);">
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

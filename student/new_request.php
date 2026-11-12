<?php
// student/new_request.php — Submit Document Request
$pageTitle = "New Document Request";
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireStudent();
$user = currentUser();
$error = '';

// Fetch active document types
$docsStmt = $pdo->query("SELECT * FROM document_types WHERE is_active = 1 ORDER BY name ASC");
$documentTypes = $docsStmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doc_type_id = (int)($_POST['document_type_id'] ?? 0);
    $purpose     = trim($_POST['purpose'] ?? '');
    $copies      = max(1, (int)($_POST['copies'] ?? 1));

    if ($doc_type_id <= 0 || empty($purpose)) {
        $error = "Please choose a document type and state the purpose.";
    } else {
        $findDoc = $pdo->prepare("SELECT * FROM document_types WHERE id = ? AND is_active = 1 LIMIT 1");
        $findDoc->execute([$doc_type_id]);
        $doc = $findDoc->fetch();

        if (!$doc) {
            $error = "Selected document type is not available.";
        } else {
            $totalAmount = $doc['price'] * $copies;

            // Optional upload
            $paymentProofFileName = null;
            if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
                $fileName = $_FILES['payment_proof']['name'];
                $fileTmp  = $_FILES['payment_proof']['tmp_name'];
                $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                if (in_array($ext, $allowedExtensions)) {
                    $newFileName = 'proof_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                    $uploadDir = __DIR__ . '/../uploads/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    if (move_uploaded_file($fileTmp, $uploadDir . $newFileName)) {
                        $paymentProofFileName = $newFileName;
                    }
                }
            }

            // Generate unique tracking code e.g. DOC-2026-XXXXX
            $trackingCode = 'DOC-' . date('Y') . '-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));

            try {
                $pdo->beginTransaction();

                $insStmt = $pdo->prepare("
                    INSERT INTO document_requests (tracking_code, user_id, document_type_id, purpose, copies, total_amount, status, payment_proof)
                    VALUES (?, ?, ?, ?, ?, ?, 'Pending', ?)
                ");
                $insStmt->execute([
                    $trackingCode,
                    $user['id'],
                    $doc_type_id,
                    $purpose,
                    $copies,
                    $totalAmount,
                    $paymentProofFileName
                ]);
                $requestId = $pdo->lastInsertId();

                $histStmt = $pdo->prepare("
                    INSERT INTO status_history (request_id, status, remarks, changed_by)
                    VALUES (?, 'Pending', 'Official application submitted by student.', ?)
                ");
                $histStmt->execute([$requestId, $user['id']]);

                $pdo->commit();

                header("Location: /DocuTrack/track.php?code=" . urlencode($trackingCode));
                exit();
            } catch (PDOException $e) {
                $pdo->rollBack();
                $error = "Failed to submit request: " . $e->getMessage();
            }
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div style="max-width: 700px; margin: 1rem auto 3rem auto;">
    <div class="card" style="box-shadow: var(--shadow-hover); border-top: 4px solid var(--gold-bright);">
        <h2 class="card-title">
            <i class="fa-solid fa-feather-pointed"></i> Request School Credential
        </h2>
        <p style="color: var(--muted); font-size: 0.9rem; margin-bottom: 1.75rem;">
            Select your required academic document and review turnaround time and official prerequisites.
        </p>

        <?php if (!empty($error)): ?>
            <div style="background: #fdf2f2; color: #991b1b; padding: 0.85rem 1rem; border-radius: 8px; font-size: 0.88rem; margin-bottom: 1.25rem; border-left: 3px solid #ef4444;">
                <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="/DocuTrack/student/new_request.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label class="form-label" for="document_type_id">Official Document Type *</label>
                <select name="document_type_id" id="document_type_id" class="form-select" required>
                    <option value="">-- Choose Credential --</option>
                    <?php foreach ($documentTypes as $doc): ?>
                        <option value="<?= $doc['id'] ?>" 
                                data-price="<?= $doc['price'] ?>" 
                                data-reqs="<?= htmlspecialchars($doc['requirements'] ?? 'None specified') ?>">
                            <?= htmlspecialchars($doc['name']) ?> (₱<?= number_format($doc['price'], 2) ?> &bull; Est. <?= $doc['processing_days'] ?> working days)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Dynamic Requirements Box with Gold Trim -->
            <div id="doc_requirements_box" style="display: none; background: #faf4e8; border: 1.5px solid var(--gold-border); border-radius: 10px; padding: 1.15rem; margin-bottom: 1.5rem; font-size: 0.88rem; color: #5c430e;">
                <div style="font-family: var(--font-display); font-weight: 700; color: var(--gold-dark); margin-bottom: 0.3rem;">
                    <i class="fa-solid fa-stamp" style="color: var(--gold-bright);"></i> Prerequisites & Attached Requirements:
                </div>
                <p id="doc_requirements_text" style="margin-top: 0.25rem; margin-bottom: 0; font-size: 0.925rem; color: #3b2b0a;"></p>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="copies">Number of Copies</label>
                    <input type="number" id="copies" name="copies" class="form-control" value="1" min="1" max="10" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Calculated Fee Assessment</label>
                    <div id="total_amount_display" style="padding: 0.72rem 1rem; background: var(--bg-silk); border: 1.5px solid var(--border-gold); border-radius: 8px; font-weight: 800; color: var(--burgundy-primary); font-size: 1.25rem; font-family: var(--font-display);">
                        ₱0.00
                    </div>
                    <input type="hidden" name="total_amount" id="total_amount" value="0.00">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="purpose">Purpose of Document Request *</label>
                <input type="text" id="purpose" name="purpose" class="form-control" placeholder="e.g. Employment, Scholarship, PRC Board Examination, Transfer" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="payment_proof">Proof of Payment or Valid Student ID (Optional)</label>
                <input type="file" id="payment_proof" name="payment_proof" class="form-control" accept="image/*,.pdf">
                <small style="color: var(--muted); font-size: 0.8rem;">Supported formats: JPG, PNG, PDF (Max 5MB)</small>
            </div>

            <div style="display: flex; gap: 0.85rem; margin-top: 2rem;">
                <button type="submit" class="btn btn-gold" style="flex: 1; padding: 0.85rem;">
                    <i class="fa-solid fa-paper-plane"></i> File Document Request
                </button>
                <a href="/DocuTrack/student/dashboard.php" class="btn btn-secondary" style="padding: 0.85rem 1.5rem;">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

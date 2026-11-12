<?php
// student/new_request.php - Submit Document Request
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
        // Find document info
        $findDoc = $pdo->prepare("SELECT * FROM document_types WHERE id = ? AND is_active = 1 LIMIT 1");
        $findDoc->execute([$doc_type_id]);
        $doc = $findDoc->fetch();

        if (!$doc) {
            $error = "Selected document type is not available.";
        } else {
            $totalAmount = $doc['price'] * $copies;

            // Handle optional proof of payment upload
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

                // Insert into status_history
                $histStmt = $pdo->prepare("
                    INSERT INTO status_history (request_id, status, remarks, changed_by)
                    VALUES (?, 'Pending', 'Request submitted by student.', ?)
                ");
                $histStmt->execute([$requestId, $user['id']]);

                $pdo->commit();

                // Redirect to track page
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

<div style="max-width: 650px; margin: 0 auto;">
    <div class="card">
        <h2 class="card-title">
            <i class="fa-solid fa-file-circle-plus" style="color: var(--primary);"></i> Request School Document
        </h2>
        <p style="color: var(--muted); font-size: 0.875rem; margin-bottom: 1.5rem;">
            Fill out the details below. Once submitted, you'll receive a tracking code to monitor processing progress in real time.
        </p>

        <?php if (!empty($error)): ?>
            <div style="background: #fee2e2; color: #991b1b; padding: 0.75rem 1rem; border-radius: 6px; font-size: 0.9rem; margin-bottom: 1.25rem;">
                <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="/DocuTrack/student/new_request.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label class="form-label" for="document_type_id">Select Document *</label>
                <select name="document_type_id" id="document_type_id" class="form-select" required>
                    <option value="">-- Choose Document --</option>
                    <?php foreach ($documentTypes as $doc): ?>
                        <option value="<?= $doc['id'] ?>" 
                                data-price="<?= $doc['price'] ?>" 
                                data-reqs="<?= htmlspecialchars($doc['requirements'] ?? 'None specified') ?>">
                            <?= htmlspecialchars($doc['name']) ?> (₱<?= number_format($doc['price'], 2) ?> &bull; Est. <?= $doc['processing_days'] ?> days)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Dynamic Requirements Box -->
            <div id="doc_requirements_box" style="display: none; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 1rem; margin-bottom: 1.25rem; font-size: 0.85rem; color: #1e40af;">
                <strong><i class="fa-solid fa-circle-info"></i> Document Requirements:</strong>
                <p id="doc_requirements_text" style="margin-top: 0.25rem; margin-bottom: 0;"></p>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="copies">Number of Copies</label>
                    <input type="number" id="copies" name="copies" class="form-control" value="1" min="1" max="10" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Total Fee</label>
                    <div id="total_amount_display" style="padding: 0.65rem 0.9rem; background: var(--bg-light); border: 1px solid var(--border); border-radius: 8px; font-weight: 700; color: var(--primary); font-size: 1.1rem;">
                        ₱0.00
                    </div>
                    <input type="hidden" name="total_amount" id="total_amount" value="0.00">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="purpose">Purpose of Request *</label>
                <input type="text" id="purpose" name="purpose" class="form-control" placeholder="e.g. Scholarship application, Employment, Board exam, Transfer" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="payment_proof">Attach Proof of Payment or Valid ID (Optional)</label>
                <input type="file" id="payment_proof" name="payment_proof" class="form-control" accept="image/*,.pdf">
                <small style="color: var(--muted); font-size: 0.78rem;">Supported formats: JPG, PNG, PDF (Max 5MB)</small>
            </div>

            <div style="display: flex; gap: 0.75rem; margin-top: 1.5rem;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">
                    <i class="fa-solid fa-paper-plane"></i> Submit Request
                </button>
                <a href="/DocuTrack/student/dashboard.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

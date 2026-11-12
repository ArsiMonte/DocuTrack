<?php
// admin/documents.php - Manage Document Types Catalog
$pageTitle = "Document Catalog";
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin();
$message = '';
$error = '';

// Add / Edit handling
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $name   = trim($_POST['name'] ?? '');
    $desc   = trim($_POST['description'] ?? '');
    $price  = (float)($_POST['price'] ?? 0);
    $days   = (int)($_POST['processing_days'] ?? 3);
    $reqs   = trim($_POST['requirements'] ?? '');

    if ($action === 'create') {
        if (empty($name)) {
            $error = "Document name is required.";
        } else {
            try {
                $ins = $pdo->prepare("INSERT INTO document_types (name, description, price, processing_days, requirements) VALUES (?, ?, ?, ?, ?)");
                $ins->execute([$name, $desc, $price, $days, $reqs]);
                $message = "Document type added successfully.";
            } catch (PDOException $e) {
                $error = "Failed to add document: " . $e->getMessage();
            }
        }
    } elseif ($action === 'toggle') {
        $id = (int)($_POST['id'] ?? 0);
        $toggleStmt = $pdo->prepare("UPDATE document_types SET is_active = IF(is_active=1, 0, 1) WHERE id = ?");
        $toggleStmt->execute([$id]);
        $message = "Document status updated.";
    }
}

// Fetch all documents
$docs = $pdo->query("SELECT * FROM document_types ORDER BY id ASC")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.75rem; color: var(--dark); font-weight: 700;">Document Catalog Management</h1>
        <p style="color: var(--muted); font-size: 0.9rem;">Configure available certificates, official records, fees, and processing lead times.</p>
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

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1.5rem; align-items: flex-start;">
    <!-- Add Document Type Form -->
    <div class="card">
        <h3 class="card-title"><i class="fa-solid fa-plus-circle" style="color: var(--primary);"></i> Add Document Type</h3>
        <form action="/DocuTrack/admin/documents.php" method="POST">
            <input type="hidden" name="action" value="create">

            <div class="form-group">
                <label class="form-label" for="name">Document Name *</label>
                <input type="text" id="name" name="name" class="form-control" placeholder="e.g. Certificate of Graduation" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="price">Fee (₱)</label>
                <input type="number" step="0.50" id="price" name="price" class="form-control" value="50.00" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="processing_days">Estimated Turnaround (Days)</label>
                <input type="number" id="processing_days" name="processing_days" class="form-control" value="3" min="1" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="requirements">Prerequisites / Requirements</label>
                <textarea id="requirements" name="requirements" rows="2" class="form-control" placeholder="e.g. Valid ID, Clearance"></textarea>
            </div>

            <div class="form-group">
                <label class="form-label" for="description">Description</label>
                <textarea id="description" name="description" rows="2" class="form-control" placeholder="Brief description of the record"></textarea>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">
                <i class="fa-solid fa-floppy-disk"></i> Save Document
            </button>
        </form>
    </div>

    <!-- Existing Documents Table -->
    <div class="card">
        <h3 class="card-title"><i class="fa-solid fa-list"></i> Current Offerings</h3>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Document</th>
                        <th>Price</th>
                        <th>Days</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($docs as $d): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($d['name']) ?></strong>
                                <?php if (!empty($d['requirements'])): ?>
                                    <div style="font-size: 0.78rem; color: var(--muted);"><i class="fa-solid fa-paperclip"></i> <?= htmlspecialchars($d['requirements']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>₱<?= number_format($d['price'], 2) ?></td>
                            <td><?= $d['processing_days'] ?> working days</td>
                            <td>
                                <?php if ($d['is_active']): ?>
                                    <span class="badge badge-ready">Active</span>
                                <?php else: ?>
                                    <span class="badge badge-rejected">Disabled</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form action="/DocuTrack/admin/documents.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="toggle">
                                    <input type="hidden" name="id" value="<?= $d['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-secondary" title="Toggle active status">
                                        <?= $d['is_active'] ? '<i class="fa-solid fa-ban"></i> Disable' : '<i class="fa-solid fa-check"></i> Enable' ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

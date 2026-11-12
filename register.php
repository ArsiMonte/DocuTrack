<?php
// register.php — Student Registration
$pageTitle = "Student Registration";
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    header("Location: /DocuTrack/student/dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id     = trim($_POST['student_id'] ?? '');
    $full_name      = trim($_POST['full_name'] ?? '');
    $email          = trim($_POST['email'] ?? '');
    $contact_number = trim($_POST['contact_number'] ?? '');
    $course_section = trim($_POST['course_section'] ?? '');
    $password       = $_POST['password'] ?? '';
    $confirm_pass   = $_POST['confirm_password'] ?? '';

    if (empty($student_id) || empty($full_name) || empty($email) || empty($password)) {
        $error = "Please fill in all mandatory identification fields.";
    } elseif ($password !== $confirm_pass) {
        $error = "Passwords do not match. Please re-enter.";
    } elseif (strlen($password) < 6) {
        $error = "Security password must contain at least 6 characters.";
    } else {
        try {
            $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR student_id = ? LIMIT 1");
            $checkStmt->execute([$email, $student_id]);
            if ($checkStmt->fetch()) {
                $error = "An account with that Student ID or Email is already registered.";
            } else {
                $hashed = password_hash($password, PASSWORD_BCRYPT);
                $ins = $pdo->prepare("
                    INSERT INTO users (student_id, full_name, email, password, contact_number, course_section, role)
                    VALUES (?, ?, ?, ?, ?, ?, 'student')
                ");
                $ins->execute([$student_id, $full_name, $email, $hashed, $contact_number, $course_section]);

                header("Location: /DocuTrack/login.php?registered=1");
                exit();
            }
        } catch (PDOException $e) {
            $error = "Registration failure: " . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div style="max-width: 600px; margin: 2.5rem auto 4rem auto;">
    <div class="card" style="box-shadow: var(--shadow-hover); border-top: 4px solid var(--gold-bright);">
        <div style="text-align: center; margin-bottom: 1.5rem;">
            <div style="width: 56px; height: 56px; border-radius: 50%; background: var(--burgundy-gradient); color: var(--gold-bright); display: inline-flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 0.75rem; border: 2px solid var(--gold-bright); box-shadow: var(--gold-glow);">
                <i class="fa-solid fa-user-graduate"></i>
            </div>
            <h2 class="card-title" style="justify-content: center; margin-bottom: 0.25rem;">
                Student Account Registration
            </h2>
            <p style="color: var(--muted); font-size: 0.88rem;">
                Enroll your profile to submit online applications for official registrar documents.
            </p>
        </div>

        <?php if (!empty($error)): ?>
            <div style="background: #fdf2f2; color: #991b1b; padding: 0.85rem 1rem; border-radius: 8px; font-size: 0.88rem; margin-bottom: 1.25rem; border-left: 3px solid #ef4444;">
                <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="/DocuTrack/register.php" method="POST">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="student_id">Student ID / LRN Number *</label>
                    <input type="text" id="student_id" name="student_id" class="form-control" placeholder="e.g. 2023-10045" required value="<?= htmlspecialchars($_POST['student_id'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="full_name">Complete Legal Name *</label>
                    <input type="text" id="full_name" name="full_name" class="form-control" placeholder="e.g. Maria Santos Dela Cruz" required value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="email">Institutional / Personal Email *</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="maria@email.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="contact_number">Mobile Contact Number</label>
                    <input type="text" id="contact_number" name="contact_number" class="form-control" placeholder="09123456789" value="<?= htmlspecialchars($_POST['contact_number'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="course_section">Degree Program & Year / Section</label>
                <input type="text" id="course_section" name="course_section" class="form-control" placeholder="e.g. BS Information Technology 3-A" value="<?= htmlspecialchars($_POST['course_section'] ?? '') ?>">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="password">Security Password *</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Minimum 6 characters" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="confirm_password">Confirm Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Re-enter password" required>
                </div>
            </div>

            <button type="submit" class="btn btn-gold" style="width: 100%; margin-top: 0.5rem; padding: 0.8rem;">
                <i class="fa-solid fa-id-card"></i> Create Student Profile
            </button>
        </form>

        <div style="margin-top: 1.75rem; text-align: center; font-size: 0.875rem; color: var(--muted); border-top: 1px solid var(--border-subtle); padding-top: 1.25rem;">
            Already registered? <a href="/DocuTrack/login.php" style="color: var(--burgundy-primary); font-weight: 700; text-decoration: none;">Sign In</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

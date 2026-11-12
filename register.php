<?php
// register.php - Student Registration
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
        $error = "Please fill in all mandatory fields.";
    } elseif ($password !== $confirm_pass) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        try {
            // Check if student_id or email already exists
            $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR student_id = ? LIMIT 1");
            $checkStmt->execute([$email, $student_id]);
            if ($checkStmt->fetch()) {
                $error = "An account with that Student ID or Email already exists.";
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
            $error = "Registration error: " . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div style="max-width: 560px; margin: 2rem auto;">
    <div class="card">
        <h2 class="card-title" style="text-align: center; margin-bottom: 0.25rem;">
            <i class="fa-solid fa-user-plus" style="color: var(--primary);"></i> Student Registration
        </h2>
        <p style="text-align: center; color: var(--muted); font-size: 0.875rem; margin-bottom: 1.5rem;">
            Create an account to request, track, and manage your school credentials online.
        </p>

        <?php if (!empty($error)): ?>
            <div style="background: #fee2e2; color: #991b1b; padding: 0.75rem 1rem; border-radius: 6px; font-size: 0.9rem; margin-bottom: 1.25rem;">
                <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="/DocuTrack/register.php" method="POST">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="student_id">Student ID / LRN *</label>
                    <input type="text" id="student_id" name="student_id" class="form-control" placeholder="e.g. 2023-10045" required value="<?= htmlspecialchars($_POST['student_id'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="full_name">Full Name *</label>
                    <input type="text" id="full_name" name="full_name" class="form-control" placeholder="e.g. Juan C. Dela Cruz" required value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="email">Email Address *</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="juan@email.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="contact_number">Mobile / Contact No.</label>
                    <input type="text" id="contact_number" name="contact_number" class="form-control" placeholder="09123456789" value="<?= htmlspecialchars($_POST['contact_number'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="course_section">Course / Program & Year / Section</label>
                <input type="text" id="course_section" name="course_section" class="form-control" placeholder="e.g. BS Information Technology 3-A" value="<?= htmlspecialchars($_POST['course_section'] ?? '') ?>">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label" for="password">Password (min 6 chars) *</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Create password" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="confirm_password">Confirm Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Re-enter password" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 0.5rem;">
                Create Account
            </button>
        </form>

        <div style="margin-top: 1.5rem; text-align: center; font-size: 0.875rem; color: var(--muted); border-top: 1px solid var(--border); padding-top: 1.25rem;">
            Already have an account? <a href="/DocuTrack/login.php" style="color: var(--primary); font-weight: 600;">Log In</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

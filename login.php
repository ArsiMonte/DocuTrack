<?php
// login.php - DocuTrack User Authentication
$pageTitle = "Login";
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    if (isAdmin()) {
        header("Location: /DocuTrack/admin/dashboard.php");
    } else {
        header("Location: /DocuTrack/student/dashboard.php");
    }
    exit();
}

$error = '';
$success = '';

if (isset($_GET['registered'])) {
    $success = "Registration successful! You may now log in.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Please fill in all required fields.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['student_id'] = $user['student_id'];

                if ($user['role'] === 'admin') {
                    header("Location: /DocuTrack/admin/dashboard.php");
                } else {
                    header("Location: /DocuTrack/student/dashboard.php");
                }
                exit();
            } else {
                $error = "Invalid email or password combination.";
            }
        } catch (PDOException $e) {
            $error = "System error during authentication: " . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div style="max-width: 440px; margin: 2rem auto;">
    <div class="card">
        <h2 class="card-title" style="text-align: center; margin-bottom: 0.25rem;">
            <i class="fa-solid fa-right-to-bracket" style="color: var(--primary);"></i> Login to DocuTrack
        </h2>
        <p style="text-align: center; color: var(--muted); font-size: 0.875rem; margin-bottom: 1.5rem;">
            Access your student request dashboard or staff portal.
        </p>

        <?php if (!empty($error)): ?>
            <div style="background: #fee2e2; color: #991b1b; padding: 0.75rem 1rem; border-radius: 6px; font-size: 0.9rem; margin-bottom: 1.25rem;">
                <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div style="background: #ecfdf5; color: #065f46; padding: 0.75rem 1rem; border-radius: 6px; font-size: 0.9rem; margin-bottom: 1.25rem;">
                <i class="fa-solid fa-check-circle"></i> <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <form action="/DocuTrack/login.php" method="POST">
            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="e.g. juan@docutrack.edu" required autofocus>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Enter password" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 0.5rem;">
                Sign In
            </button>
        </form>

        <div style="margin-top: 1.5rem; text-align: center; font-size: 0.875rem; color: var(--muted); border-top: 1px solid var(--border); padding-top: 1.25rem;">
            Don't have an account yet? <a href="/DocuTrack/register.php" style="color: var(--primary); font-weight: 600;">Register as Student</a>
        </div>

        <div style="margin-top: 1rem; background: var(--bg-light); padding: 0.75rem; border-radius: 6px; font-size: 0.78rem; color: var(--muted);">
            <strong>Demo Credentials:</strong><br>
            • Admin: <code>admin@docutrack.edu</code> / <code>admin123</code><br>
            • Student: <code>student@docutrack.edu</code> / <code>student123</code>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

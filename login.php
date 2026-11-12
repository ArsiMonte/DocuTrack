<?php
// login.php — DocuTrack User Authentication
$pageTitle = "Sign In";
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
    $success = "Registration confirmed! You may now sign in to your student portal.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Please supply your registered credentials.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
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
                $error = "Invalid credential combination. Please verify your email and password.";
            }
        } catch (PDOException $e) {
            $error = "Authentication exception: " . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div style="max-width: 460px; margin: 3rem auto 4rem auto;">
    <div class="card" style="box-shadow: var(--shadow-hover); border-top: 4px solid var(--gold-bright);">
        <div style="text-align: center; margin-bottom: 1.5rem;">
            <div style="width: 56px; height: 56px; border-radius: 50%; background: var(--burgundy-gradient); color: var(--gold-bright); display: inline-flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 0.75rem; border: 2px solid var(--gold-bright); box-shadow: var(--gold-glow);">
                <i class="fa-solid fa-key"></i>
            </div>
            <h2 class="card-title" style="justify-content: center; margin-bottom: 0.25rem;">
                Portal Access
            </h2>
            <p style="color: var(--muted); font-size: 0.88rem;">
                Sign in to manage official school records and requests.
            </p>
        </div>

        <?php if (!empty($error)): ?>
            <div style="background: #fdf2f2; color: #991b1b; padding: 0.85rem 1rem; border-radius: 8px; font-size: 0.88rem; margin-bottom: 1.25rem; border-left: 3px solid #ef4444;">
                <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div style="background: #f0fdf4; color: #166534; padding: 0.85rem 1rem; border-radius: 8px; font-size: 0.88rem; margin-bottom: 1.25rem; border-left: 3px solid #22c55e;">
                <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <form action="/DocuTrack/login.php" method="POST">
            <div class="form-group">
                <label class="form-label" for="email">Institutional / Personal Email</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="e.g. juan@docutrack.edu" required autofocus>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Security Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Enter password" required>
            </div>

            <button type="submit" class="btn btn-gold" style="width: 100%; margin-top: 0.5rem; padding: 0.8rem;">
                <i class="fa-solid fa-shield-halved"></i> Authenticate & Enter
            </button>
        </form>

        <div style="margin-top: 1.75rem; text-align: center; font-size: 0.875rem; color: var(--muted); border-top: 1px solid var(--border-subtle); padding-top: 1.25rem;">
            New student applicant? <a href="/DocuTrack/register.php" style="color: var(--burgundy-primary); font-weight: 700; text-decoration: none;">Register an Account</a>
        </div>

        <div style="margin-top: 1.25rem; background: var(--bg-silk); padding: 0.85rem 1rem; border-radius: 8px; border: 1px solid var(--border-gold); font-size: 0.8rem; color: var(--dark);">
            <div style="font-family: var(--font-display); font-weight: 700; color: var(--burgundy-dark); margin-bottom: 0.25rem;">
                <i class="fa-solid fa-id-badge" style="color: var(--gold-primary);"></i> Quick Testing Credentials:
            </div>
            &bull; Registrar: <code>admin@docutrack.edu</code> / <code>admin123</code><br>
            &bull; Student: <code>student@docutrack.edu</code> / <code>student123</code>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

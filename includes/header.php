<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$isLoggedIn = isset($_SESSION['user_id']);
$userRole = $_SESSION['user_role'] ?? null;
$userName = $_SESSION['user_name'] ?? 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . " - DocuTrack" : "DocuTrack - Registrar Document Request System" ?></title>
    <link rel="stylesheet" href="/DocuTrack/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<header class="navbar">
    <a href="/DocuTrack/index.php" class="navbar-brand">
        <i class="fa-solid fa-folder-open"></i>
        <span>DocuTrack</span>
        <span class="brand-badge">Registrar</span>
    </a>

    <nav>
        <ul class="nav-links">
            <li><a href="/DocuTrack/index.php"><i class="fa-solid fa-house"></i> Home</a></li>
            <li><a href="/DocuTrack/track.php"><i class="fa-solid fa-magnifying-glass"></i> Track Status</a></li>
            
            <?php if ($isLoggedIn): ?>
                <?php if ($userRole === 'admin'): ?>
                    <li><a href="/DocuTrack/admin/dashboard.php"><i class="fa-solid fa-gauge"></i> Admin Dashboard</a></li>
                    <li><a href="/DocuTrack/admin/requests.php"><i class="fa-solid fa-file-lines"></i> Requests</a></li>
                    <li><a href="/DocuTrack/admin/documents.php"><i class="fa-solid fa-list-check"></i> Catalog</a></li>
                <?php else: ?>
                    <li><a href="/DocuTrack/student/dashboard.php"><i class="fa-solid fa-user"></i> My Requests</a></li>
                    <li><a href="/DocuTrack/student/new_request.php"><i class="fa-solid fa-plus-circle"></i> Request Document</a></li>
                <?php endif; ?>
                <li><span style="color: var(--muted); font-size: 0.9rem;">Hello, <strong><?= htmlspecialchars($userName) ?></strong></span></li>
                <li><a href="/DocuTrack/logout.php" class="btn btn-sm btn-outline"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
            <?php else: ?>
                <li><a href="/DocuTrack/login.php" class="btn btn-sm btn-secondary"><i class="fa-solid fa-arrow-right-to-bracket"></i> Login</a></li>
                <li><a href="/DocuTrack/register.php" class="btn btn-sm btn-primary"><i class="fa-solid fa-user-plus"></i> Register</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>

<main class="main-content">

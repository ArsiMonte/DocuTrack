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
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . " — DocuTrack" : "DocuTrack — Registrar Document Services" ?></title>
    <!-- Luxury Typography: Cinzel for prestige accents, Playfair Display for headings, Plus Jakarta Sans for body -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;600;700;800&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/DocuTrack/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<header class="navbar">
    <a href="/DocuTrack/index.php" class="navbar-brand">
        <div class="brand-crest">
            <i class="fa-solid fa-graduation-cap"></i>
        </div>
        <div class="brand-text-group">
            <span class="brand-title">DocuTrack</span>
            <span class="brand-sub">Registrar Services</span>
        </div>
        <span class="brand-badge"><i class="fa-solid fa-award"></i> Official Portal</span>
    </a>

    <nav>
        <ul class="nav-links">
            <li><a href="/DocuTrack/index.php"><i class="fa-solid fa-house"></i> Home</a></li>
            <li><a href="/DocuTrack/track.php"><i class="fa-solid fa-compass"></i> Track Status</a></li>
            
            <?php if ($isLoggedIn): ?>
                <?php if ($userRole === 'admin'): ?>
                    <li><a href="/DocuTrack/admin/dashboard.php"><i class="fa-solid fa-chart-line"></i> Dashboard</a></li>
                    <li><a href="/DocuTrack/admin/requests.php"><i class="fa-solid fa-file-invoice"></i> Requests</a></li>
                    <li><a href="/DocuTrack/admin/documents.php"><i class="fa-solid fa-layer-group"></i> Catalog</a></li>
                <?php else: ?>
                    <li><a href="/DocuTrack/student/dashboard.php"><i class="fa-solid fa-scroll"></i> My Requests</a></li>
                    <li><a href="/DocuTrack/student/new_request.php"><i class="fa-solid fa-plus-circle"></i> Request Document</a></li>
                <?php endif; ?>
                <li>
                    <span class="user-pill">
                        <i class="fa-solid fa-user-circle"></i> <?= htmlspecialchars($userName) ?>
                    </span>
                </li>
                <li><a href="/DocuTrack/logout.php" class="btn btn-sm btn-logout"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a></li>
            <?php else: ?>
                <li><a href="/DocuTrack/login.php" class="btn btn-sm btn-nav-login"><i class="fa-solid fa-key"></i> Log In</a></li>
                <li><a href="/DocuTrack/register.php" class="btn btn-sm btn-gold"><i class="fa-solid fa-user-plus"></i> Student Portal</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>

<main class="main-content">

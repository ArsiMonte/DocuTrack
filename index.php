<?php
// index.php — DocuTrack Regal Landing Page
$pageTitle = "Official Document Request & Tracking Portal";
require_once __DIR__ . '/includes/header.php';
?>

<!-- Luxury Hero Section -->
<section class="hero-luxury">
    <div class="hero-pretitle">Office of the University Registrar</div>
    <h1 class="hero-title">
        The Premier Portal for <span>Document Services</span>
    </h1>
    <p class="hero-subtitle">
        Experience streamlined, transparent, and dignified academic record acquisition. Request official transcripts, certificates, and clearances with real-time milestone tracking.
    </p>

    <!-- Tracking Lookup Box with Gold Styling -->
    <div style="max-width: 620px; margin: 0 auto; background: rgba(255, 255, 255, 0.96); padding: 1.5rem 1.75rem; border-radius: 14px; box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3); border: 2px solid var(--gold-bright); text-align: left;">
        <div style="display: flex; align-items: center; gap: 0.6rem; margin-bottom: 0.4rem;">
            <i class="fa-solid fa-compass" style="color: var(--gold-dark); font-size: 1.1rem;"></i>
            <span style="font-family: var(--font-display); font-weight: 700; color: var(--burgundy-dark); font-size: 1rem; letter-spacing: 0.5px;">
                Direct Status Inquiry
            </span>
        </div>
        <p style="font-size: 0.825rem; color: var(--muted); margin-bottom: 1rem;">
            Track your application lifecycle instantly using your official tracking reference.
        </p>
        <form action="/DocuTrack/track.php" method="GET" style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
            <input type="text" name="code" class="form-control" placeholder="e.g. DOC-2026-A1B2C3" required style="flex: 1; min-width: 220px; font-size: 1rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 600; border-color: var(--border-gold);">
            <button type="submit" class="btn btn-gold" style="white-space: nowrap; padding: 0.72rem 1.6rem;">
                <i class="fa-solid fa-magnifying-glass"></i> Track Request
            </button>
        </form>
    </div>
</section>

<!-- Luxury Feature Pillars -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.75rem; margin-bottom: 2.5rem;">
    <div class="feature-card">
        <div class="feature-icon-wrapper">
            <i class="fa-solid fa-feather-pointed"></i>
        </div>
        <h3 class="feature-title">1. Request Credentials</h3>
        <p style="color: var(--muted); font-size: 0.925rem;">
            Submit authenticated applications for Transcripts of Records (TOR), Honorable Dismissals, Good Moral, and Diplomas in minutes.
        </p>
    </div>

    <div class="feature-card">
        <div class="feature-icon-wrapper">
            <i class="fa-solid fa-hourglass-half"></i>
        </div>
        <h3 class="feature-title">2. Milestone Monitoring</h3>
        <p style="color: var(--muted); font-size: 0.925rem;">
            Follow each phase with complete visibility: from receipt audit and registrar verification to signatory approval.
        </p>
    </div>

    <div class="feature-card">
        <div class="feature-icon-wrapper">
            <i class="fa-solid fa-stamp"></i>
        </div>
        <h3 class="feature-title">3. Scheduled Claiming</h3>
        <p style="color: var(--muted); font-size: 0.925rem;">
            Receive an accurate target release schedule and official confirmation before visiting the Registrar counter.
        </p>
    </div>
</div>

<!-- Quick CTA Banner -->
<div class="card" style="background: linear-gradient(135deg, #fffcf7 0%, #fdf8ee 100%); border: 1px solid var(--border-gold); text-align: center; padding: 2.5rem 1.5rem;">
    <h3 style="font-family: var(--font-serif); font-size: 1.6rem; color: var(--burgundy-dark); margin-bottom: 0.5rem;">
        Are you a current student or alumnus?
    </h3>
    <p style="color: var(--muted); max-width: 600px; margin: 0 auto 1.5rem auto; font-size: 0.95rem;">
        Create your personalized account to submit requests, attach clearance receipts, and manage all past and active document transactions.
    </p>
    <div style="display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap;">
        <a href="/DocuTrack/register.php" class="btn btn-gold" style="padding: 0.75rem 1.75rem;">
            <i class="fa-solid fa-user-plus"></i> Open Student Account
        </a>
        <a href="/DocuTrack/login.php" class="btn btn-primary" style="padding: 0.75rem 1.75rem;">
            <i class="fa-solid fa-key"></i> Sign In to Portal
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

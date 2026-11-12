<?php
// index.php - DocuTrack Landing Page
$pageTitle = "Home";
require_once __DIR__ . '/includes/header.php';
?>

<div style="text-align: center; padding: 3rem 1rem 2rem 1rem;">
    <h1 style="font-size: 2.5rem; color: var(--primary); margin-bottom: 0.5rem; font-weight: 800;">
        Welcome to DocuTrack
    </h1>
    <p style="font-size: 1.15rem; color: var(--muted); max-width: 650px; margin: 0 auto 2rem auto;">
        A Web-Based Document Request & Monitoring System designed to streamline Registrar services, eliminate long queues, and provide real-time tracking for students.
    </p>

    <!-- Quick Tracking Lookup Form -->
    <div class="card" style="max-width: 580px; margin: 0 auto; box-shadow: var(--hover-shadow); text-align: left;">
        <h3 style="font-size: 1.1rem; margin-bottom: 0.75rem; color: var(--dark); display: flex; align-items: center; gap: 0.5rem;">
            <i class="fa-solid fa-magnifying-glass" style="color: var(--primary);"></i> Track Your Document Request
        </h3>
        <p style="font-size: 0.85rem; color: var(--muted); margin-bottom: 1rem;">
            Have a tracking reference code? Enter it below to check the real-time status of your credentials without logging in.
        </p>
        <form action="/DocuTrack/track.php" method="GET" style="display: flex; gap: 0.5rem;">
            <input type="text" name="code" class="form-control" placeholder="e.g. DOC-2026-1049" required style="font-size: 1rem; text-transform: uppercase;">
            <button type="submit" class="btn btn-primary" style="white-space: nowrap;">
                <i class="fa-solid fa-arrow-right"></i> Track
            </button>
        </form>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; margin-top: 2rem;">
    <div class="card" style="text-align: center; padding: 2rem;">
        <div style="width: 54px; height: 54px; border-radius: 50%; background: #eff6ff; color: var(--primary); display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem auto; font-size: 1.5rem;">
            <i class="fa-solid fa-paper-plane"></i>
        </div>
        <h3 class="card-title" style="margin-bottom: 0.5rem;">1. Submit Request</h3>
        <p style="color: var(--muted); font-size: 0.9rem;">
            Register or log in to choose from official documents: Transcript of Records (TOR), Good Moral, Certifications, and more.
        </p>
    </div>

    <div class="card" style="text-align: center; padding: 2rem;">
        <div style="width: 54px; height: 54px; border-radius: 50%; background: #ecfdf5; color: var(--secondary); display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem auto; font-size: 1.5rem;">
            <i class="fa-solid fa-clock-rotate-left"></i>
        </div>
        <h3 class="card-title" style="margin-bottom: 0.5rem;">2. Monitor Progress</h3>
        <p style="color: var(--muted); font-size: 0.9rem;">
            Track each phase from Payment Verification, Document Processing, to Release readiness with full transparency.
        </p>
    </div>

    <div class="card" style="text-align: center; padding: 2rem;">
        <div style="width: 54px; height: 54px; border-radius: 50%; background: #fef3c7; color: var(--accent); display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem auto; font-size: 1.5rem;">
            <i class="fa-solid fa-building-circle-check"></i>
        </div>
        <h3 class="card-title" style="margin-bottom: 0.5rem;">3. Claim or Download</h3>
        <p style="color: var(--muted); font-size: 0.9rem;">
            Get notified when your records are ready for claiming at the Registrar's Office without waiting in long lines.
        </p>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

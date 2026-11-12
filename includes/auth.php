<?php
// includes/auth.php - Session Management & Access Control

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if a user is logged in
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

/**
 * Check if the logged-in user is an admin
 */
function isAdmin(): bool {
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Check if the logged-in user is a student
 */
function isStudent(): bool {
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'student';
}

/**
 * Require student or admin authentication; redirect to login if not authenticated
 */
function requireLogin(): void {
    if (!isLoggedIn()) {
        header("Location: /DocuTrack/login.php?error=unauthorized");
        exit();
    }
}

/**
 * Require admin authentication
 */
function requireAdmin(): void {
    requireLogin();
    if (!isAdmin()) {
        header("Location: /DocuTrack/index.php?error=forbidden");
        exit();
    }
}

/**
 * Require student authentication
 */
function requireStudent(): void {
    requireLogin();
    if (!isStudent()) {
        header("Location: /DocuTrack/admin/dashboard.php");
        exit();
    }
}

/**
 * Return current logged-in user info
 */
function currentUser(): ?array {
    if (!isLoggedIn()) {
        return null;
    }
    return [
        'id'        => $_SESSION['user_id'],
        'name'      => $_SESSION['user_name'] ?? 'User',
        'email'     => $_SESSION['user_email'] ?? '',
        'role'      => $_SESSION['user_role'] ?? 'student',
        'student_id'=> $_SESSION['student_id'] ?? ''
    ];
}

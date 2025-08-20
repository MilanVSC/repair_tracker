<?php
// Preveri če config.php še ni vključen
if (!isset($conn)) {
    include_once 'config.php';
}

// Preveri ali je uporabnik prijavljen
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Preveri ali je uporabnik admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Preveri ali je uporabnik tehnik
function isTechnician() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'technician';
}

// Preusmeri če ni prijavljen
function requireLogin() {
    if (!isLoggedIn()) {
        header("location: login.php");
        exit;
    }
}

// Preusmeri če ni admin
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header("location: dashboard.php");
        exit;
    }
}
?>
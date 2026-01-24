<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Sanitize input data

function sanitize($data)
{
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

//  Check if user is admin
function isAdmin()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function redirect($url)
{
    header("Location: $url");
    exit;
}
function requireLogin()
{
    if (!isLoggedIn()) {
        redirect('login.php');
    }
}
function requireAdmin()
{
    requireLogin();
    if (!isAdmin()) {
        die("Access Denied: You do not have permission to view this page.");
    }
}
function getCurrentUserId()
{
    return $_SESSION['user_id'] ?? null;
}
?>
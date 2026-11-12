<?php

// Start the session if one has not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load the database connection
require_once __DIR__ . '/../config.php';

// Generate a CSRF token for the session
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Check whether a user is logged in
function is_logged_in(): bool
{
    return isset($_SESSION['user']);
}

// Check whether the logged-in user is an administrator
function is_admin(): bool
{
    return is_logged_in() && $_SESSION['user']['role'] === 'admin';
}

// Redirect users to the login page if they are not logged in
function require_login(): void
{
    if (!is_logged_in()) {
        header('Location: /login.php');
        exit;
    }
}

// Check the submitted CSRF token
function verify_csrf_token(): bool
{
    return isset($_POST['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

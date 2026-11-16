<?php

require_once __DIR__ . '/includes/bootstrap.php';

// Only allow POST requests for logout
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /login.php');
    exit;
}

// Check the CSRF token
if (!verify_csrf_token()) {
    http_response_code(403);
    exit('Invalid request.');
}

// Clear all session data
$_SESSION = [];

// Delete the session cookie
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        '',S
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// Destroy the session
session_destroy();

// Redirect to login
header('Location: /login.php');
exit;
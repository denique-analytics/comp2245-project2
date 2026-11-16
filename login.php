<?php

require_once __DIR__ . '/includes/bootstrap.php';

// Redirect users who are already logged in
if (is_logged_in()) {
    header('Location: /');
    exit;
}

$error = '';
$email = '';


// Check whether the login form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Check the CSRF token
    if (!verify_csrf_token()) {
        $error = 'Invalid request. Please try again.';
    } else {

        // Get the submitted form values
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        // Make sure the values are strings
        if (!is_string($email) || !is_string($password)) {
            $error = 'Invalid email or password.';
        } else {

            $email = trim($email);

            // Check that both fields were entered
            if ($email === '' || $password === '') {
                $error = 'Invalid email or password.';
            } else {

                // Find the user by email
                $stmt = $pdo->prepare(
                    'SELECT id, firstname, lastname, email, password, role
                     FROM users
                     WHERE email = ?'
                );

                $stmt->execute([$email]);
                $user = $stmt->fetch();

                // Check the submitted password
                if ($user && password_verify($password, $user['password'])) {

                    // Regenerate the session ID after login
                    session_regenerate_id(true);

                    // Store the logged-in user's details
                    $_SESSION['user'] = [
                        'id' => (int) $user['id'],
                        'firstname' => $user['firstname'],
                        'lastname' => $user['lastname'],
                        'email' => $user['email'],
                        'role' => $user['role']
                    ];

                    header('Location: /');
                    exit;

                } else {
                    $error = 'Invalid email or password.';
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Fix-It Desk</title>
</head>
<body>

    <h1>Fix-It Desk</h1>
    <h2>Login</h2>

    <?php if ($error !== ''): ?>
        <p><?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
    <?php endif; ?>

    <form action="login.php" method="POST">

        <input
            type="hidden"
            name="csrf_token"
            value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
        >

        <label for="email">Email:</label>
        <input
            type="email"
            id="email"
            name="email"
            value="<?= htmlspecialchars($email, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
            required
        >

        <br><br>

        <label for="password">Password:</label>
        <input
            type="password"
            id="password"
            name="password"
            required
        >

        <br><br>

        <button type="submit">Login</button>

    </form>

</body>
</html>
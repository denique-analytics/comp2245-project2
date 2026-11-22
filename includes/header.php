<?php
// Shared top of every page (except login). Set $pageTitle before including.
// Expects bootstrap.php to be loaded already (session + csrf_token).
$h = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
$navUser = $_SESSION['user'] ?? null;
$pageTitle = $pageTitle ?? 'Fix-It Desk';

// One-time message, e.g. "Ticket #3 was deleted." (set in ticket-delete.php)
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="<?= $h($_SESSION['csrf_token'] ?? '') ?>">
  <title><?= $h($pageTitle) ?> &ndash; Fix-It Desk</title>
  <link rel="stylesheet" href="/styles.css">
</head>
<body>
<header class="site-header">
  <a class="brand" href="/index.php">Fix-It Desk</a>
  <?php if ($navUser): ?>
    <nav>
      <a href="/index.php">Tickets</a>
      <a href="/ticket-new.php">New ticket</a>
      <?php if (($navUser['role'] ?? '') === 'admin'): ?>
        <a href="/users.php">Users</a>
      <?php endif; ?>
    </nav>
    <div class="user-box">
      <span><?= $h(($navUser['firstname'] ?? '') . ' ' . ($navUser['lastname'] ?? '')) ?></span>
      <form method="post" action="/logout.php">
        <input type="hidden" name="csrf_token" value="<?= $h($_SESSION['csrf_token'] ?? '') ?>">
        <button type="submit" class="btn">Log out</button>
      </form>
    </div>
  <?php endif; ?>
</header>
<?php if ($flash): ?>
  <p class="flash success" role="status"><?= $h($flash) ?></p>
<?php endif; ?>

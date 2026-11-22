<?php
require __DIR__ . '/includes/bootstrap.php';

$currentUser = $currentUser ?? ($_SESSION['user'] ?? null);
if (!$currentUser) { header('Location: login.php'); exit; }
if (empty($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }
$h = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
$len = fn($s) => function_exists('mb_strlen') ? mb_strlen($s, 'UTF-8') : strlen($s);

if ($currentUser['role'] !== 'admin') {
    http_response_code(403);
    $pageTitle = 'Forbidden';
    require __DIR__ . '/includes/header.php';
    echo '<main class="container"><h1>403 &ndash; Forbidden</h1><p>Only the admin can open this page.</p><p><a href="index.php">&larr; All tickets</a></p></main>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$old    = ['firstname' => '', 'lastname' => '', 'email' => '', 'role' => 'member'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = (string)($_POST['csrf_token'] ?? '');
    $expected = (string)($_SESSION['csrf_token'] ?? '');
    if ($expected === '' || !hash_equals($expected, $token)) { http_response_code(403); exit('Invalid security token.'); }

    foreach (['firstname', 'lastname', 'email'] as $f) $old[$f] = trim((string)($_POST[$f] ?? ''));
    $old['role'] = (string)($_POST['role'] ?? 'member');
    $password    = (string)($_POST['password'] ?? '');

    if ($old['firstname'] === '' || $len($old['firstname']) > 50) $errors['firstname'] = 'Enter a first name (50 characters at most).';
    if ($old['lastname'] === ''  || $len($old['lastname'])  > 50) $errors['lastname']  = 'Enter a last name (50 characters at most).';

    if ($old['email'] === '' || $len($old['email']) > 255 || !filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Enter a valid email address.';
    } else {
        $dup = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $dup->execute([$old['email']]);
        if ($dup->fetch()) $errors['email'] = 'That email already belongs to a user.';
    }

    if (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
        $errors['password'] = 'Use at least 8 characters, with a letter and a number.';
    }
    if (!in_array($old['role'], ['admin', 'member'], true)) $errors['role'] = 'Choose a role.';

    if (!$errors) {
        try {
            $pdo->prepare('INSERT INTO users (firstname, lastname, email, password, role) VALUES (?, ?, ?, ?, ?)')
                ->execute([$old['firstname'], $old['lastname'], $old['email'],
                           password_hash($password, PASSWORD_DEFAULT), $old['role']]);
            $_SESSION['users_flash'] = 'User added.';
            header('Location: users.php');
            exit;
        } catch (PDOException $ex) {
            if ($ex->getCode() === '23000') $errors['email'] = 'That email already belongs to a user.'; // UNIQUE race
            else throw $ex;
        }
    }
}

$flash = $_SESSION['users_flash'] ?? null;
unset($_SESSION['users_flash']);

// No password column selected, so a hash can never reach the page.
$users = $pdo->query('SELECT firstname, lastname, email, role, created_at FROM users ORDER BY lastname, firstname')->fetchAll();

$pageTitle = 'Users';
require __DIR__ . '/includes/header.php';
?>
<main class="container">
  <h1>Users</h1>
  <?php if ($flash): ?><p class="success" role="status"><?= $h($flash) ?></p><?php endif; ?>

  <div class="users-layout">
    <div class="card table-wrap">
      <table>
        <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Added</th></tr></thead>
        <tbody>
        <?php foreach ($users as $u): ?>
          <tr>
            <td><?= $h($u['firstname'] . ' ' . $u['lastname']) ?></td>
            <td><?= $h($u['email']) ?></td>
            <td><?= $h(ucfirst($u['role'])) ?></td>
            <td><?= $h(date('j M Y', strtotime($u['created_at']))) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <form method="post" action="users.php" class="card" novalidate>
      <h2>Add a user</h2>
      <input type="hidden" name="csrf_token" value="<?= $h($_SESSION['csrf_token']) ?>">

      <label for="firstname">First name</label>
      <input id="firstname" name="firstname" value="<?= $h($old['firstname']) ?>">
      <?php if (isset($errors['firstname'])): ?><p class="error"><?= $h($errors['firstname']) ?></p><?php endif; ?>

      <label for="lastname">Last name</label>
      <input id="lastname" name="lastname" value="<?= $h($old['lastname']) ?>">
      <?php if (isset($errors['lastname'])): ?><p class="error"><?= $h($errors['lastname']) ?></p><?php endif; ?>

      <label for="email">Email</label>
      <input id="email" name="email" type="email" value="<?= $h($old['email']) ?>">
      <?php if (isset($errors['email'])): ?><p class="error"><?= $h($errors['email']) ?></p><?php endif; ?>

      <label for="password">Password</label>
      <input id="password" name="password" type="password" autocomplete="new-password">
      <p class="hint">At least 8 characters, with a letter and a number.</p>
      <?php if (isset($errors['password'])): ?><p class="error"><?= $h($errors['password']) ?></p><?php endif; ?>

      <label for="role">Role</label>
      <select id="role" name="role">
        <option value="member" <?= $old['role'] === 'member' ? 'selected' : '' ?>>Member</option>
        <option value="admin"  <?= $old['role'] === 'admin'  ? 'selected' : '' ?>>Admin</option>
      </select>
      <?php if (isset($errors['role'])): ?><p class="error"><?= $h($errors['role']) ?></p><?php endif; ?>

      <button type="submit" class="btn btn-primary">Add user</button>
    </form>
  </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>

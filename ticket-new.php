<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

$users = $pdo->query("SELECT id, firstname, lastname FROM users ORDER BY firstname, lastname")->fetchAll();

$errors = [];
$title = '';
$description = '';
$priority = '';
$assigned_to = '';
$allowed_priorities = ['Low', 'Medium', 'High'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token()) {
        http_response_code(403);
        exit('Invalid request.');
    }

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $priority = $_POST['priority'] ?? '';
    $assigned_to = $_POST['assigned_to'] ?? '';

    if ($title === '') {
        $errors['title'] = 'Enter a title.';
    } elseif (strlen($title) > 150) {
        $errors['title'] = 'The title must be 150 characters or fewer.';
    }

    if ($description === '') {
        $errors['description'] = 'Describe the problem.';
    }

    if (!in_array($priority, $allowed_priorities, true)) {
        $errors['priority'] = 'Choose a priority.';
    }

    if ($assigned_to !== '') {
        $valid_ids = array_map('intval', array_column($users, 'id'));

        if (!is_string($assigned_to) || !ctype_digit($assigned_to) || !in_array((int) $assigned_to, $valid_ids, true)) {
            $errors['assigned_to'] = 'Choose a person from the list.';
        }
    }

      if (empty($errors)) {
        $stmt = $pdo->prepare(
            "INSERT INTO tickets (title, description, priority, created_by, assigned_to)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $title,
            $description,
            $priority,
            $_SESSION['user']['id'],
            $assigned_to === '' ? null : (int) $assigned_to
        ]);

        header('Location: /index.php');
        exit;

       }
}

$pageTitle = 'New ticket';
require __DIR__ . '/includes/header.php';

?>

<main class="container">

    <h1>New ticket</h1>

    <div class="card">

<form method="post" action="/ticket-new.php">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

    <div>
        <label for="title">Title</label><br>
        <input type="text" id="title" name="title" value="<?= htmlspecialchars($title) ?>">
        <?php if (isset($errors['title'])): ?>
            <p class="error"><?= htmlspecialchars($errors['title']) ?></p>
        <?php endif; ?>
    </div>

      <div>
        <label for="description">Description</label><br>
        <textarea id="description" name="description" rows="5"><?= htmlspecialchars($description) ?></textarea>
        <?php if (isset($errors['description'])): ?>
            <p class="error"><?= htmlspecialchars($errors['description']) ?></p>
        <?php endif; ?>
    </div>

    <div>
        <label for="priority">Priority</label><br>
        <select id="priority" name="priority">
            <option value="">Choose...</option>
            <?php foreach ($allowed_priorities as $option): ?>
                <option value="<?= htmlspecialchars($option) ?>" <?= $priority === $option ? 'selected' : '' ?>>
                    <?= htmlspecialchars($option) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (isset($errors['priority'])): ?>
            <p class="error"><?= htmlspecialchars($errors['priority']) ?></p>
        <?php endif; ?>
    </div>

    <div>
        <label for="assigned_to">Assign to</label><br>
        <select id="assigned_to" name="assigned_to">
            <option value="">Nobody yet</option>
            <?php foreach ($users as $person): ?>
                <option value="<?= (int) $person['id'] ?>" <?= (string) $assigned_to === (string) $person['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($person['firstname'] . ' ' . $person['lastname']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (isset($errors['assigned_to'])): ?>
            <p class="error"><?= htmlspecialchars($errors['assigned_to']) ?></p>
        <?php endif; ?>
    </div>

    <button type="submit">Create ticket</button>
        <a href="/index.php">Cancel</a>
</form>

    </div>

</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
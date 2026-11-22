<?php
require __DIR__ . '/includes/bootstrap.php';

$currentUser = $currentUser ?? ($_SESSION['user'] ?? null);
if (!$currentUser) { header('Location: login.php'); exit; }
if (empty($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }
$h = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$ticket = null;
if ($id) {
    $stmt = $pdo->prepare(
        'SELECT t.*,
                CONCAT(c.firstname, " ", c.lastname) AS creator_name,
                CONCAT(a.firstname, " ", a.lastname) AS assignee_name
         FROM tickets t
         JOIN users c      ON c.id = t.created_by
         LEFT JOIN users a ON a.id = t.assigned_to
         WHERE t.id = ?'
    );
    $stmt->execute([$id]);
    $ticket = $stmt->fetch();
}

if (!$ticket) {
    http_response_code(404);
    $pageTitle = 'Ticket not found';
    require __DIR__ . '/includes/header.php';
    echo '<main class="container"><h1>Ticket not found</h1><p><a href="index.php">&larr; All tickets</a></p></main>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$noteStmt = $pdo->prepare(
    'SELECT n.body, n.created_at, CONCAT(u.firstname, " ", u.lastname) AS author
     FROM notes n
     JOIN users u ON u.id = n.created_by
     WHERE n.ticket_id = ?
     ORDER BY n.created_at ASC, n.id ASC'
);
$noteStmt->execute([$ticket['id']]);
$notes = $noteStmt->fetchAll();

$canDelete  = $currentUser['role'] === 'admin' || (int)$ticket['created_by'] === (int)$currentUser['id'];
$assignedToMe = (int)$ticket['assigned_to'] === (int)$currentUser['id'];
$fmt = fn($s) => date('j M Y, g:i a', strtotime($s));

$pageTitle = 'Ticket #' . $ticket['id'];
require __DIR__ . '/includes/header.php';
?>
<main class="container" id="ticket-page" data-ticket-id="<?= (int)$ticket['id'] ?>" data-csrf="<?= $h($_SESSION['csrf_token']) ?>">
  <p><a href="index.php">&larr; All tickets</a></p>

  <div class="ticket-head">
    <h1><span class="ticket-num">#<?= (int)$ticket['id'] ?></span> <?= $h($ticket['title']) ?></h1>
    <div class="ticket-actions">
      <a class="btn" href="ticket-edit.php?id=<?= (int)$ticket['id'] ?>">Edit</a>
      <?php if ($canDelete): ?>
        <button type="button" class="btn btn-danger" id="delete-btn">Delete</button>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($canDelete): ?>
  <div class="confirm-box" id="delete-confirm" hidden>
    <p>Delete this ticket and all of its notes? This can't be undone.</p>
    <form method="post" action="ticket-delete.php">
      <input type="hidden" name="csrf_token" value="<?= $h($_SESSION['csrf_token']) ?>">
      <input type="hidden" name="ticket_id" value="<?= (int)$ticket['id'] ?>">
      <button type="submit" class="btn btn-danger">Yes, delete it</button>
      <button type="button" class="btn" id="cancel-delete">Cancel</button>
    </form>
  </div>
  <?php endif; ?>

  <div class="ticket-layout">
    <section class="ticket-main">
      <div class="card description"><?= $h($ticket['description']) ?></div>

      <h2>Notes</h2>
      <div id="notes-list">
        <?php foreach ($notes as $n): ?>
          <article class="note">
            <p class="note-meta"><strong><?= $h($n['author']) ?></strong> &middot; <?= $h($fmt($n['created_at'])) ?></p>
            <p class="note-body"><?= $h($n['body']) ?></p>
          </article>
        <?php endforeach; ?>
      </div>
      <p id="no-notes" <?= $notes ? 'hidden' : '' ?>>No notes yet.</p>

      <form id="note-form" class="card" novalidate>
        <label for="note-body">Add a note</label>
        <textarea id="note-body" name="body" rows="4"></textarea>
        <p class="error" id="note-error" role="alert" hidden></p>
        <button type="submit" class="btn btn-primary">Add note</button>
      </form>
    </section>

    <aside class="ticket-side card">
      <dl>
        <dt>Status</dt>      <dd><span id="status-pill" class="pill"><?= $h($ticket['status']) ?></span></dd>
        <dt>Priority</dt>    <dd><span class="pill priority-<?= $h(strtolower($ticket['priority'])) ?>"><?= $h($ticket['priority']) ?></span></dd>
        <dt>Assigned to</dt> <dd id="assignee"><?= $h($ticket['assignee_name'] ?? 'Nobody') ?></dd>
        <dt>Created by</dt>  <dd><?= $h($ticket['creator_name']) ?></dd>
        <dt>Created</dt>     <dd><?= $h($fmt($ticket['created_at'])) ?></dd>
        <dt>Last updated</dt><dd id="updated"><?= $h($fmt($ticket['updated_at'])) ?></dd>
      </dl>

      <label for="status-select">Change status</label>
      <select id="status-select">
        <?php foreach (['Open', 'In progress', 'Closed'] as $s): ?>
          <option value="<?= $h($s) ?>" <?= $s === $ticket['status'] ? 'selected' : '' ?>><?= $h($s) ?></option>
        <?php endforeach; ?>
      </select>

      <button type="button" class="btn" id="assign-btn" <?= $assignedToMe ? 'hidden' : '' ?>>Assign to me</button>
      <p id="action-msg" role="status"></p>
    </aside>
  </div>
</main>
<script src="ticket.js" defer></script>
<?php require __DIR__ . '/includes/footer.php'; ?>

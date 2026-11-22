<?php
require __DIR__ . '/../includes/bootstrap.php';
header('Content-Type: application/json');

function fail($code, $msg) {
    http_response_code($code);
    echo json_encode(['ok' => false, 'error' => $msg]);
    exit;
}

$currentUser = $currentUser ?? ($_SESSION['user'] ?? null);
if (!$currentUser)                                fail(401, 'Please log in.');
if ($_SERVER['REQUEST_METHOD'] !== 'POST')        fail(405, 'POST only.');
$expected = (string)($_SESSION['csrf_token'] ?? '');
$sent     = (string)($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
if ($expected === '' || !hash_equals($expected, $sent)) fail(403, 'Invalid security token.');

$in = json_decode(file_get_contents('php://input'), true);
if (!is_array($in)) fail(400, 'Bad request.');

$ticketId = filter_var($in['ticket_id'] ?? null, FILTER_VALIDATE_INT);
if (!$ticketId) fail(400, 'Bad ticket id.');

$check = $pdo->prepare('SELECT id FROM tickets WHERE id = ?');
$check->execute([$ticketId]);
if (!$check->fetch()) fail(404, 'Ticket not found.');

if (array_key_exists('status', $in)) {          // status change
    $status = $in['status'] ?? '';
    if (!in_array($status, ['Open', 'In progress', 'Closed'], true)) fail(422, 'Invalid status.');
    $pdo->prepare('UPDATE tickets SET status = ? WHERE id = ?')->execute([$status, $ticketId]);
} else {                                          // no status sent = Assign to me
    $pdo->prepare('UPDATE tickets SET assigned_to = ? WHERE id = ?')
        ->execute([$currentUser['id'], $ticketId]);
}

$sel = $pdo->prepare(
    'SELECT t.status, t.assigned_to, t.updated_at,
            CONCAT(a.firstname, " ", a.lastname) AS assignee_name
     FROM tickets t LEFT JOIN users a ON a.id = t.assigned_to
     WHERE t.id = ?'
);
$sel->execute([$ticketId]);
$t = $sel->fetch();

echo json_encode([
    'ok'            => true,
    'status'        => $t['status'],
    'assigned_to'   => $t['assigned_to'] === null ? null : (int)$t['assigned_to'],
    'assignee_name' => $t['assignee_name'] ?? 'Nobody',
    'updated_at'    => date('j M Y, g:i a', strtotime($t['updated_at'])),
]);

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

$len = fn($s) => function_exists('mb_strlen') ? mb_strlen($s, 'UTF-8') : strlen($s);

$in = json_decode(file_get_contents('php://input'), true);
if (!is_array($in)) fail(400, 'Bad request.');

$ticketId = filter_var($in['ticket_id'] ?? null, FILTER_VALIDATE_INT);
$body     = trim((string)($in['body'] ?? ''));

if (!$ticketId)                 fail(400, 'Bad ticket id.');
if ($body === '')               fail(422, 'A note can\'t be empty.');
if ($len($body) > 5000)    fail(422, 'A note can be at most 5000 characters.');

$check = $pdo->prepare('SELECT id FROM tickets WHERE id = ?');
$check->execute([$ticketId]);
if (!$check->fetch()) fail(404, 'Ticket not found.');

$ins = $pdo->prepare('INSERT INTO notes (ticket_id, body, created_by) VALUES (?, ?, ?)');
$ins->execute([$ticketId, $body, $currentUser['id']]);

$sel = $pdo->prepare(
    'SELECT n.body, n.created_at, CONCAT(u.firstname, " ", u.lastname) AS author
     FROM notes n JOIN users u ON u.id = n.created_by WHERE n.id = ?'
);
$sel->execute([$pdo->lastInsertId()]);
$n = $sel->fetch();

http_response_code(201);
echo json_encode([
    'ok'   => true,
    'note' => [
        'author'     => $n['author'],
        'body'       => $n['body'],
        'created_at' => date('j M Y, g:i a', strtotime($n['created_at'])),
    ],
]);

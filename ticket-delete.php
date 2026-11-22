<?php
require __DIR__ . '/includes/bootstrap.php';

$currentUser = $currentUser ?? ($_SESSION['user'] ?? null);
if (!$currentUser) { header('Location: login.php'); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Method not allowed.'); }
$token = (string)($_POST['csrf_token'] ?? '');
$expected = (string)($_SESSION['csrf_token'] ?? '');
if ($expected === '' || !hash_equals($expected, $token)) { http_response_code(403); exit('Invalid security token.'); }

$id = filter_input(INPUT_POST, 'ticket_id', FILTER_VALIDATE_INT);
if (!$id) { http_response_code(400); exit('Bad ticket id.'); }

$stmt = $pdo->prepare('SELECT created_by FROM tickets WHERE id = ?');
$stmt->execute([$id]);
$ticket = $stmt->fetch();
if (!$ticket) { http_response_code(404); exit('Ticket not found.'); }

// Server-side permission check: admin or the person who created it.
if ($currentUser['role'] !== 'admin' && (int)$ticket['created_by'] !== (int)$currentUser['id']) {
    http_response_code(403);
    exit('You are not allowed to delete this ticket.');
}

// Notes go too, via ON DELETE CASCADE on notes.ticket_id.
$pdo->prepare('DELETE FROM tickets WHERE id = ?')->execute([$id]);

$_SESSION['flash'] = 'Ticket #' . $id . ' was deleted.';
header('Location: index.php');
exit;

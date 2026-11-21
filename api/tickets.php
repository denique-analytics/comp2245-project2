<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_api_login();

header('Content-Type: application/json');

$filter = $_GET['filter'] ?? 'all';

if (!in_array($filter, ['all', 'open', 'mine'], true)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid filter.'
    ]);
    exit;
}

$sql = "SELECT t.id, t.title, t.priority, t.status, t.created_at,
               creator.firstname AS creator_first,
               creator.lastname AS creator_last,
               assignee.firstname AS assignee_first,
               assignee.lastname AS assignee_last
        FROM tickets t
        INNER JOIN users creator ON t.created_by = creator.id
        LEFT JOIN users assignee ON t.assigned_to = assignee.id";
$params = [];

if ($filter === 'open') {
    $sql .= " WHERE t.status != 'Closed'";
} elseif ($filter === 'mine') {
    $sql .= " WHERE t.assigned_to = ?";
    $params[] = $_SESSION['user']['id'];
}

$sql .= " ORDER BY t.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tickets = $stmt->fetchAll();

echo json_encode([
    'success' => true,
    'count' => count($tickets),
    'tickets' => $tickets
]);
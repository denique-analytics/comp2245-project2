<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login(); 

$sql = "SELECT t.id, t.title, t.priority, t.status, t.created_at,
               creator.firstname AS creator_first,
               creator.lastname AS creator_last,
               assignee.firstname AS assignee_first,
               assignee.lastname AS assignee_last
        FROM tickets t
        INNER JOIN users creator ON t.created_by = creator.id
        LEFT JOIN users assignee ON t.assigned_to = assignee.id
        ORDER BY t.created_at DESC";

$tickets = $pdo->query($sql)->fetchAll();

?>

<h1>Tickets</h1>
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Title</th>
            <th>Priority</th>
            <th>Status</th>
            <th>Created by</th>
            <th>Assigned to</th>
            <th>Created</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($tickets as $ticket): ?>
        <tr>
            <td><?= (int) $ticket['id'] ?></td>
            <td><a href="/ticket.php?id=<?= (int) $ticket['id'] ?>"><?= htmlspecialchars($ticket['title']) ?></a></td>
            <td><?= htmlspecialchars($ticket['priority']) ?></td>
            <td><?= htmlspecialchars($ticket['status']) ?></td>
            <td><?= htmlspecialchars($ticket['creator_first'] . ' ' . $ticket['creator_last']) ?></td>
            <td>
                <?php if ($ticket['assignee_first'] === null): ?>
                    Nobody
                <?php else: ?>
                    <?= htmlspecialchars($ticket['assignee_first'] . ' ' . $ticket['assignee_last']) ?>
                <?php endif; ?>
            </td>
            <td><?= htmlspecialchars(date('j M Y, g:i a', strtotime($ticket['created_at']))) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
const filterButtons = document.querySelectorAll('#filters button');
const rows = document.getElementById('ticket-rows');
const countText = document.getElementById('ticket-count');

function makeCell(text) {
    const cell = document.createElement('td');
    cell.textContent = text;
    return cell;
}

function formatDate(dateString) {
    const date = new Date(dateString.replace(' ', 'T'));
    return date.toLocaleString('en-GB', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    });
}

function buildRow(ticket) {
    const row = document.createElement('tr');

    row.appendChild(makeCell(ticket.id));

    const titleCell = document.createElement('td');
    const link = document.createElement('a');
    link.href = '/ticket.php?id=' + encodeURIComponent(ticket.id);
    link.textContent = ticket.title;
    titleCell.appendChild(link);
    row.appendChild(titleCell);

    row.appendChild(makeCell(ticket.priority));
    row.appendChild(makeCell(ticket.status));
    row.appendChild(makeCell(ticket.creator_first + ' ' + ticket.creator_last));

    const assignedTo = ticket.assignee_first === null
        ? 'Nobody'
        : ticket.assignee_first + ' ' + ticket.assignee_last;
    row.appendChild(makeCell(assignedTo));

    row.appendChild(makeCell(formatDate(ticket.created_at)));

    return row;
}

async function loadTickets(filter) {
    try {
        const response = await fetch('/api/tickets.php?filter=' + encodeURIComponent(filter));
        const data = await response.json();

        if (!data.success) {
            countText.textContent = data.message || 'Could not load tickets.';
            return;
        }

        rows.replaceChildren();
        data.tickets.forEach(function (ticket) {
            rows.appendChild(buildRow(ticket));
        });

        countText.textContent = data.count + (data.count === 1 ? ' ticket' : ' tickets');
    } catch (error) {
        countText.textContent = 'Could not load tickets.';
    }
}

filterButtons.forEach(function (button) {
    button.addEventListener('click', function () {
        filterButtons.forEach(function (other) {
            other.classList.remove('active');
        });
        button.classList.add('active');
        loadTickets(button.dataset.filter);
    });
});
(() => {
  const page = document.getElementById('ticket-page');
  if (!page) return;

  const csrf = page.dataset.csrf || document.querySelector('meta[name="csrf-token"]')?.content || '';
  const ticketId = Number(page.dataset.ticketId);

  const $ = (id) => document.getElementById(id);
  const noteForm = $('note-form'), noteBody = $('note-body'), noteError = $('note-error');
  const notesList = $('notes-list'), noNotes = $('no-notes');
  const statusSelect = $('status-select'), statusPill = $('status-pill');
  const assignBtn = $('assign-btn'), assignee = $('assignee'), updated = $('updated');
  const actionMsg = $('action-msg');

  // POST JSON with the CSRF token in a header. Always resolves to {ok, status, data}.
  async function postJson(url, payload) {
    try {
      const res = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf },
        body: JSON.stringify(payload),
      });
      if (res.status === 401) { window.location.href = 'login.php'; return { ok: false, data: {} }; }
      const data = await res.json();
      return { ok: res.ok && data.ok === true, data };
    } catch (err) {
      return { ok: false, data: { error: 'Could not reach the server. Please try again.' } };
    }
  }

  function showMsg(text, isError = false) {
    actionMsg.textContent = text;
    actionMsg.classList.toggle('error', isError);
  }

  // ---- Add a note ----
  function addNoteToPage(note) {
    const article = document.createElement('article');
    article.className = 'note';

    const meta = document.createElement('p');
    meta.className = 'note-meta';
    const strong = document.createElement('strong');
    strong.textContent = note.author;
    meta.append(strong, ' \u00b7 ' + note.created_at);

    const body = document.createElement('p');
    body.className = 'note-body';
    body.textContent = note.body;            // textContent, never innerHTML

    article.append(meta, body);
    notesList.appendChild(article);
    noNotes.hidden = true;
  }

  noteForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const body = noteBody.value.trim();
    noteError.hidden = true;
    if (body === '') {
      noteError.textContent = 'Write a note before adding it.';
      noteError.hidden = false;
      return;
    }
    const btn = noteForm.querySelector('button[type="submit"]');
    btn.disabled = true;
    const { ok, data } = await postJson('api/notes.php', { ticket_id: ticketId, body });
    btn.disabled = false;
    if (ok) {
      addNoteToPage(data.note);
      noteBody.value = '';
    } else {
      noteError.textContent = data.error || 'Could not add the note.';
      noteError.hidden = false;
    }
  });

  // ---- Change status ----
  let lastStatus = statusSelect.value;
  statusSelect.addEventListener('change', async () => {
    showMsg('Saving\u2026');
    const { ok, data } = await postJson('api/ticket-update.php', {
      ticket_id: ticketId, status: statusSelect.value,
    });
    if (ok) {
      lastStatus = data.status;
      statusPill.textContent = data.status;
      updated.textContent = data.updated_at;
      showMsg('Saved.');
    } else {
      statusSelect.value = lastStatus;       // put it back
      showMsg(data.error || 'Could not save.', true);
    }
  });

  // ---- Assign to me ----
  assignBtn.addEventListener('click', async () => {
    showMsg('Saving\u2026');
    const { ok, data } = await postJson('api/ticket-update.php', { ticket_id: ticketId });
    if (ok) {
      assignee.textContent = data.assignee_name;
      updated.textContent = data.updated_at;
      assignBtn.hidden = true;
      showMsg('Saved.');
    } else {
      showMsg(data.error || 'Could not save.', true);
    }
  });

  // ---- Delete confirmation (on the page itself) ----
  const deleteBtn = $('delete-btn'), confirmBox = $('delete-confirm'), cancelDelete = $('cancel-delete');
  if (deleteBtn && confirmBox) {
    deleteBtn.addEventListener('click', () => { confirmBox.hidden = false; deleteBtn.hidden = true; });
    cancelDelete.addEventListener('click', () => { confirmBox.hidden = true; deleteBtn.hidden = false; });
  }
})();

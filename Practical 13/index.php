<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Manager</title>
  <style>
    :root {
      color-scheme: light;
      font-family: Arial, sans-serif;
      color: #1f2937;
      background: #f3f4f6;
    }

    body {
      max-width: 900px;
      margin: 40px auto;
      padding: 0 20px;
    }

    h1 { margin-bottom: 8px; }
    .subtitle { margin-top: 0; color: #6b7280; }

    .panel {
      margin: 24px 0;
      padding: 22px;
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 2px 12px rgb(0 0 0 / 7%);
    }

    form {
      display: grid;
      grid-template-columns: repeat(3, minmax(0, 1fr)) auto;
      gap: 12px;
      align-items: end;
    }

    label { display: grid; gap: 6px; font-size: 14px; }
    input {
      box-sizing: border-box;
      width: 100%;
      padding: 10px;
      border: 1px solid #d1d5db;
      border-radius: 6px;
      font: inherit;
    }

    button {
      padding: 10px 14px;
      border: 0;
      border-radius: 6px;
      background: #2563eb;
      color: #fff;
      font: inherit;
      cursor: pointer;
    }

    button:hover { background: #1d4ed8; }
    button.secondary { background: #6b7280; }
    button.danger { background: #dc2626; }
    button:disabled { opacity: .6; cursor: wait; }

    .actions { display: flex; gap: 8px; }
    .message { min-height: 20px; margin: 12px 0 0; }
    .message.error { color: #b91c1c; }
    .message.success { color: #15803d; }
    .table-wrap { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 12px 10px; border-bottom: 1px solid #e5e7eb; text-align: left; }
    th { color: #4b5563; font-size: 13px; }
    .empty { color: #6b7280; text-align: center; }

    @media (max-width: 700px) {
      form { grid-template-columns: 1fr; }
      .actions { flex-wrap: wrap; }
    }
  </style>
</head>
<body>
  <h1>Student Manager</h1>
  <p class="subtitle">Add, view, edit, and delete students without reloading the page.</p>

  <section class="panel" aria-labelledby="form-heading">
    <h2 id="form-heading">Add a student</h2>
    <form id="student-form">
      <label>
        Name
        <input name="name" maxlength="120" required>
      </label>
      <label>
        Email
        <input name="email" type="email" maxlength="190" required>
      </label>
      <label>
        Course
        <input name="course" maxlength="120" required>
      </label>
      <div class="actions">
        <button id="save-button" type="submit">Add student</button>
        <button id="cancel-button" class="secondary" type="button" hidden>Cancel</button>
      </div>
    </form>
    <p id="message" class="message" role="status" aria-live="polite"></p>
  </section>

  <section class="panel" aria-labelledby="list-heading">
    <h2 id="list-heading">Students</h2>
    <div class="table-wrap">
      <table>
        <thead>
          <tr><th>Name</th><th>Email</th><th>Course</th><th>Actions</th></tr>
        </thead>
        <tbody id="student-list">
          <tr><td class="empty" colspan="4">Loading students…</td></tr>
        </tbody>
      </table>
    </div>
  </section>

  <script>
    const apiUrl = 'api/students.php';
    const form = document.querySelector('#student-form');
    const list = document.querySelector('#student-list');
    const message = document.querySelector('#message');
    const saveButton = document.querySelector('#save-button');
    const cancelButton = document.querySelector('#cancel-button');
    let editingId = null;

    function showMessage(text, type = '') {
      message.textContent = text;
      message.className = `message ${type}`;
    }

    async function request(url, options = {}) {
      const response = await fetch(url, {
        ...options,
        headers: {
          Accept: 'application/json',
          ...(options.body ? { 'Content-Type': 'application/json' } : {}),
          ...options.headers
        }
      });
      const data = await response.json();
      if (!response.ok) {
        throw new Error(data.error || 'The request could not be completed.');
      }
      return data;
    }

    function renderStudents(students) {
      list.replaceChildren();
      if (students.length === 0) {
        const row = document.createElement('tr');
        row.innerHTML = '<td class="empty" colspan="4">No students yet.</td>';
        list.append(row);
        return;
      }

      students.forEach((student) => {
        const row = document.createElement('tr');
        for (const value of [student.name, student.email, student.course]) {
          const cell = document.createElement('td');
          cell.textContent = value;
          row.append(cell);
        }

        const actions = document.createElement('td');
        const actionGroup = document.createElement('div');
        actionGroup.className = 'actions';

        const editButton = document.createElement('button');
        editButton.type = 'button';
        editButton.className = 'secondary';
        editButton.textContent = 'Edit';
        editButton.addEventListener('click', () => beginEdit(student));

        const deleteButton = document.createElement('button');
        deleteButton.type = 'button';
        deleteButton.className = 'danger';
        deleteButton.textContent = 'Delete';
        deleteButton.addEventListener('click', () => deleteStudent(student.id));

        actionGroup.append(editButton, deleteButton);
        actions.append(actionGroup);
        row.append(actions);
        list.append(row);
      });
    }

    async function loadStudents() {
      try {
        const data = await request(apiUrl);
        renderStudents(data.students);
      } catch (error) {
        list.innerHTML = '<tr><td class="empty" colspan="4">Students could not be loaded.</td></tr>';
        showMessage(error.message, 'error');
      }
    }

    function beginEdit(student) {
      editingId = student.id;
      form.elements.name.value = student.name;
      form.elements.email.value = student.email;
      form.elements.course.value = student.course;
      document.querySelector('#form-heading').textContent = 'Edit student';
      saveButton.textContent = 'Save changes';
      cancelButton.hidden = false;
      form.elements.name.focus();
      showMessage('');
    }

    function resetForm() {
      editingId = null;
      form.reset();
      document.querySelector('#form-heading').textContent = 'Add a student';
      saveButton.textContent = 'Add student';
      cancelButton.hidden = true;
    }

    form.addEventListener('submit', async (event) => {
      event.preventDefault();
      const student = Object.fromEntries(new FormData(form));
      const isEditing = editingId !== null;
      saveButton.disabled = true;
      showMessage('');

      try {
        await request(isEditing ? `${apiUrl}?id=${encodeURIComponent(editingId)}` : apiUrl, {
          method: isEditing ? 'PUT' : 'POST',
          body: JSON.stringify(student)
        });
        resetForm();
        await loadStudents();
        showMessage(isEditing ? 'Student updated.' : 'Student added.', 'success');
      } catch (error) {
        showMessage(error.message, 'error');
      } finally {
        saveButton.disabled = false;
      }
    });

    cancelButton.addEventListener('click', () => {
      resetForm();
      showMessage('');
    });

    async function deleteStudent(id) {
      if (!window.confirm('Delete this student?')) return;
      showMessage('');
      try {
        await request(`${apiUrl}?id=${encodeURIComponent(id)}`, { method: 'DELETE' });
        if (editingId === id) resetForm();
        await loadStudents();
        showMessage('Student deleted.', 'success');
      } catch (error) {
        showMessage(error.message, 'error');
      }
    }

    loadStudents();
  </script>
</body>
</html>

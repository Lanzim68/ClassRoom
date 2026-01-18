// Обработка входа
document.getElementById('loginForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    
    const response = await fetch('php/login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ username, password })
    });
    
    const result = await response.json();
    
    if (result.success) {
        localStorage.setItem('user', JSON.stringify(result.user));
        if (result.user.role === 'teacher') {
            window.location.href = 'teacher.html';
        } else {
            window.location.href = 'dashboard.html';
        }
    } else {
        document.getElementById('errorMsg').textContent = result.message;
    }
});

// Загрузка данных класса (для ученика)
async function loadClassData() {
    const user = JSON.parse(localStorage.getItem('user'));
    if (!user) return;
    
    const response = await fetch('php/get-data.php?classId=' + user.classId);
    const data = await response.json();
    
    const tasksContainer = document.getElementById('tasks');
    tasksContainer.innerHTML = '';
    
    data.tasks.forEach(task => {
        const taskEl = document.createElement('div');
        taskEl.className = 'task';
        taskEl.innerHTML = `
            <h3>${task.title}</h3>
            <p>${task.description}</p>
            <button class="gc-button" onclick="submitTask(${task.id})">Сдать задание</button>
        `;
        tasksContainer.appendChild(taskEl);
    });
}

// Отправка задания
async function submitTask(taskId) {
    const user = JSON.parse(localStorage.getItem('user'));
    const response = await fetch('php/submit-task.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ userId: user.id, taskId })
    });
    
    const result = await response.json();
    alert(result.message);
}

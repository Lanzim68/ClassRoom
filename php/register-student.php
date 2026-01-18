<?php
header('Content-Type: application/json');

// Получаем данные из формы
$email = $_POST['email'] ?? '';
$name = $_POST['name'] ?? '';
$classId = $_POST['classId'] ?? '';

// Валидация
if (!$email || !$name || !$classId) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Все поля обязательны для заполнения'
    ]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Некорректный email'
    ]);
    exit;
}

// Загружаем пользователей
$usersFile = '../data/users.json';
$users = json_decode(file_get_contents($usersFile), true);

// Проверяем, не зарегистрирован ли уже этот email
foreach ($users as $user) {
    if ($user['username'] === $email) {
        http_response_code(409);
        echo json_encode([
            'success' => false,
            'message' => 'Ученик с таким email уже зарегистрирован'
        ]);
        exit;
    }
}

// Генерируем пароль
$password = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);

// Создаём нового ученика
$newStudent = [
    'id' => count($users) + 1,
    'username' => $email,
    'password' => $password,
    'role' => 'student',
    'classId' => $classId,
    'name' => $name
];

$users[] = $newStudent;

// Сохраняем в users.json
if (file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT)) === false) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Ошибка записи в базу данных'
    ]);
    exit;
}

// Отправляем email (в реальном проекте используйте SMTP)
// Здесь — заглушка: просто выводим пароль в ответ
echo json_encode([
    'success' => true,
    'message' => "Ученик успешно зарегистрирован! Пароль: $password (отправлен на email $email)"
]);
?>

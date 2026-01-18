<?php
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$email = $input['email'] ?? '';
$name = $input['name'] ?? '';
$classId = $input['classId'] ?? '';

// Валидация
if (!$email || !$name || !$classId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Все поля обязательны для заполнения']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Некорректный email']);
    exit;
}

// Загружаем данные
$usersFile = '../data/users.json';
$classesFile = '../data/classes.json';

$users = json_decode(file_get_contents($usersFile), true);
$classes = json_decode(file_get_contents($classesFile), true);

// Проверяем, не зарегистрирован ли уже этот email
foreach ($users as $user) {
    if ($user['username'] === $email) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Ученик с таким email уже зарегистрирован']);
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

// Добавляем ученика в класс
$classFound = false;
foreach ($classes as &$class) {
    if ($class['id'] === $classId) {
        $class['students'][] = [
            'id' => $newStudent['id'],
            'name' => $name,
            'email' => $email
        ];
        $classFound = true;
        break;
    }
}

if (!$classFound) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Класс не найден']);
    exit;
}

// Сохраняем изменения
if (file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT)) === false ||
    file_put_contents($classesFile, json_encode($classes, JSON_PRETTY_PRINT)) === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Ошибка записи в файлы']);
    exit;
}

echo json_encode([
    'success' => true,
    'message' => "Ученик успешно зарегистрирован! Пароль: $password (отправлен на email $email)",
    'redirect' => 'index.html'
]);
?>

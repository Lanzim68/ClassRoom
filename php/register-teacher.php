<?php
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$email = $input['email'] ?? '';
$name = $input['name'] ?? '';
$password = $input['password'] ?? '';
$classId = $input['classId'] ?? '';
$isAdmin = isset($input['isAdmin']) ? true : false;

// Валидация
if (!$email || !$name || !$password || !$classId) {
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
        echo json_encode(['success' => false, 'message' => 'Преподаватель с таким email уже зарегистрирован']);
        exit;
    }
}

// Определяем роль
$role = $isAdmin ? 'admin' : 'teacher';

// Создаём нового преподавателя
$newTeacher = [
    'id' => count($users) + 1,
    'username' => $email,
    'password' => $password, // В реальном проекте используйте password_hash()
    'role' => $role,
    'classId' => $classId,
    'name' => $name
];

$users[] = $newTeacher;

// Обновляем класс: добавляем преподавателя
$classFound = false;
foreach ($classes as &$class) {
    if ($class['id'] === $classId) {
        $class['teacher'] = $name;
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
    'message' => "Преподаватель успешно зарегистрирован! Роль: " . ($isAdmin ? 'администратор' : 'учитель'),
    'redirect' => 'index.html'
]);
?>

<?php
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$username = $input['username'] ?? '';
$password = $input['password'] ?? '';

// Загружаем пользователей из JSON
$users = json_decode(file_get_contents('../data/users.json'), true);

foreach ($users as $user) {
    if ($user['username'] === $username && $user['password'] === $password) {
        echo json_encode([
            'success' => true,
            'user' => $user
        ]);
        exit;
    }
}

echo json_encode([
    'success' => false,
    'message' => 'Неверный логин или пароль'
]);
?>

<?php
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$username = $input['username'] ?? '';
$password = $input['password'] ?? '';

$users = json_decode(file_get_contents('../data/users.json'), true);
$classes = json_decode(file_get_contents('../data/classes.json'), true);

foreach ($users as $user) {
    if ($user['username'] === $username && $user['password'] === $password) {
        // Находим класс пользователя
        $userClass = null;
        foreach ($classes as $class) {
            if ($class['id'] === $user['classId']) {
                $userClass = $class;
                break;
            }
        }

        $response = [
            'success' => true,
            'user' => $user,
            'class' => $userClass
        ];

        // Если администратор — добавляем флаг
        if ($user['role'] === 'admin') {
            $response['user']['isAdmin'] = true;
        }

        echo json_encode($response);
        exit;
    }
}

echo json_encode([
    'success' => false,
    'message' => 'Неверный логин или пароль'
]);
?>

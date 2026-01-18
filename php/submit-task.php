<?php
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$userId = $input['userId'] ?? null;
$taskId = $input['taskId'] ?? null;

if (!$userId || !$taskId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Не переданы userId или taskId']);
    exit;
}

$classesFile = '../data/classes.json';
$classes = json_decode(file_get_contents($classesFile), true);

$found = false;
foreach ($classes as &$class) {
    foreach ($class['tasks'] as &$task) {
        if ($task['id'] == $taskId) {
            // Проверяем, есть ли уже запись для этого ученика
            $submissionIndex = -1;
            foreach ($task['submissions'] as $idx => $sub) {
                if ($sub['studentId'] == $userId) {
                    $submissionIndex = $idx;
                    break;
                }
            }

            $now = date('Y-m-d');
            $dueDate = $task['dueDate'];
            $status = ($now <= $dueDate) ? 'submitted' : 'late';

            if ($submissionIndex >= 0) {
                // Обновляем существующую запись
                $task['submissions'][$submissionIndex]['status'] = $status;
                $task['submissions'][$submissionIndex]['submittedAt'] = $now;
            } else {
                // Добавляем новую запись
                $task['submissions'][] = [
                    'studentId' => $userId,
                    'status' => $status,
                    'submittedAt' => $now
                ];
            }

            $found = true;
            break 2;
        }
    }
}

if (!$found) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Задание не найдено']);
    exit;
}

// Сохраняем обновлённые данные
if (file_put_contents($classesFile, json_encode($classes, JSON_PRETTY_PRINT)) === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Ошибка записи в файл']);
    exit;
}

echo json_encode([
    'success' => true,
    'message' => 'Задание сдано! Статус: ' . ($status == 'submitted' ? 'в срок' : 'просрочено')
]);
?>

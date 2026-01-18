<?php
header('Content-Type: application/json');

$classId = $_GET['classId'] ?? '';

$classes = json_decode(file_get_contents('../data/classes.json'), true);

foreach ($classes as $class) {
    if ($class['id'] === $classId) {
        echo json_encode($class);
        exit;
    }
}

echo json_encode(['tasks' => []]);
?>

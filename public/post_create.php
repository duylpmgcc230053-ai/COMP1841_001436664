<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// only admins can access this page
// if (!isAdmin()) {
//     header('Location: /');
//     exit;
// }

// load users and modules
$users = $pdo->query('SELECT user_id, username FROM users')->fetchAll();
$modules = $pdo->query('SELECT module_id, module_name FROM modules')->fetchAll();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body = trim($_POST['body'] ?? '');
    $user_id = $_SESSION['user_id'] ?? 0; // Use the logged-in user's ID
    $module_id = (int)($_POST['module_id'] ?? 0);

    if ($body === '') $errors[] = 'Body required';

    $image_path = null;
    if (!empty($_FILES['image']['name'])) {
        $upload = uploadImage($_FILES['image']);
        if ($upload['success']) $image_path = $upload['path'];
        else $errors[] = $upload['error'];
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('INSERT INTO posts (body, user_id, module_id, image_path) VALUES (?,?,?,?)');
        $stmt->execute([$body, $user_id, $module_id ?: null, $image_path]);
        header('Location: questions.php');
        exit;
    }
}
?>



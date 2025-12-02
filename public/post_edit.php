<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
include __DIR__ . '/../includes/header.php';

$post_id = isset($_GET['post_id']) ? (int)$_GET['post_id'] : 0;
$user_id = $_SESSION['user_id'] ?? 0;

$stmt = $pdo->prepare('SELECT * FROM posts WHERE post_id = ?');
$stmt->execute([$post_id]);
$post = $stmt->fetch();
if (!$post || $post['user_id'] !== $user_id) {
    echo '<p>You are not authorized to edit this post.</p>';
    include __DIR__ . '/../includes/footer.php';
    exit;
}

$modules = $pdo->query('SELECT module_id, module_name FROM modules')->fetchAll();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body = trim($_POST['body'] ?? '');
    $module_id = (int)($_POST['module_id'] ?? 0);

    if ($body === '') $errors[] = 'Body required';

    $image_path = $post['image_path'];
    if (!empty($_FILES['image']['name'])) {
        $upload = uploadImage($_FILES['image']);
        if ($upload['success']) $image_path = $upload['path'];
        else $errors[] = $upload['error'];
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('UPDATE posts SET body=?, module_id=?, image_path=?, updated_at=NOW() WHERE post_id=?');
        $stmt->execute([$body, $module_id ?: null, $image_path, $post_id]);
        header('Location: questions.php');
        exit;
    }
}
?>

<div style="padding: 20px;">
    <form method="POST" enctype="multipart/form-data">
        <div style="margin-bottom: 10px;">
            <label for="body">Body:</label>
            <textarea id="body" name="body" required><?= htmlspecialchars($post['body']) ?></textarea>
        </div>
        <div style="margin-bottom: 10px;">
            <label for="image">Image:</label>
            <input type="file" id="image" name="image">
            <?php if ($post['image_path']): ?>
                <img src="<?= htmlspecialchars($post['image_path']) ?>" alt="Post Image" style="max-width: 100px; display: block; margin-top: 10px;">
            <?php endif; ?>
        </div>
        <div style="margin-bottom: 10px;">
            <label for="module_id">Module:</label>
            <select id="module_id" name="module_id">
                <?php foreach ($modules as $module): ?>
                    <option value="<?= $module['module_id'] ?>" <?= $module['module_id'] == $post['module_id'] ? 'selected' : '' ?>><?= htmlspecialchars($module['module_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit">Save Changes</button>
    </form>
    <?php if ($errors): ?>
        <ul style="color: red;">
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
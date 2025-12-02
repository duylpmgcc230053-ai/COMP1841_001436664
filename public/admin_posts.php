<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// role check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header('Location: questions.php');
  exit;
}

// initialize flash
$_SESSION['admin_errors'] = $_SESSION['admin_errors'] ?? [];
$_SESSION['admin_success'] = $_SESSION['admin_success'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'delete_post') {
        $post_id = (int)($_POST['post_id'] ?? 0);
        if ($post_id > 0) {
            $pdo->beginTransaction();
            try {
                // remove comments
                $stmt = $pdo->prepare('DELETE FROM comments WHERE post_id = ?');
                $stmt->execute([$post_id]);
                // fetch image
                $stmt = $pdo->prepare('SELECT image_path FROM posts WHERE post_id = ?');
                $stmt->execute([$post_id]);
                $row = $stmt->fetch();
                if ($row && $row['image_path']) @unlink(__DIR__ . '/' . $row['image_path']);
                // delete post
                $stmt = $pdo->prepare('DELETE FROM posts WHERE post_id = ?');
                $stmt->execute([$post_id]);
                $pdo->commit();
                $_SESSION['admin_success'] = 'Post deleted.';
                $_SESSION['admin_errors'] = [];
            } catch (Exception $e) {
                $pdo->rollBack();
                $_SESSION['admin_errors'] = ['Failed to delete post.'];
                $_SESSION['admin_success'] = '';
            }
        }
    }
}

header('Location: admin.html.php');
exit;

?>

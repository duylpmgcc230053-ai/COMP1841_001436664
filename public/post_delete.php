<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_id = (int)($_POST['post_id'] ?? 0);
    $user_id = $_SESSION['user_id'] ?? 0;
    if ($post_id > 0 && $user_id > 0) {
        // ensure only owner can delete
        $stmt = $pdo->prepare('SELECT image_path, user_id FROM posts WHERE post_id = ?');
        $stmt->execute([$post_id]);
        $row = $stmt->fetch();
        if ($row && $row['user_id'] == $user_id) {
            $pdo->beginTransaction();
            try {
                if ($row['image_path']) {
                    @unlink(__DIR__ . '/' . $row['image_path']);
                }
                // delete comments for the post (if any)
                $stmt = $pdo->prepare('DELETE FROM comments WHERE post_id = ?');
                $stmt->execute([$post_id]);
                $stmt = $pdo->prepare('DELETE FROM posts WHERE post_id = ?');
                $stmt->execute([$post_id]);
                $pdo->commit();
            } catch (Exception $e) {
                $pdo->rollBack();
            }
        }
    }
}
header('Location: questions.php');
exit;
?>